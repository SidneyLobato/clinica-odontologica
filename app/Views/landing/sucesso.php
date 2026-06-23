<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Clínica criada com sucesso!</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Segoe UI',Arial,sans-serif;background:linear-gradient(135deg,#0A1A1A 0%,#0D2B2B 60%,#0A1F1F 100%);
  min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem;}
.card{background:#fff;border-radius:16px;padding:2.5rem;width:100%;max-width:460px;
  box-shadow:0 24px 64px rgba(0,0,0,.4);text-align:center;}
.check{width:64px;height:64px;border-radius:50%;background:#D5F5E3;color:#27AE60;
  display:flex;align-items:center;justify-content:center;font-size:1.8rem;margin:0 auto 1.25rem;}
h1{font-size:1.4rem;font-weight:800;color:#1A2332;margin-bottom:.4rem;}
.sub{color:#5A6A7A;font-size:.9rem;margin-bottom:1.75rem;line-height:1.5;}
.info-box{background:#F0F4F8;border-radius:10px;padding:1.1rem 1.25rem;text-align:left;
  margin-bottom:1.5rem;}
.info-row{display:flex;justify-content:space-between;padding:.4rem 0;font-size:.85rem;
  border-bottom:1px solid #DDE3EC;}
.info-row:last-child{border-bottom:none;}
.info-row b{color:#1A2332;}
.info-row span:last-child{color:#5A6A7A;}
.btn-entrar{display:block;width:100%;padding:.85rem;background:#0EA5A4;color:#fff;
  border:none;border-radius:8px;font-size:1rem;font-weight:700;cursor:pointer;
  text-decoration:none;transition:background .18s;}
.btn-entrar:hover{background:#0C8B8A;}
.nota{margin-top:1.25rem;font-size:.78rem;color:#9DAEC2;line-height:1.5;}
</style>
</head>
<body>
<div class="card">
  <div class="check"><i class="fas fa-check"></i></div>
  <h1>Sua clínica foi criada!</h1>
  <p class="sub">Use o e-mail e a senha que você acabou de cadastrar para entrar.</p>

  <div class="info-box">
    <div class="info-row"><span><b>Clínica</b></span><span><?= htmlspecialchars($nome) ?></span></div>
    <div class="info-row"><span><b>E-mail de acesso</b></span><span><?= htmlspecialchars($email) ?></span></div>
    <div class="info-row"><span><b>Senha</b></span><span>a que você cadastrou agora</span></div>
  </div>

  <a href="<?= htmlspecialchars($linkLogin) ?>" class="btn-entrar">
    <i class="fas fa-sign-in-alt"></i> Entrar na minha clínica
  </a>

  <p class="nota">
    Você está testando em ambiente local, então todas as clínicas usam o mesmo
    endereço de login — o sistema reconhece sua clínica automaticamente pelo
    e-mail. Quando o sistema estiver em um domínio próprio, cada clínica
    poderá ter seu próprio endereço (ex: <?= htmlspecialchars($slug) ?>.seudominio.com.br).
  </p>
</div>
</body>
</html>
