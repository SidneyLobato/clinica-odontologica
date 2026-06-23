<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Clínicas — Super Admin</title>
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
.card{background:var(--branco);border-radius:10px;padding:1.5rem;
  box-shadow:0 1px 8px rgba(0,0,0,.07);margin-bottom:1.5rem;}
.card-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;}
.card-head h2{font-size:1.05rem;font-weight:700;color:var(--escuro);display:flex;align-items:center;gap:.5rem;}
.busca-form{display:flex;gap:.5rem;}
.busca-form input{padding:.4rem .75rem;border:1.5px solid var(--borda);border-radius:6px;font-size:.85rem;width:260px;}
.busca-form input:focus{border-color:var(--teal);outline:none;}
.busca-form button{padding:.4rem .9rem;background:var(--teal);color:#fff;border:none;
  border-radius:6px;font-size:.85rem;font-weight:600;cursor:pointer;}
table{width:100%;border-collapse:collapse;font-size:.87rem;}
thead th{background:var(--escuro);color:#9DB0C4;padding:.6rem .85rem;font-size:.72rem;
  text-transform:uppercase;letter-spacing:.6px;text-align:left;}
tbody td{padding:.55rem .85rem;border-bottom:1px solid var(--borda);vertical-align:middle;}
tbody tr:last-child td{border-bottom:none;}
tbody tr:hover td{background:#EFFBFB;}
.badge{padding:3px 9px;border-radius:20px;font-size:.72rem;font-weight:700;}
.badge-success{background:#D5F5E3;color:#1E7E34;}
.badge-warning{background:#FEF9E7;color:#B7770D;}
.badge-danger{background:#FDECEA;color:#C0392B;}
.badge-info{background:#D6EAF8;color:#1A5276;}
.badge-secondary{background:#F2F3F4;color:#5D6D7E;}
.btn{display:inline-flex;align-items:center;gap:4px;padding:.28rem .65rem;
  border-radius:5px;border:none;cursor:pointer;font-size:.78rem;font-weight:600;text-decoration:none;}
.btn-danger{background:var(--danger);color:#fff;}
.btn-success{background:var(--success);color:#fff;}
.btn-warning{background:var(--warning);color:#fff;}
.btn-info{background:#2980B9;color:#fff;}
.msg-box{background:#D5F5E3;border:1px solid #82E0AA;border-radius:6px;
  padding:.65rem 1rem;margin-bottom:1rem;color:#1E7E34;font-size:.87rem;}
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
  <?php if (isset($_GET['msg'])): ?>
  <div class="msg-box">
    <i class="fas fa-check-circle"></i>
    <?php
    echo match($_GET['msg']) {
      'suspenso'  => 'Clínica suspensa com sucesso.',
      'ativado'   => 'Clínica ativada com sucesso.',
      'cancelado' => 'Clínica cancelada.',
      default     => 'Ação realizada.',
    };
    ?>
  </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-head">
      <h2><i class="fas fa-clinic-medical" style="color:var(--teal)"></i> Clínicas cadastradas (<?= count($empresas) ?>)</h2>
      <form class="busca-form" method="GET" action="<?= SA_BASE_URL ?>empresas">
        <input type="text" name="busca" placeholder="Buscar por nome, slug ou e-mail..."
               value="<?= htmlspecialchars($busca ?? '') ?>">
        <button type="submit"><i class="fas fa-search"></i> Buscar</button>
      </form>
    </div>

    <table>
      <thead>
        <tr>
          <th>#</th><th>Clínica</th><th>Slug / URL</th><th>E-mail</th><th>Plano</th>
          <th>Trial até</th><th>Usuários</th><th>Pacientes</th><th>Status</th><th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($empresas as $e): ?>
        <tr>
          <td><?= $e['id'] ?></td>
          <td><strong><?= htmlspecialchars($e['nome']) ?></strong></td>
          <td>
            <code style="font-size:.8rem"><?= htmlspecialchars($e['slug']) ?></code>
            <a href="<?= 'http://' . $e['slug'] . '.' . $_SERVER['HTTP_HOST'] ?>"
               target="_blank" style="margin-left:4px;color:var(--teal);font-size:.75rem;">
              <i class="fas fa-external-link-alt"></i>
            </a>
          </td>
          <td><?= htmlspecialchars($e['email']) ?></td>
          <td>
            <?php if ($e['plano'] === 'trial'): ?>
              <span class="badge badge-warning">Trial</span>
            <?php elseif ($e['plano'] === 'profissional'): ?>
              <span class="badge badge-info">Pro</span>
              <div style="font-size:.72rem;color:var(--sub);margin-top:2px;">R$ <?= number_format($e['valor_mensal'],2,',','.') ?>/mês</div>
            <?php else: ?>
              <span class="badge badge-secondary">Básico</span>
              <div style="font-size:.72rem;color:var(--sub);margin-top:2px;">R$ <?= number_format($e['valor_mensal'],2,',','.') ?>/mês</div>
            <?php endif; ?>
          </td>
          <td><?= $e['trial_ate'] ? date('d/m/Y', strtotime($e['trial_ate'])) : '—' ?></td>
          <td><?= $e['total_usuarios'] ?></td>
          <td><?= $e['total_pacientes'] ?></td>
          <td>
            <?php if ($e['status'] === 'ativo'): ?>
              <span class="badge badge-success">Ativo</span>
            <?php elseif ($e['status'] === 'suspenso'): ?>
              <span class="badge badge-warning">Suspenso</span>
            <?php else: ?>
              <span class="badge badge-danger">Cancelado</span>
            <?php endif; ?>
          </td>
          <td style="white-space:nowrap;display:flex;gap:4px;">
            <a href="<?= SA_BASE_URL ?>empresa/<?= $e['id'] ?>" class="btn btn-info">
              <i class="fas fa-cog"></i> Gerenciar</a>
            <?php if ($e['status'] === 'ativo'): ?>
              <a href="<?= SA_BASE_URL ?>suspender/<?= $e['id'] ?>"
                 onclick="return confirm('Suspender esta clínica?')"
                 class="btn btn-warning"><i class="fas fa-pause"></i></a>
            <?php elseif ($e['status'] === 'suspenso'): ?>
              <a href="<?= SA_BASE_URL ?>ativar/<?= $e['id'] ?>" class="btn btn-success">
                <i class="fas fa-play"></i></a>
            <?php endif; ?>
            <?php if ($e['status'] !== 'cancelado'): ?>
              <a href="<?= SA_BASE_URL ?>cancelar/<?= $e['id'] ?>"
                 onclick="return confirm('Cancelar permanentemente esta clínica?')"
                 class="btn btn-danger"><i class="fas fa-times"></i></a>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($empresas)): ?>
        <tr><td colspan="10" style="text-align:center;color:var(--sub);padding:2rem;">
          Nenhuma clínica encontrada.
        </td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
