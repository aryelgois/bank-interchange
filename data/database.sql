CREATE DATABASE cnab240 CHARSET = UTF8 COLLATE = utf8_general_ci;
USE cnab240;

CREATE TABLE `banks` (
    `id`            int(10)         UNSIGNED NOT NULL AUTO_INCREMENT,
    `code`          char(3)         NOT NULL,
    `name`          varchar(30)     NOT NULL,
    `tax`           decimal(6,4)    NOT NULL,
    PRIMARY KEY (`id`)
);

CREATE TABLE `people` (
    `id`            int(10)         UNSIGNED NOT NULL AUTO_INCREMENT,
    `document`      varchar(14)     NOT NULL,
    `name`          varchar(40)     NOT NULL,
    PRIMARY KEY (`id`)
);

CREATE TABLE `assignors` (
    `id`            int(10)         UNSIGNED NOT NULL,
    `bank`          int(10)         UNSIGNED NOT NULL,
    `covenant`      char(20)        NOT NULL,
    `agency`        char(5)         NOT NULL,
    `agency_cd`     char(1)         NOT NULL,
    `account`       char(12)        NOT NULL,
    `account_cd`    char(1)         NOT NULL,
    `edi7`          char(6)         NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`id`) REFERENCES `people` (`id`),
    FOREIGN KEY (`bank`) REFERENCES `banks` (`id`)
);

-- @deprecated If no other information is required, references to payers will
--             simply point to `people`. Or, `people` will be dropped and both
--             `assignors` and `payers` will have `people` columns.
CREATE TABLE `payers` (
    `id`            int(10)         UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`id`) REFERENCES `people` (`id`)
);

CREATE TABLE `fulladdress` (
    `id`            int(10)         UNSIGNED NOT NULL AUTO_INCREMENT,
    `person`        int(10)         UNSIGNED NOT NULL,
    `county`        int(10)         UNSIGNED NOT NULL, -- Foreign to database Address: `counties` (`id`)
    `neighborhood`  varchar(15)     NOT NULL,
    `street`        varchar(40)     NOT NULL,
    `number`        varchar(40)     NOT NULL,
    `zipcode`       varchar(8)      NOT NULL,
    `detail`        varchar(40)     NOT NULL DEFAULT '',
    `stamp`         timestamp       NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`person`) REFERENCES `people` (`id`)
);

CREATE TABLE `services` (
    `id`            int(10)         UNSIGNED NOT NULL AUTO_INCREMENT,
    `description`   varchar(25)     NOT NULL,
    `value`         decimal(17,4)   NOT NULL,
    PRIMARY KEY (`id`)
);

CREATE TABLE `transactions` (
    `id`            int(10)         UNSIGNED NOT NULL AUTO_INCREMENT,
    `assignor`      int(10)         UNSIGNED NOT NULL,
    `payer`         int(10)         UNSIGNED NOT NULL,
    `status`        tinyint(1)      UNSIGNED NOT NULL DEFAULT 0,
    `due`           date            NOT NULL,
    `stamp`         timestamp       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`assignor`) REFERENCES `assignors` (`id`),
    FOREIGN KEY (`payer`) REFERENCES `payers` (`id`)
);

CREATE TABLE `transaction_items` (
    `transaction`   int(10)         UNSIGNED NOT NULL,
    `service`       int(10)         UNSIGNED NOT NULL,
    PRIMARY KEY (`transaction`, `service`),
    FOREIGN KEY (`transaction`) REFERENCES `transactions` (`id`),
    FOREIGN KEY (`service`) REFERENCES `services` (`id`)
);

-- CREATE TABLE `shipping_files` ( -- @TODO
--     `id`            int(10)         UNSIGNED NOT NULL AUTO_INCREMENT,
--     `description`   varchar(40)     NOT NULL,
--     PRIMARY KEY (`transaction`, `service`)
-- );
