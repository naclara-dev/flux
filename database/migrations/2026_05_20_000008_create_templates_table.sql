-- up
CREATE TABLE templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
	wallet_id INT NOT NULL,
	category_id INT NOT NULL,
	entity_id INT NOT NULL,
    type ENUM('I', 'E') NOT NULL,
    title VARCHAR(255) NOT NULL,
	amount DECIMAL(12,2) NOT NULL,
    interval_value INT DEFAULT 1,
    frequency_id INT NOT NULL,
	month_day INT DEFAULT 1,	
    start_date DATE NOT NULL,
    end_date DATE,
    next_run_date DATE,
    active BOOLEAN DEFAULT TRUE,
	defines_cycle BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (wallet_id) REFERENCES wallets(id),
    FOREIGN KEY (category_id) REFERENCES categories(id),
    FOREIGN KEY (entity_id) REFERENCES entities(id),
    FOREIGN KEY (frequency_id) REFERENCES frequencies(id)
);

-- down
DROP TABLE IF EXISTS templates;
