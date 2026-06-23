<?php
/**
 * TenantResolver
 * Identifica qual clínica (empresa) está acessando o sistema, a partir
 * do subdomínio (produção) ou da sessão (desenvolvimento local).
 *
 * Exemplos:
 *   prevdentistas.seusistema.com.br  → tipo 'empresa', empresa_id = X
 *   admin.seusistema.com.br          → tipo 'superadmin'
 *   seusistema.com.br                → tipo 'landing' (página de cadastro)
 */
class TenantResolver
{
    /** Tipo de acesso: 'landing' | 'empresa' | 'superadmin' */
    public static string $tipo = 'landing';

    /** Dados da empresa atual (null se landing, superadmin, ou ainda não logado) */
    public static ?array $empresa = null;

    /** ID da empresa atual (atalho para $empresa['id']) */
    public static ?int $empresaId = null;

    public static function resolve(PDO $pdo): void
    {
        $host = strtolower($_SERVER['HTTP_HOST'] ?? '');
        $host = explode(':', $host)[0]; // remove porta, ex: localhost:8080

        // Desenvolvimento local sem subdomínio (localhost, 127.0.0.1)
        // Antes do login, não sabemos a qual clínica o visitante pertence —
        // o AuthController identifica pelo e-mail/login digitado no formulário
        // único de login. (?empresa_id=X na URL funciona como atalho manual.)
        if (in_array($host, ['localhost', '127.0.0.1']) || !str_contains($host, '.')) {
            self::$tipo = 'empresa';
            if (isset($_GET['empresa_id']) && ctype_digit((string)$_GET['empresa_id'])) {
                $_SESSION['empresa_id'] = (int)$_GET['empresa_id'];
            }
            $eid = $_SESSION['empresa_id'] ?? null;
            if ($eid !== null) {
                $stmt = $pdo->prepare("SELECT * FROM empresas WHERE id = ? AND status = 'ativo' LIMIT 1");
                $stmt->execute([$eid]);
                $empresa = $stmt->fetch();
                if ($empresa) {
                    self::$empresa   = $empresa;
                    self::$empresaId = (int)$empresa['id'];
                }
            }
            return;
        }

        // Extrai o subdomínio comparando com o domínio raiz configurado
        // (config/app.php → ROOT_DOMAIN), funciona com qualquer domínio.
        $raiz = defined('ROOT_DOMAIN') ? strtolower(ROOT_DOMAIN) : 'localhost';

        if ($host === $raiz || $host === 'www.' . $raiz) {
            self::$tipo = 'landing';
            return;
        }

        if (!str_ends_with($host, '.' . $raiz)) {
            self::$tipo = 'landing';
            return;
        }

        $subdominio = substr($host, 0, -(strlen($raiz) + 1));

        if ($subdominio === 'admin') {
            self::$tipo = 'superadmin';
            return;
        }

        if ($subdominio === 'www') {
            self::$tipo = 'landing';
            return;
        }

        $stmt = $pdo->prepare("SELECT * FROM empresas WHERE slug = ? AND status = 'ativo' LIMIT 1");
        $stmt->execute([$subdominio]);
        $empresa = $stmt->fetch();

        if (!$empresa) {
            self::$tipo = 'landing';
            http_response_code(404);
            self::renderClinicaNaoEncontrada($subdominio);
            exit;
        }

        self::$tipo      = 'empresa';
        self::$empresa   = $empresa;
        self::$empresaId = (int)$empresa['id'];
    }

    private static function renderClinicaNaoEncontrada(string $slug): void
    {
        echo '<!DOCTYPE html><html lang="pt-BR"><head><meta charset="UTF-8">
        <title>Não encontrado</title>
        <style>body{font-family:sans-serif;display:flex;align-items:center;justify-content:center;min-height:100vh;background:#f0f4f8;}
        .box{background:#fff;padding:2.5rem;border-radius:12px;box-shadow:0 2px 16px rgba(0,0,0,.1);text-align:center;max-width:400px;}
        h1{color:#E74C3C;font-size:1.4rem;}p{color:#666;margin:.75rem 0;}
        a{color:#0EA5A4;text-decoration:none;font-weight:600;}</style></head>
        <body><div class="box">
        <h1>🔍 Clínica não encontrada</h1>
        <p>O endereço <strong>' . htmlspecialchars($slug) . '</strong> não corresponde a nenhuma clínica cadastrada.</p>
        <p>Verifique se o endereço está correto ou<br><a href="/">cadastre sua clínica aqui</a>.</p>
        </div></body></html>';
    }
}
