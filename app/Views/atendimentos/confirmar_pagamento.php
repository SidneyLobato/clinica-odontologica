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
<div style="margin-top:.75rem;display:flex;gap:1.5rem;flex-wrap:wrap;">
    <p>Pago: <strong id="tot_pago">R$ 0,00</strong></p>
    <p>Restante: <strong id="restante" style="color:red;">R$ 0,00</strong></p>
    <p>Taxa de cartão: <strong id="tot_taxa" style="color:#d68910;">R$ 0,00</strong></p>
    <p>Você recebe (líquido): <strong id="tot_liquido" style="color:#1d7a3c;">R$ 0,00</strong></p>
</div>
<button type="button" id="btn_confirmar" class="btn btn-success" style="width:100%;margin-top:1rem;">Confirmar Pagamento</button>
</div></div>
<style>.toast{position:fixed;top:20px;right:20px;padding:15px 20px;border-radius:5px;color:#fff;z-index:9999;opacity:0;visibility:hidden;transition:opacity .5s,visibility .5s,transform .5s;transform:translateX(100%);}.toast.show{opacity:1;visibility:visible;transform:translateX(0);}.toast.error{background:#c0392b;}.toast.success{background:#27ae60;}.pag-row{display:flex;gap:.5rem;margin-bottom:.25rem;flex-wrap:wrap;align-items:center;}.pag-row-taxa{font-size:.78rem;color:#888;margin:0 0 .6rem 2px;}</style>
<script>
const BASE=<?=json_encode(BASE_URL)?>,bands=<?=json_encode($bandeiras)?>;
const matrizCredito=<?=json_encode($matrizCredito ?: new stdClass())?>;
const matrizDebito=<?=json_encode($matrizDebito ?: new stdClass())?>;
let atId=0,total=0;
const toast=document.getElementById('toast');
function showToast(m,t){toast.textContent=m;toast.className='toast show '+t;setTimeout(()=>toast.className='toast',4000);}
function taxaPercentual(forma,bid,parcelas){
    if(!bid) return 0;
    if(forma==='debito') return parseFloat(matrizDebito[bid])||0;
    if(forma==='credito') return parseFloat((matrizCredito[bid]||{})[parcelas])||0;
    return 0;
}
document.getElementById('sel_atend').addEventListener('change',function(){
    atId=parseInt(this.value)||0;total=parseFloat(this.options[this.selectedIndex]?.dataset?.total)||0;
    document.getElementById('form_pag').style.display=atId?'block':'none';
    document.getElementById('pag_container').innerHTML='';criarLinhaPag();updatePag();
});
function criarLinhaPag(){
    const wrap=document.createElement('div');
    const row=document.createElement('div');row.className='pag-row';
    const sf=document.createElement('select');['dinheiro','pix','debito','credito'].forEach(f=>{const o=document.createElement('option');o.value=f;o.textContent=f.charAt(0).toUpperCase()+f.slice(1);sf.appendChild(o);});
    const val=document.createElement('input');val.type='number';val.step='0.01';val.placeholder='Valor';val.style.width='100px';
    const sb=document.createElement('select');sb.style.visibility='hidden';
    const bo=document.createElement('option');bo.value='';bo.textContent='Bandeira...';sb.appendChild(bo);
    bands.forEach(b=>{const o=document.createElement('option');o.value=b.id;o.textContent=b.nome;sb.appendChild(o);});
    const sp=document.createElement('select');sp.style.visibility='hidden';
    for(let i=1;i<=10;i++){const o=document.createElement('option');o.value=i;o.textContent=i+'x';sp.appendChild(o);}
    const repWrap=document.createElement('label');repWrap.style.cssText='visibility:hidden;display:flex;align-items:center;gap:4px;font-size:.78rem;color:#555;';
    const repChk=document.createElement('input');repChk.type='checkbox';repChk.checked=true;
    const repHidden=document.createElement('input');repHidden.type='hidden';repHidden.value='1';
    repChk.addEventListener('change',()=>{repHidden.value=repChk.checked?'1':'0';atualizarTaxaLinha();});
    repWrap.append(repChk,document.createTextNode('Repassar taxa ao cliente'));
    const rm=document.createElement('button');rm.type='button';rm.className='btn btn-danger';rm.style.padding='4px 8px';rm.textContent='✕';

    const taxaLinha=document.createElement('div');taxaLinha.className='pag-row-taxa';

    function atualizarTaxaLinha(){
        const forma=sf.value,valor=parseFloat(val.value)||0,bid=sb.value,parc=parseInt(sp.value)||1;
        if((forma!=='debito'&&forma!=='credito')||!bid||valor<=0){taxaLinha.textContent='';return;}
        if(forma==='debito'&&!repChk.checked){taxaLinha.innerHTML='<i>Taxa de débito não repassada — clínica absorve o custo.</i>';return;}
        const perc=taxaPercentual(forma,bid,parc);
        const taxaR=valor*(perc/100);
        taxaLinha.innerHTML='Taxa '+(forma==='debito'?'débito':'crédito '+parc+'x')+': <strong>'+perc.toFixed(2).replace('.',',')+'%</strong> = R$ '+taxaR.toFixed(2).replace('.',',')+
            ' &nbsp;·&nbsp; Líquido: <strong style="color:#1d7a3c;">R$ '+(valor-taxaR).toFixed(2).replace('.',',')+'</strong>';
    }

    sf.addEventListener('change',()=>{
        sb.style.visibility=(sf.value==='credito'||sf.value==='debito')?'visible':'hidden';
        sp.style.visibility=sf.value==='credito'?'visible':'hidden';
        repWrap.style.visibility=sf.value==='debito'?'visible':'hidden';
        if(sf.value!=='debito'){repChk.checked=true;repHidden.value='1';}
        atualizarTaxaLinha();
    });
    sb.addEventListener('change',atualizarTaxaLinha);
    sp.addEventListener('change',atualizarTaxaLinha);
    val.addEventListener('input',()=>{updatePag();atualizarTaxaLinha();});
    rm.addEventListener('click',()=>{wrap.remove();updatePag();});

    row.append(sf,val,sb,sp,repWrap,repHidden,rm);
    wrap.append(row,taxaLinha);
    document.getElementById('pag_container').appendChild(wrap);
}
function updatePag(){
    let p=0,taxaTotal=0;
    document.querySelectorAll('#pag_container .pag-row').forEach(row=>{
        const sels=row.querySelectorAll('select');
        const forma=sels[0]?.value;
        const valor=parseFloat(row.querySelector('input[type="number"]')?.value)||0;
        p+=valor;
        if(valor<=0) return;
        const bid=sels[1]?.value, parc=parseInt(sels[2]?.value)||1;
        const chk=row.querySelector('input[type="checkbox"]');
        if(forma==='debito'&&bid&&chk&&!chk.checked) return;
        if((forma==='debito'||forma==='credito')&&bid){
            taxaTotal+=valor*(taxaPercentual(forma,bid,parc)/100);
        }
    });
    const r=total-p;
    document.getElementById('tot_pago').textContent='R$ '+p.toFixed(2).replace('.',',');
    const re=document.getElementById('restante');re.textContent='R$ '+r.toFixed(2).replace('.',',');re.style.color=Math.abs(r)<.01?'green':'red';
    document.getElementById('tot_taxa').textContent='R$ '+taxaTotal.toFixed(2).replace('.',',');
    document.getElementById('tot_liquido').textContent='R$ '+(p-taxaTotal).toFixed(2).replace('.',',');
}
document.getElementById('add_pag').addEventListener('click',criarLinhaPag);
document.getElementById('btn_confirmar').addEventListener('click',async function(){
    if(!atId){showToast('Selecione um atendimento.','error');return;}
    const rows=document.querySelectorAll('#pag_container .pag-row');
    const fd=new FormData();fd.append('atendimento_id',atId);
    rows.forEach(r=>{
        fd.append('pagamentos[forma][]',r.querySelector('select:nth-child(1)').value);
        fd.append('pagamentos[valor][]',r.querySelector('input[type="number"]').value);
        const sp=r.querySelectorAll('select');fd.append('pagamentos[parcelas][]',sp[2]?.style.visibility!=='hidden'?sp[2].value:'1');
        fd.append('pagamentos[bandeira_id][]',sp[1]?.style.visibility!=='hidden'?sp[1].value:'');
        fd.append('pagamentos[repassar_debito][]',r.querySelector('input[type="hidden"]').value);
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
