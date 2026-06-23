<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Super Admin — Sistema para Clínicas</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{--teal:#0EA5A4;--laranja:#F5A623;--escuro:#0A1A1A;--bg:#F0F4F8;--branco:#fff;
  --texto:#1A2332;--sub:#5A6A7A;--borda:#DDE3EC;--success:#27AE60;--danger:#E74C3C;--warning:#F39C12;}
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Segoe UI',Arial,sans-serif;background:var(--bg);color:var(--texto);font-size:14px;}
nav{background:var(--escuro);height:56px;display:flex;align-items:center;
  padding:0 1.5rem;gap:1.5rem;border-bottom:2px solid #FF6B2B;}
.brand{color:#fff;font-weight:800;font-size:1.1rem;}
.brand span{color:#FF6B2B;}
nav a{color:#8A9DB0;text-decoration:none;font-size:.83rem;font-weight:600;
  padding:.35rem .7rem;border-radius:5px;transition:all .18s;}
nav a:hover{color:#fff;background:rgba(255,107,43,.15);}
.nav-right{margin-left:auto;display:flex;align-items:center;gap:.75rem;}
.badge-sa{background:#FF6B2B;color:#fff;font-size:.65rem;font-weight:700;
  padding:2px 8px;border-radius:20px;letter-spacing:.5px;}
.nav-user{color:#6B7F94;font-size:.8rem;}
.btn-sair{background:rgba(255,107,43,.15);border:1px solid rgba(255,107,43,.3);
  color:#FF6B2B!important;padding:.3rem .8rem!important;border-radius:20px!important;font-size:.78rem!important;}
.container{max-width:1300px;margin:1.5rem auto;padding:0 1.5rem;}

.mrr-banner{background:linear-gradient(135deg,#0A1A1A,#0D2B2B);border-radius:12px;padding:1.5rem 1.75rem;
  margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;
  box-shadow:0 8px 24px rgba(0,0,0,.15);}
.mrr-banner .lbl{color:#8A9DB0;font-size:.75rem;text-transform:uppercase;letter-spacing:1px;font-weight:700;margin-bottom:.3rem;}
.mrr-banner .val{color:#fff;font-size:2.2rem;font-weight:800;}
.mrr-stats{display:flex;gap:1.75rem;}
.mrr-stats div{text-align:center;}
.mrr-stats .n{color:#fff;font-size:1.3rem;font-weight:800;}
.mrr-stats .l{color:#6B7F94;font-size:.7rem;text-transform:uppercase;letter-spacing:.5px;}

.stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1.5rem;}
.stat-card{background:var(--branco);border-radius:10px;padding:1.2rem 1.4rem;
  box-shadow:0 1px 8px rgba(0,0,0,.07);border-left:4px solid var(--teal);}
.stat-card h3{font-size:.68rem;color:var(--sub);text-transform:uppercase;letter-spacing:.8px;margin-bottom:.4rem;}
.stat-card .val{font-size:1.75rem;font-weight:800;color:var(--escuro);}
.stat-card.laranja{border-left-color:var(--laranja);}
.stat-card.orange{border-left-color:#FF6B2B;}

.alerta-trial{background:#FFF8E8;border:1px solid #FCE9A8;border-left:4px solid var(--warning);
  border-radius:8px;padding:1rem 1.25rem;margin-bottom:1.5rem;}
.alerta-trial h3{font-size:.9rem;color:#8A6309;display:flex;align-items:center;gap:.5rem;margin-bottom:.6rem;}
.alerta-trial ul{list-style:none;}
.alerta-trial li{display:flex;justify-content:space-between;padding:.35rem 0;font-size:.84rem;
  border-bottom:1px solid #FCE9A8;}
.alerta-trial li:last-child{border-bottom:none;}
.alerta-trial a{color:#8A4A00;text-decoration:none;font-weight:600;}
.alerta-trial a:hover{text-decoration:underline;}
.alerta-trial .dias{font-weight:700;}
.alerta-trial .vencido{color:var(--danger);}

.card{background:var(--branco);border-radius:10px;padding:1.5rem;
  box-shadow:0 1px 8px rgba(0,0,0,.07);margin-bottom:1.5rem;}
.card h2{font-size:1.05rem;font-weight:700;color:var(--escuro);margin-bottom:1rem;
  display:flex;align-items:center;gap:.5rem;}
table{width:100%;border-collapse:collapse;font-size:.87rem;}
thead th{background:var(--escuro);color:#9DB0C4;padding:.6rem .85rem;font-size:.72rem;
  text-transform:uppercase;letter-spacing:.6px;text-align:left;}
tbody td{padding:.55rem .85rem;border-bottom:1px solid var(--borda);vertical-align:middle;}
tbody tr:last-child td{border-bottom:none;}
tbody tr.clicavel{cursor:pointer;transition:background .15s;}
tbody tr.clicavel:hover td{background:#EFFBFB;}
.badge{padding:3px 9px;border-radius:20px;font-size:.72rem;font-weight:700;}
.badge-success{background:#D5F5E3;color:#1E7E34;}
.badge-warning{background:#FEF9E7;color:#B7770D;}
.badge-danger{background:#FDECEA;color:#C0392B;}
.badge-info{background:#D6EAF8;color:#1A5276;}
.badge-secondary{background:#F2F3F4;color:#5D6D7E;}
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
  <div class="mrr-banner">
    <div>
      <div class="lbl">Receita recorrente mensal (MRR)</div>
      <div class="val">R$ <?= number_format($totais['mrr'] ?? 0,2,',','.') ?></div>
    </div>
    <div class="mrr-stats">
      <div><div class="n"><?= $totais['profissional'] ?? 0 ?></div><div class="l">Pro</div></div>
      <div><div class="n"><?= $totais['basico'] ?? 0 ?></div><div class="l">Básico</div></div>
      <div><div class="n"><?= $totais['trials'] ?? 0 ?></div><div class="l">Trial</div></div>
    </div>
  </div>

  <div class="stat-grid">
    <div class="stat-card">
      <h3>Total de clínicas</h3>
      <div class="val"><?= $totais['total'] ?? 0 ?></div>
    </div>
    <div class="stat-card" style="border-left-color:var(--success);">
      <h3>Clínicas ativas</h3>
      <div class="val"><?= $totais['ativas'] ?? 0 ?></div>
    </div>
    <div class="stat-card laranja">
      <h3>Em trial</h3>
      <div class="val"><?= $totais['trials'] ?? 0 ?></div>
    </div>
    <div class="stat-card" style="border-left-color:var(--danger);">
      <h3>Suspensas</h3>
      <div class="val"><?= $totais['suspensas'] ?? 0 ?></div>
    </div>
    <div class="stat-card orange">
      <h3>Total usuários</h3>
      <div class="val"><?= $totais['total_usuarios'] ?? 0 ?></div>
    </div>
    <div class="stat-card">
      <h3>Total de atendimentos</h3>
      <div class="val"><?= $totais['total_atendimentos'] ?? 0 ?></div>
    </div>
  </div>

  <?php if (!empty($trialsVencendo)): ?>
  <div class="alerta-trial">
    <h3><i class="fas fa-hourglass-half"></i> Trials vencendo nos próximos 7 dias (<?= count($trialsVencendo) ?>)</h3>
    <ul>
      <?php foreach ($trialsVencendo as $t):
        $dias = (int)((strtotime($t['trial_ate']) - strtotime(date('Y-m-d'))) / 86400);
      ?>
      <li>
        <a href="<?= SA_BASE_URL ?>empresa/<?= $t['id'] ?>"><?= htmlspecialchars($t['nome']) ?></a>
        <span class="dias <?= $dias < 0 ? 'vencido' : '' ?>">
          <?= $dias < 0 ? 'Vencido há '.abs($dias).' dia(s)' : ($dias === 0 ? 'Vence hoje' : "Vence em $dias dia(s)") ?>
        </span>
      </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <div class="card">
    <h2><i class="fas fa-clinic-medical" style="color:var(--teal)"></i> Últimas clínicas cadastradas</h2>
    <table>
      <thead>
        <tr><th>Clínica</th><th>Slug</th><th>E-mail</th><th>Plano</th><th>Status</th><th>Cadastro</th></tr>
      </thead>
      <tbody>
        <?php foreach ($recentes as $e): ?>
        <tr class="clicavel" onclick="window.location='<?= SA_BASE_URL ?>empresa/<?= $e['id'] ?>'">
          <td><strong><?= htmlspecialchars($e['nome']) ?></strong></td>
          <td><code><?= htmlspecialchars($e['slug']) ?></code></td>
          <td><?= htmlspecialchars($e['email']) ?></td>
          <td><?= ucfirst($e['plano']) ?></td>
          <td>
            <?php if ($e['status'] === 'ativo'): ?>
              <span class="badge badge-success">Ativo</span>
            <?php elseif ($e['status'] === 'suspenso'): ?>
              <span class="badge badge-warning">Suspenso</span>
            <?php else: ?>
              <span class="badge badge-danger">Cancelado</span>
            <?php endif; ?>
          </td>
          <td><?= date('d/m/Y', strtotime($e['criado_em'])) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <div style="margin-top:1rem;">
      <a href="<?= SA_BASE_URL ?>empresas" style="color:var(--teal);font-size:.85rem;font-weight:600;text-decoration:none;">
        Ver todas as clínicas →
      </a>
    </div>
  </div>
</div>
</body>
</html>
