CREATE DATABASE bank_interchange CHARSET = UTF8 COLLATE = utf8_general_ci;
USE bank_interchange;

-- Basic Tables
--
-- Low level Tables containing very basic data

CREATE TABLE `full_addresses` (
    `id`            int(10)         UNSIGNED NOT NULL AUTO_INCREMENT,
    `county`        int(10)         UNSIGNED NOT NULL, -- Foreign: `address` `counties` (`id`)
    `neighborhood`  varchar(60)     NOT NULL,
    `place`         varchar(60)     NOT NULL,
    `number`        varchar(20)     NOT NULL,
    `zipcode`       varchar(8)      NOT NULL,
    `detail`        varchar(60)     NOT NULL DEFAULT '',
    `update`        timestamp       NOT NULL,
    PRIMARY KEY (`id`)
);

CREATE TABLE `people` (
    `id`            int(10)         UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`          varchar(60)     NOT NULL,
    `document`      varchar(14)     NOT NULL,
    PRIMARY KEY (`id`)
);

CREATE TABLE `species` (
    `id`            tinyint(3)      UNSIGNED NOT NULL AUTO_INCREMENT,
    `symbol`        varchar(5)      NOT NULL,
    `name`          varchar(30)     NOT NULL,
    `name_plural`   varchar(30),
    `febraban`      char(2)         NOT NULL,
    `decimals`      tinyint(2)      NOT NULL DEFAULT 2,
    `thousand`      char(1)         NOT NULL,
    `decimal`       char(1)         NOT NULL,
    PRIMARY KEY (`id`)
);

CREATE TABLE `wallets` (
    `id`            tinyint(3)      UNSIGNED NOT NULL AUTO_INCREMENT,
    `symbol`        char(2)         NOT NULL,
    `name`          varchar(60)     NOT NULL,
    `febraban`      tinyint(2)      NOT NULL,
    `operation`     tinyint(2)      NOT NULL,
    PRIMARY KEY (`id`)
);

-- Main Tables
--
-- Tables with most important data for this package

CREATE TABLE `banks` (
    `id`            int(10)         UNSIGNED NOT NULL AUTO_INCREMENT,
    `code`          char(3)         NOT NULL,
    `name`          varchar(60)     NOT NULL,
    `view`          varchar(30)     NOT NULL,
    `logo`          varchar(30),
    `tax`           decimal(6,4)    NOT NULL,
    PRIMARY KEY (`id`)
);

CREATE TABLE `assignors` (
    `id`            int(10)         UNSIGNED NOT NULL AUTO_INCREMENT,
    `person`        int(10)         UNSIGNED NOT NULL,
    `address`       int(10)         UNSIGNED NOT NULL,
    `bank`          int(10)         UNSIGNED NOT NULL,
    `wallet`        tinyint(3)      UNSIGNED NOT NULL,
    `covenant`      char(20)        NOT NULL,
    `agency`        char(5)         NOT NULL,
    `agency_cd`     char(1)         NOT NULL,
    `account`       char(11)        NOT NULL,
    `account_cd`    char(1)         NOT NULL,
    `edi`           char(6)         NOT NULL,
    `logo`          varchar(30),
    `url`           varchar(30),
    PRIMARY KEY (`id`),
    FOREIGN KEY (`person`) REFERENCES `people` (`id`),
    FOREIGN KEY (`address`) REFERENCES `full_addresses` (`id`),
    FOREIGN KEY (`bank`) REFERENCES `banks` (`id`),
    FOREIGN KEY (`wallet`) REFERENCES `wallets` (`id`)
);

CREATE TABLE `payers` (
    `id`            int(10)         UNSIGNED NOT NULL AUTO_INCREMENT,
    `person`        int(10)         UNSIGNED NOT NULL,
    `address`       int(10)         UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`person`) REFERENCES `people` (`id`),
    FOREIGN KEY (`address`) REFERENCES `full_addresses` (`id`)
);

CREATE TABLE `titles` (
    `id`            int(10)         UNSIGNED NOT NULL AUTO_INCREMENT,
    `assignor`      int(10)         UNSIGNED NOT NULL,
    `payer`         int(10)         UNSIGNED NOT NULL,
    `guarantor`     int(10)         UNSIGNED,
    `specie`        tinyint(3)      UNSIGNED NOT NULL,
    `our_number`    int(10)         UNSIGNED NOT NULL,
    `status`        tinyint(1),
    `doc_type`      char(1)         NOT NULL DEFAULT 1,
    `kind`          tinyint(2)      UNSIGNED NOT NULL,
    `value`         decimal(17,4)   NOT NULL,
    `value_paid`    decimal(17,4)   NOT NULL DEFAULT 0,
    `iof`           decimal(17,4)   NOT NULL,
    `rebate`        decimal(17,4)   NOT NULL,
    `fine_type`     tinyint(1)      NOT NULL DEFAULT 3,
    `fine_date`     date,
    `fine_value`    decimal(17,4),
    `discount_type` enum('1','2')   NOT NULL DEFAULT '1',
    `discount_date` date,
    `discount_value` decimal(17,4),
    `description`   varchar(25)     NOT NULL,
    `due`           date            NOT NULL,
    `stamp`         timestamp       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update`        datetime,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`assignor`) REFERENCES `assignors` (`id`),
    FOREIGN KEY (`payer`) REFERENCES `payers` (`id`),
    FOREIGN KEY (`guarantor`) REFERENCES `payers` (`id`),
    FOREIGN KEY (`specie`) REFERENCES `species` (`id`),
    UNIQUE KEY `assignor__AND__our_number`(`assignor`, `our_number`)
);

CREATE TABLE `shipping_files` (
    `id`            int(10)         UNSIGNED NOT NULL AUTO_INCREMENT,
    `assignor`      int(10)         UNSIGNED NOT NULL,
    `counter`       int(10)         UNSIGNED NOT NULL,
    `status`        tinyint(1),
    `stamp`         timestamp       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update`        datetime,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`assignor`) REFERENCES `assignors` (`id`),
    UNIQUE KEY `assignor__AND__counter`(`assignor`, `counter`)
);

CREATE TABLE `shipping_file_titles` (
    `shipping_file` int(10)         UNSIGNED NOT NULL,
    `title`         int(10)         UNSIGNED NOT NULL,
    FOREIGN KEY (`shipping_file`) REFERENCES `shipping_files` (`id`),
    FOREIGN KEY (`title`) REFERENCES `titles` (`id`),
    PRIMARY KEY (`shipping_file`, `title`)
);
