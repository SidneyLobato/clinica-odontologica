<?php
/**
 * EmpresaController
 * Roda na landing page (seusistema.com.br) — sem tenant ativo.
 * Gerencia a página pública e o cadastro self-service de novas clínicas.
 */
class EmpresaController
{
    protected PDO $db;

    public function __construct(PDO $pdo)
    {
        $this->db = $pdo;
    }

    public function landing(): void
    {
        require __DIR__ . '/../Views/landing/index.php';
    }

    public function cadastrar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . LANDING_BASE_URL);
            exit;
        }

        $nomeResponsavel = trim($_POST['nome_responsavel'] ?? '');
        $nome     = trim($_POST['nome'] ?? '');
        $slug     = strtolower(preg_replace('/[^a-z0-9\-]/', '', str_replace(' ', '-', trim($_POST['slug'] ?? ''))));
        $email    = trim($_POST['email'] ?? '');
        $senha    = $_POST['senha'] ?? '';
        $confirma = $_POST['confirma_senha'] ?? '';
        $telefone = trim($_POST['telefone'] ?? '');

        $erros = [];

        if (strlen($nomeResponsavel) < 2) $erros[] = 'Digite seu nome.';
        if (strlen($nome)  < 2)  $erros[] = 'Nome da clínica muito curto.';
        if (strlen($slug)  < 3)  $erros[] = 'Endereço (slug) deve ter ao menos 3 caracteres.';
        if (strlen($slug)  > 30) $erros[] = 'Endereço (slug) deve ter no máximo 30 caracteres.';
        if (in_array($slug, ['admin', 'www', 'api', 'mail', 'smtp', 'ftp', 'webmail', 'superadmin', 'cadastro'])) {
            $erros[] = 'Esse endereço é reservado. Escolha outro.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';
        if (strlen($senha) < 6)  $erros[] = 'Senha deve ter ao menos 6 caracteres.';
        if ($senha !== $confirma) $erros[] = 'As senhas não coincidem.';

        $model = new EmpresaModel($this->db);

        if (empty($erros) && !$model->slugDisponivel($slug)) {
            $erros[] = 'Esse endereço já está em uso. Escolha outro.';
        }

        if (!empty($erros)) {
            $dados = compact('nomeResponsavel', 'nome', 'slug', 'email', 'telefone');
            require __DIR__ . '/../Views/landing/index.php';
            return;
        }

        try {
            $eid = $model->provisionar([
                'nome'              => $nome,
                'slug'              => $slug,
                'email'             => $email,
                'senha'             => $senha,
                'telefone'          => $telefone,
                'nome_proprietario' => $nomeResponsavel,
            ]);

            $hostAtual = explode(':', strtolower($_SERVER['HTTP_HOST'] ?? ''))[0];

            if (in_array($hostAtual, ['localhost', '127.0.0.1'], true)) {
                $linkLogin = BASE_URL . "login";
                require __DIR__ . '/../Views/landing/sucesso.php';
                return;
            }

            $scheme  = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $dominio = preg_replace('/^www\./', '', $hostAtual);
            header("Location: $scheme://$slug.$dominio/login?novo=1");
            exit;
        } catch (\Exception $e) {
            $erros = ['Erro ao criar a conta. Tente novamente.'];
            $dados = compact('nomeResponsavel', 'nome', 'slug', 'email', 'telefone');
            require __DIR__ . '/../Views/landing/index.php';
        }
    }
}
