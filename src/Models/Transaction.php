<?php

namespace App\model;

class Transaction
{
    private $id = null;
    private $walletId = null;
    private $categoryId = null;
    private $entityId = null;
    private $ruleId = null;
    private $paymentMethodId = null;
    private $title = null;
    private $paid = null;
    private $amount = null;
    private $occurrenceDate = null;
    private $dueDate = null;
    private $paidAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getWalletId(): ?int
    {
        return $this->walletId;
    }

    public function setWalletId(?int $walletId): void
    {
        $this->walletId = $walletId;
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    public function setCategoryId(?int $categoryId): void
    {
        $this->categoryId = $categoryId;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(?int $entityId): void
    {
        $this->entityId = $entityId;
    }

    public function getRuleId(): ?int
    {
        return $this->ruleId;
    }

    public function setRuleId(?int $ruleId): void
    {
        $this->ruleId = $ruleId;
    }

    public function getPaymentMethodId(): ?int
    {
        return $this->paymentMethodId;
    }

    public function setPaymentMethodId(?int $paymentMethodId): void
    {
        $this->paymentMethodId = $paymentMethodId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    public function isPaid(): ?bool
    {
        return $this->paid;
    }

    public function setPaid(?bool $paid): void
    {
        $this->paid = $paid;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(?float $amount): void
    {
        $this->amount = $amount;
    }

    public function getOccurrenceDate(): ?string
    {
        return $this->occurrenceDate;
    }

    public function setOccurrenceDate(?string $occurrenceDate): void
    {
        $this->occurrenceDate = $occurrenceDate;
    }

    public function getDueDate(): ?string
    {
        return $this->dueDate;
    }

    public function setDueDate(?string $dueDate): void
    {
        $this->dueDate = $dueDate;
    }

    public function getPaidAt(): ?string
    {
        return $this->paidAt;
    }

    public function setPaidAt(?string $paidAt): void
    {
        $this->paidAt = $paidAt;
    }
}
