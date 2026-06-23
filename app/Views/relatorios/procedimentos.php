<div class="card">
    <h2><i class="fa fa-medkit" style="color:#E67E22;"></i> Relatório por Procedimentos</h2>

    <form method="GET" action="<?=BASE_URL?>relatorios/procedimentos" style="display:flex;gap:1rem;align-items:flex-end;flex-wrap:wrap;margin:1rem 0 1.5rem;">
        <div class="form-group" style="margin:0;"><label>Início</label><input type="date" name="inicio" value="<?=$ini?>"></div>
        <div class="form-group" style="margin:0;"><label>Fim</label><input type="date" name="fim" value="<?=$fim?>"></div>
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>

    <?php if($dados):?>

    <!-- Gráficos lado a lado -->
    <div style="display:grid;grid-template-columns:1.4fr 1fr;gap:1rem;margin-bottom:1.5rem;">
        <div style="background:#f8f9fa;border-radius:10px;padding:1.1rem 1.25rem;">
            <h3 style="font-size:.85rem;font-weight:700;color:#1a2332;margin-bottom:1rem;">
                <i class="fa fa-bar-chart" style="color:#E67E22;"></i> Mais realizados (por quantidade)
            </h3>
            <div style="max-height:220px;">
                <canvas id="graficoProcsQtd"></canvas>
            </div>
        </div>
        <div style="background:#f8f9fa;border-radius:10px;padding:1.1rem 1.25rem;">
            <h3 style="font-size:.85rem;font-weight:700;color:#1a2332;margin-bottom:1rem;">
                <i class="fa fa-dollar" style="color:#27AE60;"></i> Por faturamento
            </h3>
            <div style="max-height:220px;">
                <canvas id="graficoProcsVal"></canvas>
            </div>
        </div>
    </div>

    <!-- Tabela -->
    <table class="mobile-card-table">
        <thead><tr>
            <th>Procedimento</th>
            <th style="text-align:center;">Qtd.</th>
            <th style="text-align:center;">%</th>
            <th style="text-align:right;">Faturamento Total</th>
        </tr></thead>
        <tbody>
        <?php foreach($dados as $p):$pct=$total>0?($p['quantidade_executada']/$total)*100:0;?>
        <tr>
            <td><strong><?=htmlspecialchars($p['procedimento_nome'])?></strong></td>
            <td style="text-align:center;color:#E67E22;font-weight:700;"><?=$p['quantidade_executada']?></td>
            <td style="text-align:center;color:#888;"><?=number_format($pct,1,',','.')?>%</td>
            <td style="text-align:right;color:#27AE60;font-weight:600;">R$ <?=number_format($p['valor_bruto_total'],2,',','.')?></td>
        </tr>
        <?php endforeach;?>
        </tbody>
        <tfoot style="background:#f8f9fa;font-weight:700;">
            <tr>
                <td>TOTAL</td>
                <td style="text-align:center;"><?=$total?></td>
                <td style="text-align:center;">100%</td>
                <td style="text-align:right;">R$ <?=number_format(array_sum(array_column($dados,'valor_bruto_total')),2,',','.')?></td>
            </tr>
        </tfoot>
    </table>

    <script>
    const top8 = <?=json_encode(array_slice($dados,0,8))?>;
    const nomes = top8.map(p=>p.procedimento_nome.length>22?p.procedimento_nome.substring(0,22)+'…':p.procedimento_nome);
    const qtds  = top8.map(p=>+p.quantidade_executada);
    const vals  = top8.map(p=>+p.valor_bruto_total);
    const cores = ['#005b96','#E67E22','#27AE60','#8E44AD','#E74C3C','#3498DB','#F39C12','#1ABC9C'];

    new Chart(document.getElementById('graficoProcsQtd').getContext('2d'),{
        type:'bar',
        data:{labels:nomes,datasets:[{label:'Qtd.',data:qtds,backgroundColor:cores.map(c=>c+'CC'),borderRadius:4}]},
        options:{indexAxis:'y',responsive:true,maintainAspectRatio:false,
            plugins:{legend:{display:false},tooltip:{callbacks:{label:v=>v.raw+' vez(es)'}}},
            scales:{x:{grid:{display:false},ticks:{font:{size:10}}},y:{grid:{color:'#f0f0f0'},ticks:{font:{size:10}}}}}
    });
    new Chart(document.getElementById('graficoProcsVal').getContext('2d'),{
        type:'doughnut',
        data:{labels:nomes,datasets:[{data:vals,backgroundColor:cores,borderWidth:2,borderColor:'#fff'}]},
        options:{responsive:true,maintainAspectRatio:false,cutout:'55%',
            plugins:{legend:{position:'bottom',labels:{font:{size:10},padding:6}},
            tooltip:{callbacks:{label:v=>'R$ '+v.raw.toLocaleString('pt-BR',{minimumFractionDigits:2})}}}}
    });
    </script>

    <?php else:?>
    <p style="color:#b0bec5;text-align:center;padding:2rem;">Nenhum dado encontrado para o período.</p>
    <?php endif;?>
</div>
