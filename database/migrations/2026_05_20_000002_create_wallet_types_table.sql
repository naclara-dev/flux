-- up
CREATE TABLE wallet_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

-- down
DROP TABLE IF EXISTS wallet_types;
