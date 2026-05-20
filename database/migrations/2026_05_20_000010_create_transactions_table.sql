-- up
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    wallet_id INT NOT NULL,
    category_id INT,
    entity_id INT,
    rule_id INT,
    payment_method_id INT,
    title VARCHAR(255) NOT NULL,
    paid BOOLEAN DEFAULT FALSE,
    amount DECIMAL(12,2) DEFAULT 0,
    occurrence_date DATE NOT NULL,
    due_date DATE,
    paid_at DATE,
    FOREIGN KEY (wallet_id) REFERENCES wallets(id),
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (entity_id) REFERENCES entities(id),
    FOREIGN KEY (rule_id) REFERENCES rules(id),
    FOREIGN KEY (payment_method_id) REFERENCES payment_methods(id)
);

-- down
DROP TABLE IF EXISTS transactions;
