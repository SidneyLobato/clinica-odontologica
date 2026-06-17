<div id="toast" class="toast"></div>
<div class="card"><h2>Confirmar Pagamento</h2>
<div class="form-group"><label>Atendimento Pendente</label>
<select id="sel_atend" style="width:100%;padding:8px;">
    <option value="">Selecione um atendimento pendente...</option>
    <?php foreach($pendentes as $at):?>
    <option value="<?=$at['id']?>" data-total="<?=$at['valor_total']?>">
        #<?=$at['id']?> — <?=htmlspecialchars($at['paciente_nome'])?> — R$ <?=number_format($at['valor_total'],2,',','.')?> — <?=date('d/m/Y',strtotime($at['data_atendimento']))?>
    </option>
    <?php endforeach;?>
</select></div>
<div id="form_pag" style="display:none;">
<h3>Formas de Pagamento</h3>
<div id="pag_container"></div>
<button type="button" id="add_pag" class="btn btn-info">+ Adicionar</button>
<div style="margin-top:.75rem;"><p>Pago: <strong id="tot_pago">R$ 0,00</strong> | Restante: <strong id="restante" style="color:red;">R$ 0,00</strong></p></div>
<button type="button" id="btn_confirmar" class="btn btn-success" style="width:100%;margin-top:1rem;">Confirmar Pagamento</button>
</div></div>
<style>.toast{position:fixed;top:20px;right:20px;padding:15px 20px;border-radius:5px;color:#fff;z-index:9999;opacity:0;visibility:hidden;transition:opacity .5s,visibility .5s,transform .5s;transform:translateX(100%);}.toast.show{opacity:1;visibility:visible;transform:translateX(0);}.toast.error{background:#c0392b;}.toast.success{background:#27ae60;}.pag-row{display:flex;gap:.5rem;margin-bottom:.5rem;flex-wrap:wrap;}</style>
<script>
const BASE=<?=json_encode(BASE_URL)?>,bands=<?=json_encode($bandeiras)?>;
let atId=0,total=0;
const toast=document.getElementById('toast');
function showToast(m,t){toast.textContent=m;toast.className='toast show '+t;setTimeout(()=>toast.className='toast',4000);}
document.getElementById('sel_atend').addEventListener('change',function(){
    atId=parseInt(this.value)||0;total=parseFloat(this.options[this.selectedIndex]?.dataset?.total)||0;
    document.getElementById('form_pag').style.display=atId?'block':'none';
    document.getElementById('pag_container').innerHTML='';criarLinhaPag();updatePag();
});
function criarLinhaPag(){
    const row=document.createElement('div');row.className='pag-row';
    const sf=document.createElement('select');['dinheiro','pix','debito','credito'].forEach(f=>{const o=document.createElement('option');o.value=f;o.textContent=f.charAt(0).toUpperCase()+f.slice(1);sf.appendChild(o);});
    const val=document.createElement('input');val.type='number';val.step='0.01';val.placeholder='Valor';val.style.width='100px';
    const sb=document.createElement('select');sb.style.display='none';
    const bo=document.createElement('option');bo.value='';bo.textContent='Bandeira...';sb.appendChild(bo);
    bands.forEach(b=>{const o=document.createElement('option');o.value=b.id;o.textContent=b.nome;sb.appendChild(o);});
    const sp=document.createElement('select');sp.style.display='none';
    for(let i=1;i<=10;i++){const o=document.createElement('option');o.value=i;o.textContent=i+'x';sp.appendChild(o);}
    const rm=document.createElement('button');rm.type='button';rm.className='btn btn-danger';rm.style.padding='4px 8px';rm.textContent='✕';
    sf.addEventListener('change',()=>{sb.style.display=(sf.value==='credito'||sf.value==='debito')?'':'none';sp.style.display=sf.value==='credito'?'':'none';});
    val.addEventListener('input',updatePag);rm.addEventListener('click',()=>{row.remove();updatePag();});
    row.append(sf,val,sb,sp,rm);document.getElementById('pag_container').appendChild(row);
}
function updatePag(){
    let p=0;document.querySelectorAll('#pag_container .pag-row input[type="number"]').forEach(i=>p+=parseFloat(i.value)||0);
    const r=total-p;
    document.getElementById('tot_pago').textContent='R$ '+p.toFixed(2).replace('.',',');
    const re=document.getElementById('restante');re.textContent='R$ '+r.toFixed(2).replace('.',',');re.style.color=Math.abs(r)<.01?'green':'red';
}
document.getElementById('add_pag').addEventListener('click',criarLinhaPag);
document.getElementById('btn_confirmar').addEventListener('click',async function(){
    if(!atId){showToast('Selecione um atendimento.','error');return;}
    const rows=document.querySelectorAll('#pag_container .pag-row');
    const fd=new FormData();fd.append('atendimento_id',atId);
    rows.forEach(r=>{
        fd.append('pagamentos[forma][]',r.querySelector('select:nth-child(1)').value);
        fd.append('pagamentos[valor][]',r.querySelector('input[type="number"]').value);
        const sp=r.querySelectorAll('select');fd.append('pagamentos[parcelas][]',sp[2]?.style.display!=='none'?sp[2].value:'1');
        fd.append('pagamentos[bandeira_id][]',sp[1]?.style.display!=='none'?sp[1].value:'');
    });
    const btn=this;btn.disabled=true;btn.textContent='Salvando...';
    try{
        const r=await fetch(BASE+'atendimentos/confirmarPagamento',{method:'POST',body:fd});
        const j=await r.json();
        if(j.sucesso){showToast(j.mensagem,'success');setTimeout(()=>location.href=BASE+'dashboard',1500);}
        else{showToast(j.erro,'error');btn.disabled=false;btn.textContent='Confirmar Pagamento';}
    }catch(e){showToast('Erro de comunicação.','error');btn.disabled=false;btn.textContent='Confirmar Pagamento';}
});
</script>
