<?php

namespace App\model;

class Wallet
{
    private $id = null;
    private $name = null;
    private $typeId = null;
    private $initialBalance = null;
    private $active = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getTypeId(): ?int
    {
        return $this->typeId;
    }

    public function setTypeId(?int $typeId): void
    {
        $this->typeId = $typeId;
    }

    public function getInitialBalance(): ?float
    {
        return $this->initialBalance;
    }

    public function setInitialBalance(?float $initialBalance): void
    {
        $this->initialBalance = $initialBalance;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): void
    {
        $this->active = $active;
    }
}
