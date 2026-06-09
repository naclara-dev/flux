<?php

namespace App\Models\Entities;

class Template
{
    private $id = null;
    private $userId = null;
    private $walletId = null;
    private $categoryId = null;
    private $entityId = null;
    private $title = null;
    private $amount = null;
    private $intervalValue = null;
    private $frequencyId = null;
    private $monthDay = null;
    private $startDate = null;
    private $endDate = null;
    private $nextRunDate = null;
    private $active = null;

    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }
    public function getUserId(): ?int { return $this->userId; }
    public function setUserId(?int $userId): void { $this->userId = $userId; }
    public function getWalletId(): ?int { return $this->walletId; }
    public function setWalletId(?int $walletId): void { $this->walletId = $walletId; }
    public function getCategoryId(): ?int { return $this->categoryId; }
    public function setCategoryId(?int $categoryId): void { $this->categoryId = $categoryId; }
    public function getEntityId(): ?int { return $this->entityId; }
    public function setEntityId(?int $entityId): void { $this->entityId = $entityId; }
    public function getTitle(): ?string { return $this->title; }
    public function setTitle(?string $title): void { $this->title = $title; }
    public function getAmount(): ?float { return $this->amount; }
    public function setAmount(?float $amount): void { $this->amount = $amount; }
    public function getIntervalValue(): ?int { return $this->intervalValue; }
    public function setIntervalValue(?int $intervalValue): void { $this->intervalValue = $intervalValue; }
    public function getFrequencyId(): ?int { return $this->frequencyId; }
    public function setFrequencyId(?int $frequencyId): void { $this->frequencyId = $frequencyId; }
    public function getMonthDay(): ?int { return $this->monthDay; }
    public function setMonthDay(?int $monthDay): void { $this->monthDay = $monthDay; }
    public function getStartDate(): ?string { return $this->startDate; }
    public function setStartDate(?string $startDate): void { $this->startDate = $startDate; }
    public function getEndDate(): ?string { return $this->endDate; }
    public function setEndDate(?string $endDate): void { $this->endDate = $endDate; }
    public function getNextRunDate(): ?string { return $this->nextRunDate; }
    public function setNextRunDate(?string $nextRunDate): void { $this->nextRunDate = $nextRunDate; }
    public function isActive(): ?bool { return $this->active; }
    public function setActive(?bool $active): void { $this->active = $active; }
}
