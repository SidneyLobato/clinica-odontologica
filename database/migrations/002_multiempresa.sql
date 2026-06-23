-- ============================================================================
-- MIGRAÇÃO 002 — Multi-empresa (SaaS)
-- ============================================================================
-- REGRA DE OURO: esta migração é 100% ADITIVA.
--   • Nenhum DROP TABLE, nenhum TRUNCATE, nenhum DELETE em massa.
--   • Só ALTER TABLE ADD COLUMN / ADD INDEX / ADD CONSTRAINT.
--   • Toda linha que já existe recebe empresa_id = 1 automaticamente
--     (via DEFAULT 1), ou seja, a clínica "Prev Dentistas" que já está
--     em uso continua com TODO o histórico dela intacto.
--
-- ANTES DE RODAR: faça backup do banco.
--   mysqldump -u root -p clinica_prev_dentistas > backup_antes_saas.sql
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ──────────────────────────────────────────────────────────────────────────
-- 1. TABELA DE EMPRESAS (tenants) — nova, não afeta nada existente
-- ──────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `empresas` (
  `id`                int          NOT NULL AUTO_INCREMENT,
  `nome`               varchar(150) NOT NULL,
  `cnpj`               varchar(18)  DEFAULT NULL,
  `email`              varchar(100) NOT NULL,
  `telefone`           varchar(20)  DEFAULT NULL,
  `slug`               varchar(50)  NOT NULL UNIQUE COMMENT 'Subdomínio: slug.seusistema.com.br',
  `plano`              enum('trial','basico','profissional') NOT NULL DEFAULT 'trial',
  `valor_mensal`       decimal(10,2) NOT NULL DEFAULT 0.00,
  `status`             enum('ativo','suspenso','cancelado')  NOT NULL DEFAULT 'ativo',
  `trial_ate`          date         DEFAULT NULL,
  `observacoes_admin`  text         DEFAULT NULL,
  `criado_em`          datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- A clínica que JÁ EXISTE e já está em uso vira a empresa_id = 1.
-- Usa o nome que já está salvo em config_sistema (chave 'nome_clinica'),
-- ou "Prev Dentistas" como alternativa.
INSERT INTO `empresas` (`id`, `nome`, `slug`, `email`, `plano`, `status`)
SELECT 1,
       COALESCE((SELECT valor FROM config_sistema WHERE chave = 'nome_clinica' LIMIT 1), 'Prev Dentistas'),
       'prevdentistas',
       'contato@prevdentistas.com.br',
       'profissional',
       'ativo'
ON DUPLICATE KEY UPDATE nome = VALUES(nome);
-- ↑ Depois, no painel super admin, você pode corrigir o e-mail/telefone
--   reais da clínica a qualquer momento — isso aqui é só o valor inicial.

-- ──────────────────────────────────────────────────────────────────────────
-- 2. SUPER ADMINS — nova tabela, gerencia a plataforma toda
-- ──────────────────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `super_admins` (
  `id`        int          NOT NULL AUTO_INCREMENT,
  `nome`      varchar(100) NOT NULL,
  `login`     varchar(50)  NOT NULL UNIQUE,
  `senha`     varchar(255) NOT NULL,
  `ativo`     tinyint(1)   NOT NULL DEFAULT 1,
  `criado_em` datetime     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Super admin inicial (login: superadmin / senha: superadmin123)
-- TROQUE ESSA SENHA depois de instalar — veja o guia de instalação.
INSERT INTO `super_admins` (`nome`, `login`, `senha`) VALUES
('Super Admin', 'superadmin', '$2b$10$onVTUEKNmQsgohHlQiwaz.nv6gcdxtfKZRSrED1hjfxgRf9xvIzw2')
ON DUPLICATE KEY UPDATE nome = VALUES(nome);

-- ──────────────────────────────────────────────────────────────────────────
-- 3. ADICIONAR empresa_id NAS TABELAS QUE JÁ EXISTEM
--    Todas as linhas atuais ficam com empresa_id = 1 automaticamente.
-- ──────────────────────────────────────────────────────────────────────────

ALTER TABLE `usuarios`
  ADD COLUMN `empresa_id` int NOT NULL DEFAULT 1 AFTER `id`,
  ADD CONSTRAINT `fk_usuarios_empresa`
    FOREIGN KEY (`empresa_id`) REFERENCES `empresas`(`id`) ON DELETE CASCADE;

ALTER TABLE `pacientes`
  ADD COLUMN `empresa_id` int NOT NULL DEFAULT 1 AFTER `id`,
  ADD CONSTRAINT `fk_pacientes_empresa`
    FOREIGN KEY (`empresa_id`) REFERENCES `empresas`(`id`) ON DELETE CASCADE;

ALTER TABLE `procedimentos`
  ADD COLUMN `empresa_id` int NOT NULL DEFAULT 1 AFTER `id`,
  ADD CONSTRAINT `fk_procedimentos_empresa`
    FOREIGN KEY (`empresa_id`) REFERENCES `empresas`(`id`) ON DELETE CASCADE;

ALTER TABLE `despesas`
  ADD COLUMN `empresa_id` int NOT NULL DEFAULT 1 AFTER `id`,
  ADD CONSTRAINT `fk_despesas_empresa`
    FOREIGN KEY (`empresa_id`) REFERENCES `empresas`(`id`) ON DELETE CASCADE;

ALTER TABLE `atendimentos`
  ADD COLUMN `empresa_id` int NOT NULL DEFAULT 1 AFTER `id`,
  ADD CONSTRAINT `fk_atendimentos_empresa`
    FOREIGN KEY (`empresa_id`) REFERENCES `empresas`(`id`) ON DELETE CASCADE;

ALTER TABLE `bandeiras_cartao`
  ADD COLUMN `empresa_id` int NOT NULL DEFAULT 1 AFTER `id`,
  ADD CONSTRAINT `fk_bandeiras_empresa`
    FOREIGN KEY (`empresa_id`) REFERENCES `empresas`(`id`) ON DELETE CASCADE;

ALTER TABLE `taxas_cartao`
  ADD COLUMN `empresa_id` int NOT NULL DEFAULT 1 AFTER `id`,
  ADD CONSTRAINT `fk_taxas_empresa`
    FOREIGN KEY (`empresa_id`) REFERENCES `empresas`(`id`) ON DELETE CASCADE;

ALTER TABLE `config_comissoes`
  ADD COLUMN `empresa_id` int NOT NULL DEFAULT 1 AFTER `id`,
  ADD CONSTRAINT `fk_comissoes_empresa`
    FOREIGN KEY (`empresa_id`) REFERENCES `empresas`(`id`) ON DELETE CASCADE;

ALTER TABLE `config_comissoes_individuais`
  ADD COLUMN `empresa_id` int NOT NULL DEFAULT 1 AFTER `id`,
  ADD CONSTRAINT `fk_comind_empresa`
    FOREIGN KEY (`empresa_id`) REFERENCES `empresas`(`id`) ON DELETE CASCADE;

ALTER TABLE `config_especialidades`
  ADD COLUMN `empresa_id` int NOT NULL DEFAULT 1 AFTER `id`,
  ADD CONSTRAINT `fk_espec_empresa`
    FOREIGN KEY (`empresa_id`) REFERENCES `empresas`(`id`) ON DELETE CASCADE;

ALTER TABLE `config_rateio`
  ADD COLUMN `empresa_id` int NOT NULL DEFAULT 1 AFTER `id`,
  ADD CONSTRAINT `fk_rateio_empresa`
    FOREIGN KEY (`empresa_id`) REFERENCES `empresas`(`id`) ON DELETE CASCADE;

ALTER TABLE `config_sistema`
  ADD COLUMN `empresa_id` int NOT NULL DEFAULT 1 AFTER `id`,
  ADD CONSTRAINT `fk_config_empresa`
    FOREIGN KEY (`empresa_id`) REFERENCES `empresas`(`id`) ON DELETE CASCADE;

-- ──────────────────────────────────────────────────────────────────────────
-- 4. CORRIGIR UNICIDADES — de "única no banco inteiro" para "única por empresa"
--    (usa DROP INDEX IF EXISTS — não dá erro mesmo que o nome seja diferente)
-- ──────────────────────────────────────────────────────────────────────────

ALTER TABLE `usuarios` DROP INDEX IF EXISTS `login`;
ALTER TABLE `usuarios` ADD UNIQUE KEY `uk_usuario_login_empresa` (`login`, `empresa_id`);

ALTER TABLE `bandeiras_cartao` DROP INDEX IF EXISTS `uk_nome`;
ALTER TABLE `bandeiras_cartao` ADD UNIQUE KEY `uk_bandeira_nome_empresa` (`nome`, `empresa_id`);

ALTER TABLE `config_especialidades` DROP INDEX IF EXISTS `uk_tipo`;
ALTER TABLE `config_especialidades` ADD UNIQUE KEY `uk_especialidade_tipo_empresa` (`tipo`, `empresa_id`);

ALTER TABLE `config_sistema` DROP INDEX IF EXISTS `uk_chave`;
ALTER TABLE `config_sistema` ADD UNIQUE KEY `uk_config_chave_empresa` (`chave`, `empresa_id`);

ALTER TABLE `pacientes` DROP INDEX IF EXISTS `cpf`;
ALTER TABLE `pacientes` ADD UNIQUE KEY `uk_paciente_cpf_empresa` (`cpf`, `empresa_id`);

-- ──────────────────────────────────────────────────────────────────────────
-- 5. ÍNDICES PARA PERFORMANCE
-- ──────────────────────────────────────────────────────────────────────────
CREATE INDEX `idx_usuarios_empresa`      ON `usuarios`(`empresa_id`);
CREATE INDEX `idx_pacientes_empresa`     ON `pacientes`(`empresa_id`);
CREATE INDEX `idx_procedimentos_empresa` ON `procedimentos`(`empresa_id`);
CREATE INDEX `idx_despesas_empresa`      ON `despesas`(`empresa_id`);
CREATE INDEX `idx_atendimentos_empresa`  ON `atendimentos`(`empresa_id`);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- FIM — confira ao final:
--   SELECT COUNT(*) FROM pacientes;     → deve continuar mostrando 49
--   SELECT COUNT(*) FROM atendimentos;  → deve continuar mostrando 85 (ids existentes)
--   SELECT * FROM empresas;             → deve mostrar 1 linha (id=1, Prev Dentistas)
-- ============================================================================
