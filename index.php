<?php
// ── BOOTSTRAP ─────────────────────────────────────────────────────────────────
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/app.php';

// Carrega o TenantResolver antes de qualquer outra coisa
require_once __DIR__ . '/app/Core/TenantResolver.php';

// Resolve o tenant a partir do subdomínio (ou sessão, em dev local)
TenantResolver::resolve($pdo);

// BASE_URL dinâmica (funciona em localhost/pasta e em produção com subdomínios)
if (!defined('BASE_URL')) {
    $scheme    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $scriptDir = preg_replace('#/+#', '/', $scriptDir); // colapsa barras duplicadas
    $subdir    = rtrim($scriptDir, '/') . '/';
    define('BASE_URL', $scheme . '://' . $_SERVER['HTTP_HOST'] . $subdir);
}

if (!defined('APP_NAME')) {
    define('APP_NAME', TenantResolver::$empresa['nome'] ?? 'Sistema para Clínicas Odontológicas');
}

require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/app/Models/BaseModel.php';
require_once __DIR__ . '/app/Controllers/BaseController.php';

spl_autoload_register(function (string $c) {
    foreach ([
        __DIR__ . '/app/Controllers/' . $c . '.php',
        __DIR__ . '/app/Models/'      . $c . '.php',
        __DIR__ . '/app/Core/'        . $c . '.php',
    ] as $f) {
        if (file_exists($f)) { require_once $f; return; }
    }
});

// ── ROTEAMENTO ────────────────────────────────────────────────────────────────
// Normaliza a URI bruta ANTES de qualquer parsing: troca \ por /, e colapsa
// barras duplicadas (//) — isso evita que parse_url() confunda "//login" com
// uma URL "protocol-relative" (que tem host="login" e path vazio), o que
// causaria a rota cair sempre em "" (Dashboard) e gerar loop de redirecionamento.
$rawUri = $_SERVER['REQUEST_URI'] ?? '/';
$rawUri = str_replace('\\', '/', $rawUri);
$rawUri = preg_replace('#/{2,}#', '/', $rawUri);

$uri    = parse_url($rawUri, PHP_URL_PATH) ?: '/';
$script = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
$script = preg_replace('#/+#', '/', $script);
$script = rtrim($script, '/');

$path   = trim(substr($uri, strlen($script)), '/');
$parts  = $path === '' ? [] : explode('/', $path);
$rota   = $parts[0] ?? '';
$acao   = preg_replace('/[^a-zA-Z0-9_]/', '', $parts[1] ?? 'index') ?: 'index';
$param  = $parts[2] ?? null;

// ── ATALHOS DE DESENVOLVIMENTO ───────────────────────────────────────────────
// Permitem acessar o painel super admin e a página de cadastro de clínicas
// em ambiente local SEM precisar configurar subdomínio no Apache/hosts.
// Use: http://localhost/prev_mvc/superadmin/...
// Use: http://localhost/prev_mvc/cadastro
$hostAtual  = explode(':', strtolower($_SERVER['HTTP_HOST'] ?? ''))[0];
$ehLocalDev = in_array($hostAtual, ['localhost', '127.0.0.1'], true);

$atalhoDevSA = $ehLocalDev && $rota === 'superadmin';
if ($atalhoDevSA) {
    TenantResolver::$tipo = 'superadmin';
    $rota  = $parts[1] ?? '';
    $acao  = preg_replace('/[^a-zA-Z0-9_]/', '', $parts[2] ?? 'index') ?: 'index';
    $param = $parts[3] ?? null;
}

$atalhoDevCadastro = $ehLocalDev && $rota === 'cadastro';
if ($atalhoDevCadastro) {
    TenantResolver::$tipo = 'landing';
    $rota = $parts[1] ?? '';
}

define('SA_BASE_URL',      $atalhoDevSA       ? BASE_URL . 'superadmin/' : BASE_URL);
define('LANDING_BASE_URL', $atalhoDevCadastro ? BASE_URL . 'cadastro/'   : BASE_URL);

// URL para "cadastre sua clínica", usável em qualquer tela (ex: link na tela
// de login). Três cenários:
//  1. Acesso via atalho de dev (localhost/prev_mvc/...)        → /cadastro
//  2. Acesso via subdomínio X.localhost (teste local)          → descobre a
//     pasta do projeto pelo DocumentRoot e monta o link certo
//  3. Produção (domínio de verdade)                            → raiz do domínio
if ($ehLocalDev) {
    define('SIGNUP_URL', BASE_URL . 'cadastro');
} elseif (str_ends_with($hostAtual, '.localhost')) {
    $pastaProjeto = basename(rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT'] ?? ''), '/'));
    define('SIGNUP_URL', 'http://localhost/' . $pastaProjeto . '/cadastro');
} else {
    $schemeSignup = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    define('SIGNUP_URL', $schemeSignup . '://' . (defined('ROOT_DOMAIN') ? ROOT_DOMAIN : $hostAtual) . '/');
}

// ── LANDING PAGE (seusistema.com.br — sem clínica no subdomínio) ──────────────
if (TenantResolver::$tipo === 'landing') {
    require_once __DIR__ . '/app/Models/EmpresaModel.php';
    require_once __DIR__ . '/app/Controllers/EmpresaController.php';
    $ctrl = new EmpresaController($pdo);
    match ($rota) {
        'cadastrar' => $ctrl->cadastrar(),
        default     => $ctrl->landing(),
    };
    exit;
}

// ── SUPER ADMIN (admin.seusistema.com.br) ─────────────────────────────────────
if (TenantResolver::$tipo === 'superadmin') {
    require_once __DIR__ . '/app/Models/EmpresaModel.php';
    require_once __DIR__ . '/app/Controllers/SuperAdminController.php';

    $rotasSa = [
        ''                  => ['SuperAdminController', 'index'],
        'dashboard'         => ['SuperAdminController', 'index'],
        'login'             => ['SuperAdminController', 'login'],
        'logout'            => ['SuperAdminController', 'logout'],
        'empresas'          => ['SuperAdminController', 'empresas'],
        'empresa'           => ['SuperAdminController', 'empresa'],
        'suspender'         => ['SuperAdminController', 'suspender'],
        'ativar'            => ['SuperAdminController', 'ativar'],
        'cancelar'          => ['SuperAdminController', 'cancelar'],
        'salvar-plano'      => ['SuperAdminController', 'salvarPlano'],
        'salvar-observacao' => ['SuperAdminController', 'salvarObservacao'],
        'editar-dados'      => ['SuperAdminController', 'editarDados'],
        'login-como'        => ['SuperAdminController', 'loginComo'],
    ];

    [$ctrl, $met] = $rotasSa[$rota] ?? ['SuperAdminController', 'index'];
    $controller   = new $ctrl($pdo);
    $rotasComId   = ['suspender', 'ativar', 'cancelar', 'empresa', 'salvar-plano', 'salvar-observacao', 'editar-dados', 'login-como'];
    $argumentoSa  = in_array($rota, $rotasComId, true) ? $acao : $param;
    $controller->$met($argumentoSa);
    exit;
}

// ── CLÍNICA (slug.seusistema.com.br) ──────────────────────────────────────────
if (TenantResolver::$tipo === 'empresa') {
    $rotas = [
        ''              => ['DashboardController',    'index'],
        'dashboard'     => ['DashboardController',    'index'],
        'login'         => ['AuthController',         'login'],
        'logout'        => ['AuthController',         'logout'],
        'sair-modo-suporte' => ['AuthController',     'sairModoSuporte'],
        'pacientes'     => ['PacienteController',     $acao],
        'usuarios'      => ['UsuarioController',      $acao],
        'despesas'      => ['DespesaController',      $acao],
        'procedimentos' => ['ProcedimentoController', $acao],
        'atendimentos'  => ['AtendimentoController',  $acao],
        'relatorios'    => ['RelatorioController',    $acao],
        'admin'         => ['AdminController',        $acao],
        'configuracoes' => ['ConfiguracaoController',  $acao],
        'recibo'        => ['ReciboController',        'index'],
        'buscar-paciente'                => ['PacienteController',    'buscar'],
        'buscar-procedimentos-pendentes' => ['AtendimentoController', 'buscarPendentes'],
        'verificar-pagamento-pendente'   => ['AtendimentoController', 'verificarPendente'],
    ];

    if (!isset($rotas[$rota])) { http_response_code(404); echo "<h1>404 — $rota</h1>"; exit; }
    [$ctrl, $met] = $rotas[$rota];
    $controller   = new $ctrl($pdo);
    if (!method_exists($controller, $met)) $met = 'index';
    $controller->$met($param);
    exit;
}
