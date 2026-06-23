<?php
require_once __DIR__ . '/session.php';

function is_logado(): bool         { return isset($_SESSION['usuario_id']); }
function is_admin(): bool          { return ($_SESSION['usuario_perfil'] ?? '') === 'proprietario'; }
function is_dentista(): bool       { return ($_SESSION['usuario_perfil'] ?? '') === 'dentista'; }
function is_recepcionista(): bool  { return ($_SESSION['usuario_perfil'] ?? '') === 'recepcionista'; }

/**
 * Garante que o usuário logado pertence à clínica ativa no tenant atual.
 * Tem uma trava de segurança contra loop de redirecionamento: se detectar
 * mais de 3 redirecionamentos seguidos em poucos segundos (sinal de algo
 * errado no servidor/rota), para tudo, limpa a sessão e mostra um erro
 * claro em vez de ficar redirecionando para sempre.
 */
function requer_login(): void {
    if (!is_logado()) {
        _guarda_contra_loop_e_redireciona(BASE_URL . 'login');
    }
    $eid = TenantResolver::$empresaId;
    if ($eid !== null && (int)($_SESSION['empresa_id'] ?? 0) !== $eid) {
        session_unset();
        _guarda_contra_loop_e_redireciona(BASE_URL . 'login?erro=sessao');
    }
}

function _guarda_contra_loop_e_redireciona(string $destino): void {
    $agora = time();
    $contagem = $_SESSION['_redirect_guard_count']  ?? 0;
    $ultimo   = $_SESSION['_redirect_guard_time']   ?? 0;

    if (($agora - $ultimo) < 5) {
        $contagem++;
    } else {
        $contagem = 1;
    }
    $_SESSION['_redirect_guard_count'] = $contagem;
    $_SESSION['_redirect_guard_time']  = $agora;

    if ($contagem > 3) {
        // Algo está causando redirecionamentos repetidos — para tudo
        // em vez de continuar o loop, e limpa a sessão para sair do estado ruim.
        session_unset();
        http_response_code(500);
        echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8">
        <title>Erro temporário</title>
        <style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;
        min-height:100vh;background:#f0f4f8;margin:0;}
        .box{background:#fff;padding:2.5rem;border-radius:12px;box-shadow:0 2px 16px rgba(0,0,0,.1);
        text-align:center;max-width:420px;}
        h1{color:#E74C3C;font-size:1.3rem;}p{color:#666;margin:.75rem 0;font-size:.9rem;}
        a{color:#0EA5A4;text-decoration:none;font-weight:600;}</style></head>
        <body><div class="box">
        <h1>⚠️ Algo deu errado</h1>
        <p>Detectamos vários redirecionamentos seguidos e paramos por segurança,
        para evitar um loop infinito. A sessão foi limpa.</p>
        <p><a href="' . BASE_URL . 'login">Clique aqui para tentar entrar novamente</a></p>
        </div></body></html>';
        exit;
    }

    header('Location: ' . $destino);
    exit;
}

function requer_admin(): void {
    requer_login();
    if (!is_admin()) { header('Location: ' . BASE_URL . 'dashboard'); exit; }
}

function requer_perfil(array $p): void {
    requer_login();
    if (!in_array($_SESSION['usuario_perfil'] ?? '', $p)) { header('Location: ' . BASE_URL . 'dashboard'); exit; }
}
