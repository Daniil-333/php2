<?php

namespace Geekbrains\App\Blog\Repositories\UsersRepository;

use Geekbrains\App\Blog\Exceptions\UserNotFoundException;
use Geekbrains\App\Blog\User;
use Geekbrains\App\Blog\UUID;

class InMemoryUsersRepository implements UsersRepositoryInterface
{

    private array $users = [];


    public function save(User $user): void
    {
        $this->users[] = $user;
    }

    /**
     * @param int $id
     * @return User
     * @throws UserNotFoundException
     */
    public function get(UUID $id): User
    {
        foreach ($this->users as $user) {
            if ($user->id() === $id) {
                return $user;
            }
        }
        throw new UserNotFoundException("User not found: $id");
    }

    public function getByUsername(string $username): User
    {
        // TODO: Implement getByUsername() method.
    }
}