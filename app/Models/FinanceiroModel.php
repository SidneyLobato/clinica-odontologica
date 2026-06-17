<?php
/**
 * FinanceiroModel
 * Substitui a classe estática Financeiro.php do legado.
 * Todas as taxas, comissões e percentuais lidos do banco — NADA hardcoded.
 * Mantém a mesma lógica de cálculo do original, agora configurável.
 */
class FinanceiroModel extends BaseModel {
    protected string $table = 'config_sistema';

    // ── CONFIG SISTEMA ─────────────────────────────────────────────────────
    public function getConfig(string $c, string $pad = ''): string {
        $s = $this->db->prepare("SELECT valor FROM config_sistema WHERE chave=:c LIMIT 1");
        $s->execute(['c' => $c]); $r = $s->fetch(); return $r ? $r['valor'] : $pad;
    }
    public function setConfig(string $c, string $v): void {
        $this->db->prepare("INSERT INTO config_sistema(chave,valor)VALUES(:c,:v) ON DUPLICATE KEY UPDATE valor=:v2,atualizado_em=NOW()")->execute(['c'=>$c,'v'=>$v,'v2'=>$v]);
    }

    // ── BANDEIRAS E TAXAS ──────────────────────────────────────────────────
    public function listaBandeiras(): array {
        return $this->db->query("SELECT * FROM bandeiras_cartao WHERE ativo=1 ORDER BY nome")->fetchAll();
    }
    public function matrizTaxas(): array {
        $rows = $this->db->query("SELECT bandeira_id,parcelas,percentual FROM taxas_cartao WHERE vigencia_fim IS NULL")->fetchAll();
        $m = [];
        foreach ($rows as $r) $m[$r['bandeira_id']][$r['parcelas']] = (float)$r['percentual'];
        return $m;
    }
    public function getTaxaCartao(int $bid, int $parcelas): float {
        $s = $this->db->prepare("SELECT percentual FROM taxas_cartao WHERE bandeira_id=:b AND parcelas=:p AND vigencia_fim IS NULL ORDER BY vigencia_inicio DESC LIMIT 1");
        $s->execute(['b'=>$bid,'p'=>$parcelas]); $r = $s->fetch(); return $r ? (float)$r['percentual'] : 0.0;
    }
    /** Salva taxa com histórico imutável — fecha vigência anterior, cria novo registro */
    public function salvarTaxa(int $bid, int $p, float $pct, int $uid): void {
        $this->db->beginTransaction();
        try {
            $this->db->prepare("UPDATE taxas_cartao SET vigencia_fim=NOW() WHERE bandeira_id=:b AND parcelas=:p AND vigencia_fim IS NULL")->execute(['b'=>$bid,'p'=>$p]);
            $this->db->prepare("INSERT INTO taxas_cartao(bandeira_id,parcelas,percentual,vigencia_inicio,vigencia_fim,usuario_id) VALUES(:b,:p,:pct,:vi,NULL,:u)")->execute(['b'=>$bid,'p'=>$p,'pct'=>$pct,'vi'=>date('Y-m-d H:i:s'),'u'=>$uid]);
            $this->db->commit();
        } catch (\Exception $e) { $this->db->rollBack(); throw $e; }
    }

    /**
     * Salva várias taxas de uma vez, em uma única transação.
     * $itens: array de ['bandeira_id'=>int, 'parcelas'=>int, 'percentual'=>float]
     * Só grava o que realmente mudou em relação à matriz atual (evita registros de histórico desnecessários).
     * Retorna o número de taxas efetivamente atualizadas.
     */
    public function salvarTaxasEmLote(array $itens, int $uid): int {
        $matrizAtual = $this->matrizTaxas();
        $agora = date('Y-m-d H:i:s');
        $atualizados = 0;

        $this->db->beginTransaction();
        try {
            $stmtFecha = $this->db->prepare(
                "UPDATE taxas_cartao SET vigencia_fim=:vf WHERE bandeira_id=:b AND parcelas=:p AND vigencia_fim IS NULL"
            );
            $stmtInsere = $this->db->prepare(
                "INSERT INTO taxas_cartao(bandeira_id,parcelas,percentual,vigencia_inicio,vigencia_fim,usuario_id) VALUES(:b,:p,:pct,:vi,NULL,:u)"
            );

            foreach ($itens as $item) {
                $bid  = (int)$item['bandeira_id'];
                $parc = (int)$item['parcelas'];
                $pct  = (float)$item['percentual'];

                if ($parc < 1 || $parc > 10 || $pct < 0) continue;

                $valorAtual = $matrizAtual[$bid][$parc] ?? null;
                if ($valorAtual !== null && abs($valorAtual - $pct) < 0.001) continue; // sem mudança real

                $stmtFecha->execute(['vf'=>$agora,'b'=>$bid,'p'=>$parc]);
                $stmtInsere->execute(['b'=>$bid,'p'=>$parc,'pct'=>$pct,'vi'=>$agora,'u'=>$uid]);
                $atualizados++;
            }

            $this->db->commit();
            return $atualizados;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    /** Calcula líquido da maquininha — fiel à lógica original, agora usando banco */
    public function calcularLiquidoMaquininha(float $val, string $forma, int $parcelas = 1, int $bid = 0): array {
        $taxaPerc = 0.0;
        if ($forma === 'debito' && $bid > 0) {
            $taxaPerc = $this->getTaxaCartao($bid, 1);
        } elseif ($forma === 'credito' && $bid > 0) {
            $taxaPerc = $this->getTaxaCartao($bid, max(1, $parcelas));
        }
        $valorTaxa    = round($val * ($taxaPerc / 100), 2);
        $valorLiquido = round($val - $valorTaxa, 2);
        return [
            'valor_taxa'              => $valorTaxa,
            'valor_liquido'           => $valorLiquido,
            'parcela'                 => round($valorLiquido / max(1, $parcelas), 2),
            'taxa_aplicada_percentual'=> $taxaPerc,
        ];
    }

    // ── COMISSÕES ──────────────────────────────────────────────────────────
    public function getRegraComissaoGlobal(): array {
        $r = $this->db->query("SELECT * FROM config_comissoes WHERE ativo=1 ORDER BY vigencia_inicio DESC LIMIT 1")->fetch();
        return $r ?: ['percentual_ate_meta'=>20.0,'percentual_acima_meta'=>30.0,'valor_meta'=>10000.0];
    }
    public function getRegraComissaoDentista(int $did): array {
        $s = $this->db->prepare("SELECT * FROM config_comissoes_individuais WHERE dentista_id=:id AND ativo=1 ORDER BY vigencia_inicio DESC LIMIT 1");
        $s->execute(['id'=>$did]); return $s->fetch() ?: $this->getRegraComissaoGlobal();
    }
    /**
     * Calcula comissão — mesma lógica do Financeiro::calcularComissao() original,
     * mas agora os percentuais vêm do banco, não de constantes.
     */
    public function calcularComissao(float $val, string $cat, int $did, float $fatMensal, string $natureza = '', float $custoAux = 0.0): array {
        $comissao = 0.0; $custoLab = 0.0;
        $cenario  = $this->getConfig('cenario_comissao', 'global');
        $regra    = $cenario === 'individual' ? $this->getRegraComissaoDentista($did) : $this->getRegraComissaoGlobal();

        switch ($cat) {
            case 'geral':
                $perc     = $fatMensal >= (float)$regra['valor_meta'] ? (float)$regra['percentual_acima_meta'] : (float)$regra['percentual_ate_meta'];
                $comissao = round($val * ($perc / 100), 2);
                break;
            case 'especializado':
                $nat = strtolower($natureza);
                if ($nat === 'canal' || $nat === 'cirurgia_especializada') {
                    $comissao = round($val * ($this->getPercEspecialidade('canal') / 100), 2);
                    $custoLab = (float)$custoAux;
                } elseif ($nat === 'protese') {
                    $comissao = round($val * ($this->getPercEspecialidade('protese') / 100), 2);
                    $custoLab = (float)$custoAux;
                } else { // orto e outros
                    $comissao = round($val * ($this->getPercEspecialidade('orto') / 100), 2);
                }
                break;
            case 'protese':
                $comissao = round($val * ($this->getPercEspecialidade('protese') / 100), 2);
                $custoLab = (float)$custoAux;
                break;
        }
        return ['dentista' => $comissao, 'auxiliar' => round($custoLab, 2)];
    }

    /** Busca percentual de especialidade do banco (tabela config_especialidades) */
    public function getPercEspecialidade(string $tipo): float {
        $s = $this->db->prepare("SELECT percentual FROM config_especialidades WHERE tipo=:t AND ativo=1 LIMIT 1");
        $s->execute(['t' => $tipo]); $r = $s->fetch();
        // Fallback para os valores originais hardcoded caso não exista no banco
        $defaults = ['canal'=>10.0,'cirurgia_especializada'=>10.0,'orto'=>50.0,'protese'=>10.0,'implante'=>50.0];
        return $r ? (float)$r['percentual'] : ($defaults[$tipo] ?? 10.0);
    }

    // ── RATEIO ─────────────────────────────────────────────────────────────
    public function getRegraRateio(string $cat): array {
        $s = $this->db->prepare("SELECT * FROM config_rateio WHERE (categoria_procedimento=:c OR categoria_procedimento='todas') AND ativo=1 ORDER BY CASE WHEN categoria_procedimento=:c2 THEN 0 ELSE 1 END, vigencia_inicio DESC LIMIT 1");
        $s->execute(['c' => $cat, 'c2' => $cat]); $r = $s->fetch();
        return $r ?: ['percentual_especialista'=>50.0,'percentual_vendedor'=>10.0,'percentual_clinica'=>40.0];
    }
    public function calcularRateio(float $valLiq, string $cat, bool $isOrto = false): array {
        $regra = $this->getRegraRateio($cat);
        $esp   = (float)$regra['percentual_especialista'];
        $vend  = $isOrto ? 0.0 : (float)$regra['percentual_vendedor'];
        $ev    = round($valLiq * ($esp  / 100), 2);
        $vv    = round($valLiq * ($vend / 100), 2);
        $cv    = round($valLiq - $ev - $vv, 2);
        return ['valor_especialista' => $ev, 'valor_vendedor' => $vv, 'valor_clinica' => $cv];
    }
}
