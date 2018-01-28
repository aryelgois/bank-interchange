-- Currencies

INSERT INTO `currencies` (`id`, `symbol`, `name`, `name_plural`, `decimals`, `thousand`, `decimal`) VALUES
(1, 'R$', 'Real', 'Reais', null, '', ',');

INSERT INTO `currency_codes` (`bank`, `cnab240`, `cnab400`) VALUES
(1, '09', '?');
(2, '09', '1');
(3, '??', '0');

-- Wallets
-- @todo verify data

INSERT INTO `wallets` (`id`, `febraban`, `operation`, `symbol`, `name`) VALUES
(1, 0, 51, 'SR', 'Sem Registro'),
(2, 1, 21, 'CS', 'Cobrança Simples'),
(3, 2, 41, 'CV', 'Cobrança Vinculada'),
(4, 4, 21, 'CS', 'Cobrança Simples'),   -- used by BancoDoNordeste
(5, 5, 41, 'CV', 'Cobrança Vinculada'); -- ditto

-- Banks
-- @todo verify `tax`

INSERT INTO `banks` (`id`, `code`, `name`, `view`, `logo`, `tax`) VALUES
(1, '104', 'Caixa Econômica Federal', 'CaixaEconomicaFederal', 'caixa.jpg', '2.0000'),
(2, '047', 'Banese', 'Banese', 'banese.jpg', '2.0000'),
(3, '004', 'B. do Nordeste', 'BancoDoNordeste', 'banco_do_nordeste.jpg', '2.0000');
