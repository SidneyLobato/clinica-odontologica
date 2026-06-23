<?php
class AuthController extends BaseController
{
    public function login(?string $p = null): void
    {
        if (is_logado()) {
            $this->redirect('dashboard');
            return;
        }

        if ($this->isPost()) {
            $login   = trim($_POST['login'] ?? '');
            $senha   = $_POST['senha'] ?? '';
            $model   = new UsuarioModel($this->db);
            $usuario = $model->porLogin($login); // busca em todas as clínicas pelo login digitado

            if ($usuario && password_verify($senha, $usuario['senha'])) {
                $_SESSION['usuario_id']     = $usuario['id'];
                $_SESSION['usuario_nome']   = $usuario['nome'];
                $_SESSION['usuario_perfil'] = $usuario['perfil'];
                $_SESSION['empresa_id']     = $usuario['empresa_id'];

                // Busca o nome da clínica do usuário autenticado
                $se = $this->db->prepare("SELECT nome FROM empresas WHERE id = ?");
                $se->execute([$usuario['empresa_id']]);
                $_SESSION['empresa_nome'] = $se->fetchColumn() ?: '';

                $this->redirect('dashboard');
            } else {
                $this->renderSemLayout('auth/login', ['erro' => true]);
            }
            return;
        }

        $this->renderSemLayout('auth/login', ['erro' => isset($_GET['erro'])]);
    }

    public function logout(?string $p = null): void
    {
        session_destroy();
        $this->redirect('login');
    }

    /** Sai do "modo suporte" (entrou como clínica via super admin) e volta ao painel */
    public function sairModoSuporte(?string $p = null): void
    {
        unset(
            $_SESSION['usuario_id'], $_SESSION['usuario_nome'], $_SESSION['usuario_perfil'],
            $_SESSION['empresa_id'], $_SESSION['empresa_nome'], $_SESSION['suporte_via_super_admin']
        );

        if (!empty($_SESSION['super_admin_id'])) {
            header('Location: ' . BASE_URL . 'superadmin');
            exit;
        }
        $this->redirect('login');
    }
}
