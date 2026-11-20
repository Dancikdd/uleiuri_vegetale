-- Rulează o singură dată, după schema.sql, în schema uleiuri_vegetale.
-- Conturile demonstrative folosesc parola: Lab4Demo2026!
-- Valoarea salvată mai jos este un hash generat cu password_hash() din PHP.

START TRANSACTION;

INSERT INTO categories (id, name, description) VALUES
    (1, 'Uleiuri rafinate', 'Uleiuri potrivite pentru gătitul zilnic.'),
    (2, 'Presate la rece', 'Uleiuri obținute prin presare la rece.'),
    (3, 'Uleiuri bio', 'Uleiuri din ingrediente certificate ecologic.');

INSERT INTO users (id, name, email, password_hash, role) VALUES
    (1, 'Administrator Demo', 'admin@uleiuri.local', '$2y$10$NV4ih3DiPSCflOk7r6YvP.6LEN5JPxkq5Ji0lAWZzgqocYxMxbWze', 'administrator'),
    (2, 'Ana Popescu', 'ana@example.com', '$2y$10$NV4ih3DiPSCflOk7r6YvP.6LEN5JPxkq5Ji0lAWZzgqocYxMxbWze', 'client'),
    (3, 'Mihai Ionescu', 'mihai@example.com', '$2y$10$NV4ih3DiPSCflOk7r6YvP.6LEN5JPxkq5Ji0lAWZzgqocYxMxbWze', 'client');

INSERT INTO items (id, category_id, name, description, price, quantity_available, is_active) VALUES
    (1, 1, 'Ulei de floarea-soarelui 1L', 'Ulei rafinat pentru gătit.', 35.99, 40, 1),
    (2, 1, 'Ulei de porumb 1L', 'Ulei rafinat cu gust neutru.', 42.50, 25, 1),
    (3, 1, 'Ulei de rapiță 1L', 'Potrivit pentru preparate calde.', 39.00, 30, 1),
    (4, 1, 'Ulei de soia 1L', 'Ulei vegetal pentru bucătărie.', 36.00, 18, 1),
    (5, 1, 'Ulei din sâmburi de struguri 500ml', 'Ulei rafinat versatil.', 49.90, 0, 0),
    (6, 2, 'Ulei de măsline extravirgin 500ml', 'Presat la rece, cu aromă intensă.', 89.90, 22, 1),
    (7, 2, 'Ulei de dovleac 250ml', 'Presat la rece, cu gust de nucă.', 74.50, 14, 1),
    (8, 2, 'Ulei de in 250ml', 'Presat la rece, pentru consum la rece.', 58.00, 12, 1),
    (9, 2, 'Ulei de nucă 250ml', 'Aromat, presat la rece.', 82.00, 9, 1),
    (10, 2, 'Ulei de susan 250ml', 'Presat la rece, potrivit pentru salate.', 65.00, 11, 1),
    (11, 3, 'Ulei de cocos bio 500ml', 'Certificat bio, presat la rece.', 72.00, 16, 1),
    (12, 3, 'Ulei de măsline bio 500ml', 'Extravirgin, certificat bio.', 105.00, 8, 1),
    (13, 3, 'Ulei de floarea-soarelui bio 1L', 'Presat la rece, certificat bio.', 68.00, 13, 1),
    (14, 3, 'Ulei de in bio 250ml', 'Certificat bio, presat la rece.', 69.00, 0, 0),
    (15, 3, 'Ulei de cânepă bio 250ml', 'Aromat și certificat bio.', 91.00, 7, 1);

INSERT INTO requests (id, user_id, created_at, status) VALUES
    (1, 2, CURRENT_TIMESTAMP - INTERVAL 12 DAY, 'approved'),
    (2, 3, CURRENT_TIMESTAMP - INTERVAL 9 DAY, 'approved'),
    (3, 2, CURRENT_TIMESTAMP - INTERVAL 5 DAY, 'pending'),
    (4, 3, CURRENT_TIMESTAMP - INTERVAL 2 DAY, 'rejected'),
    (5, 2, CURRENT_TIMESTAMP - INTERVAL 1 DAY, 'approved');

INSERT INTO request_items (request_id, item_id, quantity, unit_price) VALUES
    (1, 1, 2, 35.99),
    (1, 6, 1, 89.90),
    (2, 7, 1, 74.50),
    (2, 8, 2, 58.00),
    (3, 11, 1, 72.00),
    (4, 3, 3, 39.00),
    (5, 12, 1, 105.00),
    (5, 15, 1, 91.00);

COMMIT;
