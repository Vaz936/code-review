<?php

declare(strict_types=1);

final class UserRepository
{
    private array $storage = [];

    public function save(User $user): void
    {
        $this->storage[$user->getEmail()] = $user;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->storage[$email] ?? null;
    }

    public function findAll(): array
    {
        return array_values($this->storage);
    }
}

final class UserService
{
    public function __construct(
        private UserRepository $repository
    ) {
    }

    public function register(
        string $email,
        string $plainPassword,
        int $age
    ): User {
        if ($this->repository->findByEmail($email)) {
            throw new RuntimeException('User already exists');
        }

        $user = new User(
            email: strtolower($email),
            passwordHash: password_hash($plainPassword, PASSWORD_DEFAULT),
            age: $age,
            createdAt: new DateTime()
        );

        $this->repository->save($user);

        return $user;
    }

    public function authenticate(string $email, string $password): bool
    {
        $user = $this->repository->findByEmail($email);

        if ($user === null) {
            return false;
        }

        return password_verify($password, $user->getPasswordHash());
    }

    public function getAdults(): array
    {
        return array_filter(
            $this->repository->findAll(),
            fn (User $user) => $user->getAge() >= 18
        );
    }
}

final class User
{
    public function __construct(
        private string $email,
        private string $passwordHash,
        private int $age,
        private DateTime $createdAt
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getAge(): int
    {
        return $this->age;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
