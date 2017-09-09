USE bank_interchange;

INSERT INTO `fulladdress` (`id`, `county`, `neighborhood`, `place`, `number`, `zipcode`, `detail`) VALUES
(1, 5389, 'PHP', 'R. dos Developers', '101', '49000000', '');

INSERT INTO `banks` (`id`, `code`, `name`, `tax`) VALUES
(1, '047', 'Banese', '2.0000');

INSERT INTO `assignors` (`id`, `bank`, `document`, `name`, `covenant`, `agency`, `agency_cd`, `account`, `account_cd`, `edi7`) VALUES
(1, 1, '00000000000006', 'My e-Comerce', '111', '222', '3', '4444444', '5', '666666');

INSERT INTO `payers` (`id`, `address`, `document`, `name`) VALUES
(1, 1, '00000000191', 'Nome do Cliente');

INSERT INTO `titles`(`id`, `assignor`, `payer`, `status`, `wallet`, `doc_type`, `kind`, `specie`, `value`, `iof`, `rebate`, `description`, `due`) VALUES
(1, 1, 1, 0, 1, '1', 99, 9, '50.0000', '0.0000', '0.0000', 'Compra no site', '2017-09-09')
