<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';

// Carrega bases PRIMEIRO (obrigatório antes do autoloader)
require_once __DIR__ . '/app/Models/BaseModel.php';
require_once __DIR__ . '/app/Controllers/BaseController.php';

// Autoloader para derivadas
spl_autoload_register(function (string $c) {
    foreach ([__DIR__.'/app/Controllers/'.$c.'.php', __DIR__.'/app/Models/'.$c.'.php'] as $f) {
        if (file_exists($f)) { require_once $f; return; }
    }
});

// Roteamento
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base   = rtrim(parse_url(BASE_URL, PHP_URL_PATH), '/');
$path   = trim(str_replace($base, '', $uri), '/');
$parts  = explode('/', $path);
$rota   = $parts[0] ?: 'dashboard';
$acao   = preg_replace('/[^a-zA-Z0-9_]/', '', $parts[1] ?? 'index') ?: 'index';
$param  = $parts[2] ?? null;

$rotas = [
    ''              => ['DashboardController',    'index'],
    'dashboard'     => ['DashboardController',    'index'],
    'login'         => ['AuthController',         'login'],
    'logout'        => ['AuthController',         'logout'],
    'pacientes'     => ['PacienteController',     $acao],
    'usuarios'      => ['UsuarioController',      $acao],
    'despesas'      => ['DespesaController',      $acao],
    'procedimentos' => ['ProcedimentoController', $acao],
    'atendimentos'  => ['AtendimentoController',  $acao],
    'relatorios'    => ['RelatorioController',    $acao],
    'admin'         => ['AdminController',        $acao],
    'configuracoes' => ['ConfiguracaoController', $acao],
    'recibo'        => ['ReciboController',       'index'],
    'buscar-paciente'               => ['PacienteController',    'buscar'],
    'buscar-procedimentos-pendentes'=> ['AtendimentoController', 'buscarPendentes'],
    'verificar-pagamento-pendente'  => ['AtendimentoController', 'verificarPendente'],
];

if (!isset($rotas[$rota])) { http_response_code(404); echo "<h1>404 — $rota</h1>"; exit; }
[$ctrl, $met] = $rotas[$rota];
$controller   = new $ctrl($pdo);
if (!method_exists($controller, $met)) $met = 'index';
$controller->$met($param);
