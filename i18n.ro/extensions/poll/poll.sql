CREATE TABLE `poll_vote` (
  `poll_id` VARCHAR(32),
  `poll_user` VARCHAR(255),
  `poll_ip` VARCHAR(255),
  `poll_answer` INTEGER(3),
  `poll_date` DATETIME,
  PRIMARY KEY  (`poll_id`,`poll_user`)
);

CREATE TABLE `poll_message` (
  `poll_id` VARCHAR(32),
  `poll_user` VARCHAR(255),
  `poll_ip` VARCHAR(255),
  `poll_msg` VARCHAR(255),
  `poll_date` DATETIME,
  PRIMARY KEY  (`poll_id`,`poll_user`)
);

CREATE TABLE `poll_info` (
  `poll_id` VARCHAR(32),
  `poll_txt` TEXT,
  `poll_date` DATETIME,
  `poll_title` VARCHAR(255),
  `poll_domain` VARCHAR(10),
  PRIMARY KEY  (`poll_id`)
);
