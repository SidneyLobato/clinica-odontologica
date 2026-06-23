<?php
// BASE_URL e APP_NAME são definidos dinamicamente no index.php
// com base no subdomínio e na clínica resolvida pelo TenantResolver.
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__ . '/..');
}

// ── DOMÍNIO RAIZ DA PLATAFORMA ───────────────────────────────────────────────
// Em desenvolvimento local (WAMP/XAMPP), deixe 'localhost'.
// Quando for para produção, troque para o seu domínio real, ex: 'seusistema.com.br'
if (!defined('ROOT_DOMAIN')) {
    define('ROOT_DOMAIN', 'localhost');
}
