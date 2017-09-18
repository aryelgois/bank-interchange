USE bank_interchange;

-- Species

INSERT INTO `species` (`id`, `symbol`, `name`, `cnab240`, `cnab400`, `thousand`, `decimal`) VALUES
(1, 'R$', 'Real', 9, 0, '', ',');


-- Wallets
-- @todo verify data

INSERT INTO `wallets` (`id`, `symbol`, `cnab240`, `cnab400`, `operation`, `name`, `notes`) VALUES
(1,  'SR', 0, '1', 47, 'Sem Registro', null),
(2,  'CS', 1, '1', 47, 'Cobrança Simples', 'Boleto emitido pelo Banco'),
(3,  'CV', 2, '2', 45, 'Cobrança Vinculada', null),
(4,  'CC', 3, '3', 46, 'Cobrança Caucionada', null),
(5,  'CS', 1, '4', 50, 'Cobrança Simples', 'Boleto emitido pelo cliente'),
(6,  'CV', 2, '5', 45, 'Cobrança Vinculada', 'Boleto emitido pelo cliente'),
(7,  'CC', 3, '6', 46, 'Cobrança Caucionada', 'Bloquete emitido pelo cliente'),
(8,  'CD', 4, '7', 4,  'Cobrança Descontada', null),
(9,  'CS', 0, '8', 48, 'Moedas (Cob. Escritural)', 'Bloquete emitido pelo Banco'),
(10, 'CS', 0, '9', 97, 'Ex / TD', null),
(11, 'CS', 1, '0', 51, 'Cobrança Simplificada', null),
(12, 'CS', 1, 'A', 49, 'Cobrança Simples Correspondentes', null),
(13, 'CS', 1, 'B', 52, 'Cobrança Simples Empresarial', null),
(14, 'CS', 1, 'C', 58, 'Cobrança Simples Empresarial', 'Bloquete emitido pelo cliente'),
(15, '',   0, 'D', 95, 'Ex. Cobrança Caucionada / Vinculada', null),
(16, 'CC', 0, 'E', 63, 'Custódia de Cheques', null),
(17, 'CS', 1, 'F', 53, 'Cobrança Simples Apólice', null),
(18, 'CS', 1, 'G', 54, 'Cobrança Simples Apólice Moedas', null),
(19, 'CS', 1, 'H', 55, 'Cobrança Simplificada Numerada', null),
(20, 'CS', 1, 'I', 57, 'Cobrança Simplificada Especial', null),
(21, 'CS', 1, 'J', 59, 'Cobrança Simplificada Apólice', null),
(22, '',   0, 'K', 61, 'Regularização de Títulos sem Registro', null),
(23, 'CV', 5, '',  0,  'Cobrança Vendor', null);


-- Banks
-- @todo verify `tax`

INSERT INTO `banks` (`id`, `code`, `name`, `view`, `logo`, `tax`) VALUES
(1, '104', 'Caixa Econômica Federal', 'CaixaEconomicaFederal', 'caixa.jpg', '2.0000'),
(2, '047', 'Banese', 'Banese', 'banese.jpg', '2.0000'),
(3, '004', 'B. do Nordeste', 'BancoDoNordeste', 'banco_do_nordeste.jpg', '2.0000');
