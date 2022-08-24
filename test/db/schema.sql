-- login to mysql with admin privileges and run the following


CREATE DATABASE IF NOT EXISTS `db_class_test_db`;

use db_class_test_db;

CREATE USER 'db_class_test_user'@'localhost' IDENTIFIED WITH mysql_native_password BY 'mypassword';

GRANT ALL PRIVILEGES ON db_class_test_db.fringe_shows TO 'db_class_test_user'@'localhost';

CREATE TABLE IF NOT EXISTS `fringe_shows`(
    id int(11) AUTO_INCREMENT,
    title VARCHAR(300) NULL,
    description VARCHAR(500) NULL,
    stars VARCHAR(5) DEFAULT '0',
    PRIMARY KEY (`id`)
);


FLUSH PRIVILEGES;



--- run these commands to drop the database


DROP DATABASE `db_class_test_db`;
DROP USER 'db_class_test_user'@'localhost';