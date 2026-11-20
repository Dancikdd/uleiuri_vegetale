-- Rulează o singură dată în schema proiectului pentru a adăuga suportul imaginilor.
ALTER TABLE items
    ADD COLUMN image_path VARCHAR(255) NULL AFTER description;
