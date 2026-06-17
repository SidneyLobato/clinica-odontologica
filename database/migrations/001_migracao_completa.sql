-- ============================================================
-- MIGRAÇÃO COMPLETA — Sistema Prev Dentistas (MVC Refatorado)
-- Execute APÓS importar: database/clinica_prev_dentistas.sql
-- REGRA FUNDAMENTAL: SÓ ADICIONA — nunca remove, nunca recria
-- ============================================================

-- ============================================================
-- PASSO 1: Novas colunas nas tabelas existentes
-- (NULL para compatibilidade — registros antigos ficam intactos)
-- ============================================================

ALTER TABLE `atendimentos`
    ADD COLUMN IF NOT EXISTS `id_dentista_especialista` INT NULL
        COMMENT 'Especialista que executou o procedimento',
    ADD COLUMN IF NOT EXISTS `id_dentista_vendedor` INT NULL
        COMMENT 'Clínico geral que captou/vendeu o tratamento';

ALTER TABLE `atendimento_procedimentos`
    ADD COLUMN IF NOT EXISTS `natureza` VARCHAR(100) NULL
        COMMENT 'canal, orto, cirurgia_especializada, protese, implante',
    ADD COLUMN IF NOT EXISTS `local`    VARCHAR(100) NULL DEFAULT 'Todos'
        COMMENT 'Número do dente ou Todos',
    ADD COLUMN IF NOT EXISTS `descricao` TEXT NULL
        COMMENT 'Observações adicionais';

ALTER TABLE `atendimento_pagamentos`
    ADD COLUMN IF NOT EXISTS `bandeira_id`   INT          NULL
        COMMENT 'FK para bandeiras_cartao',
    ADD COLUMN IF NOT EXISTS `taxa_snapshot` DECIMAL(5,2) NULL DEFAULT 0.00
        COMMENT 'Taxa % aplicada no momento do lançamento — nunca recalcular';

-- ============================================================
-- PASSO 2: Bandeiras de cartão
-- ============================================================
CREATE TABLE IF NOT EXISTS `bandeiras_cartao` (
    `id`    INT         NOT NULL AUTO_INCREMENT,
    `nome`  VARCHAR(50) NOT NULL,
    `ativo` TINYINT(1)  NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_bandeira_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `bandeiras_cartao` (`nome`) VALUES
    ('Visa'), ('Mastercard'), ('Elo'), ('Hipercard'),
    ('American Express'), ('debito');

-- ============================================================
-- PASSO 3: Taxas de cartão com histórico imutável de vigência
-- ============================================================
CREATE TABLE IF NOT EXISTS `taxas_cartao` (
    `id`              INT           NOT NULL AUTO_INCREMENT,
    `bandeira_id`     INT           NOT NULL,
    `parcelas`        TINYINT       NOT NULL COMMENT '1 a 10',
    `percentual`      DECIMAL(5,2)  NOT NULL,
    `vigencia_inicio` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `vigencia_fim`    DATETIME      NULL,
    `usuario_id`      INT           NULL,
    PRIMARY KEY (`id`),
    KEY `idx_taxa` (`bandeira_id`, `parcelas`, `vigencia_fim`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Taxas iniciais baseadas nos valores originais do Financeiro.php
-- Débito genérico
INSERT IGNORE INTO `taxas_cartao` (`bandeira_id`, `parcelas`, `percentual`, `vigencia_inicio`) VALUES
(6,1,0.99,NOW());
-- Visa
INSERT IGNORE INTO `taxas_cartao` (`bandeira_id`, `parcelas`, `percentual`, `vigencia_inicio`) VALUES
(1,1,3.00,NOW()),(1,2,4.43,NOW()),(1,3,5.27,NOW()),(1,4,6.10,NOW()),(1,5,6.92,NOW()),
(1,6,7.73,NOW()),(1,7,8.50,NOW()),(1,8,9.20,NOW()),(1,9,9.90,NOW()),(1,10,10.76,NOW());
-- Mastercard
INSERT IGNORE INTO `taxas_cartao` (`bandeira_id`, `parcelas`, `percentual`, `vigencia_inicio`) VALUES
(2,1,3.00,NOW()),(2,2,4.43,NOW()),(2,3,5.27,NOW()),(2,4,6.10,NOW()),(2,5,6.92,NOW()),
(2,6,7.73,NOW()),(2,7,8.50,NOW()),(2,8,9.20,NOW()),(2,9,9.90,NOW()),(2,10,10.76,NOW());
-- Elo
INSERT IGNORE INTO `taxas_cartao` (`bandeira_id`, `parcelas`, `percentual`, `vigencia_inicio`) VALUES
(3,1,3.20,NOW()),(3,2,4.60,NOW()),(3,3,5.50,NOW()),(3,4,6.30,NOW()),(3,5,7.10,NOW()),
(3,6,7.90,NOW()),(3,7,8.70,NOW()),(3,8,9.40,NOW()),(3,9,10.10,NOW()),(3,10,11.00,NOW());
-- Hipercard
INSERT IGNORE INTO `taxas_cartao` (`bandeira_id`, `parcelas`, `percentual`, `vigencia_inicio`) VALUES
(4,1,3.00,NOW()),(4,2,4.50,NOW()),(4,3,5.40,NOW()),(4,4,6.20,NOW()),(4,5,7.00,NOW()),
(4,6,7.80,NOW()),(4,7,8.60,NOW()),(4,8,9.30,NOW()),(4,9,10.00,NOW()),(4,10,10.80,NOW());
-- American Express
INSERT IGNORE INTO `taxas_cartao` (`bandeira_id`, `parcelas`, `percentual`, `vigencia_inicio`) VALUES
(5,1,3.50,NOW()),(5,2,5.00,NOW()),(5,3,5.80,NOW()),(5,4,6.60,NOW()),(5,5,7.40,NOW());

-- ============================================================
-- PASSO 4: Configuração de comissões — regra global
-- ============================================================
CREATE TABLE IF NOT EXISTS `config_comissoes` (
    `id`                    INT           NOT NULL AUTO_INCREMENT,
    `percentual_ate_meta`   DECIMAL(5,2)  NOT NULL DEFAULT 20.00,
    `percentual_acima_meta` DECIMAL(5,2)  NOT NULL DEFAULT 30.00,
    `valor_meta`            DECIMAL(12,2) NOT NULL DEFAULT 10000.00,
    `ativo`                 TINYINT(1)    NOT NULL DEFAULT 1,
    `vigencia_inicio`       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `usuario_id`            INT           NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrar regra original hardcoded (20% até R$10.000, 30% acima)
INSERT IGNORE INTO `config_comissoes`
    (`percentual_ate_meta`, `percentual_acima_meta`, `valor_meta`, `ativo`)
VALUES (20.00, 30.00, 10000.00, 1);

-- ============================================================
-- PASSO 5: Comissões individuais por dentista
-- ============================================================
CREATE TABLE IF NOT EXISTS `config_comissoes_individuais` (
    `id`                    INT           NOT NULL AUTO_INCREMENT,
    `dentista_id`           INT           NOT NULL,
    `percentual_ate_meta`   DECIMAL(5,2)  NOT NULL,
    `percentual_acima_meta` DECIMAL(5,2)  NOT NULL,
    `valor_meta`            DECIMAL(12,2) NOT NULL,
    `ativo`                 TINYINT(1)    NOT NULL DEFAULT 1,
    `vigencia_inicio`       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `usuario_id`            INT           NULL,
    PRIMARY KEY (`id`),
    KEY `idx_cci` (`dentista_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- PASSO 6: Percentuais configuráveis por tipo de especialidade
-- ============================================================
CREATE TABLE IF NOT EXISTS `config_especialidades` (
    `id`         INT          NOT NULL AUTO_INCREMENT,
    `tipo`       VARCHAR(50)  NOT NULL COMMENT 'canal, orto, cirurgia_especializada, protese, implante',
    `percentual` DECIMAL(5,2) NOT NULL,
    `ativo`      TINYINT(1)   NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_tipo` (`tipo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrar valores originais hardcoded do Financeiro.php
INSERT IGNORE INTO `config_especialidades` (`tipo`, `percentual`) VALUES
    ('canal',                 10.00),
    ('cirurgia_especializada',10.00),
    ('orto',                  50.00),
    ('protese',               10.00),
    ('implante',              50.00);

-- ============================================================
-- PASSO 7: Rateio por categoria de procedimento
-- ============================================================
CREATE TABLE IF NOT EXISTS `config_rateio` (
    `id`                      INT          NOT NULL AUTO_INCREMENT,
    `categoria_procedimento`  VARCHAR(50)  NOT NULL DEFAULT 'todas',
    `percentual_especialista` DECIMAL(5,2) NOT NULL DEFAULT 50.00,
    `percentual_vendedor`     DECIMAL(5,2) NOT NULL DEFAULT 10.00,
    `percentual_clinica`      DECIMAL(5,2) NOT NULL DEFAULT 40.00,
    `ativo`                   TINYINT(1)   NOT NULL DEFAULT 1,
    `vigencia_inicio`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `usuario_id`              INT          NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `config_rateio`
    (`categoria_procedimento`, `percentual_especialista`, `percentual_vendedor`, `percentual_clinica`)
VALUES
    ('todas',        50.00, 10.00, 40.00),
    ('especializado',50.00, 10.00, 40.00),
    ('protese',      10.00,  0.00, 90.00);

-- ============================================================
-- PASSO 8: Configuração geral do sistema (chave→valor)
-- ============================================================
CREATE TABLE IF NOT EXISTS `config_sistema` (
    `id`           INT          NOT NULL AUTO_INCREMENT,
    `chave`        VARCHAR(100) NOT NULL,
    `valor`        TEXT         NOT NULL,
    `descricao`    VARCHAR(255) NULL,
    `atualizado_em` DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_chave` (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `config_sistema` (`chave`, `valor`, `descricao`) VALUES
    ('cenario_comissao', 'global',         'Cenário: global | individual'),
    ('nome_clinica',     'Prev Dentistas', 'Nome da clínica'),
    ('moeda_simbolo',    'R$',             'Símbolo monetário');

-- ============================================================
-- PASSO 9: Histórico de preços dos procedimentos
-- ============================================================
CREATE TABLE IF NOT EXISTS `historico_precos_procedimentos` (
    `id`              INT           NOT NULL AUTO_INCREMENT,
    `procedimento_id` INT           NOT NULL,
    `valor`           DECIMAL(10,2) NOT NULL,
    `vigencia_inicio` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `vigencia_fim`    DATETIME      NULL,
    `usuario_id`      INT           NULL,
    PRIMARY KEY (`id`),
    KEY `idx_hpp` (`procedimento_id`, `vigencia_fim`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Popular histórico com preços atuais (ponto de partida)
INSERT IGNORE INTO `historico_precos_procedimentos` (`procedimento_id`, `valor`, `vigencia_inicio`)
SELECT `id`, `valor_base`, NOW() FROM `procedimentos` WHERE `valor_base` IS NOT NULL;

-- ============================================================
-- PASSO 10: Rateios calculados por atendimento
-- ============================================================
CREATE TABLE IF NOT EXISTS `rateios_atendimento` (
    `id`                       INT           NOT NULL AUTO_INCREMENT,
    `id_atendimento`           INT           NOT NULL,
    `id_dentista_especialista` INT           NULL,
    `id_dentista_vendedor`     INT           NULL,
    `valor_bruto`              DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `valor_taxa_cartao`        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `valor_especialista`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `valor_vendedor`           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `valor_clinica`            DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `valor_liquido`            DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `calculado_em`             DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_rateio` (`id_atendimento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- FIM DA MIGRAÇÃO
-- Nenhuma tabela original foi apagada.
-- Nenhum dado histórico foi alterado.
-- ============================================================
