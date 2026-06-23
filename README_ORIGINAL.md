# Sistema Prev Dentistas — Refatoração MVC Completa
## Projeto Integrado II — UFPA 2026

---

## Como instalar (passo a passo)

### 1. Banco de dados
```sql
-- Passo 1: criar banco e importar dados originais
CREATE DATABASE IF NOT EXISTS clinica_prev_dentistas CHARACTER SET utf8mb4;
mysql -u root -p clinica_prev_dentistas < database/clinica_prev_dentistas.sql

-- Passo 2: executar a migração (só adiciona, nunca apaga)
mysql -u root -p clinica_prev_dentistas < database/migrations/001_migracao_completa.sql
```

### 2. Configurações
Edite `config/app.php`:
```php
define('BASE_URL', '/prev_mvc/');  // ajuste ao seu servidor
```

Edite `config/database.php`:
```php
$host     = 'localhost';
$db_name  = 'clinica_prev_dentistas';
$username = 'root';
$password = 'SUA_SENHA';
```

### 3. Apache / XAMPP
- Coloque a pasta `prev_mvc/` dentro de `htdocs/`
- Certifique-se que `mod_rewrite` está ativo
- O `.htaccess` já está configurado

---

## Arquitetura MVC

```
index.php                        ← Front Controller único
.htaccess                        ← Todas as rotas → index.php

config/
  app.php                        ← BASE_URL, ROOT_PATH
  database.php                   ← Conexão PDO
  session.php                    ← session_start()
  auth.php                       ← is_logado(), is_admin(), requer_*()

app/
  Controllers/
    BaseController.php           ← render(), json(), redirect()
    AuthController.php           ← login, logout
    DashboardController.php      ← painel principal
    PacienteController.php       ← CRUD pacientes
    UsuarioController.php        ← CRUD usuários
    DespesaController.php        ← CRUD despesas
    ProcedimentoController.php   ← CRUD + histórico de preços
    AtendimentoController.php    ← lançamento + confirmar pagamento
    RelatorioController.php      ← diário, dentistas, paciente, financeiro, rateio
    AdminController.php          ← taxas, comissões, especialidades, rateio
    ConfiguracaoController.php   ← perfil e senha do usuário
    ReciboController.php         ← recibo imprimível

  Models/
    BaseModel.php                ← findById, findAll, insert, update, delete
    FinanceiroModel.php          ← substitui Financeiro.php estático (lê do banco)
    PacienteModel.php
    UsuarioModel.php
    DespesaModel.php
    ProcedimentoModel.php        ← atualizarPreco com histórico
    AtendimentoModel.php         ← salvarCompleto + confirmarPagamento
    RelatorioModel.php

  Views/
    layout/header.php + footer.php
    auth/login.php
    dashboard/index.php
    pacientes/index.php + form.php + _form.php
    usuarios/index.php + form.php
    despesas/index.php
    procedimentos/index.php + historico.php
    atendimentos/form.php        ← odontograma clicável + dentista vendedor/especialista
    atendimentos/confirmar_pagamento.php
    relatorios/diario.php
    relatorios/dentistas.php
    relatorios/paciente.php      ← odontograma colorido com histórico
    relatorios/procedimentos.php
    relatorios/financeiro.php    ← gráfico de evolução financeira
    relatorios/rateio.php        ← colunas separadas: especialista|vendedor|clínica|taxa|líquido
    admin/index.php              ← painel admin
    admin/taxas.php              ← grid bandeira × parcelas
    admin/comissoes.php          ← global + individual + especialidades
    admin/rateio.php             ← configuração de rateio
    configuracoes/index.php
    recibo/index.php             ← recibo imprimível

database/
  clinica_prev_dentistas.sql     ← banco original com dados
  migrations/
    001_migracao_completa.sql    ← APENAS adiciona tabelas/colunas novas
```

---

## Tabelas novas (migration)

| Tabela | Finalidade |
|---|---|
| `bandeiras_cartao` | Visa, Master, Elo, etc. |
| `taxas_cartao` | % por bandeira × parcelas, com histórico de vigência |
| `config_comissoes` | Regra global (% até meta, % acima, valor meta) |
| `config_comissoes_individuais` | Regra individual por dentista |
| `config_especialidades` | % por tipo: canal, orto, cirurgia, prótese, implante |
| `config_rateio` | Divisão especialista/vendedor/clínica por categoria |
| `config_sistema` | Chave→valor: cenário_comissao, nome_clinica, etc. |
| `historico_precos_procedimentos` | Preços anteriores preservados |
| `rateios_atendimento` | Rateio calculado e gravado por atendimento |

## Colunas novas nas tabelas existentes

| Tabela | Coluna | Finalidade |
|---|---|---|
| `atendimentos` | `id_dentista_especialista` | Especialista que executou |
| `atendimentos` | `id_dentista_vendedor` | Clínico que vendeu |
| `atendimento_procedimentos` | `natureza` | canal, orto, cirurgia... |
| `atendimento_procedimentos` | `local` | Número do dente |
| `atendimento_procedimentos` | `descricao` | Observações |
| `atendimento_pagamentos` | `bandeira_id` | Bandeira do cartão |
| `atendimento_pagamentos` | `taxa_snapshot` | % travado no momento do lançamento |

---

## Fluxo de atendimento (fiel ao original)

1. Usuário seleciona paciente (autocomplete) e dentista
2. Clica no dente no odontograma → modal com procedimentos
3. Cada procedimento tem: nome, quantidade, natureza, local, status (finalizado/pendente)
4. Procedimentos **finalizados** → atendimento com `status_pagamento = 'pendente'`
5. Procedimentos **pendentes** → atendimento separado com `status_pagamento = 'nao_aplicavel'`
6. Para confirmar pagamento: menu `Confirmar Pagamento` → seleciona atendimento → informa pagamentos com bandeira e parcelas
7. Sistema calcula taxa (snapshot), recalcula comissão com faturamento atualizado, registra rateio

---

## Requisitos do trabalho atendidos

| Requisito | Implementação |
|---|---|
| MVC com POO | Controllers, Models, Views separados. Zero lógica no HTML. |
| Taxas configuráveis por bandeira × parcela | `admin/taxas` → tabela `taxas_cartao` |
| Histórico de taxas imutável | `vigencia_inicio/fim` — ao alterar, fecha vigência anterior |
| Comissões configuráveis (cenário global e individual) | `admin/comissoes` → `config_comissoes` + `config_comissoes_individuais` |
| Especialidades configuráveis (canal, orto, cirurgia, implante, prótese) | `admin/comissoes` → `config_especialidades` |
| Histórico de preços dos procedimentos | `procedimentos/historico` → `historico_precos_procedimentos` |
| Dentista vendedor + especialista na tela de atendimento | `atendimentos/form` — campos `id_dentista_especialista` e `id_dentista_vendedor` |
| Rateio configurável por categoria | `admin/rateio` → `config_rateio` |
| Relatório com valores separados | `relatorios/rateio` — colunas: especialista, vendedor, clínica, taxa, líquido |
| Script SQL completo | `database/migrations/001_migracao_completa.sql` |
| Compatibilidade retroativa | ALTER TABLE IF NOT EXISTS — nenhuma tabela apagada |
