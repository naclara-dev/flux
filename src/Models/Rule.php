<?php

namespace App\model;

class Rule
{
    private $id = null;
    private $name = null;
    private $description = null;
    private $intervalValue = null;
    private $frequencyId = null;
    private $startDate = null;
    private $endDate = null;
    private $nextRunDate = null;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getIntervalValue(): ?int
    {
        return $this->intervalValue;
    }

    public function setIntervalValue(?int $intervalValue): void
    {
        $this->intervalValue = $intervalValue;
    }

    public function getFrequencyId(): ?int
    {
        return $this->frequencyId;
    }

    public function setFrequencyId(?int $frequencyId): void
    {
        $this->frequencyId = $frequencyId;
    }

    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    public function setStartDate(?string $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    public function setEndDate(?string $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function getNextRunDate(): ?string
    {
        return $this->nextRunDate;
    }

    public function setNextRunDate(?string $nextRunDate): void
    {
        $this->nextRunDate = $nextRunDate;
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
