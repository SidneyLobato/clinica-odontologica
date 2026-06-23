<?php
class UsuarioModel extends BaseModel {
    protected string $table = 'usuarios';

    public function listarTodos(): array {
        [$where, $params] = $this->filtro();
        $s = $this->db->prepare("SELECT id,nome,login,perfil FROM usuarios $where ORDER BY nome");
        $s->execute($params);
        return $s->fetchAll();
    }

    public function listarDentistas(): array {
        [$where, $params] = $this->filtro("perfil='dentista'");
        $s = $this->db->prepare("SELECT id,nome FROM usuarios $where ORDER BY nome");
        $s->execute($params);
        return $s->fetchAll();
    }

    /** Busca por login em TODAS as clínicas (login único faz parte do fluxo de login unificado) */
    public function porLogin(string $l): ?array {
        $s = $this->db->prepare("SELECT * FROM usuarios WHERE login=:l LIMIT 1");
        $s->execute(['l' => $l]);
        return $s->fetch() ?: null;
    }

    public function temAtendimentos(int $id): bool {
        $eidClause = $this->empresaId !== null ? ' AND empresa_id = ?' : '';
        $params    = $this->empresaId !== null ? [$id, $this->empresaId] : [$id];
        $s = $this->db->prepare("SELECT COUNT(*) FROM atendimentos WHERE id_dentista=?$eidClause");
        $s->execute($params);
        return $s->fetchColumn() > 0;
    }

    public function salvar(array $d, ?int $id = null): int {
        $c = ['nome' => trim($d['nome']), 'login' => trim($d['login']), 'perfil' => $d['perfil']];
        if (!empty($d['senha'])) $c['senha'] = password_hash($d['senha'], PASSWORD_BCRYPT);
        if ($id) { $this->update($id, $c); return $id; }
        return $this->insert($c);
    }
}
