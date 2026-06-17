<?php
class PacienteModel extends BaseModel {
    protected string $table = 'pacientes';

    public function listarPaginado(string $b, int $off, int $pp): array {
        $like = "%$b%";
        $s = $this->db->prepare("SELECT * FROM pacientes WHERE nome LIKE :b1 OR cpf LIKE :b2 ORDER BY nome LIMIT :l OFFSET :o");
        $s->bindValue(':b1', $like);
        $s->bindValue(':b2', $like);
        $s->bindValue(':l',  $pp,  PDO::PARAM_INT);
        $s->bindValue(':o',  $off, PDO::PARAM_INT);
        $s->execute();
        return $s->fetchAll();
    }

    public function contar(string $b): int {
        $like = "%$b%";
        $s = $this->db->prepare("SELECT COUNT(*) FROM pacientes WHERE nome LIKE :b1 OR cpf LIKE :b2");
        $s->execute([':b1' => $like, ':b2' => $like]);
        return (int)$s->fetchColumn();
    }

    public function autocomplete(string $t): array {
        $like = "%$t%";
        $s = $this->db->prepare("SELECT id,nome,cpf,telefone,email,cep,endereco,numero,bairro,cidade,estado,data_nascimento FROM pacientes WHERE nome LIKE :t1 OR cpf LIKE :t2 LIMIT 10");
        $s->execute([':t1' => $like, ':t2' => $like]);
        return $s->fetchAll();
    }

    public function temAtendimentos(int $id): bool {
        $s = $this->db->prepare("SELECT COUNT(*) FROM atendimentos WHERE paciente_id=?");
        $s->execute([$id]);
        return $s->fetchColumn() > 0;
    }

    public function salvar(array $d, ?int $id=null): int {
        $c = [
            'nome'            => $d['paciente_nome']            ?? '',
            'cpf'             => $d['paciente_cpf']             ?: null,
            'data_nascimento' => $d['paciente_data_nascimento'] ?: null,
            'email'           => $d['paciente_email']           ?: null,
            'telefone'        => $d['paciente_telefone']        ?: null,
            'cep'             => $d['paciente_cep']             ?: null,
            'endereco'        => $d['paciente_endereco']        ?: null,
            'numero'          => $d['paciente_numero']          ?: null,
            'bairro'          => $d['paciente_bairro']          ?: null,
            'cidade'          => $d['paciente_cidade']          ?: null,
            'estado'          => $d['paciente_estado']          ?: null,
        ];
        if ($id) { $this->update($id, $c); return $id; }
        return $this->insert($c);
    }
}
