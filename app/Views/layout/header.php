<?php
function isActive($u):bool{$uri=$_SERVER['REQUEST_URI'];foreach((array)$u as $x){if(str_contains($uri,$x))return true;}return false;}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Clínica Odontológica</title>
    <link rel="stylesheet" href="<?=BASE_URL?>public/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<header class="navbar">
    <div class="logo"><a href="<?=BASE_URL?>dashboard" style="text-decoration:none;color:inherit;">🦷 Clínica Odontológica</a></div>
    <?php if(is_logado()):?>
    <div class="menu-toggle" id="mobile-menu"><span></span><span></span><span></span></div>
    <?php endif;?>
    <nav class="menu" id="navbar-menu">
    <?php if(is_logado()):?>
        <a href="<?=BASE_URL?>dashboard" class="<?=isActive('dashboard')?'active':''?>"><i class="fa fa-home"></i> Dashboard</a>
        <div class="dropdown"><a href="javascript:void(0)" class="<?=isActive(['atendimentos'])?'active':''?>"><i class="fa fa-stethoscope"></i> Atendimentos <small>▾</small></a>
            <div class="dropdown-content">
                <a href="<?=BASE_URL?>atendimentos"><i class="fa fa-plus-circle"></i> Novo Lançamento</a>
                <a href="<?=BASE_URL?>atendimentos/confirmarPagamento"><i class="fa fa-check-circle"></i> Confirmar Pagamento</a>
            </div>
        </div>
        <?php if(is_admin()||is_dentista()||is_recepcionista()):?>
        <div class="dropdown"><a href="javascript:void(0)"><i class="fa fa-address-book"></i> Cadastros <small>▾</small></a>
            <div class="dropdown-content">
                <a href="<?=BASE_URL?>pacientes"><i class="fa fa-user"></i> Pacientes</a>
                <?php if(is_admin()):?>
                <a href="<?=BASE_URL?>procedimentos"><i class="fa fa-medkit"></i> Procedimentos</a>
                <a href="<?=BASE_URL?>despesas"><i class="fa fa-money"></i> Despesas</a>
                <a href="<?=BASE_URL?>usuarios"><i class="fa fa-users"></i> Usuários</a>
                <?php endif;?>
            </div>
        </div>
        <?php endif;?>
        <div class="dropdown"><a href="javascript:void(0)" class="<?=isActive('relatorios')?'active':''?>"><i class="fa fa-bar-chart"></i> Relatórios <small>▾</small></a>
            <div class="dropdown-content">
                <a href="<?=BASE_URL?>relatorios/diario"><i class="fa fa-calendar"></i> Resumo Diário</a>
                <?php if(is_admin()||is_dentista()):?>
                <a href="<?=BASE_URL?>relatorios/dentistas"><i class="fa fa-user-md"></i> Por Dentista</a>
                <a href="<?=BASE_URL?>relatorios/paciente"><i class="fa fa-file-text-o"></i> Por Paciente</a>
                <?php endif;?>
                <?php if(is_admin()):?>
                <a href="<?=BASE_URL?>relatorios/financeiro"><i class="fa fa-line-chart"></i> Financeiro Geral</a>
                <a href="<?=BASE_URL?>relatorios/rateio"><i class="fa fa-pie-chart"></i> Rateio entre Dentistas</a>
                <a href="<?=BASE_URL?>relatorios/procedimentos"><i class="fa fa-list-alt"></i> Por Procedimento</a>
                <?php endif;?>
            </div>
        </div>
        <?php if(is_admin()):?>
        <div class="dropdown"><a href="javascript:void(0)" class="<?=isActive('admin')?'active':''?>"><i class="fa fa-university"></i> Financeiro <small>▾</small></a>
            <div class="dropdown-content">
                <a href="<?=BASE_URL?>admin/taxas"><i class="fa fa-credit-card"></i> Taxas de Cartão</a>
                <a href="<?=BASE_URL?>admin/comissoes"><i class="fa fa-percent"></i> Comissões</a>
                <a href="<?=BASE_URL?>admin/rateio"><i class="fa fa-share-alt"></i> Rateio</a>
            </div>
        </div>
        <?php endif;?>
        <a href="<?=BASE_URL?>configuracoes" class="<?=isActive('configuracoes')?'active':''?>"><i class="fa fa-user-circle"></i> Minha Conta</a>
    <?php endif;?>
    </nav>
    <?php if(is_logado()):?>
    <div class="user-menu"><span><i class="fa fa-user-circle"></i> Olá, <?=htmlspecialchars($_SESSION['usuario_nome'])?></span><a href="<?=BASE_URL?>logout" class="btn btn-secondary"><i class="fa fa-sign-out"></i> Sair</a></div>
    <?php endif;?>
</header>
<script>
document.addEventListener('DOMContentLoaded',function(){
    var t=document.getElementById('mobile-menu'),n=document.getElementById('navbar-menu');
    if(t&&n)t.addEventListener('click',()=>n.classList.toggle('active'));
    document.querySelectorAll('.dropdown').forEach(d=>{
        d.querySelector('a').addEventListener('click',e=>{
            if(window.innerWidth<=768){e.preventDefault();var c=d.querySelector('.dropdown-content');
            document.querySelectorAll('.dropdown-content').forEach(x=>{if(x!==c)x.style.display='none';});
            c.style.display=c.style.display==='block'?'none':'block';}
        });
    });
});
</script>
<main class="container">
