<?php
class AuthController extends BaseController {
    public function login(?string $p=null): void {
        if(is_logado()){$this->redirect('dashboard');return;}
        if($this->isPost()){
            $u=(new UsuarioModel($this->db))->porLogin(trim($_POST['login']??''));
            if($u&&password_verify($_POST['senha']??'',$u['senha'])){
                $_SESSION['usuario_id']=$u['id'];$_SESSION['usuario_nome']=$u['nome'];$_SESSION['usuario_perfil']=$u['perfil'];
                $this->redirect('dashboard');
            }else $this->renderSemLayout('auth/login',['erro'=>true]);
            return;
        }
        $this->renderSemLayout('auth/login',['erro'=>isset($_GET['erro'])]);
    }
    public function logout(?string $p=null): void { session_destroy();$this->redirect('login'); }
}
