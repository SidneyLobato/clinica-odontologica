# Como contribuir / Como fazer manutenção

Este documento explica as convenções adotadas no projeto para quem for dar manutenção ou adicionar novas funcionalidades.

---

## Regra mais importante: nunca quebre o isolamento entre empresas

Toda query que busca, insere, atualiza ou deleta dados de negócio **deve** incluir `empresa_id`. O `BaseModel` faz isso automaticamente para as operações padrão (`findById`, `findAll`, `insert`, `update`, `delete`). Para queries customizadas (em controllers ou models específicos), sempre inclua manualmente:

```php
// ✅ CORRETO
$s = $this->db->prepare("SELECT * FROM tabela WHERE id = ? AND empresa_id = ?");
$s->execute([$id, $this->empresaId]);

// ❌ ERRADO — expõe dados de outras empresas
$s = $this->db->prepare("SELECT * FROM tabela WHERE id = ?");
$s->execute([$id]);
```

---

## Adicionando uma nova tabela

1. Adicione `empresa_id int NOT NULL DEFAULT 1` na criação da tabela
2. Adicione `FOREIGN KEY (empresa_id) REFERENCES empresas(id)`
3. Adicione um `CREATE INDEX` em `empresa_id`
4. Crie um Model que estenda `BaseModel`
5. Crie a migração correspondente em `database/migrations/`

---

## Adicionando uma nova rota

No `index.php`, dentro do array `$rotas`:
```php
'minha-rota' => ['MeuController', 'meuMetodo'],
```

O controller deve estender `BaseController` e chamar `requer_login()` (ou `requer_admin()`) no início de cada método público.

---

## Convenções de nomenclatura

| O quê | Convenção | Exemplo |
|---|---|---|
| Controllers | PascalCase + Controller | `ProdutoController` |
| Models | PascalCase + Model | `ProdutoModel` |
| Views | kebab-case ou snake_case | `form.php`, `lista.php` |
| Métodos | camelCase | `listarTodos()`, `salvar()` |
| Tabelas | snake_case | `os_pagamentos` |
| Colunas | snake_case | `empresa_id`, `criado_em` |

---

## Migrações

- **Sempre use `ALTER TABLE ADD COLUMN`**, nunca recrie a tabela
- **Nunca use `DROP`, `TRUNCATE` ou `DELETE` em massa** em migrações
- Nomeie o arquivo com número sequencial: `004_nome_da_migracao.sql`
- Documente no topo do arquivo o que cada comando faz

---

## Dados sensíveis

- **Nunca commite** `config/database.php` com senha real
- Use o arquivo de exemplo como referência e mantenha o real fora do git (`.gitignore`)
- Senhas no banco são sempre `password_hash($senha, PASSWORD_BCRYPT)`
