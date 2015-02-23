CREATE TABLE IF NOT EXISTS `users` (
  `userId` INT(6) UNSIGNED UNIQUE PRIMARY KEY AUTO_INCREMENT NOT NULL,
  `username` VARCHAR (64) UNIQUE NOT NULL,
  `password` VARCHAR (60) NOT NULL,
  `userType` INT (1) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `tasks` (
  `taskId`       INT(6) UNSIGNED UNIQUE AUTO_INCREMENT,
  `title`        VARCHAR(255)    NOT NULL,
  `fromUserId`   INT(6) UNSIGNED NOT NULL,
  `fromUsername` VARCHAR(64)     NOT NULL,
  `toUserId`     INT(6) UNSIGNED,
  `toUsername`   VARCHAR(64),
  `price`        DECIMAL(13, 2),
  `comission`    DECIMAL(13, 2),
  `taskType`     VARCHAR(1)             DEFAULT '0',
  `sysblock`     VARCHAR(1)             DEFAULT 'F',
  `ts`           BIGINT
)
  ENGINE = InnoDB;

CREATE TABLE IF NOT EXISTS `wallets` (
  `userId` INT(6) UNSIGNED PRIMARY KEY UNIQUE,
  `money` DECIMAL(10, 2),
  `blocked` DECIMAL(10,2),
  `paid` DECIMAL(10,2)
)