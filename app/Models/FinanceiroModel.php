<?php
/**
 * FinanceiroModel — Multi-empresa
 * Todas as taxas, comissões, especialidades e rateio são isolados por
 * empresa_id automaticamente.
 */
class FinanceiroModel extends BaseModel {
    protected string $table = 'config_sistema';

    // ── CONFIG SISTEMA ─────────────────────────────────────────────────────
    public function getConfig(string $c, string $pad = ''): string {
        [$where, $params] = $this->filtro('chave=:c', ['c' => $c]);
        $s = $this->db->prepare("SELECT valor FROM config_sistema $where LIMIT 1");
        $s->execute($params);
        $r = $s->fetch();
        return $r ? $r['valor'] : $pad;
    }

    public function setConfig(string $c, string $v): void {
        if ($this->empresaId !== null) {
            $this->db->prepare(
                "INSERT INTO config_sistema(empresa_id,chave,valor)VALUES(:eid,:c,:v)
                 ON DUPLICATE KEY UPDATE valor=:v2,atualizado_em=NOW()"
            )->execute(['eid' => $this->empresaId, 'c' => $c, 'v' => $v, 'v2' => $v]);
        } else {
            $this->db->prepare(
                "INSERT INTO config_sistema(chave,valor)VALUES(:c,:v) ON DUPLICATE KEY UPDATE valor=:v2,atualizado_em=NOW()"
            )->execute(['c' => $c, 'v' => $v, 'v2' => $v]);
        }
    }

    // ── BANDEIRAS E TAXAS ──────────────────────────────────────────────────
    public function listaBandeiras(): array {
        [$where, $params] = $this->filtro('ativo=1');
        $s = $this->db->prepare("SELECT * FROM bandeiras_cartao $where ORDER BY nome");
        $s->execute($params);
        return $s->fetchAll();
    }

    public function matrizTaxas(): array {
        [$where, $params] = $this->filtro("vigencia_fim IS NULL AND tipo='credito'");
        $s = $this->db->prepare("SELECT bandeira_id,parcelas,percentual FROM taxas_cartao $where");
        $s->execute($params);
        $m = [];
        foreach ($s->fetchAll() as $r) $m[$r['bandeira_id']][$r['parcelas']] = (float)$r['percentual'];
        return $m;
    }

    /** Taxa de débito vigente por bandeira (sempre parcelas=1, não tem parcelamento) */
    public function matrizTaxasDebito(): array {
        [$where, $params] = $this->filtro("vigencia_fim IS NULL AND tipo='debito'");
        $s = $this->db->prepare("SELECT bandeira_id,percentual FROM taxas_cartao $where");
        $s->execute($params);
        $m = [];
        foreach ($s->fetchAll() as $r) $m[$r['bandeira_id']] = (float)$r['percentual'];
        return $m;
    }

    public function getTaxaCartao(int $bid, int $parcelas, string $tipo = 'credito'): float {
        [$where, $params] = $this->filtro(
            'bandeira_id=:b AND parcelas=:p AND tipo=:tp AND vigencia_fim IS NULL',
            ['b' => $bid, 'p' => $parcelas, 'tp' => $tipo]
        );
        $s = $this->db->prepare("SELECT percentual FROM taxas_cartao $where ORDER BY vigencia_inicio DESC LIMIT 1");
        $s->execute($params);
        $r = $s->fetch();
        return $r ? (float)$r['percentual'] : 0.0;
    }

    /** Salva taxa com histórico imutável — fecha vigência anterior, cria novo registro */
    public function salvarTaxa(int $bid, int $p, float $pct, int $uid, string $tipo = 'credito'): void {
        $this->db->beginTransaction();
        try {
            $eidClause = $this->empresaId !== null ? ' AND empresa_id=:eid' : '';
            $p1 = ['b' => $bid, 'p' => $p, 'tp' => $tipo];
            if ($this->empresaId !== null) $p1['eid'] = $this->empresaId;
            $this->db->prepare(
                "UPDATE taxas_cartao SET vigencia_fim=NOW() WHERE bandeira_id=:b AND parcelas=:p AND tipo=:tp AND vigencia_fim IS NULL$eidClause"
            )->execute($p1);

            $this->db->prepare(
                "INSERT INTO taxas_cartao(empresa_id,bandeira_id,tipo,parcelas,percentual,vigencia_inicio,vigencia_fim,usuario_id)
                 VALUES(:eid,:b,:tp,:p,:pct,:vi,NULL,:u)"
            )->execute([
                'eid' => $this->empresaId, 'b' => $bid, 'tp' => $tipo, 'p' => $p,
                'pct' => $pct, 'vi' => date('Y-m-d H:i:s'), 'u' => $uid,
            ]);
            $this->db->commit();
        } catch (\Exception $e) { $this->db->rollBack(); throw $e; }
    }

    /**
     * Salva várias taxas de uma vez (crédito e/ou débito), em uma única
     * transação. Só grava o que realmente mudou.
     * $itens: [{bandeira_id, parcelas, percentual, tipo}]
     */
    public function salvarTaxasEmLote(array $itens, int $uid): int {
        $matrizCredito = $this->matrizTaxas();
        $matrizDebito  = $this->matrizTaxasDebito();
        $agora       = date('Y-m-d H:i:s');
        $atualizados = 0;

        $this->db->beginTransaction();
        try {
            $eidClause = $this->empresaId !== null ? ' AND empresa_id=:eid' : '';
            $stmtFecha = $this->db->prepare(
                "UPDATE taxas_cartao SET vigencia_fim=:vf WHERE bandeira_id=:b AND parcelas=:p AND tipo=:tp AND vigencia_fim IS NULL$eidClause"
            );
            $stmtInsere = $this->db->prepare(
                "INSERT INTO taxas_cartao(empresa_id,bandeira_id,tipo,parcelas,percentual,vigencia_inicio,vigencia_fim,usuario_id)
                 VALUES(:eid,:b,:tp,:p,:pct,:vi,NULL,:u)"
            );

            foreach ($itens as $item) {
                $bid  = (int)$item['bandeira_id'];
                $tipo = ($item['tipo'] ?? 'credito') === 'debito' ? 'debito' : 'credito';
                $parc = $tipo === 'debito' ? 1 : (int)$item['parcelas'];
                $pct  = (float)$item['percentual'];
                if ($parc < 1 || $parc > 10 || $pct < 0) continue;

                $valorAtual = $tipo === 'debito'
                    ? ($matrizDebito[$bid] ?? null)
                    : ($matrizCredito[$bid][$parc] ?? null);
                if ($valorAtual !== null && abs($valorAtual - $pct) < 0.001) continue;

                $pf = ['vf' => $agora, 'b' => $bid, 'p' => $parc, 'tp' => $tipo];
                if ($this->empresaId !== null) $pf['eid'] = $this->empresaId;
                $stmtFecha->execute($pf);

                $stmtInsere->execute([
                    'eid' => $this->empresaId, 'b' => $bid, 'tp' => $tipo, 'p' => $parc,
                    'pct' => $pct, 'vi' => $agora, 'u' => $uid,
                ]);
                $atualizados++;
            }

            $this->db->commit();
            return $atualizados;
        } catch (\Exception $e) { $this->db->rollBack(); throw $e; }
    }

    /**
     * Calcula o valor líquido após taxa da maquininha.
     * Para débito, $repassarTaxa decide se a taxa é descontada (cobrada do
     * cliente / repassada) ou se a clínica absorve o custo (não desconta).
     */
    public function calcularLiquidoMaquininha(float $val, string $forma, int $parcelas = 1, int $bid = 0, bool $repassarTaxa = true): array {
        $taxaPerc = 0.0;
        if ($forma === 'debito' && $bid > 0) {
            $taxaPerc = $repassarTaxa ? $this->getTaxaCartao($bid, 1, 'debito') : 0.0;
        } elseif ($forma === 'credito' && $bid > 0) {
            $taxaPerc = $this->getTaxaCartao($bid, max(1, $parcelas), 'credito');
        }
        $valorTaxa    = round($val * ($taxaPerc / 100), 2);
        $valorLiquido = round($val - $valorTaxa, 2);
        return [
            'valor_taxa'               => $valorTaxa,
            'valor_liquido'            => $valorLiquido,
            'parcela'                  => round($valorLiquido / max(1, $parcelas), 2),
            'taxa_aplicada_percentual' => $taxaPerc,
        ];
    }

    // ── COMISSÕES ──────────────────────────────────────────────────────────
    public function getRegraComissaoGlobal(): array {
        [$where, $params] = $this->filtro('ativo=1');
        $s = $this->db->prepare("SELECT * FROM config_comissoes $where ORDER BY vigencia_inicio DESC LIMIT 1");
        $s->execute($params);
        $r = $s->fetch();
        return $r ?: ['percentual_ate_meta' => 20.0, 'percentual_acima_meta' => 30.0, 'valor_meta' => 10000.0];
    }

    public function getRegraComissaoDentista(int $did): array {
        [$where, $params] = $this->filtro('dentista_id=:id AND ativo=1', ['id' => $did]);
        $s = $this->db->prepare("SELECT * FROM config_comissoes_individuais $where ORDER BY vigencia_inicio DESC LIMIT 1");
        $s->execute($params);
        return $s->fetch() ?: $this->getRegraComissaoGlobal();
    }

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
                } else {
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

    public function getPercEspecialidade(string $tipo): float {
        [$where, $params] = $this->filtro('tipo=:t AND ativo=1', ['t' => $tipo]);
        $s = $this->db->prepare("SELECT percentual FROM config_especialidades $where LIMIT 1");
        $s->execute($params);
        $r = $s->fetch();
        $defaults = ['canal' => 10.0, 'cirurgia_especializada' => 10.0, 'orto' => 50.0, 'protese' => 10.0, 'implante' => 50.0];
        return $r ? (float)$r['percentual'] : ($defaults[$tipo] ?? 10.0);
    }

    // ── RATEIO ─────────────────────────────────────────────────────────────
    public function getRegraRateio(string $cat): array {
        [$where, $params] = $this->filtro(
            "(categoria_procedimento=:c OR categoria_procedimento='todas') AND ativo=1",
            ['c' => $cat]
        );
        $s = $this->db->prepare(
            "SELECT * FROM config_rateio $where
             ORDER BY CASE WHEN categoria_procedimento=:c2 THEN 0 ELSE 1 END, vigencia_inicio DESC LIMIT 1"
        );
        $params['c2'] = $cat;
        $s->execute($params);
        $r = $s->fetch();
        return $r ?: ['percentual_especialista' => 50.0, 'percentual_vendedor' => 10.0, 'percentual_clinica' => 40.0];
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
