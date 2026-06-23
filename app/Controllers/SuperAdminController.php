<?php
/**
 * SuperAdminController
 * Painel de controle da plataforma (admin.seusistema.com.br).
 * Só você tem acesso. Gerencia todas as clínicas clientes.
 */
class SuperAdminController
{
    protected PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    // ── AUTENTICAÇÃO ─────────────────────────────────────────────────────────

    public function login(?string $p = null): void
    {
        if ($this->isSuperLogado()) {
            $this->redirect('');
            return;
        }

        $erro = false;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $login = trim($_POST['login'] ?? '');
            $senha = $_POST['senha'] ?? '';
            $s     = $this->db->prepare("SELECT * FROM super_admins WHERE login = ? AND ativo = 1 LIMIT 1");
            $s->execute([$login]);
            $sa = $s->fetch();

            if ($sa && password_verify($senha, $sa['senha'])) {
                $_SESSION['super_admin_id']   = $sa['id'];
                $_SESSION['super_admin_nome'] = $sa['nome'];
                $this->redirect('');
                return;
            }
            $erro = true;
        }

        require __DIR__ . '/../Views/superadmin/login.php';
    }

    public function logout(?string $p = null): void
    {
        unset($_SESSION['super_admin_id'], $_SESSION['super_admin_nome']);
        $this->redirect('login');
    }

    // ── DASHBOARD ────────────────────────────────────────────────────────────

    public function index(?string $p = null): void
    {
        $this->requerSuperAdmin();
        $model = new EmpresaModel($this->db);

        $totais         = $model->estatisticasGerais();
        $trialsVencendo = $model->trialsVencendo(7);
        $recentes       = $this->db->query("SELECT * FROM empresas ORDER BY criado_em DESC LIMIT 10")->fetchAll();

        require __DIR__ . '/../Views/superadmin/index.php';
    }

    // ── CLÍNICAS ─────────────────────────────────────────────────────────────

    public function empresas(?string $p = null): void
    {
        $this->requerSuperAdmin();
        $busca    = trim($_GET['busca'] ?? '');
        $model    = new EmpresaModel($this->db);
        $empresas = $model->listarTodas($busca);
        require __DIR__ . '/../Views/superadmin/empresas.php';
    }

    public function empresa(?string $p = null): void
    {
        $this->requerSuperAdmin();
        $id    = (int)($p ?? 0);
        $model = new EmpresaModel($this->db);
        $empresa = $model->buscarDetalhe($id);
        if (!$empresa) { $this->redirect('empresas'); return; }
        $usuarios = $model->listarUsuariosDaEmpresa($id);
        $msg = $_GET['msg'] ?? '';
        require __DIR__ . '/../Views/superadmin/empresa_detalhe.php';
    }

    public function suspender(?string $p = null): void
    {
        $this->requerSuperAdmin();
        $id = (int)($p ?? 0);
        (new EmpresaModel($this->db))->alterarStatus($id, 'suspenso');
        $this->redirect('empresas?msg=suspenso');
    }

    public function ativar(?string $p = null): void
    {
        $this->requerSuperAdmin();
        $id = (int)($p ?? 0);
        (new EmpresaModel($this->db))->alterarStatus($id, 'ativo');
        $this->redirect('empresas?msg=ativado');
    }

    public function cancelar(?string $p = null): void
    {
        $this->requerSuperAdmin();
        $id = (int)($p ?? 0);
        (new EmpresaModel($this->db))->alterarStatus($id, 'cancelado');
        $this->redirect('empresas?msg=cancelado');
    }

    public function salvarPlano(?string $p = null): void
    {
        $this->requerSuperAdmin();
        $id = (int)($p ?? 0);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect("empresa/$id"); return; }

        $plano     = $_POST['plano'] ?? '';
        $valor     = (float)str_replace(',', '.', $_POST['valor_mensal'] ?? 0);
        $diasTrial = (int)($_POST['dias_trial'] ?? 30);

        (new EmpresaModel($this->db))->alterarPlano($id, $plano, $valor, $diasTrial);
        $this->redirect("empresa/$id?msg=plano_atualizado");
    }

    public function salvarObservacao(?string $p = null): void
    {
        $this->requerSuperAdmin();
        $id = (int)($p ?? 0);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect("empresa/$id"); return; }
        (new EmpresaModel($this->db))->salvarObservacao($id, trim($_POST['observacoes'] ?? ''));
        $this->redirect("empresa/$id?msg=obs_salva");
    }

    public function editarDados(?string $p = null): void
    {
        $this->requerSuperAdmin();
        $id = (int)($p ?? 0);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect("empresa/$id"); return; }
        (new EmpresaModel($this->db))->atualizarDados($id, [
            'nome'     => trim($_POST['nome'] ?? ''),
            'email'    => trim($_POST['email'] ?? ''),
            'telefone' => trim($_POST['telefone'] ?? ''),
            'cnpj'     => trim($_POST['cnpj'] ?? ''),
        ]);
        $this->redirect("empresa/$id?msg=dados_salvos");
    }

    /** "Entrar como" — loga como o proprietário da clínica, para dar suporte */
    public function loginComo(?string $p = null): void
    {
        $this->requerSuperAdmin();
        $id    = (int)($p ?? 0);
        $model = new EmpresaModel($this->db);
        $user  = $model->proprietarioDaEmpresa($id);
        if (!$user) { $this->redirect("empresa/$id?msg=sem_usuario"); return; }

        $_SESSION['usuario_id']     = $user['id'];
        $_SESSION['usuario_nome']   = $user['nome'];
        $_SESSION['usuario_perfil'] = $user['perfil'];
        $_SESSION['empresa_id']     = $user['empresa_id'];
        $se = $this->db->prepare("SELECT nome FROM empresas WHERE id=?");
        $se->execute([$user['empresa_id']]);
        $_SESSION['empresa_nome'] = $se->fetchColumn() ?: '';
        $_SESSION['suporte_via_super_admin'] = true;

        // Nota: em produção com subdomínios reais, isso exige o mesmo domínio
        // entre o painel e a clínica (ou um link mágico com token — não
        // implementado nesta versão). Funciona direto em desenvolvimento local.
        header('Location: ' . BASE_URL . 'dashboard');
        exit;
    }

    // ── HELPERS ──────────────────────────────────────────────────────────────

    private function isSuperLogado(): bool
    {
        return !empty($_SESSION['super_admin_id']);
    }

    private function requerSuperAdmin(): void
    {
        if (!$this->isSuperLogado()) {
            $this->redirect('login');
        }
    }

    private function redirect(string $url): void
    {
        header('Location: ' . SA_BASE_URL . ltrim($url, '/'));
        exit;
    }
}
