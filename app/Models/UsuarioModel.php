<?php
class UsuarioModel extends BaseModel {
    protected string $table = 'usuarios';
    public function listarTodos(): array { return $this->db->query("SELECT id,nome,login,perfil FROM usuarios ORDER BY nome")->fetchAll(); }
    public function listarDentistas(): array { return $this->db->query("SELECT id,nome FROM usuarios WHERE perfil='dentista' ORDER BY nome")->fetchAll(); }
    public function porLogin(string $l): ?array { $s=$this->db->prepare("SELECT * FROM usuarios WHERE login=:l LIMIT 1");$s->execute(['l'=>$l]);return $s->fetch()?:null; }
    public function temAtendimentos(int $id): bool { $s=$this->db->prepare("SELECT COUNT(*) FROM atendimentos WHERE id_dentista=?");$s->execute([$id]);return$s->fetchColumn()>0; }
    public function salvar(array $d, ?int $id=null): int {
        $c=['nome'=>trim($d['nome']),'login'=>trim($d['login']),'perfil'=>$d['perfil']];
        if(!empty($d['senha']))$c['senha']=password_hash($d['senha'],PASSWORD_BCRYPT);
        if($id){$this->update($id,$c);return $id;}return $this->insert($c);
    }
}
