-- ============================================================================
-- MIGRAÇÃO 003 — Taxas separadas: Débito x Crédito
-- ============================================================================
-- 100% ADITIVA — nenhum DROP, nenhum TRUNCATE, nenhuma linha apagada.
--
-- O que muda:
--   • taxas_cartao ganha uma coluna `tipo` (debito | credito).
--     Todas as taxas que já existem viram automaticamente "credito"
--     (mantém o comportamento atual intacto).
--   • Cria automaticamente uma taxa de "debito" inicial pra cada bandeira,
--     copiando o valor que hoje está em "crédito 1x" — você ajusta depois
--     na tela de Taxas de Cartão.
--   • atendimento_pagamentos ganha uma coluna `repassado_cliente`,
--     pra registrar se a taxa do débito foi cobrada do cliente ou não
--     em cada pagamento (decisão feita na hora do lançamento).
--
-- ANTES DE RODAR: faça backup do banco.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

ALTER TABLE `taxas_cartao`
  ADD COLUMN `tipo` ENUM('debito','credito') NOT NULL DEFAULT 'credito' AFTER `bandeira_id`;

ALTER TABLE `atendimento_pagamentos`
  ADD COLUMN `repassado_cliente` TINYINT(1) NOT NULL DEFAULT 1 AFTER `taxa_snapshot`;

-- Semeia uma taxa de débito inicial por bandeira/empresa, copiando o valor
-- vigente de crédito 1x (ponto de partida — ajustável depois na tela admin).
INSERT INTO `taxas_cartao` (`empresa_id`, `bandeira_id`, `tipo`, `parcelas`, `percentual`, `vigencia_inicio`, `usuario_id`)
SELECT `empresa_id`, `bandeira_id`, 'debito', 1, `percentual`, NOW(), `usuario_id`
FROM `taxas_cartao`
WHERE `tipo` = 'credito' AND `parcelas` = 1 AND `vigencia_fim` IS NULL;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================================
-- Confira ao final:
--   SELECT bandeira_id, tipo, parcelas, percentual FROM taxas_cartao
--   WHERE vigencia_fim IS NULL ORDER BY bandeira_id, tipo, parcelas;
--   → deve mostrar, pra cada bandeira, 1 linha "debito" (parcelas=1) e
--     várias linhas "credito" (parcelas 1 a 10), como já tinha antes.
-- ============================================================================
