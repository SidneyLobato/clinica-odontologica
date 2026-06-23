# Prev Dentistas SaaS — Sistema de Gestão para Clínicas Odontológicas

> Sistema multi-empresa (SaaS) desenvolvido em PHP MVC para gestão completa de clínicas odontológicas: atendimentos, odontograma, procedimentos, comissões de dentistas, rateio e financeiro.

---

## Índice

- [Visão Geral](#visão-geral)
- [Funcionalidades](#funcionalidades)
- [Arquitetura](#arquitetura)
- [Banco de Dados](#banco-de-dados)
- [Instalação](#instalação)
- [Configuração Multi-Tenant](#configuração-multi-tenant)
- [Painel Super Admin](#painel-super-admin)
- [Lógica Financeira](#lógica-financeira)
- [Segurança e Isolamento de Dados](#segurança-e-isolamento-de-dados)
- [Estrutura de Pastas](#estrutura-de-pastas)
- [Rastreabilidade e Histórico](#rastreabilidade-e-histórico)
- [Migrações de Banco](#migrações-de-banco)
- [Changelog](#changelog)

---

## Visão Geral

O **Prev Dentistas SaaS** é uma plataforma que permite que **múltiplas clínicas odontológicas** utilizem o mesmo sistema de gestão de forma isolada. Cada clínica tem seus próprios dados, dentistas, pacientes e histórico financeiro — sem nenhuma interferência entre elas.

A plataforma possui dois níveis de acesso:

| Nível | Quem é | O que faz |
|---|---|---|
| **Super Admin** | Dono da plataforma | Gerencia todas as clínicas, planos, cobrança e suporte |
| **Clínica** | Cliente da plataforma | Gerencia atendimentos, pacientes, financeiro da própria clínica |

---

## Funcionalidades

### Para cada clínica (tenant)

- **Atendimentos**
  - Lançamento com múltiplos procedimentos por atendimento
  - Status por procedimento: **Finalizado** (pago na hora) ou **Pendente** (aguarda pagamento)
  - Confirmação de pagamento com cálculo automático de taxas e comissões
  - Upload de arquivo/raio-x por atendimento
- **Odontograma** — rastreamento de status por dente (feito/pendente/misto)
- **Pacientes** — cadastro completo com histórico clínico e odontograma por paciente
- **Procedimentos** — tabela de preços com histórico imutável de valores
- **Financeiro**
  - Taxas de cartão separadas por **Débito** e **Crédito** (1x a 10x por bandeira)
  - Checkbox "Repassar taxa ao cliente" no débito — a clínica decide na hora
  - Taxa calculada em tempo real na tela de pagamento
  - Histórico imutável de vigências
- **Comissões**
  - Cenário Global (uma regra para todos os dentistas)
  - Cenário Individual (regra diferente por dentista)
  - Suporte a categorias especiais: canal, cirurgia especializada, prótese, orto, implante
- **Rateio** — divisão configurável entre especialista, vendedor e clínica para procedimentos especializados
- **Despesas** — registro de saídas financeiras
- **Relatórios com gráficos**
  - Resumo Diário (cartões + gráfico de comissões por dentista)
  - Por Dentista (gráfico de barras: faturamento × comissão × valor para a clínica)
  - Financeiro Geral (gráfico de linha: faturamento × despesas × lucro + formas de pagamento)
  - Por Procedimento (barras horizontais por quantidade + rosca por faturamento)
  - Rateio detalhado (especialista, vendedor, clínica, taxa)
  - Por Paciente (histórico clínico + odontograma)
- **Dashboard** — cartões de resumo do mês, alerta de pendentes, últimos atendimentos
- **Recibo de pagamento** — para impressão, com nome dinâmico da clínica
- **Usuários** — perfis: `proprietario`, `dentista`, `recepcionista`

### Para o Super Admin

- Dashboard com **MRR**, total de clínicas, trials e suspensas
- Alerta de **trials vencendo nos próximos 7 dias**
- Gestão de planos: Trial → Básico → Profissional, com valor mensal configurável
- Ativar, suspender ou cancelar clínicas
- Anotações internas por clínica (visíveis só para o super admin)
- **"Entrar como suporte"** — faz login como proprietário da clínica sem precisar da senha

---

## Arquitetura

```
┌─────────────────────────────────────────────────────┐
│                   NAVEGADOR DO USUÁRIO               │
└────────────────────────┬────────────────────────────┘
                         │ HTTP
┌────────────────────────▼────────────────────────────┐
│                  APACHE (WAMP / Linux)               │
│           mod_rewrite → index.php                    │
└────────────────────────┬────────────────────────────┘
                         │
┌────────────────────────▼────────────────────────────┐
│                     index.php                        │
│  1. Resolve o tenant (TenantResolver)                │
│  2. Identifica o tipo de acesso:                     │
│     • landing     → EmpresaController (cadastro)     │
│     • superadmin  → SuperAdminController             │
│     • empresa     → Controllers de negócio           │
│  3. Roteia para o Controller correto                 │
└────────────┬──────────────────────┬─────────────────┘
             │                      │
┌────────────▼──────────┐  ┌───────▼────────────────┐
│     Controllers        │  │        Models           │
│  Recebem a requisição  │  │  Toda lógica de dados   │
│  Chamam os Models      │  │  BaseModel com filtro   │
│  Passam dados pra View │  │  automático empresa_id  │
└────────────┬──────────┘  └───────┬────────────────┘
             │                      │
┌────────────▼──────────────────────▼────────────────┐
│              Views (PHP + Chart.js)                  │
│    HTML + CSS + gráficos nos relatórios              │
└─────────────────────────────────────────────────────┘
```

### Padrão MVC

- **Models** estendem `BaseModel`, que aplica `empresa_id` automaticamente em todas as queries
- **Controllers** estendem `BaseController`, que injeta `$this->empresaId` e `$this->empresa`
- **Views** recebem os dados via `extract()` e não fazem queries diretas
- **Chart.js** é carregado globalmente no `header.php` e usado nas views de relatório

### Resolução de Tenant

O `TenantResolver` identifica qual clínica está acessando:

1. **Produção**: subdomínio da URL (`prevdentistas.seusistema.com.br` → slug `prevdentistas`)
2. **Desenvolvimento local**: sessão PHP ou parâmetro `?empresa_id=X`

---

## Banco de Dados

### Estratégia Multi-Tenant

Banco único (`clinica_prev_dentistas`) com coluna `empresa_id` em **todas as tabelas de negócio**. O isolamento é automático via `BaseModel::filtro()`.

### Principais tabelas

| Tabela | Descrição |
|---|---|
| `empresas` | Clínicas cadastradas na plataforma (tenants) |
| `super_admins` | Usuários do painel de administração da plataforma |
| `usuarios` | Equipe de cada clínica (proprietário, dentista, recepcionista) |
| `pacientes` | Pacientes de cada clínica |
| `procedimentos` | Tabela de procedimentos e preços de cada clínica |
| `historico_precos_procedimentos` | Histórico imutável de preços por procedimento |
| `atendimentos` | Atendimentos realizados |
| `atendimento_procedimentos` | Procedimentos executados em cada atendimento (com status por dente) |
| `atendimento_pagamentos` | Pagamentos registrados por atendimento |
| `rateios_atendimento` | Rateio calculado por atendimento especializado |
| `bandeiras_cartao` | Bandeiras de cartão por clínica |
| `taxas_cartao` | Taxas com `tipo` (debito/credito), `parcelas`, histórico de vigência |
| `config_comissoes` | Regra global de comissão com vigência |
| `config_comissoes_individuais` | Regras individuais por dentista |
| `config_especialidades` | Percentuais por tipo de especialidade (canal, prótese, orto...) |
| `config_rateio` | Regras de rateio por categoria de procedimento |
| `despesas` | Saídas financeiras da clínica |
| `config_sistema` | Configurações gerais da clínica |

### Relacionamento empresa_id

```sql
empresas (id)
   ├── usuarios.empresa_id
   ├── pacientes.empresa_id
   ├── procedimentos.empresa_id
   ├── atendimentos.empresa_id
   ├── bandeiras_cartao.empresa_id
   ├── taxas_cartao.empresa_id
   ├── config_comissoes.empresa_id
   ├── config_comissoes_individuais.empresa_id
   ├── config_especialidades.empresa_id
   ├── config_rateio.empresa_id
   ├── config_sistema.empresa_id
   └── despesas.empresa_id
```

---

## Instalação

### Requisitos

- PHP 8.1+
- MySQL 5.7+ ou MariaDB 10.4+
- Apache com `mod_rewrite` habilitado (WAMP, XAMPP ou Linux)
- Extensão `intl` do PHP (para formatação de datas em português)

### Passo a passo

**1. Copiar os arquivos**
```
C:\wamp64\www\prev_mvc\   (Windows/WAMP)
/var/www/html/prev_mvc/   (Linux)
```

**2. Criar o banco de dados**

Se for uma instalação do zero:
```sql
CREATE DATABASE clinica_prev_dentistas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```
E importe `database/clinica_prev_dentistas.sql`.

Se já tem um banco existente (clínica em uso), **não crie um banco novo** — apenas rode as migrações abaixo.

**3. Rodar as migrações**

> ⚠️ Faça backup antes: `mysqldump -u root -p clinica_prev_dentistas > backup.sql`

```
database/migrations/002_multiempresa.sql      ← obrigatória (transforma em SaaS)
database/migrations/003_taxas_debito_credito.sql
```

**4. Configurar a conexão**

Edite `config/database.php`:
```php
$host     = 'localhost';
$db_name  = 'clinica_prev_dentistas';
$username = 'root';
$password = '';
```

**5. Configurar o Apache** (desenvolvimento local)

Em `httpd-vhosts.conf`:
```apache
<VirtualHost *:80>
    ServerName prevdentistas.localhost
    DocumentRoot "c:/wamp64/www/prev_mvc"
</VirtualHost>
```

Em `hosts`:
```
127.0.0.1   prevdentistas.localhost
127.0.0.1   admin.localhost
```

**6. Acessar o sistema**

| URL | O que é |
|---|---|
| `http://localhost/prev_mvc/login` | Login unificado (qualquer clínica) |
| `http://localhost/prev_mvc/cadastro` | Cadastro self-service de nova clínica |
| `http://localhost/prev_mvc/superadmin/login` | Painel do super admin |
| `http://prevdentistas.localhost/` | Acesso direto por subdomínio |

**Credenciais padrão super admin:** `superadmin` / `superadmin123` ← **troque imediatamente**

---

## Configuração Multi-Tenant

### Desenvolvimento local

O sistema detecta `localhost` e usa a sessão PHP para identificar a clínica. O login unificado identifica a clínica pelo e-mail digitado — não é necessário nenhum link especial por clínica.

### Produção (com domínio próprio)

1. Defina `ROOT_DOMAIN` em `config/app.php`:
```php
define('ROOT_DOMAIN', 'seusistema.com.br');
```

2. Configure DNS com wildcard:
```
*.seusistema.com.br → seu servidor
```

3. Configure Apache:
```apache
<VirtualHost *:443>
    ServerName seusistema.com.br
    ServerAlias *.seusistema.com.br
    DocumentRoot /var/www/html/prev_mvc
</VirtualHost>
```

---

## Painel Super Admin

Acesso: `admin.seusistema.com.br` (produção) ou `localhost/prev_mvc/superadmin/` (dev)

### Gestão de planos

| Plano | Comportamento |
|---|---|
| **Trial** | Acesso gratuito por N dias (padrão: 30) |
| **Básico** | Plano pago com valor mensal configurável |
| **Profissional** | Plano pago com valor mensal configurável |

### Modo suporte

O super admin pode entrar em qualquer clínica como o proprietário dela, sem saber a senha. Uma faixa laranja indica o modo suporte e permite voltar ao painel com um clique.

---

## Lógica Financeira

### Fluxo de um atendimento

```
1. Dentista lança o atendimento
   └── Seleciona paciente, procedimentos, valores e status de cada um

2. Para procedimentos "Finalizados" (pagos na hora)
   └── Sistema pede imediatamente a forma de pagamento
       └── Calcula taxa de cartão em tempo real
       └── Registra pagamento e calcula comissão

3. Para procedimentos "Pendentes"
   └── Atendimento fica aguardando pagamento
   └── Aparece no dashboard como "pendente"
   └── Recepcionista confirma pagamento depois
       └── Recalcula comissão com o faturamento mensal real

4. Comissão recalculada no momento do pagamento
   └── Leva em conta o faturamento acumulado no mês
   └── Aplica percentual correto (abaixo ou acima da meta)
```

### Categorias de procedimento e cálculo de comissão

| Categoria | Lógica de comissão |
|---|---|
| `geral` | % sobre o valor, com meta mensal (percentual sobe se ultrapassar a meta) |
| `especializado` | % fixo por tipo (canal, cirurgia, orto, prótese, implante) |
| `protese` | % fixo de prótese, com desconto do custo de laboratório |

### Rateio (procedimentos especializados)

Para procedimentos das categorias `especializado` e `protese`, o valor líquido é dividido entre:
- **Especialista** — dentista que executou o procedimento
- **Vendedor** — dentista que indicou/captou o caso (exceto para orto)
- **Clínica** — percentual da clínica

Os percentuais são configuráveis por categoria em `Financeiro → Rateio`.

### Taxas de cartão

- **Débito**: taxa única à vista. Checkbox "Repassar taxa ao cliente" na hora do lançamento — se desmarcado, a clínica absorve o custo e o valor total não é descontado do paciente
- **Crédito**: taxa por número de parcelas (1x a 10x) por bandeira
- Toda alteração de taxa cria um novo registro no histórico (vigência). Atendimentos antigos sempre calculam com a taxa da época

---

## Segurança e Isolamento de Dados

### Isolamento por empresa_id

Todo acesso a dados passa pelo `BaseModel`, que aplica o filtro automaticamente:

```php
protected function filtro(string $extra = '', array $params = []): array {
    $conditions = [];
    if ($this->empresaId !== null) {
        $conditions[]    = 'empresa_id = :__eid';
        $params['__eid'] = $this->empresaId;
    }
    // ...
}
```

Um usuário de uma clínica **nunca consegue ver, editar ou apagar** dados de outra clínica — mesmo conhecendo o ID numérico de um registro — porque a query sempre inclui `AND empresa_id = {id_da_clinica_atual}`.

### Verificações específicas

- Ao editar um atendimento: valida se pertence à clínica antes de alterar
- Ao confirmar pagamento: valida se o atendimento pertence à clínica
- Ao exibir recibo: valida `empresa_id` antes de exibir qualquer dado
- Ao salvar comissão/rateio: filtra `UPDATE` por `empresa_id` para não afetar outras clínicas

### Sessão

Usa `session_name('PREVDENTISTAS_SESSID')` para evitar conflito de cookies com outros sistemas no mesmo domínio `.localhost`.

### Proteção contra loop de redirecionamento

O `config/auth.php` possui uma trava: se detectar mais de 3 redirecionamentos em menos de 5 segundos, limpa a sessão e exibe uma tela de erro explicativa em vez de continuar o loop.

---

## Estrutura de Pastas

```
prev_mvc/
├── app/
│   ├── Controllers/
│   │   ├── BaseController.php          # Classe base com empresa_id injetado
│   │   ├── AuthController.php          # Login unificado + modo suporte
│   │   ├── AdminController.php         # Taxas, comissões, rateio (admin da clínica)
│   │   ├── DashboardController.php
│   │   ├── AtendimentoController.php   # Lançamento + confirmação de pagamento
│   │   ├── PacienteController.php
│   │   ├── ProcedimentoController.php
│   │   ├── DespesaController.php
│   │   ├── RelatorioController.php
│   │   ├── ReciboController.php
│   │   ├── UsuarioController.php
│   │   ├── ConfiguracaoController.php
│   │   ├── EmpresaController.php       # Landing page + cadastro self-service
│   │   └── SuperAdminController.php    # Painel da plataforma
│   ├── Models/
│   │   ├── BaseModel.php               # CRUD com filtro automático empresa_id
│   │   ├── AtendimentoModel.php        # Lógica complexa de pagamento + comissão
│   │   ├── FinanceiroModel.php         # Taxas (débito/crédito), comissões, rateio
│   │   ├── RelatorioModel.php          # Queries de relatórios e gráficos
│   │   ├── PacienteModel.php
│   │   ├── ProcedimentoModel.php       # Inclui histórico de preços
│   │   ├── DespesaModel.php
│   │   ├── UsuarioModel.php
│   │   └── EmpresaModel.php            # Gestão de tenants + provisionamento
│   ├── Views/
│   │   ├── layout/
│   │   │   ├── header.php              # Navbar + Chart.js + dropdown corrigido
│   │   │   └── footer.php
│   │   ├── auth/login.php              # Login dinâmico por clínica
│   │   ├── dashboard/index.php         # Cartões + pendentes + últimos atendimentos
│   │   ├── atendimentos/
│   │   │   ├── form.php                # Lançamento com taxa em tempo real
│   │   │   └── confirmar_pagamento.php # Confirmação com taxa em tempo real
│   │   ├── pacientes/
│   │   ├── relatorios/                 # Cada relatório com seu gráfico específico
│   │   ├── admin/                      # Taxas, comissões, rateio
│   │   ├── recibo/index.php            # Para impressão — nome dinâmico da clínica
│   │   ├── landing/                    # Página de cadastro self-service
│   │   └── superadmin/                 # Painel da plataforma
│   └── Core/
│       └── TenantResolver.php          # Resolve qual clínica está acessando
├── config/
│   ├── app.php                         # ROOT_DOMAIN e constantes
│   ├── database.php                    # Conexão PDO
│   ├── auth.php                        # Funções de sessão, permissão e anti-loop
│   └── session.php                     # session_name único (PREVDENTISTAS_SESSID)
├── database/
│   ├── clinica_prev_dentistas.sql      # Schema completo (fresh install)
│   └── migrations/
│       ├── 001_migracao_completa.sql   # Migração original pré-SaaS
│       ├── 002_multiempresa.sql        # Transforma o sistema em SaaS multi-empresa
│       └── 003_taxas_debito_credito.sql # Separa débito e crédito nas taxas
├── public/
│   └── assets/
│       └── css/style.css
├── uploads/                            # Arquivos de atendimentos (raio-x, etc.)
├── .htaccess
└── index.php                           # Roteador principal
```

---

## Rastreabilidade e Histórico

### Preços de procedimentos

```
Cada alteração de preço cria um registro em historico_precos_procedimentos
com vigencia_inicio e vigencia_fim. Atendimentos antigos sempre referenciam
o valor cobrado na época — não o valor atual.

procedimentos (id=5, valor_base=350.00)  ← valor atual
historico_precos_procedimentos
  ├── procedimento_id=5, valor=200.00, vigencia_fim=2025-01-15  ← histórico
  ├── procedimento_id=5, valor=300.00, vigencia_fim=2026-03-01  ← histórico
  └── procedimento_id=5, valor=350.00, vigencia_fim=NULL        ← vigente
```

### Taxas de cartão

```
taxas_cartao
  ├── bandeira=Visa tipo=credito parcelas=1 pct=3.00 vigencia_fim=2026-01-01  ← histórico
  ├── bandeira=Visa tipo=credito parcelas=1 pct=2.80 vigencia_fim=NULL         ← vigente
  └── bandeira=Visa tipo=debito  parcelas=1 pct=1.50 vigencia_fim=NULL         ← vigente
```

### Comissões

Mesmo padrão de vigência. O valor pago ao dentista em cada atendimento fica registrado em `atendimentos.comissao_dentista`, independente de futuras alterações na regra.

### Rateio

Para procedimentos especializados, o valor exato pago a cada parte (especialista, vendedor, clínica) fica registrado em `rateios_atendimento` no momento da confirmação do pagamento.

---

## Migrações de Banco

Todas as migrações são **100% aditivas**:

| Arquivo | O que faz |
|---|---|
| `clinica_prev_dentistas.sql` | Schema inicial completo |
| `001_migracao_completa.sql` | Melhorias pré-SaaS (índices, ajustes de schema) |
| `002_multiempresa.sql` | Cria tabelas `empresas` e `super_admins`; adiciona `empresa_id` em 12 tabelas via `ALTER TABLE ADD COLUMN DEFAULT 1`; corrige unicidades de globais para por-empresa; cria índices de performance |
| `003_taxas_debito_credito.sql` | Adiciona coluna `tipo` ENUM('debito','credito') em `taxas_cartao`; adiciona `repassado_cliente` em `atendimento_pagamentos`; semeia taxas de débito iniciais copiando o crédito 1x de cada bandeira |

### Garantias da migração 002

- Toda linha existente em qualquer tabela recebe `empresa_id = 1` automaticamente (via `DEFAULT 1`)
- A clínica original vira empresa_id=1 com todos os dados intactos
- Nenhum `DROP`, `TRUNCATE` ou `DELETE` em massa é executado

---

## Changelog

### v1.0.0 — Sistema original (mono-clínica)
- Sistema de gestão odontológica para uma única clínica
- Atendimentos, procedimentos, odontograma, despesas, relatórios
- Comissões por dentista com regras global e individual
- Rateio entre especialista, vendedor e clínica
- Taxas de cartão por bandeira e parcelas com histórico

### v2.0.0 — Multi-empresa (SaaS)
- Arquitetura multi-tenant com banco único e `empresa_id`
- `TenantResolver` por subdomínio (produção) ou sessão (dev)
- `BaseModel` com filtro automático de clínica em todas as queries
- Painel Super Admin com MRR, gestão de planos e modo suporte
- Cadastro self-service de novas clínicas com provisionamento automático
- Login unificado — sistema identifica a clínica pelo e-mail digitado
- Recibos e tela de login com nome dinâmico da clínica
- Migração 002: 12 tabelas alteradas, dados originais 100% preservados
- Correção de bugs de isolamento: comissão/rateio não afeta mais outras clínicas
- Proteção contra loop infinito de redirecionamento

### v2.1.0 — Taxas Débito/Crédito separadas
- Débito e crédito com taxas independentes por bandeira
- Checkbox "Repassar taxa ao cliente" no débito (decisão feita na hora do lançamento)
- Cálculo em tempo real: taxa %, valor da taxa e líquido exibidos antes de salvar
- Histórico imutável mantido para débito e crédito separadamente

### v2.2.0 — Dashboard e Relatórios com gráficos
- Dashboard reformulado: cartões de resumo + alerta de pendentes + últimos atendimentos
- Gráficos movidos para os relatórios específicos (cada informação no seu lugar)
- Relatório por Dentista: gráfico de barras comparativo
- Relatório Financeiro: gráfico de linha faturamento × despesas × lucro + formas de pagamento
- Relatório por Procedimento: barras horizontais por quantidade + rosca por faturamento
- Relatório Diário: cartões do dia + gráfico de comissões por dentista
- Dropdown do menu corrigido (cor sólida, sem transparência)
- Cabeçalho com grid 3 colunas — menu sempre centralizado
- Formulário de cadastro com campo "Seu nome" separado do nome da clínica
