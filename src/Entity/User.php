<?php

declare(strict_types=1);

namespace Lmc\User\Repository\Db\Entity;

use Lmc\User\Repository\UserInterface;

use function iterator_to_array;

class User implements UserInterface
{
    protected int|string|null $id  = null;
    protected ?string $username    = null;
    protected ?string $password    = null;
    protected ?string $email       = null;
    protected ?string $displayName = null;
    protected array $roles         = [];
    protected ?int $state          = null;

    public function getId(): string|int|null
    {
        return $this->id;
    }

    public function setId(string|int $id): UserInterface
    {
        $this->id = $id;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): UserInterface
    {
        $this->username = $username;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): UserInterface
    {
        $this->email = $email;
        return $this;
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(string $displayName): UserInterface
    {
        $this->displayName = $displayName;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): UserInterface
    {
        $this->password = $password;
        return $this;
    }

    public function getState(): ?int
    {
        return $this->state;
    }

    public function setState(int $state): UserInterface
    {
        $this->state = $state;
        return $this;
    }

    public function getIdentity(): string
    {
        if (null === $this->id) {
            return (string) null;
        }
        return (string) $this->id;
    }

    public function getRoles(): iterable
    {
        return $this->roles;
    }

    public function setRoles(iterable $roles): UserInterface
    {
        $this->roles = iterator_to_array($roles);
        return $this;
    }

    /**
     *@inheritDoc
     */
    public function getDetail(string $name, $default = null)
    {
        return $default;
    }

    /**
     * @inheritDoc
     */
    public function getDetails(): array
    {
        return [];
    }
}
