ALTER TABLE bikes
    ADD COLUMN IF NOT EXISTS bike_type VARCHAR(20) NOT NULL DEFAULT 'uni' AFTER category;

UPDATE bikes
SET bike_type = 'uni'
WHERE bike_type IS NULL OR TRIM(bike_type) = '';
