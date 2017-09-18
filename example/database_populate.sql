USE bank_interchange;

INSERT INTO `fulladdress` (`id`, `county`, `neighborhood`, `place`, `number`, `zipcode`, `detail`) VALUES
(1, 5389, 'PHP', 'Av. Universo', '42', '49000000', ''),
(2, 5389, 'PHP', 'R. dos Developers', '101', '49000000', '');

INSERT INTO `banks` (`id`, `code`, `name`, `view`, `logo`, `tax`) VALUES
(1, '104', 'Caixa Econômica Federal', 'CaixaEconomicaFederal', 'caixa.jpg', '2.0000'),
(1, '047', 'Banese', 'Banese', 'banese.jpg', '2.5000');

INSERT INTO `assignors` (`id`, `bank`, `address`, `document`, `name`, `covenant`, `agency`, `agency_cd`, `account`, `account_cd`, `edi7`, `logo`, `url`) VALUES
(1, 1, 1, '00000000000006', 'My e-Comerce', '111', '222', '3', '4444444', '5', '666666', 'example.png', 'www.example.com');

INSERT INTO `payers` (`id`, `address`, `document`, `name`) VALUES
(1, 1, '00000000191', 'Nome do Cliente');
