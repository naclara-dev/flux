-- up
CREATE TABLE frequencies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    unit ENUM('DAY', 'WEEK', 'MONTH', 'YEAR') NOT NULL
);

-- down
DROP TABLE IF EXISTS frequencies;
