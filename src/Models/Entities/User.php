<?php

namespace App\Models\Entities;

class User
{
    private $id = null;
    private $name = null;
    private $email = null;
    private $password = null;
    private $googleId = null;
    private $authProvider = null;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getGoogleId(): ?string
    {
        return $this->googleId;
    }

    public function getAuthProvider(): ?string
    {
        return $this->authProvider;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    public function setGoogleId(?string $googleId): void
    {
        $this->googleId = $googleId;
    }

    public function setAuthProvider(?string $authProvider): void
    {
        $this->authProvider = $authProvider;
    }
}
