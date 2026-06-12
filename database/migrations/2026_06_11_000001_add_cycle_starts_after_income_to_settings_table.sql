-- up
ALTER TABLE settings
ADD COLUMN cycle_starts_after_income TINYINT(1) DEFAULT 1 AFTER default_entity_id;

-- down
ALTER TABLE settings
DROP COLUMN cycle_starts_after_income;
