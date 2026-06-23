<?php
/**
 * BaseModel — Multi-empresa
 *
 * Toda query é automaticamente filtrada por empresa_id.
 * Os models existentes (PacienteModel, AtendimentoModel etc.) continuam
 * funcionando igual — o isolamento acontece aqui, de forma transparente.
 */
abstract class BaseModel
{
    protected PDO    $db;
    protected string $table     = '';
    protected ?int   $empresaId = null;

    public function __construct(PDO $pdo, ?int $empresaId = null)
    {
        $this->db = $pdo;
        $this->empresaId = $empresaId ?? TenantResolver::$empresaId;
    }

    /**
     * Retorna a cláusula WHERE já com empresa_id e parâmetros mesclados.
     * Uso: [$where, $params] = $this->filtro('ativo = :ativo', ['ativo' => 1]);
     */
    protected function filtro(string $extra = '', array $params = []): array
    {
        $conditions = [];

        if ($this->empresaId !== null) {
            $conditions[]    = 'empresa_id = :__eid';
            $params['__eid'] = $this->empresaId;
        }

        if ($extra !== '') {
            $conditions[] = "($extra)";
        }

        $where = $conditions ? ('WHERE ' . implode(' AND ', $conditions)) : '';
        return [$where, $params];
    }

    public function findById(int $id): ?array
    {
        [$where, $params] = $this->filtro('id = :id', ['id' => $id]);
        $s = $this->db->prepare("SELECT * FROM {$this->table} $where LIMIT 1");
        $s->execute($params);
        return $s->fetch() ?: null;
    }

    public function findAll(string $w = '', array $p = [], string $o = ''): array
    {
        [$where, $params] = $this->filtro($w, $p);
        $sql = "SELECT * FROM {$this->table} $where";
        if ($o) $sql .= " ORDER BY $o";
        $s = $this->db->prepare($sql);
        $s->execute($params);
        return $s->fetchAll();
    }

    public function insert(array $d): int
    {
        if ($this->empresaId !== null && !isset($d['empresa_id'])) {
            $d['empresa_id'] = $this->empresaId;
        }
        $cols = implode(', ', array_keys($d));
        $ph   = ':' . implode(', :', array_keys($d));
        $s    = $this->db->prepare("INSERT INTO {$this->table} ($cols) VALUES ($ph)");
        $s->execute($d);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool
    {
        // Garante que só atualiza registros da própria empresa
        if ($this->findById($id) === null) return false;

        $set = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($d)));
        $d['_id'] = $id;

        if ($this->empresaId !== null) {
            $d['__eid'] = $this->empresaId;
            $s = $this->db->prepare(
                "UPDATE {$this->table} SET $set WHERE id = :_id AND empresa_id = :__eid"
            );
        } else {
            $s = $this->db->prepare("UPDATE {$this->table} SET $set WHERE id = :_id");
        }
        return $s->execute($d);
    }

    public function delete(int $id): bool
    {
        // Garante que só exclui registros da própria empresa
        if ($this->findById($id) === null) return false;

        if ($this->empresaId !== null) {
            $s = $this->db->prepare(
                "DELETE FROM {$this->table} WHERE id = :id AND empresa_id = :eid"
            );
            return $s->execute(['id' => $id, 'eid' => $this->empresaId]);
        }
        $s = $this->db->prepare("DELETE FROM {$this->table} WHERE id = :id");
        return $s->execute(['id' => $id]);
    }

    public function count(string $w = '', array $p = []): int
    {
        [$where, $params] = $this->filtro($w, $p);
        $sql = "SELECT COUNT(*) FROM {$this->table} $where";
        $s   = $this->db->prepare($sql);
        $s->execute($params);
        return (int)$s->fetchColumn();
    }
}
