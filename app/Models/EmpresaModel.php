<?php
/**
 * EmpresaModel
 * Gerencia clínicas (tenants) e o provisionamento de dados iniciais
 * para clínicas novas que se cadastram pelo sistema.
 */
class EmpresaModel extends BaseModel
{
    protected string $table = 'empresas';

    public function __construct(PDO $pdo)
    {
        $this->db        = $pdo;
        $this->empresaId = null; // Sem filtro de tenant — gerencia os tenants em si
    }

    public function porSlug(string $slug): ?array
    {
        $s = $this->db->prepare("SELECT * FROM empresas WHERE slug = ? LIMIT 1");
        $s->execute([$slug]);
        return $s->fetch() ?: null;
    }

    public function slugDisponivel(string $slug): bool
    {
        $s = $this->db->prepare("SELECT COUNT(*) FROM empresas WHERE slug = ?");
        $s->execute([$slug]);
        return $s->fetchColumn() === 0;
    }

    public function listarTodas(string $busca = ''): array
    {
        $sql = "SELECT e.*,
                       (SELECT COUNT(*) FROM usuarios u WHERE u.empresa_id = e.id) AS total_usuarios,
                       (SELECT COUNT(*) FROM pacientes p WHERE p.empresa_id = e.id) AS total_pacientes,
                       (SELECT COUNT(*) FROM atendimentos a WHERE a.empresa_id = e.id) AS total_atendimentos
                  FROM empresas e";
        $params = [];
        if ($busca) {
            $sql   .= " WHERE e.nome LIKE :b OR e.slug LIKE :b2 OR e.email LIKE :b3";
            $params = ['b' => "%$busca%", 'b2' => "%$busca%", 'b3' => "%$busca%"];
        }
        $sql .= " ORDER BY e.criado_em DESC";
        $s = $this->db->prepare($sql);
        $s->execute($params);
        return $s->fetchAll();
    }

    public function alterarStatus(int $id, string $status): bool
    {
        $s = $this->db->prepare("UPDATE empresas SET status = ? WHERE id = ?");
        return $s->execute([$status, $id]);
    }

    public function alterarPlano(int $id, string $plano, float $valorMensal, ?int $estenderTrialDias = null): bool
    {
        $planosValidos = ['trial', 'basico', 'profissional'];
        if (!in_array($plano, $planosValidos, true)) return false;

        if ($plano === 'trial') {
            $dias = $estenderTrialDias ?? 30;
            $s = $this->db->prepare(
                "UPDATE empresas SET plano='trial', valor_mensal=0, trial_ate=DATE_ADD(NOW(), INTERVAL ? DAY) WHERE id=?"
            );
            return $s->execute([$dias, $id]);
        }
        $s = $this->db->prepare("UPDATE empresas SET plano=?, valor_mensal=?, trial_ate=NULL WHERE id=?");
        return $s->execute([$plano, $valorMensal, $id]);
    }

    public function salvarObservacao(int $id, string $obs): bool
    {
        $s = $this->db->prepare("UPDATE empresas SET observacoes_admin = ? WHERE id = ?");
        return $s->execute([$obs, $id]);
    }

    public function atualizarDados(int $id, array $d): bool
    {
        $s = $this->db->prepare("UPDATE empresas SET nome=?, email=?, telefone=?, cnpj=? WHERE id=?");
        return $s->execute([$d['nome'], $d['email'], $d['telefone'] ?: null, $d['cnpj'] ?: null, $id]);
    }

    public function buscarDetalhe(int $id): ?array
    {
        $s = $this->db->prepare(
            "SELECT e.*,
                    (SELECT COUNT(*) FROM usuarios u WHERE u.empresa_id=e.id) AS total_usuarios,
                    (SELECT COUNT(*) FROM pacientes p WHERE p.empresa_id=e.id) AS total_pacientes,
                    (SELECT COUNT(*) FROM atendimentos a WHERE a.empresa_id=e.id) AS total_atendimentos,
                    (SELECT COALESCE(SUM(valor_total),0) FROM atendimentos a WHERE a.empresa_id=e.id AND a.status_pagamento='pago')
                      AS faturamento_total
               FROM empresas e WHERE e.id=? LIMIT 1"
        );
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    public function listarUsuariosDaEmpresa(int $id): array
    {
        $s = $this->db->prepare("SELECT id,nome,login,perfil,criado_em FROM usuarios WHERE empresa_id=? ORDER BY criado_em");
        $s->execute([$id]);
        return $s->fetchAll();
    }

    /** Pega um usuário proprietário da clínica, para login de suporte (impersonate) */
    public function proprietarioDaEmpresa(int $id): ?array
    {
        $s = $this->db->prepare("SELECT * FROM usuarios WHERE empresa_id=? AND perfil='proprietario' ORDER BY id LIMIT 1");
        $s->execute([$id]);
        $u = $s->fetch();
        if ($u) return $u;
        $s = $this->db->prepare("SELECT * FROM usuarios WHERE empresa_id=? ORDER BY id LIMIT 1");
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    public function estatisticasGerais(): array
    {
        $r = $this->db->query(
            "SELECT
               COUNT(*) AS total,
               SUM(status='ativo') AS ativas,
               SUM(status='suspenso') AS suspensas,
               SUM(status='cancelado') AS canceladas,
               SUM(plano='trial') AS trials,
               SUM(plano='basico') AS basico,
               SUM(plano='profissional') AS profissional,
               SUM(CASE WHEN status='ativo' AND plano<>'trial' THEN valor_mensal ELSE 0 END) AS mrr,
               (SELECT COUNT(*) FROM usuarios) AS total_usuarios,
               (SELECT COUNT(*) FROM atendimentos) AS total_atendimentos
             FROM empresas"
        )->fetch();
        return $r ?: [];
    }

    public function trialsVencendo(int $dias = 7): array
    {
        $s = $this->db->prepare(
            "SELECT * FROM empresas
              WHERE plano='trial' AND status='ativo' AND trial_ate IS NOT NULL
                AND trial_ate <= DATE_ADD(CURDATE(), INTERVAL ? DAY)
              ORDER BY trial_ate ASC"
        );
        $s->execute([$dias]);
        return $s->fetchAll();
    }

    /**
     * Cria uma nova clínica e semeia os dados padrão (bandeiras, taxas,
     * comissão e especialidades básicas). A clínica começa SEM procedimentos
     * cadastrados — cada clínica tem sua própria tabela de preços.
     */
    public function provisionar(array $dados): int
    {
        $this->db->beginTransaction();
        try {
            $s = $this->db->prepare(
                "INSERT INTO empresas (nome, cnpj, email, telefone, slug, plano, status, trial_ate)
                 VALUES (:nome, :cnpj, :email, :telefone, :slug, 'trial', 'ativo', DATE_ADD(NOW(), INTERVAL 30 DAY))"
            );
            $s->execute([
                'nome'     => $dados['nome'],
                'cnpj'     => $dados['cnpj']     ?? null,
                'email'    => $dados['email'],
                'telefone' => $dados['telefone'] ?? null,
                'slug'     => $dados['slug'],
            ]);
            $eid = (int)$this->db->lastInsertId();

            // Usuário proprietário
            $this->db->prepare(
                "INSERT INTO usuarios (empresa_id, nome, login, senha, perfil) VALUES (:eid, :nome, :login, :senha, 'proprietario')"
            )->execute([
                'eid'   => $eid,
                'nome'  => $dados['nome_proprietario'] ?? $dados['nome'],
                'login' => $dados['email'],
                'senha' => password_hash($dados['senha'], PASSWORD_BCRYPT),
            ]);

            // Configurações do sistema
            $sc = $this->db->prepare("INSERT INTO config_sistema (empresa_id, chave, valor) VALUES (?, ?, ?)");
            foreach ([
                ['nome_clinica',     $dados['nome']],
                ['moeda_simbolo',    'R$'],
                ['cenario_comissao', 'global'],
            ] as [$chave, $valor]) {
                $sc->execute([$eid, $chave, $valor]);
            }

            // Bandeiras de cartão
            $sb = $this->db->prepare("INSERT INTO bandeiras_cartao (empresa_id, nome, ativo) VALUES (?, ?, 1)");
            $bandeira_ids = [];
            foreach (['Visa', 'Mastercard', 'Elo', 'Hipercard', 'American Express'] as $nome) {
                $sb->execute([$eid, $nome]);
                $bandeira_ids[$nome] = (int)$this->db->lastInsertId();
            }

            // Taxas de cartão padrão (parcela única, ajustável depois)
            $st = $this->db->prepare("INSERT INTO taxas_cartao (empresa_id, bandeira_id, parcelas, percentual) VALUES (?, ?, ?, ?)");
            foreach ($bandeira_ids as $bid) {
                $st->execute([$eid, $bid, 1, 3.00]);
            }

            // Regra de comissão global padrão
            $this->db->prepare(
                "INSERT INTO config_comissoes (empresa_id, percentual_ate_meta, percentual_acima_meta, valor_meta, ativo)
                 VALUES (?, 20.00, 30.00, 10000.00, 1)"
            )->execute([$eid]);

            // Especialidades padrão
            $se = $this->db->prepare("INSERT INTO config_especialidades (empresa_id, tipo, percentual, ativo) VALUES (?, ?, ?, 1)");
            foreach ([
                ['canal', 10.00], ['cirurgia_especializada', 10.00],
                ['orto', 50.00], ['protese', 10.00], ['implante', 20.00],
            ] as [$tipo, $perc]) {
                $se->execute([$eid, $tipo, $perc]);
            }

            // Rateio padrão
            $this->db->prepare(
                "INSERT INTO config_rateio (empresa_id, categoria_procedimento, percentual_especialista, percentual_vendedor, percentual_clinica, ativo)
                 VALUES (?, 'todas', 50.00, 10.00, 40.00, 1)"
            )->execute([$eid]);

            $this->db->commit();
            return $eid;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
