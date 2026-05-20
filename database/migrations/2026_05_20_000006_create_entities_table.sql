-- up
CREATE TABLE entities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    type_id INT,
    FOREIGN KEY (type_id) REFERENCES entity_types(id)
);

-- down
DROP TABLE IF EXISTS entities;
