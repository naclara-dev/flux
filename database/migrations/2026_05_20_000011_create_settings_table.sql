-- up 
CREATE TABLE settings (
	default_payment_method_id INT(11) DEFAULT 0,
    default_wallet_id INT(11) DEFAULT 0,
    default_entity_id INT(11) DEFAULT 0,
    FOREIGN KEY (default_payment_method_id) REFERENCES payment_methods(id),
    FOREIGN KEY (default_wallet_id) REFERENCES wallets(id),
    FOREIGN KEY (default_entity_id) REFERENCES entities(id)
);

-- down
DROP TABLE IF EXISTS settings;