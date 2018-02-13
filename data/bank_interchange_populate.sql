-- Banks
-- TODO verify `billet_tax`

INSERT INTO `banks` (`id`, `code`, `name`, `logo`, `billet_tax`) VALUES
(1, '104', 'Caixa Econômica Federal', 'caixa.jpg', '2.0000'),
(2, '047', 'Banese', 'banese.jpg', '2.0000'),
(3, '004', 'Banco do Nordeste', 'banco_do_nordeste.jpg', '2.0000');

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
(1, 1, '240', '02', 'DM', 'Duplicata Mercantil'),
(2, 1, '240', '04', 'DS', 'Duplicata de Serviço'),
(3, 1, '240', '08', 'NCC', 'Nota de Crédito Comercial'),
(4, 1, '240', '09', 'NCE', 'Nota de Crédito a Exportação'),
(5, 1, '240', '10', 'NCI', 'Nota de Crédito Industrial'),
(6, 1, '240', '11', 'NCR', 'Nota de Crédito Rural'),
(7, 1, '240', '12', 'NP', 'Nota Promissória'),
(8, 1, '240', '17', 'RC', 'Recibo'),
(9, 1, '240', '20', 'AP', 'Apólice de Seguro'),
(10, 1, '240', '21', 'ME', 'Mensalidade Escolar'),
(11, 1, '240', '22', 'PC', 'Parcela de Consórcio'),
(12, 1, '240', '23', 'NF', 'Nota Fiscal'),
(13, 1, '240', '99', 'OU', 'Outros'),
(14, 1, '400', '01', 'DIB', 'Duplicata impressa pelo Banese'),  -- --
(15, 1, '400', '02', 'NPB', 'NP impressa pelo Banese'),         -- Dummy symbol,
(16, 1, '400', '05', 'RCB', 'Recibo impresso pelo Banese'),     -- not actually
(17, 1, '400', '21', 'DIC', 'Duplicata impressa pelo cliente'), -- used by the
(18, 1, '400', '22', 'NPC', 'NP impressa pelo cliente'),        -- bank.
(19, 1, '400', '25', 'RIC', 'Recibo impresso pelo cliente'),    -- --
(20, 2, '400', '01', 'DM', 'Duplicata Mercantil'),
(21, 2, '400', '02', 'NP', 'Nota Promissória'),
(22, 2, '400', '03', 'CH', 'Cheque'),
(23, 2, '400', '04', 'CR', 'Carnê'), -- Dummy symbol
(24, 2, '400', '05', 'RC', 'Recibo'),
(25, 2, '400', '06', 'DS', 'Duplicata Prest. Serviços'),
(26, 2, '400', '19', 'OU', 'Outros').

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
