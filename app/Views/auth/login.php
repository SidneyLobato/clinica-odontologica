<?php $nomeClinica = TenantResolver::$empresa['nome'] ?? APP_NAME; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($nomeClinica) ?> — Entrar</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Segoe UI',Arial,sans-serif;background-color:#0A1A1A;
  background-image:radial-gradient(circle at 1px 1px, rgba(255,255,255,.06) 1px, transparent 0),
    linear-gradient(135deg,#0A1A1A 0%,#0D2B2B 60%,#0A1F1F 100%);
  background-size:24px 24px, cover;
  min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem;}
.card{background:rgba(13,30,30,.55);border:1px solid rgba(14,165,164,.25);border-radius:18px;
  padding:2.5rem;width:100%;max-width:380px;box-shadow:0 24px 64px rgba(0,0,0,.45);backdrop-filter:blur(6px);}
.logo{display:flex;flex-direction:column;align-items:center;margin-bottom:2rem;text-align:center;}
.logo-ic{width:64px;height:64px;border-radius:50%;background:rgba(14,165,164,.15);
  border:1.5px solid rgba(14,165,164,.4);display:flex;align-items:center;justify-content:center;
  font-size:1.6rem;color:#0EA5A4;margin-bottom:1rem;}
.logo-nome{font-size:1.5rem;font-weight:800;color:#fff;line-height:1.25;}
.logo-sub{color:#0EA5A4;font-size:.7rem;letter-spacing:1.5px;text-transform:uppercase;font-weight:700;margin-top:.3rem;}
.erro{background:rgba(231,76,60,.12);border:1px solid rgba(231,76,60,.35);border-radius:8px;
  padding:.7rem 1rem;margin-bottom:1.25rem;color:#FCA5A5;font-size:.85rem;display:flex;align-items:center;gap:8px;}
.form-group{margin-bottom:1.1rem;}
.form-group label{display:block;font-weight:700;font-size:.7rem;color:#9DC9C8;
  margin-bottom:.35rem;text-transform:uppercase;letter-spacing:.6px;}
input{width:100%;padding:.65rem .85rem;border:1.5px solid rgba(14,165,164,.25);border-radius:8px;
  font-size:.92rem;background:rgba(255,255,255,.05);color:#fff;transition:border .18s,box-shadow .18s;}
input::placeholder{color:#5C7A79;}
input:focus{border-color:#0EA5A4;outline:none;box-shadow:0 0 0 3px rgba(14,165,164,.15);}
.btn-entrar{width:100%;padding:.75rem;background:#0EA5A4;color:#fff;border:none;border-radius:8px;
  font-size:.95rem;font-weight:700;cursor:pointer;margin-top:.5rem;transition:background .18s;}
.btn-entrar:hover{background:#0C8B8A;}
.rodape{text-align:center;margin-top:1.5rem;font-size:.72rem;color:#5C7A79;}
</style>
</head>
<body>
<div class="card">
  <div class="logo">
    <div class="logo-ic"><i class="fas fa-tooth"></i></div>
    <div class="logo-nome"><?= htmlspecialchars($nomeClinica) ?></div>
    <div class="logo-sub">Sistema de Gestão</div>
  </div>

  <?php if (!empty($erro) && $erro): ?>
  <div class="erro"><i class="fas fa-exclamation-circle"></i> Usuário ou senha incorretos.</div>
  <?php endif; ?>

  <form method="POST" action="<?= BASE_URL ?>login">
    <div class="form-group">
      <label>Usuário</label>
      <input type="text" name="login" placeholder="Seu login" autofocus required>
    </div>
    <div class="form-group">
      <label>Senha</label>
      <input type="password" name="senha" placeholder="Sua senha" required>
    </div>
    <button type="submit" class="btn-entrar"><i class="fas fa-sign-in-alt"></i> Entrar</button>
  </form>

  <p class="rodape">&copy; <?= date('Y') ?> <?= htmlspecialchars($nomeClinica) ?></p>
  <p class="rodape" style="margin-top:.5rem;">
    Ainda não tem conta?
    <a href="<?= SIGNUP_URL ?>" style="color:#0EA5A4;font-weight:700;text-decoration:none;">Cadastre sua clínica</a>
  </p>
</div>
</body>
</html>
