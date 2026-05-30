-- Run only when upgrading from the old schema (owners.place + owners.housenumber).
-- On a fresh install 01-schema.sql is enough.

USE mapa_vysocany;

ALTER TABLE owners
    ADD COLUMN house_id BIGINT UNSIGNED NULL AFTER id;

UPDATE owners o
    INNER JOIN house h ON h.place = o.place AND h.housenumber = o.housenumber
SET o.house_id = h.id;

DELETE FROM owners WHERE house_id IS NULL;

ALTER TABLE owners
    DROP INDEX idx_place_housenumber,
    DROP COLUMN place,
    DROP COLUMN housenumber,
    MODIFY house_id BIGINT UNSIGNED NOT NULL,
    ADD INDEX idx_house_id (house_id),
    ADD CONSTRAINT fk_owners_house
        FOREIGN KEY (house_id) REFERENCES house (id)
        ON DELETE CASCADE;
