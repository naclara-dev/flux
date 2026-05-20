-- up
CREATE TABLE rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    interval_value INT DEFAULT 1,
    frequency_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    next_run_date DATE,
    active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (frequency_id) REFERENCES frequencies(id)
);

-- down
DROP TABLE IF EXISTS rules;
