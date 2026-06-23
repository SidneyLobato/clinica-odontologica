<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sistema para Clínicas Odontológicas</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root {
  --teal: #0EA5A4; --teal-d: #0C8B8A; --laranja: #F5A623;
  --escuro: #0A1A1A; --cinza-bg: #F0F4F8; --branco: #FFFFFF;
  --texto: #1A2332; --sub: #5A6A7A; --borda: #DDE3EC;
  --success: #27AE60; --danger: #E74C3C;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Segoe UI',Arial,sans-serif;background:var(--escuro);color:var(--texto);}
.hero{background:linear-gradient(135deg,#0A1A1A 0%,#0D2B2B 60%,#0A1F1F 100%);
  min-height:100vh;display:flex;flex-direction:column;}
.hero-nav{display:flex;align-items:center;justify-content:space-between;
  padding:1.25rem 2rem;border-bottom:1px solid rgba(255,255,255,.06);}
.brand{display:flex;align-items:center;gap:10px;text-decoration:none;}
.brand-nome{font-size:1.4rem;font-weight:800;}
.brand-nome .l{color:#fff;}.brand-nome .t{color:var(--teal);}
.brand-sub{font-size:.6rem;color:var(--laranja);letter-spacing:1.5px;text-transform:uppercase;font-weight:700;}
.hero-body{flex:1;display:flex;align-items:center;justify-content:center;gap:4rem;
  padding:3rem 2rem;flex-wrap:wrap;}
.hero-copy{max-width:480px;}
.hero-copy h1{font-size:2.4rem;font-weight:800;color:#fff;line-height:1.2;margin-bottom:1.25rem;}
.hero-copy h1 span{color:var(--teal);}
.hero-copy p{color:#8AA8A7;font-size:1rem;line-height:1.7;margin-bottom:1.5rem;}
.hero-features{list-style:none;display:flex;flex-direction:column;gap:.7rem;}
.hero-features li{display:flex;align-items:center;gap:.6rem;color:#8AA8A7;font-size:.9rem;}
.hero-features li i{color:var(--teal);width:18px;}
.card-cadastro{background:var(--branco);border-radius:16px;padding:2.25rem;
  width:100%;max-width:440px;box-shadow:0 24px 64px rgba(0,0,0,.4);}
.card-cadastro h2{font-size:1.3rem;font-weight:800;color:var(--escuro);margin-bottom:.25rem;}
.card-cadastro .sub{color:var(--sub);font-size:.85rem;margin-bottom:1.5rem;}
.form-group{margin-bottom:1rem;}
.form-group label{display:block;font-weight:700;font-size:.72rem;color:var(--sub);
  margin-bottom:.3rem;text-transform:uppercase;letter-spacing:.5px;}
input[type=text],input[type=email],input[type=password],input[type=tel]{
  width:100%;padding:.55rem .8rem;border:1.5px solid var(--borda);border-radius:8px;
  font-size:.9rem;color:var(--texto);transition:border .18s;}
input:focus{border-color:var(--teal);outline:none;box-shadow:0 0 0 3px rgba(14,165,164,.1);}
.slug-wrap{display:flex;align-items:center;border:1.5px solid var(--borda);border-radius:8px;overflow:hidden;}
.slug-prefix{background:var(--cinza-bg);padding:.55rem .75rem;color:var(--sub);
  font-size:.82rem;white-space:nowrap;border-right:1.5px solid var(--borda);}
.slug-wrap input{border:none;border-radius:0;flex:1;}
.slug-wrap input:focus{box-shadow:none;}
.slug-suffix{background:var(--cinza-bg);padding:.55rem .75rem;color:var(--sub);
  font-size:.82rem;white-space:nowrap;border-left:1.5px solid var(--borda);}
.btn-cadastrar{width:100%;padding:.75rem;background:var(--teal);color:#fff;border:none;
  border-radius:8px;font-size:1rem;font-weight:700;cursor:pointer;margin-top:.5rem;
  transition:background .18s;}
.btn-cadastrar:hover{background:var(--teal-d);}
.trial-note{text-align:center;color:var(--sub);font-size:.78rem;margin-top:.75rem;}
.trial-note i{color:var(--success);}
.erros{background:#FDECEA;border:1px solid #FAADA7;border-radius:8px;
  padding:.75rem 1rem;margin-bottom:1rem;}
.erros p{color:var(--danger);font-size:.85rem;margin:.2rem 0;}
.ja-tem{text-align:center;margin-top:1rem;font-size:.83rem;color:var(--sub);}
.ja-tem a{color:var(--teal);font-weight:600;text-decoration:none;}
</style>
</head>
<body>
<div class="hero">
  <nav class="hero-nav">
    <div class="brand">
      <div class="brand-nome"><span class="l">Clínica</span><span class="t">SaaS</span></div>
      <div class="brand-sub">Odontologia</div>
    </div>
    <span style="color:#8AA8A7;font-size:.85rem;">Sistema completo para clínicas odontológicas</span>
  </nav>

  <div class="hero-body">
    <div class="hero-copy">
      <h1>Gestão completa para a sua <span>clínica odontológica</span></h1>
      <p>Pacientes, atendimentos, odontograma, comissões de dentistas e financeiro — tudo em um lugar. Sem instalação, sem complicação.</p>
      <ul class="hero-features">
        <li><i class="fas fa-tooth"></i> Odontograma e histórico clínico por paciente</li>
        <li><i class="fas fa-calendar-check"></i> Lançamento de atendimentos e confirmação de pagamento</li>
        <li><i class="fas fa-percentage"></i> Comissões por dentista, especialidade e rateio configuráveis</li>
        <li><i class="fas fa-chart-line"></i> Relatórios financeiros e por procedimento</li>
        <li><i class="fas fa-users"></i> Múltiplos usuários (dentistas, recepção)</li>
        <li><i class="fas fa-credit-card"></i> Taxas de cartão configuráveis por bandeira</li>
      </ul>
    </div>

    <div class="card-cadastro">
      <h2>Crie sua conta grátis</h2>
      <p class="sub">30 dias de trial. Sem cartão de crédito.</p>

      <?php if (!empty($erros)): ?>
      <div class="erros">
        <?php foreach ($erros as $e): ?>
          <p><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($e) ?></p>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <form method="POST" action="<?= LANDING_BASE_URL ?>cadastrar">
        <div class="form-group">
          <label>Seu nome</label>
          <input type="text" name="nome_responsavel" placeholder="Ex: Maria Madalena"
                 value="<?= htmlspecialchars($dados['nome_responsavel'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label>Nome da clínica</label>
          <input type="text" name="nome" placeholder="Ex: Clínica Sorriso Feliz"
                 value="<?= htmlspecialchars($dados['nome'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label>Endereço da sua clínica <small style="color:#aaa">(sem espaços ou acentos)</small></label>
          <div class="slug-wrap">
            <span class="slug-prefix">https://</span>
            <input type="text" name="slug" id="slug" placeholder="minhaclinica"
                   value="<?= htmlspecialchars($dados['slug'] ?? '') ?>"
                   pattern="[a-z0-9\-]{3,30}" required>
            <span class="slug-suffix">.<?= htmlspecialchars(defined('ROOT_DOMAIN') ? ROOT_DOMAIN : 'seudominio.com.br') ?></span>
          </div>
        </div>

        <div class="form-group">
          <label>Seu e-mail (será seu login)</label>
          <input type="email" name="email" placeholder="voce@email.com"
                 value="<?= htmlspecialchars($dados['email'] ?? '') ?>" required>
        </div>

        <div class="form-group">
          <label>Telefone <small style="color:#aaa">(opcional)</small></label>
          <input type="tel" name="telefone" placeholder="(91) 99999-9999"
                 value="<?= htmlspecialchars($dados['telefone'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label>Senha</label>
          <input type="password" name="senha" placeholder="Mínimo 6 caracteres" required>
        </div>

        <div class="form-group">
          <label>Confirmar senha</label>
          <input type="password" name="confirma_senha" placeholder="Repita a senha" required>
        </div>

        <button type="submit" class="btn-cadastrar">
          <i class="fas fa-rocket"></i> Criar minha clínica grátis
        </button>
      </form>

      <p class="trial-note"><i class="fas fa-check-circle"></i> 30 dias grátis · Cancele quando quiser</p>
      <p class="ja-tem">Já tem conta? Acesse pelo endereço da sua clínica</p>
    </div>
  </div>
</div>

<script>
const nomeInput = document.querySelector('input[name=nome]');
const slugInput = document.getElementById('slug');
let slugEditado = slugInput.value !== '';

nomeInput.addEventListener('input', () => {
  if (slugEditado) return;
  slugInput.value = nomeInput.value
    .toLowerCase()
    .normalize('NFD').replace(/[\u0300-\u036f]/g,'')
    .replace(/[^a-z0-9]+/g,'-')
    .replace(/^-+|-+$/g,'')
    .substring(0, 30);
});
slugInput.addEventListener('input', () => { slugEditado = true; });
slugInput.addEventListener('blur', () => {
  slugInput.value = slugInput.value.replace(/[^a-z0-9\-]/g,'').replace(/^-+|-+$/g,'');
});
</script>
</body>
</html>
