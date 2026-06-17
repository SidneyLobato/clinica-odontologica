<?php
class AtendimentoController extends BaseController {
    public function index(?string $p=null): void {
        requer_perfil(['proprietario','dentista','recepcionista']);
        $dentistas     = (new UsuarioModel($this->db))->listarDentistas();
        $procedimentos = (new ProcedimentoModel($this->db))->listarTodos();
        $fin           = new FinanceiroModel($this->db);
        $bandeiras     = $fin->listaBandeiras();
        $this->render('atendimentos/form', compact('dentistas','procedimentos','bandeiras'));
    }

    public function salvar(?string $p=null): void {
        requer_perfil(['proprietario','dentista','recepcionista']);
        if (!$this->isPost()) { $this->json(['sucesso'=>false,'erro'=>'Método inválido.'],400); return; }

        // Upload de arquivo
        $urlArquivo = null;
        if (!empty($_FILES['raio_x_file']['name']) && $_FILES['raio_x_file']['error'] === UPLOAD_ERR_OK) {
            $finfo   = new finfo(FILEINFO_MIME_TYPE);
            $mime    = $finfo->file($_FILES['raio_x_file']['tmp_name']);
            $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif','application/pdf'=>'pdf'];
            if (!array_key_exists($mime, $allowed)) {
                $this->json(['sucesso'=>false,'erro'=>'Tipo de arquivo não permitido.'],400); return;
            }
            $pacNome  = preg_replace('/[^a-zA-Z0-9_-]/','',str_replace(' ','_',$_POST['paciente_nome']??'pac'));
            $fileName = uniqid().'_'.$pacNome.'.'.$allowed[$mime];
            $dest     = ROOT_PATH.'/uploads/'.$fileName;
            if (move_uploaded_file($_FILES['raio_x_file']['tmp_name'], $dest)) $urlArquivo = 'uploads/'.$fileName;
        }

        // Paciente
        $pacId = (int)($_POST['paciente_id'] ?? 0);
        if (!$pacId && !empty($_POST['paciente_nome'])) {
            $pacId = (new PacienteModel($this->db))->insert(['nome' => trim($_POST['paciente_nome'])]);
        }
        if (!$pacId) { $this->json(['sucesso'=>false,'erro'=>'Paciente inválido.'],400); return; }

        try {
            $model = new AtendimentoModel($this->db);
            $fin   = new FinanceiroModel($this->db);
            $res   = $model->salvarCompleto($_POST, $urlArquivo, $pacId);

            // ── SE há procedimentos Finalizados (pagos na hora) E dados de pagamento enviados ──
            if (!empty($res['id_finalizados']) && !empty($_POST['pagamento']['forma'])) {
                $model->registrarPagamentosFinalizados($res['id_finalizados'], $_POST['pagamento'], $fin);
            }

            $this->json(['sucesso'=>true,'mensagem'=>'Atendimento lançado com sucesso!','redirectUrl'=>BASE_URL.'dashboard']);
        } catch (\Exception $e) {
            error_log("AtendimentoController::salvar — ".$e->getMessage());
            $this->json(['sucesso'=>false,'erro'=>$e->getMessage()],500);
        }
    }

    public function confirmarPagamento(?string $p=null): void {
        requer_perfil(['proprietario','dentista','recepcionista']);
        if ($this->isPost()) {
            $atendId = (int)($_POST['atendimento_id'] ?? 0);
            $fin     = new FinanceiroModel($this->db);
            try {
                (new AtendimentoModel($this->db))->confirmarPagamento($atendId, $_POST['pagamentos'] ?? [], $fin);
                $this->json(['sucesso'=>true,'mensagem'=>'Pagamento confirmado com sucesso!','redirectUrl'=>BASE_URL.'dashboard']);
            } catch (\Exception $e) {
                $this->json(['sucesso'=>false,'erro'=>$e->getMessage()],500);
            }
            return;
        }
        $model     = new AtendimentoModel($this->db);
        $fin       = new FinanceiroModel($this->db);
        $pendentes = $model->pendentes();
        $bandeiras = $fin->listaBandeiras();
        $this->render('atendimentos/confirmar_pagamento', compact('pendentes','bandeiras'));
    }

    public function buscarPendentes(?string $p=null): void {
        requer_login();
        $atendId = (int)($_GET['atendimento_id'] ?? 0);
        $this->json((new AtendimentoModel($this->db))->procsPendentes($atendId));
    }

    public function verificarPendente(?string $p=null): void {
        requer_login();
        $pacId = (int)($_GET['paciente_id'] ?? 0);
        $s = $this->db->prepare("SELECT COUNT(*) FROM atendimentos WHERE paciente_id=? AND status_pagamento='pendente'");
        $s->execute([$pacId]);
        $this->json(['tem_pendente' => $s->fetchColumn() > 0]);
    }
}
