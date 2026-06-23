<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($empresa['nome']) ?> — Super Admin</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{--teal:#0EA5A4;--escuro:#0A1A1A;--bg:#F0F4F8;--branco:#fff;
  --texto:#1A2332;--sub:#5A6A7A;--borda:#DDE3EC;--success:#27AE60;--danger:#E74C3C;--warning:#F39C12;}
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Segoe UI',Arial,sans-serif;background:var(--bg);color:var(--texto);font-size:14px;}
nav{background:var(--escuro);height:56px;display:flex;align-items:center;
  padding:0 1.5rem;gap:1.5rem;border-bottom:2px solid #FF6B2B;}
.brand{color:#fff;font-weight:800;font-size:1.1rem;}.brand span{color:#FF6B2B;}
.badge-sa{background:#FF6B2B;color:#fff;font-size:.65rem;font-weight:700;
  padding:2px 8px;border-radius:20px;letter-spacing:.5px;}
nav a{color:#8A9DB0;text-decoration:none;font-size:.83rem;font-weight:600;
  padding:.35rem .7rem;border-radius:5px;transition:all .18s;}
nav a:hover{color:#fff;background:rgba(255,107,43,.15);}
.nav-right{margin-left:auto;display:flex;align-items:center;gap:.75rem;}
.nav-user{color:#6B7F94;font-size:.8rem;}
.btn-sair{background:rgba(255,107,43,.15);border:1px solid rgba(255,107,43,.3);
  color:#FF6B2B!important;padding:.3rem .8rem!important;border-radius:20px!important;font-size:.78rem!important;}
.container{max-width:1300px;margin:1.5rem auto;padding:0 1.5rem;}

.voltar{display:inline-flex;align-items:center;gap:6px;color:var(--sub);text-decoration:none;
  font-size:.85rem;font-weight:600;margin-bottom:1rem;}
.voltar:hover{color:var(--teal);}

.cabecalho{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:1rem;}
.cab-titulo h1{font-size:1.5rem;font-weight:800;color:var(--escuro);display:flex;align-items:center;gap:.6rem;}
.cab-titulo p{color:var(--sub);font-size:.85rem;margin-top:.2rem;}
.cab-acoes{display:flex;gap:.5rem;flex-wrap:wrap;}

.badge{padding:4px 11px;border-radius:20px;font-size:.74rem;font-weight:700;}
.badge-success{background:#D5F5E3;color:#1E7E34;}
.badge-warning{background:#FEF9E7;color:#B7770D;}
.badge-danger{background:#FDECEA;color:#C0392B;}

.btn{display:inline-flex;align-items:center;gap:5px;padding:.45rem 1rem;border-radius:6px;
  border:none;cursor:pointer;font-size:.82rem;font-weight:600;text-decoration:none;font-family:inherit;}
.btn-primary{background:var(--teal);color:#fff;}
.btn-success{background:var(--success);color:#fff;}
.btn-danger{background:var(--danger);color:#fff;}
.btn-warning{background:var(--warning);color:#fff;}
.btn-secondary{background:#4A5568;color:#fff;}
.btn-outline{background:#fff;border:1.5px solid var(--borda);color:var(--texto);}

.msg-box{background:#D5F5E3;border:1px solid #82E0AA;border-radius:6px;
  padding:.65rem 1rem;margin-bottom:1rem;color:#1E7E34;font-size:.87rem;}

.grid{display:grid;grid-template-columns:2fr 1fr;gap:1.25rem;align-items:start;}
@media(max-width:900px){.grid{grid-template-columns:1fr;}}

.card{background:var(--branco);border-radius:10px;padding:1.5rem;
  box-shadow:0 1px 8px rgba(0,0,0,.07);margin-bottom:1.25rem;}
.card h2{font-size:1rem;font-weight:700;color:var(--escuro);margin-bottom:1rem;
  display:flex;align-items:center;gap:.5rem;}

.stat-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:.85rem;margin-bottom:1.25rem;}
@media(max-width:700px){.stat-grid{grid-template-columns:repeat(2,1fr);}}
.stat-card{background:var(--branco);border-radius:10px;padding:1rem 1.1rem;
  box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid var(--teal);}
.stat-card h3{font-size:.66rem;color:var(--sub);text-transform:uppercase;letter-spacing:.6px;margin-bottom:.3rem;font-weight:700;}
.stat-card .val{font-size:1.4rem;font-weight:800;color:var(--escuro);}

.form-group{margin-bottom:1rem;}
.form-group label{display:block;font-weight:700;font-size:.72rem;color:var(--sub);
  margin-bottom:.3rem;text-transform:uppercase;letter-spacing:.5px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:.85rem;}
input[type=text],input[type=email],input[type=number],input[type=tel],select,textarea{
  width:100%;padding:.5rem .75rem;border:1.5px solid var(--borda);border-radius:6px;
  font-size:.87rem;font-family:inherit;color:var(--texto);}
input:focus,select:focus,textarea:focus{border-color:var(--teal);outline:none;box-shadow:0 0 0 3px rgba(14,165,164,.1);}
textarea{resize:vertical;min-height:80px;}

table{width:100%;border-collapse:collapse;font-size:.85rem;}
thead th{background:var(--escuro);color:#9DB0C4;padding:.55rem .75rem;font-size:.7rem;
  text-transform:uppercase;letter-spacing:.5px;text-align:left;}
tbody td{padding:.5rem .75rem;border-bottom:1px solid var(--borda);}
tbody tr:last-child td{border-bottom:none;}

.plano-atual{background:#EFFBFB;border:1px solid #B8E8E7;border-radius:8px;padding:1rem;margin-bottom:1.1rem;}
.plano-atual .linha{display:flex;justify-content:space-between;padding:.25rem 0;font-size:.85rem;}
.plano-atual .linha b{color:var(--escuro);}

.trial-alerta{background:#FEF9E7;border:1px solid #FCE9A8;border-radius:8px;padding:.75rem 1rem;
  margin-bottom:1.1rem;font-size:.83rem;color:#8A6309;display:flex;align-items:center;gap:.5rem;}
</style>
</head>
<body>
<nav>
  <div class="brand">Clínica<span>SaaS</span></div>
  <span class="badge-sa">⚡ SUPER ADMIN</span>
  <a href="<?= SA_BASE_URL ?>"><i class="fas fa-home"></i> Dashboard</a>
  <a href="<?= SA_BASE_URL ?>empresas"><i class="fas fa-clinic-medical"></i> Clínicas</a>
  <div class="nav-right">
    <span class="nav-user"><i class="fas fa-user-shield"></i> <?= htmlspecialchars($_SESSION['super_admin_nome'] ?? '') ?></span>
    <a href="<?= SA_BASE_URL ?>logout" class="nav-user btn-sair"><i class="fas fa-sign-out-alt"></i> Sair</a>
  </div>
</nav>

<div class="container">
  <a href="<?= SA_BASE_URL ?>empresas" class="voltar"><i class="fas fa-arrow-left"></i> Voltar para Clínicas</a>

  <?php if ($msg): ?>
  <div class="msg-box">
    <i class="fas fa-check-circle"></i>
    <?php
    echo match($msg) {
      'plano_atualizado' => 'Plano atualizado com sucesso.',
      'obs_salva'        => 'Observação salva.',
      'dados_salvos'      => 'Dados da clínica atualizados.',
      'sem_usuario'       => 'Essa clínica não tem nenhum usuário para entrar como suporte.',
      default            => 'Ação realizada.',
    };
    ?>
  </div>
  <?php endif; ?>

  <div class="cabecalho">
    <div class="cab-titulo">
      <h1>
        <i class="fas fa-clinic-medical" style="color:var(--teal)"></i>
        <?= htmlspecialchars($empresa['nome']) ?>
        <?php if ($empresa['status']==='ativo'): ?><span class="badge badge-success">Ativo</span>
        <?php elseif ($empresa['status']==='suspenso'): ?><span class="badge badge-warning">Suspenso</span>
        <?php else: ?><span class="badge badge-danger">Cancelado</span><?php endif; ?>
      </h1>
      <p>
        <code><?= htmlspecialchars($empresa['slug']) ?></code> ·
        Cliente desde <?= date('d/m/Y', strtotime($empresa['criado_em'])) ?>
      </p>
    </div>
    <div class="cab-acoes">
      <a href="<?= 'http://' . $empresa['slug'] . '.' . $_SERVER['HTTP_HOST'] ?>" target="_blank" class="btn btn-outline">
        <i class="fas fa-external-link-alt"></i> Ver clínica
      </a>
      <a href="<?= SA_BASE_URL ?>login-como/<?= $empresa['id'] ?>" class="btn btn-secondary"
         onclick="return confirm('Você vai entrar como o proprietário desta clínica, para fins de suporte. Continuar?')">
        <i class="fas fa-user-secret"></i> Entrar como suporte
      </a>
      <?php if ($empresa['status']==='ativo'): ?>
        <a href="<?= SA_BASE_URL ?>suspender/<?= $empresa['id'] ?>" class="btn btn-warning"
           onclick="return confirm('Suspender esta clínica?')"><i class="fas fa-pause"></i> Suspender</a>
      <?php elseif ($empresa['status']==='suspenso'): ?>
        <a href="<?= SA_BASE_URL ?>ativar/<?= $empresa['id'] ?>" class="btn btn-success">
          <i class="fas fa-play"></i> Reativar</a>
      <?php endif; ?>
    </div>
  </div>

  <div class="stat-grid">
    <div class="stat-card" style="border-left-color:var(--success);">
      <h3>Faturamento total</h3>
      <div class="val">R$ <?= number_format($empresa['faturamento_total'],2,',','.') ?></div>
    </div>
    <div class="stat-card">
      <h3>Usuários</h3>
      <div class="val"><?= $empresa['total_usuarios'] ?></div>
    </div>
    <div class="stat-card" style="border-left-color:var(--warning);">
      <h3>Pacientes</h3>
      <div class="val"><?= $empresa['total_pacientes'] ?></div>
    </div>
    <div class="stat-card" style="border-left-color:#8E44AD;">
      <h3>Atendimentos</h3>
      <div class="val"><?= $empresa['total_atendimentos'] ?></div>
    </div>
  </div>

  <div class="grid">
    <div>
      <div class="card">
        <h2><i class="fas fa-id-card" style="color:var(--teal)"></i> Dados da clínica</h2>
        <form method="POST" action="<?= SA_BASE_URL ?>editar-dados/<?= $empresa['id'] ?>">
          <div class="form-row">
            <div class="form-group">
              <label>Nome da clínica</label>
              <input type="text" name="nome" value="<?= htmlspecialchars($empresa['nome']) ?>" required>
            </div>
            <div class="form-group">
              <label>CNPJ</label>
              <input type="text" name="cnpj" value="<?= htmlspecialchars($empresa['cnpj'] ?? '') ?>">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>E-mail</label>
              <input type="email" name="email" value="<?= htmlspecialchars($empresa['email']) ?>" required>
            </div>
            <div class="form-group">
              <label>Telefone</label>
              <input type="tel" name="telefone" value="<?= htmlspecialchars($empresa['telefone'] ?? '') ?>">
            </div>
          </div>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar dados</button>
        </form>
      </div>

      <div class="card">
        <h2><i class="fas fa-users" style="color:var(--teal)"></i> Usuários (<?= count($usuarios) ?>)</h2>
        <table>
          <thead><tr><th>Nome</th><th>Login</th><th>Perfil</th><th>Desde</th></tr></thead>
          <tbody>
            <?php foreach ($usuarios as $u): ?>
            <tr>
              <td><?= htmlspecialchars($u['nome']) ?></td>
              <td><?= htmlspecialchars($u['login']) ?></td>
              <td><?= ucfirst($u['perfil']) ?></td>
              <td><?= date('d/m/Y', strtotime($u['criado_em'])) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($usuarios)): ?>
            <tr><td colspan="4" style="text-align:center;color:var(--sub);padding:1.5rem;">Nenhum usuário cadastrado.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="card">
        <h2><i class="fas fa-sticky-note" style="color:var(--teal)"></i> Observações internas</h2>
        <p style="color:var(--sub);font-size:.78rem;margin-bottom:.75rem;">Visível só para você — não aparece para a clínica.</p>
        <form method="POST" action="<?= SA_BASE_URL ?>salvar-observacao/<?= $empresa['id'] ?>">
          <div class="form-group">
            <textarea name="observacoes" placeholder="Ex: cliente pediu desconto, combinado pagamento via Pix todo dia 5..."><?= htmlspecialchars($empresa['observacoes_admin'] ?? '') ?></textarea>
          </div>
          <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Salvar observação</button>
        </form>
      </div>
    </div>

    <div>
      <div class="card">
        <h2><i class="fas fa-credit-card" style="color:var(--teal)"></i> Plano e cobrança</h2>

        <?php if ($empresa['plano']==='trial' && !empty($empresa['trial_ate'])):
          $diasRestantes = (int)((strtotime($empresa['trial_ate']) - strtotime(date('Y-m-d'))) / 86400);
        ?>
        <div class="trial-alerta">
          <i class="fas fa-hourglass-half"></i>
          <?php if ($diasRestantes >= 0): ?>
            Trial termina em <b><?= $diasRestantes ?> dia(s)</b> — <?= date('d/m/Y', strtotime($empresa['trial_ate'])) ?>
          <?php else: ?>
            <b>Trial vencido</b> desde <?= date('d/m/Y', strtotime($empresa['trial_ate'])) ?>
          <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="plano-atual">
          <div class="linha"><span>Plano atual</span><b>
            <?php echo match($empresa['plano']){'trial'=>'Trial (grátis)','basico'=>'Básico','profissional'=>'Profissional',default=>$empresa['plano']}; ?>
          </b></div>
          <div class="linha"><span>Valor mensal</span><b>R$ <?= number_format($empresa['valor_mensal'],2,',','.') ?></b></div>
        </div>

        <form method="POST" action="<?= SA_BASE_URL ?>salvar-plano/<?= $empresa['id'] ?>" id="formPlano">
          <div class="form-group">
            <label>Mudar plano para</label>
            <select name="plano" id="selectPlano" onchange="ajustaCamposPlano()">
              <option value="trial" <?= $empresa['plano']==='trial'?'selected':'' ?>>Trial (grátis, 30 dias)</option>
              <option value="basico" <?= $empresa['plano']==='basico'?'selected':'' ?>>Básico</option>
              <option value="profissional" <?= $empresa['plano']==='profissional'?'selected':'' ?>>Profissional</option>
            </select>
          </div>
          <div class="form-group" id="campoValor">
            <label>Valor mensal (R$)</label>
            <input type="number" step="0.01" min="0" name="valor_mensal" id="inputValor"
                   value="<?= $empresa['valor_mensal'] > 0 ? number_format($empresa['valor_mensal'],2,'.','') : '299.00' ?>">
          </div>
          <div class="form-group" id="campoTrialDias" style="display:none;">
            <label>Estender trial por (dias)</label>
            <input type="number" min="1" max="365" name="dias_trial" value="30">
          </div>
          <button type="submit" class="btn btn-success" style="width:100%;justify-content:center;">
            <i class="fas fa-check"></i> Confirmar mudança de plano
          </button>
        </form>
      </div>

      <div class="card">
        <h2><i class="fas fa-exclamation-triangle" style="color:var(--danger)"></i> Zona de risco</h2>
        <p style="color:var(--sub);font-size:.8rem;margin-bottom:.85rem;">Cancelar bloqueia o acesso da clínica ao sistema. Os dados continuam no banco.</p>
        <?php if ($empresa['status']!=='cancelado'): ?>
        <a href="<?= SA_BASE_URL ?>cancelar/<?= $empresa['id'] ?>" class="btn btn-danger" style="width:100%;justify-content:center;"
           onclick="return confirm('Cancelar esta clínica? O acesso dela será bloqueado.')">
          <i class="fas fa-ban"></i> Cancelar clínica
        </a>
        <?php else: ?>
        <a href="<?= SA_BASE_URL ?>ativar/<?= $empresa['id'] ?>" class="btn btn-success" style="width:100%;justify-content:center;">
          <i class="fas fa-undo"></i> Reativar clínica
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
function ajustaCamposPlano(){
  const plano = document.getElementById('selectPlano').value;
  document.getElementById('campoValor').style.display = plano==='trial' ? 'none' : 'block';
  document.getElementById('campoTrialDias').style.display = plano==='trial' ? 'block' : 'none';
}
ajustaCamposPlano();
</script>
</body>
</html>
