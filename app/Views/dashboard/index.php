<?php $fmt=fn($v)=>'R$ '.number_format($v,2,',','.'); ?>

<!-- Cabeçalho -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.1rem;flex-wrap:wrap;gap:.5rem;">
    <h1 style="font-size:1.25rem;font-weight:800;color:#1a2332;margin:0;">
        Dashboard — <?=htmlspecialchars($mesAtual)?>
    </h1>
    <div style="display:flex;align-items:center;gap:.4rem;">
        <a href="?mes=<?=$mesAnt?>" class="btn btn-secondary" style="padding:.3rem .7rem;"><i class="fa fa-chevron-left"></i></a>
        <span style="font-size:.85rem;font-weight:600;min-width:120px;text-align:center;"><?=htmlspecialchars($mesAtual)?></span>
        <a href="?mes=<?=$mesProx?>" class="btn btn-secondary" style="padding:.3rem .7rem;"><i class="fa fa-chevron-right"></i></a>
    </div>
</div>

<!-- Aviso de pendentes -->
<?php if($totalPendentes>0):?>
<div style="background:#FFF8E7;border:1px solid #FCE9A8;border-left:4px solid #F39C12;border-radius:8px;
  padding:.7rem 1rem;margin-bottom:1rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
    <span style="color:#8A6309;font-weight:600;font-size:.88rem;">
        <i class="fa fa-clock-o"></i>
        <?=$totalPendentes?> atendimento(s) aguardando pagamento — Total: <strong><?=$fmt($valorPendente)?></strong>
    </span>
    <a href="<?=BASE_URL?>atendimentos/confirmarPagamento" class="btn btn-warning" style="font-size:.8rem;padding:.3rem .8rem;">
        <i class="fa fa-check"></i> Confirmar
    </a>
</div>
<?php endif;?>

<!-- Cartões resumo -->
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:.85rem;margin-bottom:1.1rem;">
<?php
$cards=[
    ['FATURAMENTO BRUTO', $faturamentoBruto, '#005b96', $totalAtendimentos.' atend.'],
    ['TAXAS DE CARTÃO',   $totalTaxas,       '#E67E22', number_format($faturamentoBruto>0?($totalTaxas/$faturamentoBruto)*100:0,1).'% do bruto'],
    ['COMISSÕES',         $totalComissoes,   '#8E44AD', number_format($faturamentoBruto>0?($totalComissoes/$faturamentoBruto)*100:0,1).'% do bruto'],
    ['DESPESAS',          $totalDespesas,    '#E74C3C', 'Saídas no período'],
    ['RESULTADO LÍQUIDO', $lucroLiquido-$totalDespesas, '#27AE60', 'Após tudo'],
];
foreach($cards as [$lbl,$val,$cor,$sub]):?>
<div style="background:#fff;border-radius:10px;padding:1rem 1.1rem;box-shadow:0 1px 6px rgba(0,0,0,.07);border-top:3px solid <?=$cor?>;">
    <div style="font-size:.65rem;font-weight:700;color:#9DAEC2;text-transform:uppercase;letter-spacing:.5px;margin-bottom:.4rem;"><?=$lbl?></div>
    <div style="font-size:1.2rem;font-weight:800;color:<?=$val<0?'#E74C3C':$cor?>;white-space:nowrap;"><?=$fmt($val)?></div>
    <div style="font-size:.72rem;color:#b0bec5;margin-top:.2rem;"><?=$sub?></div>
</div>
<?php endforeach;?>
</div>

<!-- Últimos atendimentos -->
<div style="background:#fff;border-radius:10px;padding:1.1rem 1.25rem;box-shadow:0 1px 6px rgba(0,0,0,.07);margin-bottom:1rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.85rem;">
        <h3 style="font-size:.88rem;font-weight:700;color:#1a2332;margin:0;display:flex;align-items:center;gap:.4rem;">
            <i class="fa fa-history" style="color:#005b96;"></i> Últimos atendimentos
        </h3>
        <div style="display:flex;gap:.75rem;">
            <a href="<?=BASE_URL?>atendimentos" style="font-size:.78rem;color:#005b96;text-decoration:none;font-weight:600;">+ Novo lançamento</a>
            <a href="<?=BASE_URL?>atendimentos/confirmarPagamento" style="font-size:.78rem;color:#E67E22;text-decoration:none;font-weight:600;">Ver pendentes →</a>
        </div>
    </div>
    <?php if($ultimosAtendimentos):?>
    <table style="width:100%;font-size:.82rem;border-collapse:collapse;">
        <thead><tr>
            <th style="padding:.35rem .5rem;text-align:left;color:#9DAEC2;font-size:.68rem;text-transform:uppercase;border-bottom:1px solid #f0f4f8;">Data</th>
            <th style="padding:.35rem .5rem;text-align:left;color:#9DAEC2;font-size:.68rem;text-transform:uppercase;border-bottom:1px solid #f0f4f8;">Paciente</th>
            <th style="padding:.35rem .5rem;text-align:left;color:#9DAEC2;font-size:.68rem;text-transform:uppercase;border-bottom:1px solid #f0f4f8;">Dentista</th>
            <th style="padding:.35rem .5rem;text-align:right;color:#9DAEC2;font-size:.68rem;text-transform:uppercase;border-bottom:1px solid #f0f4f8;">Valor</th>
            <th style="padding:.35rem .5rem;text-align:center;color:#9DAEC2;font-size:.68rem;text-transform:uppercase;border-bottom:1px solid #f0f4f8;">Status</th>
        </tr></thead>
        <tbody>
        <?php foreach($ultimosAtendimentos as $at):
            $pago=$at['status_pagamento']==='pago';?>
        <tr style="border-bottom:1px solid #f8f9fa;">
            <td style="padding:.4rem .5rem;color:#9DAEC2;"><?=date('d/m/Y',strtotime($at['data_atendimento']))?></td>
            <td style="padding:.4rem .5rem;font-weight:600;"><?=htmlspecialchars($at['paciente_nome'])?></td>
            <td style="padding:.4rem .5rem;color:#555;"><?=htmlspecialchars($at['dentista'])?></td>
            <td style="padding:.4rem .5rem;text-align:right;font-weight:600;"><?=$fmt($at['valor_bruto']??0)?></td>
            <td style="padding:.4rem .5rem;text-align:center;">
                <span style="padding:2px 9px;border-radius:20px;font-size:.68rem;font-weight:700;
                background:<?=$pago?'#D5F5E3':'#FEF9E7'?>;color:<?=$pago?'#1E7E34':'#B7770D'?>;">
                    <?=$pago?'Pago':'Pendente'?>
                </span>
            </td>
        </tr>
        <?php endforeach;?>
        </tbody>
    </table>
    <?php else:?>
    <div style="height:70px;display:flex;align-items:center;justify-content:center;color:#b0bec5;font-size:.85rem;">
        Nenhum atendimento registrado.
    </div>
    <?php endif;?>
</div>



