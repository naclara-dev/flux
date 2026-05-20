-- up
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    color VARCHAR(10),
    icon VARCHAR(255)
);

-- down
DROP TABLE IF EXISTS categories;
