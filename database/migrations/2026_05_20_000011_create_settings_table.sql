-- up 
CREATE TABLE settings (
	id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	default_payment_method_id INT(11) DEFAULT NULL,
    default_wallet_id INT(11) DEFAULT NULL,
    default_entity_id INT(11) DEFAULT NULL,
    UNIQUE (user_id),
	FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (default_payment_method_id) REFERENCES payment_methods(id),
    FOREIGN KEY (default_wallet_id) REFERENCES wallets(id),
    FOREIGN KEY (default_entity_id) REFERENCES entities(id)
);

-- down
DROP TABLE IF EXISTS settings;
