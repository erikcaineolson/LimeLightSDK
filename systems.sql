CREATE DATABASE systems; 
GRANT ALL PRIVILEGES ON systemsdb.* TO 'systemsu'@'localhost' IDENTIFIED BY 'database_password';

USE systems;

CREATE TABLE system (
	sysID INT SIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	sysName VARCHAR (255),
	sysUser BLOB,
	sysPassword BLOB,
	sysKey BLOB,
	sysLocation BLOB,
	sysLogoLocation BLOB,
	sysDateAdded DATETIME,
	sysIsActive TINYINT NOT NULL DEFAULT 1
) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
