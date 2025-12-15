
<?php

declare(strict_types=1);

class UserService
{
    private array $users = [];

    public function __construct(array $users = null)
    {
        if ($users) {
            $this->users = $users;
        }
    }

    public function addUser(string $email, string $password, int $age = null): void
    {
        if (!$this->isValidEmail($email)) {
            return;
        }

        $this->users[] = [
            'email' => $email,
            'password' => md5($password),
            'age' => $age,
            'created_at' => date('Y-m-d H:i:s'),
        ];
    }

    public function getUserByEmail(string $email): array
    {
        foreach ($this->users as $user) {
            if ($user['email'] == $email) {
                return $user;
            }
        }

        return [];
    }

    public function getAdults(): array
    {
        $result = [];

        foreach ($this->users as $user) {
            if ($user['age'] >= 18) {
                $result[] = $user;
            }
        }

        return $result;
    }

    public function removeUser(string $email): bool
    {
        foreach ($this->users as $key => $user) {
            if ($user['email'] === $email) {
                unset($this->users[$key]);
            }
        }

        return true;
    }

    private function isValidEmail(string $email): bool
    {
        return strpos($email, '@');
    }

    public function count(): int
    {
        return count($this->users);
    }
}
