<div id="toast-notification" class="toast"></div>
<div class="card">
    <h2>Novo Lançamento de Atendimento</h2>
    <form id="form-atendimento" action="<?= BASE_URL ?>atendimentos/salvar" method="POST" enctype="multipart/form-data">

        <fieldset>
            <legend>Paciente</legend>
            <input type="hidden" name="paciente_id" id="paciente_id">
            <div class="form-group">
                <label>Nome do Paciente</label>
                <div style="display:flex;gap:.5rem;">
                    <input type="text" id="paciente_busca" name="paciente_nome" placeholder="Digite para buscar... (novo nome = cadastro automático)" autocomplete="off" style="flex-grow:1;">
                    <button type="button" class="btn btn-danger" id="btn_limpar_paciente">Limpar</button>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Profissional Responsável</legend>
            <div class="form-group">
                <label>Dentista Executor <span style="color:#c0392b;">*</span></label>
                <select name="id_dentista" id="id_dentista" required style="max-width:360px;">
                    <option value="">Selecione...</option>
                    <?php foreach ($dentistas as $d): ?>
                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="dica-campo">Quem realizou o atendimento. Obrigatório em todos os casos.</p>
            </div>

            <div id="bloco_especialistas" style="display:none;margin-top:.75rem;">
                <div class="aviso-rateio">
                    <span>⚠️</span>
                    <span>Esse atendimento tem procedimento especializado ou prótese. Preencha abaixo só se houver divisão de comissão com outro profissional.</span>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                    <div class="form-group" id="bloco_especialista">
                        <label>Especialista que recebe rateio</label>
                        <select name="id_dentista_especialista">
                            <option value="">Nenhum</option>
                            <?php foreach ($dentistas as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="dica-campo">Use se outro profissional (não o executor) domina a técnica e recebe parte do valor por isso.</p>
                    </div>
                    <div class="form-group" id="bloco_vendedor">
                        <label>Dentista que captou o paciente</label>
                        <select name="id_dentista_vendedor">
                            <option value="">Nenhum</option>
                            <?php foreach ($dentistas as $d): ?>
                            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="dica-campo">Use se o paciente veio através de outro dentista da clínica, que recebe uma parte por ter indicado.</p>
                    </div>
                </div>
            </div>
        </fieldset>

        <style>
        .dica-campo{font-size:.78rem;color:#6c757d;margin:.4rem 0 0;}
        .aviso-rateio{
            display:flex;gap:8px;align-items:flex-start;
            background:#fdf3e3;color:#8a5a0c;border-radius:8px;
            padding:.6rem .85rem;font-size:.82rem;margin-bottom:.85rem;
        }
        </style>

        <fieldset>
            <legend>Odontograma — clique em um dente para adicionar procedimento</legend>
            <div class="canvas-container">
                <img src="<?= BASE_URL ?>public/assets/img/odontograma.png" usemap="#image-map" class="img-odontograma" id="odontograma-img">
                <svg class="odontograma-overlay" id="odontograma-svg"></svg>
                <map name="image-map" id="image-map">
                    <area onclick="marcarDente(this,18,'Arcada Superior')" alt="18" title="Dente 18" coords="53,251,99,157" shape="rect">
                    <area onclick="marcarDente(this,17,'Arcada Superior')" alt="17" title="Dente 17" coords="147,156,103,249" shape="rect">
                    <area onclick="marcarDente(this,16,'Arcada Superior')" alt="16" title="Dente 16" coords="207,155,151,246" shape="rect">
                    <area onclick="marcarDente(this,15,'Arcada Superior')" alt="15" title="Dente 15" coords="241,152,209,242" shape="rect">
                    <area onclick="marcarDente(this,14,'Arcada Superior')" alt="14" title="Dente 14" coords="274,149,246,241" shape="rect">
                    <area onclick="marcarDente(this,13,'Arcada Superior')" alt="13" title="Dente 13" coords="314,148,277,238" shape="rect">
                    <area onclick="marcarDente(this,12,'Arcada Superior')" alt="12" title="Dente 12" coords="352,152,317,243" shape="rect">
                    <area onclick="marcarDente(this,11,'Arcada Superior')" alt="11" title="Dente 11" coords="397,153,355,246" shape="rect">
                    <area onclick="marcarDente(this,21,'Arcada Superior')" alt="21" title="Dente 21" coords="442,154,403,244" shape="rect">
                    <area onclick="marcarDente(this,22,'Arcada Superior')" alt="22" title="Dente 22" coords="479,153,446,243" shape="rect">
                    <area onclick="marcarDente(this,23,'Arcada Superior')" alt="23" title="Dente 23" coords="521,142,481,243" shape="rect">
                    <area onclick="marcarDente(this,24,'Arcada Superior')" alt="24" title="Dente 24" coords="561,146,525,239" shape="rect">
                    <area onclick="marcarDente(this,25,'Arcada Superior')" alt="25" title="Dente 25" coords="590,146,564,237" shape="rect">
                    <area onclick="marcarDente(this,26,'Arcada Superior')" alt="26" title="Dente 26" coords="648,148,593,238" shape="rect">
                    <area onclick="marcarDente(this,27,'Arcada Superior')" alt="27" title="Dente 27" coords="703,151,653,239" shape="rect">
                    <area onclick="marcarDente(this,28,'Arcada Superior')" alt="28" title="Dente 28" coords="741,149,705,241" shape="rect">
                    <area onclick="marcarDente(this,48,'Arcada Inferior')" alt="48" title="Dente 48" coords="51,285,103,360" shape="rect">
                    <area onclick="marcarDente(this,47,'Arcada Inferior')" alt="47" title="Dente 47" coords="109,284,160,363" shape="rect">
                    <area onclick="marcarDente(this,46,'Arcada Inferior')" alt="46" title="Dente 46" coords="167,281,219,363" shape="rect">
                    <area onclick="marcarDente(this,45,'Arcada Inferior')" alt="45" title="Dente 45" coords="221,278,258,378" shape="rect">
                    <area onclick="marcarDente(this,44,'Arcada Inferior')" alt="44" title="Dente 44" coords="260,275,296,390" shape="rect">
                    <area onclick="marcarDente(this,43,'Arcada Inferior')" alt="43" title="Dente 43" coords="298,276,336,384" shape="rect">
                    <area onclick="marcarDente(this,42,'Arcada Inferior')" alt="42" title="Dente 42" coords="338,275,368,384" shape="rect">
                    <area onclick="marcarDente(this,41,'Arcada Inferior')" alt="41" title="Dente 41" coords="370,276,395,383" shape="rect">
                    <area onclick="marcarDente(this,31,'Arcada Inferior')" alt="31" title="Dente 31" coords="398,275,426,380" shape="rect">
                    <area onclick="marcarDente(this,32,'Arcada Inferior')" alt="32" title="Dente 32" coords="428,275,454,382" shape="rect">
                    <area onclick="marcarDente(this,33,'Arcada Inferior')" alt="33" title="Dente 33" coords="456,274,493,391" shape="rect">
                    <area onclick="marcarDente(this,34,'Arcada Inferior')" alt="34" title="Dente 34" coords="496,274,531,383" shape="rect">
                    <area onclick="marcarDente(this,35,'Arcada Inferior')" alt="35" title="Dente 35" coords="534,274,571,379" shape="rect">
                    <area onclick="marcarDente(this,36,'Arcada Inferior')" alt="36" title="Dente 36" coords="575,274,636,384" shape="rect">
                    <area onclick="marcarDente(this,37,'Arcada Inferior')" alt="37" title="Dente 37" coords="640,274,688,384" shape="rect">
                    <area onclick="marcarDente(this,38,'Arcada Inferior')" alt="38" title="Dente 38" coords="694,272,742,375" shape="rect">
                    <area onclick="marcarDente(this,'Todos','Geral')" alt="Todos" title="Todos" coords="85,31,727,83" shape="rect">
                    <area onclick="marcarDente(this,'Todos','Geral')" alt="Todos" title="Todos" coords="72,449,727,498" shape="rect">
                </map>
            </div>
        </fieldset>

        <div id="procedimentos_pendentes_container"></div>
        <div id="procedimentos_adicionados_container">
            <h3 style="margin-top:1rem;">Procedimentos Adicionados</h3>
        </div>
        <div id="procedimentos_a_deletar_container"></div>

        <div style="text-align:right;font-size:1.4em;margin:1rem 0;">
            <strong>Total: <span id="total-procedimentos-valor">R$ 0,00</span></strong>
        </div>

        <!-- Formas de pagamento — aparece só quando há procedimentos Finalizados -->
        <div id="secao_pagamento" style="display:none;margin-bottom:1rem;">
            <fieldset>
                <legend>Forma de Pagamento <small style="color:#666;font-weight:normal;">(somente para procedimentos pagos na hora)</small></legend>
                <div id="pagamento_container"></div>
                <button type="button" id="add_pagamento" class="btn btn-info" style="margin-top:.5rem;">+ Adicionar forma</button>
                <div style="margin-top:.75rem;display:flex;gap:2rem;flex-wrap:wrap;">
                    <span>Total a pagar: <strong id="pag_total" style="color:#2980b9;">R$ 0,00</strong></span>
                    <span>Pago: <strong id="pag_pago">R$ 0,00</strong></span>
                    <span>Restante: <strong id="pag_restante" style="color:red;">R$ 0,00</strong></span>
                    <span>Taxa de cartão: <strong id="pag_taxa" style="color:#d68910;">R$ 0,00</strong></span>
                    <span>Você recebe (líquido): <strong id="pag_liquido" style="color:#1d7a3c;">R$ 0,00</strong></span>
                </div>
            </fieldset>
        </div>

        <button type="submit" class="btn btn-success" style="width:100%;">Lançar Atendimento</button>
    </form>
</div>

<!-- MODAL POR DENTE -->
<div id="modalTratamento" class="modal">
    <div class="modal-content">
        <h3><span id="modal-title"></span></h3>
        <form id="form-tratamento-modal">
            <input type="hidden" id="inputDente">
            <input type="hidden" id="inputArcada">
            <div id="procedimentos-modal-container"></div>
            <button type="button" id="add-procedimento-modal" class="btn btn-info" style="margin-top:.5rem;">+ Procedimento</button>
            <div style="text-align:right;margin-top:10px;font-size:1.1em;">
                <strong>Total: <span id="modal-total-valor">R$ 0,00</span></strong>
            </div>
            <div style="display:flex;gap:.5rem;margin-top:1rem;">
                <button type="button" id="salvar-tratamento-modal" class="btn btn-success" style="flex:1;">Salvar</button>
                <button type="button" onclick="fecharModal()" class="btn btn-secondary" style="flex:1;">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<style>
fieldset{border:1px solid #ddd;padding:1.25rem;margin-bottom:1rem;border-radius:8px;}
legend{font-weight:bold;padding:0 .5rem;color:var(--primary-color,#005b96);}
.canvas-container{position:relative;width:100%;max-width:1000px;margin:.75rem auto 0;line-height:0;}
.img-odontograma{display:block;width:100%;height:auto;}
.odontograma-overlay{position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;}
area{cursor:pointer;outline:none;}
.modal{display:none;position:fixed;z-index:10000;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,.5);justify-content:center;align-items:center;}
.modal.show{display:flex;}
.modal-content{background:#fff;padding:1.5rem;border-radius:10px;width:520px;max-width:95vw;box-shadow:0 5px 20px rgba(0,0,0,.2);max-height:90vh;overflow-y:auto;}
.proc-item{background:#f8f9fa;border:1px solid #dee2e6;border-radius:8px;padding:.75rem 1rem;margin-bottom:.5rem;display:flex;justify-content:space-between;align-items:center;}
.proc-item-info strong{display:block;font-size:.95rem;}
.proc-item-info small{color:#666;}
.modal-linha{display:flex;gap:.5rem;align-items:center;margin-bottom:.5rem;flex-wrap:wrap;}
.modal-linha select,.modal-linha input{padding:6px 8px;border:1px solid #ddd;border-radius:6px;font-size:.88rem;}
.toast{position:fixed;top:20px;right:20px;padding:14px 20px;border-radius:6px;color:#fff;font-size:.95rem;z-index:9999;opacity:0;visibility:hidden;transition:opacity .4s,visibility .4s,transform .4s;transform:translateX(100%);}
.toast.show{opacity:1;visibility:visible;transform:translateX(0);}
.toast.error{background:#c0392b;}.toast.success{background:#27ae60;}
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script>
$(function(){
    const BASE     = '<?= BASE_URL ?>';
    const procs    = <?= json_encode($procedimentos) ?>;
    const bandeiras = <?= json_encode($bandeiras) ?>;
    const matrizCredito = <?= json_encode($matrizCredito ?: new stdClass()) ?>;
    const matrizDebito  = <?= json_encode($matrizDebito ?: new stdClass()) ?>;
    let adicionados = [];

    /** Retorna a taxa % vigente pra essa bandeira/forma/parcelas */
    function taxaPercentual(forma, bid, parcelas){
        if(!bid) return 0;
        if(forma==='debito') return parseFloat(matrizDebito[bid]) || 0;
        if(forma==='credito') return parseFloat((matrizCredito[bid]||{})[parcelas]) || 0;
        return 0;
    }

    $('#paciente_busca').autocomplete({
        source: (req, resp) => $.getJSON(BASE+'buscar-paciente',{term:req.term}, data => {
            resp(data.length ? data.map(p=>({label:p.nome+(p.cpf?' ('+p.cpf+')':''),value:p.id,p})) : [{label:'Cadastrar: '+req.term,value:'new'}]);
        }),
        minLength:2,
        select(e,ui){
            if(ui.item.value==='new') $('#paciente_id').val('');
            else { $('#paciente_id').val(ui.item.p.id); $('#paciente_busca').val(ui.item.p.nome).prop('readonly',true); }
            return false;
        }
    });
    $('#btn_limpar_paciente').on('click',()=>{ $('#paciente_id').val(''); $('#paciente_busca').val('').prop('readonly',false).focus(); });

    window.marcarDente=(area,dente,arcada)=>{
        document.getElementById('inputDente').value=dente;
        document.getElementById('inputArcada').value=arcada;
        document.getElementById('modal-title').textContent=dente==='Todos'?'Procedimento Geral (Todos os dentes)':'Dente '+dente+' — '+arcada;
        document.getElementById('procedimentos-modal-container').innerHTML='';
        adicionarLinhaModal(); calcTotalModal();
        document.getElementById('modalTratamento').classList.add('show');
    };
    window.fecharModal=()=>document.getElementById('modalTratamento').classList.remove('show');

    function adicionarLinhaModal(){
        const div=document.createElement('div'); div.className='modal-linha';
        const sel=document.createElement('select'); sel.style.flex='2';
        sel.innerHTML='<option value="">Procedimento...</option>';
        procs.forEach(p=>{const o=document.createElement('option');o.value=p.id;o.dataset.val=p.valor_base;o.dataset.cat=p.categoria;o.dataset.tipo=p.tipo||'';o.textContent=p.nome;sel.appendChild(o);});
        const qty=document.createElement('input'); qty.type='number'; qty.min='1'; qty.value='1'; qty.style.width='55px'; qty.placeholder='Qtd';
        const nat=document.createElement('select'); nat.style.width='105px';
        ['geral','canal','orto','cirurgia_especializada','protese'].forEach(n=>{const o=document.createElement('option');o.value=n;o.textContent=n.charAt(0).toUpperCase()+n.slice(1).replace('_',' ');nat.appendChild(o);});
        const sta=document.createElement('select'); sta.style.width='100px';
        [{v:'pendente',l:'Pendente (pagar depois)'},{v:'finalizado',l:'Finalizado (pago na hora)'}].forEach(x=>{const o=document.createElement('option');o.value=x.v;o.textContent=x.l;sta.appendChild(o);});
        const rem=document.createElement('button'); rem.type='button'; rem.textContent='✕'; rem.className='btn btn-danger'; rem.style.padding='4px 10px';
        rem.onclick=()=>{div.remove();calcTotalModal();};
        sel.onchange=qty.oninput=calcTotalModal;
        div.append(sel,qty,nat,sta,rem);
        document.getElementById('procedimentos-modal-container').appendChild(div);
    }
    function calcTotalModal(){
        let t=0;
        document.querySelectorAll('#procedimentos-modal-container .modal-linha').forEach(d=>{
            const s=d.querySelector('select'); const q=parseInt(d.querySelector('input').value)||0;
            if(s.value) t+=(parseFloat(s.options[s.selectedIndex].dataset.val)||0)*q;
        });
        document.getElementById('modal-total-valor').textContent='R$ '+t.toFixed(2).replace('.',',');
    }
    document.getElementById('add-procedimento-modal').onclick=adicionarLinhaModal;

    function atualizarVisibilidadeRateio(){
        const temEspecial = adicionados.some(p => p.categoria==='especializado' || p.categoria==='protese');
        document.getElementById('bloco_especialistas').style.display = temEspecial ? 'block' : 'none';
        if (!temEspecial) {
            // Limpa a seleção para não enviar um rateio "fantasma" de um procedimento já removido
            const selEsp = document.querySelector('#bloco_especialista select');
            const selVend = document.querySelector('#bloco_vendedor select');
            if (selEsp) selEsp.value = '';
            if (selVend) selVend.value = '';
        }
    }

    document.getElementById('salvar-tratamento-modal').onclick=()=>{
        const dente=document.getElementById('inputDente').value;
        const arcada=document.getElementById('inputArcada').value;
        document.querySelectorAll('#procedimentos-modal-container .modal-linha').forEach(div=>{
            const sels=div.querySelectorAll('select');
            const opt=sels[0].options[sels[0].selectedIndex];
            if(!opt||!opt.value) return;
            const qty=parseInt(div.querySelector('input').value)||1;
            const val=(parseFloat(opt.dataset.val)||0)*qty;
            const cat=opt.dataset.cat||'geral';
            adicionados.push({id:opt.value,nome:opt.textContent,quantidade:qty,valor:val,dente,arcada,natureza:sels[1].value,status:sels[2].value,categoria:cat,tipo:opt.dataset.tipo});
        });
        atualizarVisibilidadeRateio();
        renderizar(); calcTotal(); fecharModal();
    };

    function renderizar(){
        const c=document.getElementById('procedimentos_adicionados_container');
        c.innerHTML='<h3 style="margin-top:1rem;">Procedimentos Adicionados</h3>';
        document.getElementById('procedimentos_a_deletar_container').innerHTML='';
        adicionados.forEach((p,i)=>{
            const div=document.createElement('div'); div.className='proc-item';
            const cor=p.status==='finalizado'?'#2980b9':'#e67e22';
            div.innerHTML=`<div class="proc-item-info"><strong>${p.nome}</strong><small>Dente: ${p.dente} &nbsp;|&nbsp; <span style="color:${cor}">${p.status==='finalizado'?'Finalizado':'Pendente'}</span></small></div><div style="display:flex;align-items:center;gap:.75rem;"><strong>R$ ${p.valor.toFixed(2).replace('.',',')}</strong><button type="button" class="btn btn-danger" style="padding:3px 10px;" onclick="removerProc(${i})">✕</button></div>`;
            c.appendChild(div);
            ['id','quantidade','valor','natureza','status_execucao','local','descricao'].forEach(campo=>{
                const inp=document.createElement('input'); inp.type='hidden';
                inp.name=`procedimentos[${campo}][]`;
                inp.value=campo==='status_execucao'?p.status:campo==='local'?p.dente:campo==='descricao'?'':p[campo]??'';
                c.appendChild(inp);
            });
        });
    }
    window.removerProc=i=>{adicionados.splice(i,1);renderizar();calcTotal();atualizarSecaoPagamento();atualizarVisibilidadeRateio();};
    function calcTotal(){
        const fin=adicionados.filter(p=>p.status==='finalizado').reduce((s,p)=>s+p.valor,0);
        const pend=adicionados.filter(p=>p.status==='pendente').reduce((s,p)=>s+p.valor,0);
        let txt='R$ '+fin.toFixed(2).replace('.',',');
        if(pend>0) txt+=' + R$ '+pend.toFixed(2).replace('.',',')+' (pendente)';
        document.getElementById('total-procedimentos-valor').textContent=txt;
        atualizarSecaoPagamento();
    }

    // ── Seção de pagamento (só para Finalizados) ──
    function atualizarSecaoPagamento(){
        const totalFin=adicionados.filter(p=>p.status==='finalizado').reduce((s,p)=>s+p.valor,0);
        const sec=document.getElementById('secao_pagamento');
        if(totalFin<=0){sec.style.display='none';return;}
        sec.style.display='block';
        document.getElementById('pag_total').textContent='R$ '+totalFin.toFixed(2).replace('.',',');
        // Se não tem nenhuma linha de pagamento ainda, cria uma
        if(document.querySelectorAll('.pag-row-form').length===0) criarLinhaPagamento();
        updatePagTotal();
    }

    function criarLinhaPagamento(){
        const row=document.createElement('div');row.className='pag-row-form';
        row.style.cssText='display:grid;grid-template-columns:140px 100px 170px 80px 1fr auto;gap:.5rem;align-items:center;margin-bottom:.5rem;';

        // Forma de pagamento
        const sf=document.createElement('select');sf.name='pagamento[forma][]';sf.style.padding='6px';
        [{v:'dinheiro',l:'Dinheiro'},{v:'pix',l:'Pix'},{v:'debito',l:'Débito'},{v:'credito',l:'Crédito'}].forEach(f=>{
            const o=document.createElement('option');o.value=f.v;o.textContent=f.l;sf.appendChild(o);
        });

        // Valor
        const val=document.createElement('input');val.type='number';val.step='0.01';val.min='0';
        val.placeholder='Valor R$';val.name='pagamento[valor][]';val.style.padding='6px';

        // Bandeira (só para crédito e débito)
        const sb=document.createElement('select');sb.style.visibility='hidden';sb.name='pagamento[bandeira_id][]';sb.style.padding='6px';
        const bo=document.createElement('option');bo.value='';bo.textContent='Bandeira...';sb.appendChild(bo);
        // Filtrar bandeiras reais — excluir 'debito' se vier como bandeira no banco
        bandeiras.filter(b=>b.nome.toLowerCase()!=='debito').forEach(b=>{
            const o=document.createElement('option');o.value=b.id;o.textContent=b.nome;sb.appendChild(o);
        });

        // Parcelas (só para crédito)
        const sp=document.createElement('select');sp.style.visibility='hidden';sp.name='pagamento[parcelas][]';sp.style.padding='6px';
        for(let i=1;i<=10;i++){const o=document.createElement('option');o.value=i;o.textContent=i+'x';sp.appendChild(o);}

        // Repassar taxa ao cliente (só para débito) — usa input oculto sincronizado
        // com o checkbox, porque checkbox desmarcado não envia valor nenhum no
        // formulário, o que bagunçaria a ordem dos pagamentos ao salvar.
        // Usa visibility (não display) pra não quebrar as colunas do grid.
        const repWrap=document.createElement('label');repWrap.style.cssText='visibility:hidden;display:flex;align-items:center;gap:5px;font-size:.76rem;color:#555;white-space:nowrap;overflow:hidden;';
        const repChk=document.createElement('input');repChk.type='checkbox';repChk.checked=true;
        const repHidden=document.createElement('input');repHidden.type='hidden';repHidden.name='pagamento[repassar_debito][]';repHidden.value='1';
        const repTxt=document.createElement('span');repTxt.textContent='Repassar taxa';
        repChk.addEventListener('change',()=>{repHidden.value=repChk.checked?'1':'0';});
        repWrap.append(repChk,repTxt);

        // Linha de informação da taxa calculada (ocupa a largura toda, abaixo dos campos)
        const taxaInfo=document.createElement('div');
        taxaInfo.style.cssText='grid-column:1/-1;font-size:.78rem;color:#888;margin-top:-2px;';
        taxaInfo.textContent='';

        // Botão remover
        const rm=document.createElement('button');rm.type='button';rm.className='btn btn-danger';
        rm.style.cssText='padding:4px 10px;height:34px;';rm.textContent='✕';

        function atualizarTaxaInfo(){
            const forma=sf.value;
            const valor=parseFloat(val.value)||0;
            const bid=sb.value;
            const parc=parseInt(sp.value)||1;
            if((forma!=='debito'&&forma!=='credito')||!bid||valor<=0){
                taxaInfo.textContent='';
                return;
            }
            if(forma==='debito'&&!repChk.checked){
                taxaInfo.innerHTML='<i>Taxa de débito não repassada — clínica absorve o custo.</i>';
                taxaInfo.style.color='#888';
                return;
            }
            const perc=taxaPercentual(forma,bid,parc);
            const taxaR$=valor*(perc/100);
            const liquido=valor-taxaR$;
            taxaInfo.innerHTML='Taxa '+(forma==='debito'?'débito':'crédito '+parc+'x')+': <strong>'+perc.toFixed(2).replace('.',',')+'%</strong>'+
                ' = R$ '+taxaR$.toFixed(2).replace('.',',')+
                ' &nbsp;·&nbsp; Você recebe: <strong style="color:#1d7a3c;">R$ '+liquido.toFixed(2).replace('.',',')+'</strong>';
            taxaInfo.style.color='#555';
        }

        sf.addEventListener('change',()=>{
            const isCard=(sf.value==='credito'||sf.value==='debito');
            sb.style.visibility=isCard?'visible':'hidden';
            sp.style.visibility=sf.value==='credito'?'visible':'hidden';
            repWrap.style.visibility=sf.value==='debito'?'visible':'hidden';
            if(!isCard){sb.value='';sp.value='1';}
            if(sf.value!=='debito'){repChk.checked=true;repHidden.value='1';}
            atualizarTaxaInfo();
        });
        sb.addEventListener('change',atualizarTaxaInfo);
        sp.addEventListener('change',atualizarTaxaInfo);
        repChk.addEventListener('change',atualizarTaxaInfo);
        val.addEventListener('input',()=>{updatePagTotal();atualizarTaxaInfo();});
        rm.addEventListener('click',()=>{row.remove();updatePagTotal();});

        row.append(sf,val,sb,sp,repWrap,repHidden,rm,taxaInfo);
        document.getElementById('pagamento_container').appendChild(row);
    }

    function updatePagTotal(){
        const totalFin=adicionados.filter(p=>p.status==='finalizado').reduce((s,p)=>s+p.valor,0);
        let pago=0, taxaTotal=0;
        document.querySelectorAll('.pag-row-form').forEach(row=>{
            const sels=row.querySelectorAll('select');
            const forma=sels[0]?.value;
            const valorInput=row.querySelector('input[type="number"]');
            const valor=parseFloat(valorInput?.value)||0;
            pago+=valor;
            if(valor<=0) return;
            const bid=sels[1]?.value;
            const parc=parseInt(sels[2]?.value)||1;
            const chk=row.querySelector('input[type="checkbox"]');
            if(forma==='debito'&&bid&&chk&&!chk.checked) return; // taxa não repassada, não conta
            if((forma==='debito'||forma==='credito')&&bid){
                const perc=taxaPercentual(forma,bid,parc);
                taxaTotal+=valor*(perc/100);
            }
        });
        const rest=totalFin-pago;
        document.getElementById('pag_pago').textContent='R$ '+pago.toFixed(2).replace('.',',');
        const re=document.getElementById('pag_restante');
        re.textContent='R$ '+rest.toFixed(2).replace('.',',');
        re.style.color=Math.abs(rest)<.01?'green':'red';
        document.getElementById('pag_taxa').textContent='R$ '+taxaTotal.toFixed(2).replace('.',',');
        document.getElementById('pag_liquido').textContent='R$ '+(pago-taxaTotal).toFixed(2).replace('.',',');
    }

    document.getElementById('add_pagamento').addEventListener('click',criarLinhaPagamento);

    const toast=document.getElementById('toast-notification');
    function showToast(m,t){toast.textContent=m;toast.className='toast show '+t;setTimeout(()=>toast.className='toast',5000);}

    $('#form-atendimento').on('submit',async function(e){
        e.preventDefault();
        if(!$('#id_dentista').val()){showToast('Selecione o dentista responsável.','error');return;}
        if(!adicionados.length){showToast('Adicione pelo menos um procedimento clicando no odontograma.','error');return;}
        // Validar pagamento se há finalizados
        const totalFin=adicionados.filter(p=>p.status==='finalizado').reduce((s,p)=>s+p.valor,0);
        if(totalFin>0){
            let pagTotal=0;
            let erroValidacao='';
            document.querySelectorAll('.pag-row-form').forEach(row=>{
                const forma=row.querySelector('select:nth-child(1)').value;
                const val=parseFloat(row.querySelector('input[type="number"]').value)||0;
                const sels=row.querySelectorAll('select');
                const bid=sels[1]?.style.visibility!=='hidden'?sels[1].value:'';
                pagTotal+=val;
                if((forma==='credito'||forma==='debito')&&!bid&&val>0)
                    erroValidacao='Selecione a bandeira do cartão para o pagamento de R$ '+val.toFixed(2).replace('.',',')+'.';
            });
            if(erroValidacao){showToast(erroValidacao,'error');return;}
            if(pagTotal<=0){showToast('Informe o valor do pagamento.','error');return;}
            if(Math.abs(pagTotal-totalFin)>0.01){showToast('Soma do pagamento (R$ '+pagTotal.toFixed(2).replace('.',',')+') diferente do total (R$ '+totalFin.toFixed(2).replace('.',',')+').','error');return;}
        }
        // Permite lançar só pendentes (gera registro nao_aplicavel sem cobrança)
        const btn=$(this).find('[type="submit"]').prop('disabled',true).text('Salvando...');
        try{
            const r=await fetch($(this).attr('action'),{method:'POST',body:new FormData(this)});
            const j=await r.json();
            if(j.sucesso){showToast(j.mensagem,'success');setTimeout(()=>location.href=j.redirectUrl,1500);}
            else{showToast(j.erro||'Erro.','error');btn.prop('disabled',false).text('Lançar Atendimento');}
        }catch{showToast('Erro de comunicação.','error');btn.prop('disabled',false).text('Lançar Atendimento');}
    });
});
</script>
