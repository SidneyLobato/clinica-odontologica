<?php
/**
 * AtendimentoModel — Fluxo de status:
 *
 * PENDENTE  = procedimento executado, pagamento ainda NÃO recebido
 *             → status_pagamento = 'pendente'
 *             → aparece no Confirmar Pagamento
 *
 * FINALIZADO = procedimento executado E pago na hora
 *             → status_pagamento = 'pago', status_execucao = 'feito'
 *             → gravado direto como pago, sem passar pelo Confirmar Pagamento
 */
class AtendimentoModel extends BaseModel {
    protected string $table = 'atendimentos';

    public function salvarCompleto(array $post, ?string $urlArquivo, int $pacienteId): array {
        $fin = new FinanceiroModel($this->db);
        $this->db->beginTransaction();
        try {
            $idDentista = (int)$post['id_dentista'];
            $idEspec    = !empty($post['id_dentista_especialista']) ? (int)$post['id_dentista_especialista'] : null;
            $idVend     = !empty($post['id_dentista_vendedor'])     ? (int)$post['id_dentista_vendedor']     : null;
            $procsIn    = $post['procedimentos'] ?? [];

            // Faturamento mensal para regra de comissão
            $ini = date('Y-m-01 00:00:00'); $fim = date('Y-m-t 23:59:59');
            $sf  = $this->db->prepare("SELECT COALESCE(SUM(ap.valor_procedimento),0) FROM atendimento_procedimentos ap JOIN atendimentos a ON ap.id_atendimento=a.id WHERE a.data_atendimento BETWEEN ? AND ?");
            $sf->execute([$ini, $fim]); $fatMensal = (float)$sf->fetchColumn();

            // Separar por status
            $pagosAgora = []; // Finalizado = pago na hora
            $pendentes  = []; // Pendente   = pagar depois
            $sp = $this->db->prepare("SELECT id,nome,categoria,valor_base FROM procedimentos WHERE id=?");

            foreach (($procsIn['id'] ?? []) as $k => $procId) {
                $qty = (int)($procsIn['quantidade'][$k] ?? 1);
                if (!$procId || $qty <= 0) continue;
                $sp->execute([$procId]); $proc = $sp->fetch();
                if (!$proc) throw new \Exception("Procedimento #$procId não encontrado.");

                $valorUnit = isset($procsIn['valor'][$k]) && $procsIn['valor'][$k] !== ''
                             ? (float)$procsIn['valor'][$k]
                             : (float)$proc['valor_base'];

                $item = [
                    'id'        => $procId,
                    'quantidade'=> $qty,
                    'valor'     => round($valorUnit, 2),
                    'categoria' => $proc['categoria'],
                    'custo_aux' => (float)($procsIn['custo_auxiliar'][$k] ?? 0),
                    'local'     => trim($procsIn['local'][$k] ?? 'Todos'),
                    'descricao' => trim($procsIn['descricao'][$k] ?? ''),
                    'natureza'  => trim($procsIn['natureza'][$k] ?? ''),
                    'status'    => trim($procsIn['status_execucao'][$k] ?? 'pendente'),
                ];

                if ($item['status'] === 'finalizado') $pagosAgora[] = $item;
                else                                   $pendentes[]  = $item;
            }

            if (empty($pagosAgora) && empty($pendentes)) throw new \Exception("Nenhum procedimento válido.");

            $idPrincipal = null;
            $idFinalizados = null;
            $si = $this->db->prepare("INSERT INTO atendimento_procedimentos(id_atendimento,id_procedimento,quantidade,valor_procedimento,custo_auxiliar,local,descricao,status_execucao,natureza)VALUES(?,?,?,?,?,?,?,?,?)");

            // ── FINALIZADOS: pago na hora ──────────────────────────────────────────
            if (!empty($pagosAgora)) {
                $valorBruto  = array_sum(array_column($pagosAgora, 'valor'));
                $totComissao = 0.0; $totCusto = 0.0;

                $idPrincipal = $this->insert([
                    'paciente_id'              => $pacienteId,
                    'id_dentista'              => $idDentista,
                    'id_dentista_especialista' => $idEspec,
                    'id_dentista_vendedor'     => $idVend,
                    'data_atendimento'         => date('Y-m-d H:i:s'),
                    'url_arquivo'              => $urlArquivo,
                ]);

                foreach ($pagosAgora as $p) {
                    $res = $fin->calcularComissao($p['valor'], $p['categoria'], $idDentista, $fatMensal + $valorBruto, $p['natureza'], $p['custo_aux']);
                    $totComissao += $res['dentista']; $totCusto += $res['auxiliar'];
                    // status_execucao = 'feito' (já pago)
                    $si->execute([$idPrincipal,$p['id'],$p['quantidade'],$p['valor'],$res['auxiliar'],$p['local'],$p['descricao'],'feito',$p['natureza']]);
                }

                $valLiq = $valorBruto - $totComissao - $totCusto;
                // status_pagamento = 'pago' direto
                $this->db->prepare("UPDATE atendimentos SET valor_total=?,comissao_dentista=?,custo_auxiliar=?,valor_liquido_clinica=?,status_pagamento='pago',taxa_cartao=0,id_dentista_especialista=?,id_dentista_vendedor=? WHERE id=?")
                    ->execute([$valorBruto,$totComissao,$totCusto,$valLiq,$idEspec,$idVend,$idPrincipal]);
                $idFinalizados = $idPrincipal;
            }

            // ── PENDENTES: aguarda pagamento ───────────────────────────────────────
            if (!empty($pendentes)) {
                $valorBruto  = array_sum(array_column($pendentes, 'valor'));
                $totComissao = 0.0; $totCusto = 0.0;

                $idPend = $this->insert([
                    'paciente_id'              => $pacienteId,
                    'id_dentista'              => $idDentista,
                    'id_dentista_especialista' => $idEspec,
                    'id_dentista_vendedor'     => $idVend,
                    'data_atendimento'         => date('Y-m-d H:i:s'),
                    'url_arquivo'              => $urlArquivo,
                ]);

                foreach ($pendentes as $p) {
                    $res = $fin->calcularComissao($p['valor'], $p['categoria'], $idDentista, $fatMensal + $valorBruto, $p['natureza'], $p['custo_aux']);
                    $totComissao += $res['dentista']; $totCusto += $res['auxiliar'];
                    // status_execucao = 'finalizado' (executado, aguarda pagamento)
                    $si->execute([$idPend,$p['id'],$p['quantidade'],$p['valor'],$res['auxiliar'],$p['local'],$p['descricao'],'finalizado',$p['natureza']]);
                }

                $valLiq = $valorBruto - $totComissao - $totCusto;
                // status_pagamento = 'pendente' → APARECE no Confirmar Pagamento
                $this->db->prepare("UPDATE atendimentos SET valor_total=?,comissao_dentista=?,custo_auxiliar=?,valor_liquido_clinica=?,status_pagamento='pendente',id_dentista_especialista=?,id_dentista_vendedor=? WHERE id=?")
                    ->execute([$valorBruto,$totComissao,$totCusto,$valLiq,$idEspec,$idVend,$idPend]);

                if (!$idPrincipal) $idPrincipal = $idPend;
            }

            $this->db->commit();
            return ['id_principal' => $idPrincipal, 'id_finalizados' => $idFinalizados];
        } catch (\Exception $e) { $this->db->rollBack(); throw $e; }
    }


    /**
     * Registra as formas de pagamento para atendimentos pagos na hora (Finalizados).
     * Chamado logo após o salvarCompleto quando há procedimentos Finalizados.
     * Sempre atualiza taxa_cartao e valor_liquido_clinica no atendimento.
     */
    public function registrarPagamentosFinalizados(int $atendId, array $pags, FinanceiroModel $fin): void {
        $this->db->beginTransaction();
        try {
            $spag = $this->db->prepare("INSERT INTO atendimento_pagamentos(id_atendimento,forma_pagamento,valor,qtd_parcelas,bandeira_id,taxa_snapshot)VALUES(?,?,?,?,?,?)");
            $totalTaxa = 0.0;

            foreach (($pags['forma'] ?? []) as $k => $forma) {
                $val  = (float)str_replace(',','.',$pags['valor'][$k] ?? 0);
                $parc = max(1, (int)($pags['parcelas'][$k] ?? 1));
                $bid  = (int)($pags['bandeira_id'][$k] ?? 0);
                if ($val <= 0) continue;

                $res = $fin->calcularLiquidoMaquininha($val, $forma, $parc, $bid);
                $totalTaxa += $res['valor_taxa'];
                $spag->execute([
                    $atendId,
                    $forma,
                    $val,
                    $parc,
                    $bid > 0 ? $bid : null,
                    $res['taxa_aplicada_percentual']
                ]);
            }

            // Sempre atualiza taxa_cartao e recalcula valor_liquido_clinica
            $at = $this->findById($atendId);
            $novoLiq = round((float)$at['valor_total'] - (float)$at['comissao_dentista'] - (float)$at['custo_auxiliar'] - $totalTaxa, 2);
            $this->db->prepare("UPDATE atendimentos SET taxa_cartao=?, valor_liquido_clinica=? WHERE id=?")
                ->execute([$totalTaxa, $novoLiq, $atendId]);

            $this->db->commit();
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function confirmarPagamento(int $atendId, array $pags, FinanceiroModel $fin): void {
        $this->db->beginTransaction();
        try {
            $at = $this->findById($atendId);
            if (!$at) throw new \Exception("Atendimento não encontrado.");
            if ($at['status_pagamento'] !== 'pendente') throw new \Exception("Este atendimento não está pendente de pagamento.");

            $totalPago = array_sum(array_map('floatval', $pags['valor'] ?? []));
            if (abs($totalPago - (float)$at['valor_total']) > 0.01) {
                throw new \Exception("Soma dos pagamentos (R$".number_format($totalPago,2,',','.')
                    .") ≠ total do atendimento (R$".number_format($at['valor_total'],2,',','.').").");
            }

            $ini = date('Y-m-01 00:00:00'); $fim = date('Y-m-t 23:59:59');
            $sf  = $this->db->prepare("SELECT COALESCE(SUM(ap.valor_procedimento),0) FROM atendimento_procedimentos ap JOIN atendimentos a ON ap.id_atendimento=a.id WHERE a.data_atendimento BETWEEN ? AND ? AND a.status_pagamento='pago' AND ap.status_execucao='feito'");
            $sf->execute([$ini,$fim]); $fatMensal = (float)$sf->fetchColumn();
            $fatCalculo = $fatMensal + (float)$at['valor_total'];

            $totalTaxa = 0.0;
            $spag = $this->db->prepare("INSERT INTO atendimento_pagamentos(id_atendimento,forma_pagamento,valor,qtd_parcelas,bandeira_id,taxa_snapshot)VALUES(?,?,?,?,?,?)");
            foreach (($pags['forma'] ?? []) as $k => $forma) {
                $val  = (float)str_replace(',','.',$pags['valor'][$k] ?? 0);
                $parc = (int)($pags['parcelas'][$k] ?? 1);
                $bid  = (int)($pags['bandeira_id'][$k] ?? 0);
                if ($val <= 0) continue;
                $res = $fin->calcularLiquidoMaquininha($val, $forma, $parc, $bid);
                $totalTaxa += $res['valor_taxa'];
                $spag->execute([$atendId,$forma,$val,$parc,$bid?:null,$res['taxa_aplicada_percentual']]);
            }

            $sproc = $this->db->prepare("SELECT ap.valor_procedimento,ap.custo_auxiliar,ap.natureza,p.categoria FROM atendimento_procedimentos ap JOIN procedimentos p ON ap.id_procedimento=p.id WHERE ap.id_atendimento=? AND ap.status_execucao='finalizado'");
            $sproc->execute([$atendId]); $procsAt = $sproc->fetchAll();
            $novaComissao = 0.0;
            foreach ($procsAt as $pr) {
                $res = $fin->calcularComissao((float)$pr['valor_procedimento'],$pr['categoria'],(int)$at['id_dentista'],$fatCalculo,$pr['natureza']??'',(float)$pr['custo_auxiliar']);
                $novaComissao += $res['dentista'];
            }

            $valLiq = (float)$at['valor_total'] - $totalTaxa - $novaComissao - (float)$at['custo_auxiliar'];
            $this->db->prepare("UPDATE atendimentos SET status_pagamento='pago',taxa_cartao=?,comissao_dentista=?,valor_liquido_clinica=? WHERE id=?")->execute([$totalTaxa,$novaComissao,$valLiq,$atendId]);
            $this->db->prepare("UPDATE atendimento_procedimentos SET status_execucao='feito' WHERE id_atendimento=? AND status_execucao='finalizado'")->execute([$atendId]);

            foreach ($procsAt as $pr) {
                if (!in_array($pr['categoria'],['especializado','protese'])) continue;
                $isOrto = strtolower($pr['natureza'] ?? '') === 'orto';
                $prop   = (float)$at['valor_total'] > 0 ? (float)$pr['valor_procedimento']/(float)$at['valor_total'] : 1.0;
                $txa    = round($totalTaxa * $prop, 2);
                $liq    = (float)$pr['valor_procedimento'] - $txa;
                $rat    = $fin->calcularRateio($liq, $pr['categoria'], $isOrto);
                $this->db->prepare("INSERT INTO rateios_atendimento(id_atendimento,id_dentista_especialista,id_dentista_vendedor,valor_bruto,valor_taxa_cartao,valor_especialista,valor_vendedor,valor_clinica,valor_liquido)VALUES(?,?,?,?,?,?,?,?,?)")
                    ->execute([$atendId,$isOrto?null:($at['id_dentista_especialista']??null),$isOrto?null:($at['id_dentista_vendedor']??null),$pr['valor_procedimento'],$txa,$rat['valor_especialista'],$rat['valor_vendedor'],$rat['valor_clinica'],$liq]);
            }
            $this->db->commit();
        } catch (\Exception $e) { $this->db->rollBack(); throw $e; }
    }

    public function pendentes(): array {
        return $this->db->query(
            "SELECT a.id, a.data_atendimento, p.nome AS paciente_nome, a.valor_total
             FROM atendimentos a
             JOIN pacientes p ON a.paciente_id = p.id
             WHERE a.status_pagamento = 'pendente'
             ORDER BY a.data_atendimento DESC LIMIT 100"
        )->fetchAll();
    }

    public function procsPendentes(int $atendId): array {
        $s = $this->db->prepare("SELECT ap.id,proc.nome AS procedimento_nome,ap.local,ap.valor_procedimento,ap.status_execucao,ap.url_arquivo FROM atendimento_procedimentos ap JOIN procedimentos proc ON ap.id_procedimento=proc.id WHERE ap.id_atendimento=? AND ap.status_execucao IN('pendente','finalizado')");
        $s->execute([$atendId]); return $s->fetchAll();
    }
}
