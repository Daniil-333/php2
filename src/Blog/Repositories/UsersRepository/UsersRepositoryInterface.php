<?php

namespace Geekbrains\App\Blog\Repositories\UsersRepository;

use Geekbrains\App\Blog\User;
use Geekbrains\App\Blog\UUID;

interface UsersRepositoryInterface
{
    public function save(User $user): void;
    public function get(UUID $uuid): User;
    public function getByUsername(string $username): User;
}