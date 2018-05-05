-- Banks
-- TODO verify `billet_tax`

INSERT INTO `banks` (`id`, `code`, `name`, `billet_tax`) VALUES
(1, '104', 'Caixa Econômica Federal', '2.0000'),
(2, '047', 'Banese', '2.0000'),
(3, '004', 'Banco do Nordeste', '2.0000');

-- Currencies
-- TODO verify `currency_codes`

INSERT INTO `currencies` (`id`, `symbol`, `name`, `name_plural`, `decimals`, `thousand`, `decimal`) VALUES
(1, 'R$', 'Real', 'Reais', 2, '', ',');

INSERT INTO `currency_codes` (`currency`, `bank`, `billet`, `cnab240`, `cnab400`) VALUES
(1, 1, '9', '09', '?'),
(1, 2, '9', '09', '1'),
(1, 3, '9', '??', '0');

-- Document kinds

INSERT INTO `document_kinds` (`id`, `bank`, `cnab`, `code`, `symbol`, `name`) VALUES
(1, 2, '240', '02', 'DM', 'Duplicata Mercantil'),
(2, 2, '240', '04', 'DS', 'Duplicata de Serviço'),
(3, 2, '240', '08', 'NCC', 'Nota de Crédito Comercial'),
(4, 2, '240', '09', 'NCE', 'Nota de Crédito a Exportação'),
(5, 2, '240', '10', 'NCI', 'Nota de Crédito Industrial'),
(6, 2, '240', '11', 'NCR', 'Nota de Crédito Rural'),
(7, 2, '240', '12', 'NP', 'Nota Promissória'),
(8, 2, '240', '17', 'RC', 'Recibo'),
(9, 2, '240', '20', 'AP', 'Apólice de Seguro'),
(10, 2, '240', '21', 'ME', 'Mensalidade Escolar'),
(11, 2, '240', '22', 'PC', 'Parcela de Consórcio'),
(12, 2, '240', '23', 'NF', 'Nota Fiscal'),
(13, 2, '240', '99', 'OU', 'Outros'),
(14, 2, '400', '01', 'DIB', 'Duplicata impressa pelo Banese'),  -- --
(15, 2, '400', '02', 'NPB', 'NP impressa pelo Banese'),         -- Dummy symbol,
(16, 2, '400', '05', 'RCB', 'Recibo impresso pelo Banese'),     -- not actually
(17, 2, '400', '21', 'DIC', 'Duplicata impressa pelo cliente'), -- used by the
(18, 2, '400', '22', 'NPC', 'NP impressa pelo cliente'),        -- bank.
(19, 2, '400', '25', 'RIC', 'Recibo impresso pelo cliente'),    -- --
(20, 3, '400', '01', 'DM', 'Duplicata Mercantil'),
(21, 3, '400', '02', 'NP', 'Nota Promissória'),
(22, 3, '400', '03', 'CH', 'Cheque'),
(23, 3, '400', '04', 'CR', 'Carnê'), -- Dummy symbol
(24, 3, '400', '05', 'RC', 'Recibo'),
(25, 3, '400', '06', 'DS', 'Duplicata Prest. Serviços'),
(26, 3, '400', '19', 'OU', 'Outros');

-- Shipping File Movements
-- TODO check if missing for cnab 400
-- NOTE commented entries are not implemented

INSERT INTO `shipping_file_movements` (`bank`, `cnab`, `code`, `name`) VALUES
(2, '240', '01', 'Entrada de Títulos'),
(2, '240', '02', 'Pedido de Baixa'),
(2, '240', '04', 'Concessão de Abatimento'),
(2, '240', '05', 'Cancelamento de Abatimento'),
(2, '240', '06', 'Alteração de Vencimento'),
(2, '240', '07', 'Concessão de Desconto'),
(2, '240', '08', 'Cancelamento de Desconto'),
(2, '240', '12', 'Alteração de Juros de Mora'),
(2, '240', '13', 'Dispensar Cobrança de Juros de Mora'),
(2, '240', '14', 'Alteração de Valor/Percentual/Data de Multa'),
(2, '240', '15', 'Dispensar Cobrança de Multa'),
(2, '240', '16', 'Alteração de Valor/Data de Desconto'),
(2, '240', '18', 'Alteração do Valor de Abatimento'),
-- (2, '240', '19', 'Prazo Limite de Recebimento - Alterar'),
(2, '240', '21', 'Alterar número do título dado pelo cedente'),
(2, '240', '31', 'Alteração de Outros Dados'),
(2, '240', '42', 'Alteração de Espécie de Título'),
(3, '400', '01', 'Entrada Normal'),
(3, '400', '02', 'Pedido de Baixa'),
(3, '400', '04', 'Concessão de Abatimento'),
(3, '400', '06', 'Alteração de Vencimento'),
(3, '400', '07', 'Alteração do Uso da empresa (Número de Controle)'),
(3, '400', '08', 'Alteração de Seu número'),
(3, '400', '09', 'Protestar'),
(3, '400', '10', 'Não Protestar'),
-- (3, '400', '12', 'Inclusão de Ocorrência'),
-- (3, '400', '13', 'Exclusão de Ocorrência'),
(3, '400', '31', 'Alteração de Outros Dados');
-- (3, '400', '32', 'Pedido de Devolução'),
-- (3, '400', '33', 'Pedido de Devolução (entregue ao Sacado)'),
-- (3, '400', '99', 'Pedido dos Títulos em Aberto');

-- Wallets
-- TODO verify data

INSERT INTO `wallets` (`id`, `bank`, `cnab`, `code`, `operation`, `symbol`, `name`) VALUES
(1, 1, '240', '1', '?', 'CR', 'Cobrança Registrada'),
(2, 2, '240', '1', '?', 'CS', 'Cobrança Simples'),
(3, 2, '400', '2', '?', 'CS', 'Cobrança Simples'),
(4, 2, '400', '7', '?', 'CE', 'Cobrança Expressa'),
(5, 3, '400', '1', '21', 'CS', 'Cobrança Simples Escritural - Boleto Emitido Pelo Banco'),
(6, 3, '400', '2', '41', 'CV', 'Cobrança Vinculada – Boleto Emitido Pelo Banco'),
(7, 3, '400', '4', '21', 'CS', 'Cobrança Simples - Boleto Emitido Pelo Cliente'),
(8, 3, '400', '5', '41', 'CV', 'Cobrança Vinculada - Boleto Emitido Pelo Cliente'),
(9, 3, '400', 'I', '51', 'SR', 'Cobrança Simplificada (Sem Registro)');
