<div class="card">
    <h2><i class="fa fa-user-md" style="color:#8E44AD;"></i> Relatório por Dentista</h2>

    <form method="GET" action="<?=BASE_URL?>relatorios/dentistas" style="display:flex;gap:1rem;align-items:flex-end;flex-wrap:wrap;margin:1rem 0 1.5rem;">
        <div class="form-group" style="margin:0;">
            <label>Mês</label>
            <input type="month" name="mes" value="<?=$mes?>">
        </div>
        <?php if(is_admin()):?>
        <div class="form-group" style="margin:0;">
            <label>Dentista</label>
            <select name="dentista_id">
                <option value="todos">Todos</option>
                <?php foreach($allDent as $d):?>
                <option value="<?=$d['id']?>" <?=$did==$d['id']?'selected':''?>><?=htmlspecialchars($d['nome'])?></option>
                <?php endforeach;?>
            </select>
        </div>
        <?php endif;?>
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>

    <?php if($relatorio):?>

    <!-- Gráfico de barras: faturamento por dentista -->
    <div style="background:#f8f9fa;border-radius:10px;padding:1.1rem 1.25rem;margin-bottom:1.5rem;">
        <h3 style="font-size:.85rem;font-weight:700;color:#1a2332;margin-bottom:1rem;">
            <i class="fa fa-bar-chart" style="color:#8E44AD;"></i> Comparativo do período
        </h3>
        <div style="max-height:260px;">
            <canvas id="graficoDentistas"></canvas>
        </div>
    </div>

    <!-- Tabela -->
    <table class="mobile-card-table">
        <thead>
            <tr>
                <th>Dentista</th>
                <th style="text-align:center;">Atendimentos</th>
                <th style="text-align:right;">Faturamento Bruto</th>
                <th style="text-align:right;">Comissão Dentista</th>
                <th style="text-align:right;">Valor p/ Clínica</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach($relatorio as $r):?>
        <tr>
            <td><strong><?=htmlspecialchars($r['dentista_nome'])?></strong></td>
            <td style="text-align:center;color:#005b96;font-weight:700;"><?=$r['total_atendimentos']?></td>
            <td style="text-align:right;">R$ <?=number_format($r['faturamento_bruto'],2,',','.')?></td>
            <td style="text-align:right;color:#8E44AD;font-weight:600;">R$ <?=number_format($r['valor_para_dentista'],2,',','.')?></td>
            <td style="text-align:right;color:#27AE60;font-weight:600;">R$ <?=number_format($r['valor_para_clinica'],2,',','.')?></td>
        </tr>
        <?php endforeach;?>
        </tbody>
        <?php if(count($relatorio)>1):
            $totalFat=array_sum(array_column($relatorio,'faturamento_bruto'));
            $totalDent=array_sum(array_column($relatorio,'valor_para_dentista'));
            $totalClin=array_sum(array_column($relatorio,'valor_para_clinica'));
        ?>
        <tfoot style="background:#f8f9fa;font-weight:700;">
            <tr>
                <td>TOTAL</td>
                <td style="text-align:center;"><?=array_sum(array_column($relatorio,'total_atendimentos'))?></td>
                <td style="text-align:right;">R$ <?=number_format($totalFat,2,',','.')?></td>
                <td style="text-align:right;color:#8E44AD;">R$ <?=number_format($totalDent,2,',','.')?></td>
                <td style="text-align:right;color:#27AE60;">R$ <?=number_format($totalClin,2,',','.')?></td>
            </tr>
        </tfoot>
        <?php endif;?>
    </table>

    <script>
    new Chart(document.getElementById('graficoDentistas').getContext('2d'),{
        type:'bar',
        data:{
            labels:<?=json_encode(array_column($relatorio,'dentista_nome'))?>,
            datasets:[
                {label:'Faturamento Bruto',data:<?=json_encode(array_map(fn($r)=>(float)$r['faturamento_bruto'],$relatorio))?>,backgroundColor:'rgba(0,91,150,.7)',borderRadius:5},
                {label:'Comissão Dentista',data:<?=json_encode(array_map(fn($r)=>(float)$r['valor_para_dentista'],$relatorio))?>,backgroundColor:'rgba(142,68,173,.7)',borderRadius:5},
                {label:'Valor p/ Clínica',data:<?=json_encode(array_map(fn($r)=>(float)$r['valor_para_clinica'],$relatorio))?>,backgroundColor:'rgba(39,174,96,.7)',borderRadius:5}
            ]
        },
        options:{responsive:true,maintainAspectRatio:false,
            plugins:{legend:{position:'top',labels:{font:{size:11}}},
            tooltip:{callbacks:{label:v=>'R$ '+v.raw.toLocaleString('pt-BR',{minimumFractionDigits:2})}}},
            scales:{y:{ticks:{callback:v=>'R$ '+v.toLocaleString('pt-BR'),font:{size:10}},grid:{color:'#f0f0f0'}},x:{grid:{display:false},ticks:{font:{size:11}}}}}
    });
    </script>

    <?php else:?>
    <p style="color:#b0bec5;text-align:center;padding:2rem;">Nenhum dado encontrado para o período.</p>
    <?php endif;?>
</div>
