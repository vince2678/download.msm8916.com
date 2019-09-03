
DROP DATABASE IF EXISTS $db;
CREATE DATABSE $db;
USE $db;

CREATE TABLE board (
    board_id int NOT NULL AUTO_INCREMENT,
    board_name varchar(50) NOT NULL,
    board_arch varchar(15) NOT NULL DEFAULT "arm",
    PRIMARY KEY (board_id),
    INDEX USING BTREE (board_name)
    ) ENGINE=InnoDB DEFAULT CHARSET='utf8';

CREATE TABLE device (
    device_id int NOT NULL AUTO_INCREMENT,
    codename VARCHAR(20) NOT NULL,
    model varchar(15),
    board_id int NOT NULL,
    unified BOOLEAN DEFAULT false,
    PRIMARY KEY (device_id),
    INDEX USING BTREE (codename),
    CONSTRAINT FOREIGN KEY (board_id) REFERENCES board(board_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET='utf8'; 

CREATE TABLE dist (
    dist_id int NOT NULL AUTO_INCREMENT,
    tag_prefix VARCHAR(15) NOT NULL,
    name_short VARCHAR(30) NOT NULL,
    name_long VARCHAR(50),
    PRIMARY KEY (dist_id)    
) ENGINE=InnoDB DEFAULT CHARSET='utf8';

CREATE TABLE release (
    release_id int NOT NULL AUTO_INCREMENT,
    release_tag VARCHAR(100) NOT NULL,
    release_date DATE NOT NULL,
    release_version VARCHAR(15),
    build_num int DEFAULT -1,
    device_id int DEFAULT -1,
    dist_id int NOT NULL,
    PRIMARY KEY (release_id),
    INDEX USING BTREE (release_tag),
    CONSTRAINT FOREIGN KEY device_id REFERENCES device(device_id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT FOREIGN KEY dist_id REFERENCES dist(dist_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET='utf8';

CREATE TABLE artifact (
    artifact_id int NOT NULL AUTO_INCREMENT,
    artifact_name VARCHAR(50),
    size int NOT NULL DEFAULT 0,
    release_id int NOT NULL,
    download_count int DEFAULT 0,
    download_url VARCHAR(512),
    PRIMARY KEY (artifact_id),
    INDEX USING BTREE (artifact_name),
    CONSTRAINT FOREIGN KEY release_id REFERENCES release(release_id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET='utf8';