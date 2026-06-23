<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
        <a href="<?=BASE_URL?>relatorios/diario?data=<?=$datAnt?>" class="btn btn-secondary"><i class="fa fa-chevron-left"></i></a>
        <h2 style="margin:0;">Resumo do Dia: <?=date('d/m/Y',strtotime($data))?></h2>
        <a href="<?=BASE_URL?>relatorios/diario?data=<?=$datProx?>" class="btn btn-secondary"><i class="fa fa-chevron-right"></i></a>
    </div>

    <form method="GET" action="<?=BASE_URL?>relatorios/diario" style="display:flex;justify-content:center;margin-bottom:1.5rem;">
        <div class="form-group" style="margin:0;max-width:250px;">
            <label>Selecionar data</label>
            <input type="date" name="data" value="<?=$data?>" onchange="this.form.submit()">
        </div>
    </form>

    <?php if(is_admin()):?>
    <!-- Cartões do dia -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.85rem;margin-bottom:1.5rem;">
        <div style="background:#f8f9fa;border-radius:8px;padding:.85rem 1rem;border-left:3px solid #005b96;">
            <div style="font-size:.65rem;font-weight:700;color:#9DAEC2;text-transform:uppercase;margin-bottom:.3rem;">Entrada Bruta</div>
            <div style="font-size:1.1rem;font-weight:800;color:#005b96;">R$ <?=number_format($resumo['faturamento_bruto'],2,',','.')?></div>
        </div>
        <div style="background:#f8f9fa;border-radius:8px;padding:.85rem 1rem;border-left:3px solid #E74C3C;">
            <div style="font-size:.65rem;font-weight:700;color:#9DAEC2;text-transform:uppercase;margin-bottom:.3rem;">Taxas</div>
            <div style="font-size:1.1rem;font-weight:800;color:#E74C3C;">R$ <?=number_format($resumo['total_taxas'],2,',','.')?></div>
        </div>
        <div style="background:#f8f9fa;border-radius:8px;padding:.85rem 1rem;border-left:3px solid #E67E22;">
            <div style="font-size:.65rem;font-weight:700;color:#9DAEC2;text-transform:uppercase;margin-bottom:.3rem;">Despesas</div>
            <div style="font-size:1.1rem;font-weight:800;color:#E67E22;">R$ <?=number_format($resumo['total_despesas'],2,',','.')?></div>
        </div>
        <div style="background:#f8f9fa;border-radius:8px;padding:.85rem 1rem;border-left:3px solid #27AE60;">
            <div style="font-size:.65rem;font-weight:700;color:#9DAEC2;text-transform:uppercase;margin-bottom:.3rem;">Lucro Líquido</div>
            <div style="font-size:1.1rem;font-weight:800;color:#27AE60;">R$ <?=number_format($resumo['lucro_liquido'],2,',','.')?></div>
        </div>
    </div>

    <!-- Gráfico de formas de pagamento do dia -->
    <?php
    $pgLabels=[]; $pgVals=[]; $pgCores=['dinheiro'=>'#27AE60','pix'=>'#3498DB','debito'=>'#9B59B6','credito'=>'#E67E22'];
    foreach($dentistas as $d){ if($d['total_comissao']>0){ $pgLabels[]=htmlspecialchars($d['nome']); $pgVals[]=(float)$d['total_comissao']; } }
    ?>
    <?php if(!empty($pgVals)):?>
    <div style="background:#f8f9fa;border-radius:10px;padding:1.1rem 1.25rem;margin-bottom:1.5rem;max-width:480px;">
        <h3 style="font-size:.85rem;font-weight:700;color:#1a2332;margin-bottom:1rem;">
            <i class="fa fa-pie-chart" style="color:#8E44AD;"></i> Comissões por dentista no dia
        </h3>
        <canvas id="graficoDia" style="max-height:200px;"></canvas>
    </div>
    <script>
    new Chart(document.getElementById('graficoDia').getContext('2d'),{
        type:'doughnut',
        data:{
            labels:<?=json_encode($pgLabels)?>,
            datasets:[{data:<?=json_encode($pgVals)?>,backgroundColor:['#8E44AD','#005b96','#27AE60','#E67E22','#E74C3C'],borderWidth:2,borderColor:'#fff'}]
        },
        options:{responsive:true,maintainAspectRatio:false,cutout:'55%',
            plugins:{legend:{position:'bottom',labels:{font:{size:11},padding:8}},
            tooltip:{callbacks:{label:v=>'R$ '+v.raw.toLocaleString('pt-BR',{minimumFractionDigits:2})}}}}
    });
    </script>
    <?php endif;?>
    <?php endif;?>

    <!-- Comissões por dentista -->
    <h3 style="font-size:.88rem;font-weight:700;margin-bottom:.75rem;"><i class="fa fa-user-md"></i> Pagamentos por Dentista</h3>
    <table class="mobile-card-table" style="margin-bottom:1.5rem;">
        <thead><tr><th>Dentista</th><th style="text-align:right;">Comissão</th></tr></thead>
        <tbody>
        <?php foreach($dentistas as $d):?>
        <tr>
            <td><?=htmlspecialchars($d['nome'])?></td>
            <td style="text-align:right;color:#27AE60;font-weight:700;">R$ <?=number_format($d['total_comissao'],2,',','.')?></td>
        </tr>
        <?php endforeach;?>
        <?php if(!$dentistas):?><tr><td colspan="2" style="text-align:center;color:#b0bec5;">Nenhum atendimento.</td></tr><?php endif;?>
        </tbody>
    </table>

    <?php if(is_admin()&&$despesas):?>
    <h3 style="font-size:.88rem;font-weight:700;margin-bottom:.75rem;"><i class="fa fa-shopping-cart"></i> Despesas do Dia</h3>
    <table class="mobile-card-table">
        <thead><tr><th>Descrição</th><th>Tipo</th><th style="text-align:right;">Valor</th></tr></thead>
        <tbody>
        <?php foreach($despesas as $d):?>
        <tr><td><?=htmlspecialchars($d['descricao'])?></td><td><?=ucfirst($d['tipo'])?></td><td style="text-align:right;">R$ <?=number_format($d['valor'],2,',','.')?></td></tr>
        <?php endforeach;?>
        </tbody>
        <tfoot style="background:#f8f9fa;font-weight:700;">
            <tr><td colspan="2">Total</td><td style="text-align:right;">R$ <?=number_format($resumo['total_despesas'],2,',','.')?></td></tr>
        </tfoot>
    </table>
    <?php endif;?>
</div>
