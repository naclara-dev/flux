<?php

namespace App\Models\Repositories;

use App\Models\Entities\Transaction;

class TransactionRepository extends Repository
{
    protected $table = 'transactions';
    protected $entityClass = Transaction::class;

    public function sumPaidAmountFromUser(int $userId): float
    {
        $query = "SELECT COALESCE(SUM(amount), 0) FROM $this->table WHERE user_id = :user_id AND paid = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();

        return (float) $stmt->fetchColumn();
    }

    public function findPreviousIncomeDate(int $userId, string $date): ?string
    {
        $query = "SELECT MAX(occurrence_date) FROM $this->table WHERE user_id = :user_id AND amount > 0 AND occurrence_date <= :date";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':date', $date);
        $stmt->execute();

        $result = $stmt->fetchColumn();

        return $result ?: null;
    }

    public function findNextIncome(int $userId, string $date): ?array
    {
        $query = "SELECT * FROM $this->table WHERE user_id = :user_id AND amount > 0 AND occurrence_date >= :date ORDER BY occurrence_date ASC LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':date', $date);
        $stmt->execute();

        $result = $stmt->fetch();

        return $result ?: null;
    }

    public function sumAmountInCycle(int $userId, string $startDate, string $endDate): float
    {
        $query = "SELECT COALESCE(SUM(amount), 0) FROM $this->table WHERE user_id = :user_id AND occurrence_date >= :start_date AND occurrence_date < :end_date";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':start_date', $startDate);
        $stmt->bindValue(':end_date', $endDate);
        $stmt->execute();

        return (float) $stmt->fetchColumn();
    }

    public function sumCommittedUntil(int $userId, string $startDate, string $endDate): float
    {
        $query = "SELECT COALESCE(SUM(ABS(amount)), 0) FROM $this->table WHERE user_id = :user_id AND amount < 0 AND paid = 0 AND COALESCE(due_date, occurrence_date) >= :start_date AND COALESCE(due_date, occurrence_date) < :end_date";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->bindValue(':start_date', $startDate);
        $stmt->bindValue(':end_date', $endDate);
        $stmt->execute();

        return (float) $stmt->fetchColumn();
    }
}
