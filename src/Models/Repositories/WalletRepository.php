<?php

namespace App\Models\Repositories;

use App\Models\Entities\Wallet;

class WalletRepository extends Repository
{
    protected $table = 'wallets';
    protected $entityClass = Wallet::class;

    public function sumInitialBalanceFromUser(int $userId): float
    {
        $query = "SELECT COALESCE(SUM(initial_balance), 0) FROM $this->table WHERE user_id = :user_id AND active = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $userId, \PDO::PARAM_INT);
        $stmt->execute();

        return (float) $stmt->fetchColumn();
    }
}
