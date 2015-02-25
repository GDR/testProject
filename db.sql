CREATE TABLE IF NOT EXISTS `users` (
  `id`       INT(6) UNSIGNED UNIQUE PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `username` VARCHAR(64) UNIQUE                                NOT NULL,
  `password` VARCHAR(60)                                       NOT NULL,
  `userType` INT(1)                                            NOT NULL
)
  ENGINE = InnoDB;


CREATE TABLE IF NOT EXISTS `issues` (
  `id`           INT(6) UNSIGNED UNIQUE AUTO_INCREMENT NOT NULL,
  `affectsId`    INT(6) UNSIGNED,
  `title`        VARCHAR(255)                          NOT NULL,
  `fromUserId`   INT(6) UNSIGNED                       NOT NULL,
  `fromUsername` VARCHAR(64)                           NOT NULL,
  `toUserId`     INT(6) UNSIGNED,
  `toUsername`   VARCHAR(64),
  `price`        DECIMAL(13, 2),
  `commission`   DECIMAL(13, 2),
  `issueType`    VARCHAR(1) DEFAULT 'O'
)
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `tasks` (
  `id` INT(6) UNSIGNED UNIQUE PRIMARY KEY NOT NULL ,
  `title` VARCHAR(255) NOT NULL,
  `userId`
)

CREATE TABLE IF NOT EXISTS `wallets` (
  `userId`  INT(6) UNSIGNED PRIMARY KEY UNIQUE,
  `money`   DECIMAL(10, 2) NOT NULL  DEFAULT 0,
  `blocked` DECIMAL(10, 2) NOT NULL  DEFAULT 0,
  `paid`    DECIMAL(10, 2) NOT NULL  DEFAULT 0,
  `ts`      BIGINT         NOT NULL  DEFAULT 0
)
  ENGINE = InnoDB;