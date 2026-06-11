-- up
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
	google_id VARCHAR(255) NULL,
	auth_provider VARCHAR(50) DEFAULT 'local'
);

-- down
DROP TABLE IF EXISTS users;
