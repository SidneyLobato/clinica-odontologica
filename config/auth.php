<?php
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/app.php';
function is_logado(): bool  { return isset($_SESSION['usuario_id']); }
function is_admin(): bool   { return ($_SESSION['usuario_perfil'] ?? '') === 'proprietario'; }
function is_dentista(): bool { return ($_SESSION['usuario_perfil'] ?? '') === 'dentista'; }
function is_recepcionista(): bool { return ($_SESSION['usuario_perfil'] ?? '') === 'recepcionista'; }
function requer_login(): void { if (!is_logado()) { header('Location: ' . BASE_URL . 'login'); exit; } }
function requer_admin(): void { requer_login(); if (!is_admin()) { header('Location: ' . BASE_URL . 'dashboard'); exit; } }
function requer_perfil(array $p): void { requer_login(); if (!in_array($_SESSION['usuario_perfil'] ?? '', $p)) { header('Location: ' . BASE_URL . 'dashboard'); exit; } }
