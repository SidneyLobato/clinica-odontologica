<?php
abstract class BaseModel {
    protected PDO $db;
    protected string $table = '';
    public function __construct(PDO $pdo) { $this->db = $pdo; }
    public function findById(int $id): ?array { $s=$this->db->prepare("SELECT * FROM {$this->table} WHERE id=:id LIMIT 1"); $s->execute(['id'=>$id]); return $s->fetch()?:null; }
    public function findAll(string $w='', array $p=[], string $o=''): array { $sql="SELECT * FROM {$this->table}"; if($w)$sql.=" WHERE $w"; if($o)$sql.=" ORDER BY $o"; $s=$this->db->prepare($sql);$s->execute($p);return $s->fetchAll(); }
    public function insert(array $d): int { $c=implode(',',array_keys($d));$ph=':'.implode(',:',array_keys($d));$s=$this->db->prepare("INSERT INTO {$this->table}($c)VALUES($ph)");$s->execute($d);return(int)$this->db->lastInsertId(); }
    public function update(int $id, array $d): bool { $set=implode(',',array_map(fn($k)=>"$k=:$k",array_keys($d)));$d['_id']=$id;$s=$this->db->prepare("UPDATE {$this->table} SET $set WHERE id=:_id");return $s->execute($d); }
    public function delete(int $id): bool { $s=$this->db->prepare("DELETE FROM {$this->table} WHERE id=:id");return $s->execute(['id'=>$id]); }
    public function count(string $w='', array $p=[]): int { $sql="SELECT COUNT(*) FROM {$this->table}";if($w)$sql.=" WHERE $w";$s=$this->db->prepare($sql);$s->execute($p);return(int)$s->fetchColumn(); }
}
