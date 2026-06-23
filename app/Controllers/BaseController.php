<?php
abstract class BaseController
{
    protected PDO    $db;
    protected ?int   $empresaId;
    protected ?array $empresa;

    public function __construct(PDO $pdo)
    {
        $this->db        = $pdo;
        $this->empresaId = TenantResolver::$empresaId;
        $this->empresa   = TenantResolver::$empresa;
    }

    protected function render(string $v, array $d = []): void
    {
        extract($d);
        $f = __DIR__ . '/../Views/' . $v . '.php';
        if (!file_exists($f)) throw new RuntimeException("View não encontrada: $v");
        require __DIR__ . '/../Views/layout/header.php';
        require $f;
        require __DIR__ . '/../Views/layout/footer.php';
    }

    protected function renderSemLayout(string $v, array $d = []): void
    {
        extract($d);
        require __DIR__ . '/../Views/' . $v . '.php';
    }

    protected function json(array $d, int $s = 200): void
    {
        http_response_code($s);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($d, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect(string $r): void
    {
        header('Location: ' . BASE_URL . ltrim($r, '/'));
        exit;
    }

    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}
