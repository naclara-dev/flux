-- up
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    color VARCHAR(10),
    icon VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- down
DROP TABLE IF EXISTS categories;
