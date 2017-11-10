-- Run after root/data/{database.sql, database_populate.sql}

USE bank_interchange;

INSERT INTO `full_addresses` (`id`, `county`, `neighborhood`, `place`, `number`, `zipcode`, `detail`) VALUES
(1, 5389, 'PHP', 'Av. Universo', '42', '49000000', ''),
(2, 5389, 'PHP', 'R. dos Developers', '101', '49000000', 'Cond. Composer');

INSERT INTO `people` (`id`, `name`, `document`) VALUES
(1, 'My e-Comerce', '00000000000006'),
(2, 'Nome do Cliente', '00000000191');

INSERT INTO `assignors` (`id`, `person`, `address`, `bank`, `wallet`, `covenant`, `agency`, `agency_cd`, `account`, `account_cd`, `edi`, `logo`, `url`) VALUES
(1, 1, 1, 1, 2, '111', '222', '3', '4444444', '5', '666666', 'example.png', 'www.example.com');

INSERT INTO `payers` (`id`, `person`, `address`) VALUES
(1, 2, 2);
