-- Separate database for Nette Tester (tests TRUNCATE tables).
-- User "user" cannot CREATE DATABASE — run as root:
--   docker compose exec -T db mysql -uroot -prootsecret < docker/mysql/03-test-database.sql

CREATE DATABASE IF NOT EXISTS mapa_vysocany_test
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE mapa_vysocany_test;

CREATE TABLE IF NOT EXISTS house (
    id BIGINT UNSIGNED NOT NULL,
    lat DOUBLE NOT NULL,
    lon DOUBLE NOT NULL,
    sjtsk_x INT NOT NULL,
    sjtsk_y INT NOT NULL,
    place VARCHAR(255) NULL,
    housenumber VARCHAR(32) NULL,
    postcode VARCHAR(16) NULL,
    country VARCHAR(8) NULL,
    conscriptionnumber VARCHAR(32) NULL,
    ruian_ref VARCHAR(32) NULL,
    PRIMARY KEY (id),
    INDEX idx_place_housenumber (place, housenumber)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS owners (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    house_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    share VARCHAR(64) NOT NULL DEFAULT '1',
    PRIMARY KEY (id),
    INDEX idx_house_id (house_id),
    CONSTRAINT fk_owners_house
        FOREIGN KEY (house_id) REFERENCES house (id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON mapa_vysocany_test.* TO 'user'@'%';
FLUSH PRIVILEGES;
