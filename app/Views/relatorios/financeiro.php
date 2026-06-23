<div class="card">
    <h2><i class="fa fa-line-chart" style="color:#27AE60;"></i> Relatório Financeiro Geral</h2>

    <form method="GET" action="<?=BASE_URL?>relatorios/financeiro" style="display:flex;gap:1rem;align-items:flex-end;flex-wrap:wrap;margin:1rem 0 1.5rem;">
        <div class="form-group" style="margin:0;"><label>Início</label><input type="date" name="inicio" value="<?=$ini?>"></div>
        <div class="form-group" style="margin:0;"><label>Fim</label><input type="date" name="fim" value="<?=$fim?>"></div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>

    <!-- Cartões do período -->
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:.85rem;margin-bottom:1.5rem;">
    <?php
    $cc=[
        ['Faturamento Bruto',$totais['bruto'],'#005b96'],
        ['Taxas de Cartão',$totais['taxa']??0,'#E67E22'],
        ['Comissões',$totais['comissao']??0,'#8E44AD'],
        ['Despesas',$totalDespesas,'#E74C3C'],
        ['Resultado Líquido',$totais['liquido']-$totalDespesas,'#27AE60'],
    ];
    foreach($cc as [$l,$v,$c]):?>
    <div style="background:#f8f9fa;border-radius:8px;padding:.85rem 1rem;border-left:3px solid <?=$c?>;">
        <div style="font-size:.65rem;font-weight:700;color:#9DAEC2;text-transform:uppercase;margin-bottom:.3rem;"><?=$l?></div>
        <div style="font-size:1.05rem;font-weight:800;color:<?=$v<0?'#E74C3C':$c?>;white-space:nowrap;">R$ <?=number_format(abs($v),2,',','.')?></div>
    </div>
    <?php endforeach;?>
    </div>

    <!-- Gráficos -->
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1rem;margin-bottom:1.5rem;">
        <div style="background:#f8f9fa;border-radius:10px;padding:1.1rem 1.25rem;">
            <h3 style="font-size:.85rem;font-weight:700;color:#1a2332;margin-bottom:1rem;">
                <i class="fa fa-area-chart" style="color:#27AE60;"></i> Faturamento × Despesas × Lucro
            </h3>
            <canvas id="graficoFinanceiro" style="max-height:220px;"></canvas>
        </div>
        <div style="background:#f8f9fa;border-radius:10px;padding:1.1rem 1.25rem;">
            <h3 style="font-size:.85rem;font-weight:700;color:#1a2332;margin-bottom:1rem;">
                <i class="fa fa-credit-card" style="color:#3498DB;"></i> Formas de pagamento
            </h3>
            <?php if(!empty($pagLabels)):?>
            <canvas id="graficoPag" style="max-height:220px;"></canvas>
            <?php else:?>
            <p style="color:#b0bec5;font-size:.83rem;text-align:center;padding-top:2rem;">Sem dados.</p>
            <?php endif;?>
        </div>
    </div>

    <!-- Tabela de atendimentos -->
    <h3 style="font-size:.88rem;font-weight:700;margin-bottom:.75rem;">Atendimentos</h3>
    <table class="mobile-card-table">
        <thead><tr><th>Data</th><th>Paciente</th><th>Procedimentos</th><th style="text-align:right;">Bruto</th><th style="text-align:right;">Líquido</th></tr></thead>
        <tbody>
        <?php foreach($atendimentos as $at):?>
        <tr>
            <td><?=date('d/m/Y',strtotime($at['data_atendimento']))?></td>
            <td><?=htmlspecialchars($at['paciente']??'')?></td>
            <td style="font-size:.82rem;color:#666;"><?=htmlspecialchars($at['procedimentos']??'')?></td>
            <td style="text-align:right;">R$ <?=number_format($at['valor_bruto'],2,',','.')?></td>
            <td style="text-align:right;color:#27AE60;font-weight:600;">R$ <?=number_format($at['valor_liquido_clinica'],2,',','.')?></td>
        </tr>
        <?php endforeach;?>
        <?php if(!$atendimentos):?><tr><td colspan="5" style="text-align:center;color:#b0bec5;padding:1.5rem;">Nenhum atendimento.</td></tr><?php endif;?>
        </tbody>
    </table>
    <?php if($totalPagAt>1):?>
    <div style="display:flex;justify-content:flex-end;gap:.4rem;margin-top:.75rem;">
        <?php for($i=1;$i<=$totalPagAt;$i++):$q=array_merge($_GET,['pagina_at'=>$i]);?>
        <a href="?<?=http_build_query($q)?>" class="btn <?=$i===$pg?'btn-primary':'btn-secondary'?>" style="padding:.25rem .6rem;font-size:.8rem;"><?=$i?></a>
        <?php endfor;?>
    </div>
    <?php endif;?>
</div>

<script>
new Chart(document.getElementById('graficoFinanceiro').getContext('2d'),{
    type:'line',
    data:{
        labels:<?=json_encode($labels)?>,
        datasets:[
            {label:'Faturamento',data:<?=json_encode($fat)?>,borderColor:'#005b96',backgroundColor:'rgba(0,91,150,.08)',tension:.3,fill:true,pointRadius:2,borderWidth:2},
            {label:'Despesas',data:<?=json_encode($desp)?>,borderColor:'#E74C3C',backgroundColor:'rgba(231,76,60,.06)',tension:.3,fill:true,pointRadius:2,borderWidth:2},
            {label:'Lucro',data:<?=json_encode($lucro)?>,borderColor:'#27AE60',backgroundColor:'rgba(39,174,96,.08)',tension:.3,fill:true,pointRadius:2,borderWidth:2}
        ]
    },
    options:{responsive:true,maintainAspectRatio:false,
        plugins:{legend:{position:'top',labels:{font:{size:10},padding:8}},
        tooltip:{callbacks:{label:v=>'R$ '+v.raw.toLocaleString('pt-BR',{minimumFractionDigits:2})}}},
        scales:{y:{ticks:{callback:v=>'R$ '+v.toLocaleString('pt-BR'),font:{size:10}},grid:{color:'#f0f0f0'}},x:{grid:{display:false},ticks:{font:{size:10},maxRotation:45}}}}
});

<?php if(!empty($pagLabels)):?>
new Chart(document.getElementById('graficoPag').getContext('2d'),{
    type:'doughnut',
    data:{
        labels:<?=json_encode($pagLabels)?>,
        datasets:[{data:<?=json_encode($pagData)?>,backgroundColor:['#27AE60','#3498DB','#9B59B6','#E67E22'],borderWidth:2,borderColor:'#fff'}]
    },
    options:{responsive:true,maintainAspectRatio:false,cutout:'55%',
        plugins:{legend:{position:'bottom',labels:{font:{size:10},padding:6}},
        tooltip:{callbacks:{label:v=>'R$ '+v.raw.toLocaleString('pt-BR',{minimumFractionDigits:2})}}}}
});
<?php endif;?>
</script>
