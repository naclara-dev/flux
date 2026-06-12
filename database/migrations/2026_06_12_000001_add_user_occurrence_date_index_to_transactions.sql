-- up
CREATE INDEX idx_transactions_user_occurrence_date
ON transactions (user_id, occurrence_date);

-- down
DROP INDEX idx_transactions_user_occurrence_date ON transactions;
