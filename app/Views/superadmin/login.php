<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Super Admin — Sistema para Clínicas</title>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Segoe UI',Arial,sans-serif;background:#0A1A1A;display:flex;
  align-items:center;justify-content:center;min-height:100vh;}
.card{background:#fff;border-radius:14px;padding:2.5rem;width:100%;max-width:380px;
  box-shadow:0 24px 64px rgba(0,0,0,.5);}
h1{font-size:1.3rem;font-weight:800;color:#0A1A1A;margin-bottom:.25rem;}
.sub{color:#5A6A7A;font-size:.85rem;margin-bottom:1.75rem;}
label{display:block;font-weight:700;font-size:.72rem;color:#5A6A7A;
  margin-bottom:.3rem;text-transform:uppercase;letter-spacing:.5px;}
input{width:100%;padding:.55rem .8rem;border:1.5px solid #DDE3EC;border-radius:8px;
  font-size:.9rem;margin-bottom:1rem;}
input:focus{border-color:#0EA5A4;outline:none;box-shadow:0 0 0 3px rgba(14,165,164,.1);}
.btn{width:100%;padding:.7rem;background:#0EA5A4;color:#fff;border:none;
  border-radius:8px;font-weight:700;font-size:.95rem;cursor:pointer;}
.btn:hover{background:#0C8B8A;}
.erro{background:#FDECEA;border:1px solid #FAADA7;border-radius:6px;
  padding:.65rem 1rem;margin-bottom:1rem;color:#E74C3C;font-size:.85rem;}
.badge-sa{display:inline-block;background:#FF6B2B;color:#fff;font-size:.65rem;
  font-weight:700;padding:2px 8px;border-radius:20px;letter-spacing:.5px;margin-bottom:1.25rem;}
</style>
</head>
<body>
<div class="card">
  <span class="badge-sa">⚡ SUPER ADMIN</span>
  <h1>Painel da Plataforma</h1>
  <p class="sub">Acesso restrito ao proprietário do sistema.</p>

  <?php if ($erro ?? false): ?>
  <div class="erro">Usuário ou senha incorretos.</div>
  <?php endif; ?>

  <form method="POST" action="<?= SA_BASE_URL ?>login">
    <label>Login</label>
    <input type="text" name="login" autofocus required>
    <label>Senha</label>
    <input type="password" name="senha" required>
    <button type="submit" class="btn">Entrar no painel</button>
  </form>
</div>
</body>
</html>
