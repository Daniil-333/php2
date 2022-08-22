<?php

namespace Geekbrains\App\Blog;

use Geekbrains\App\Person\Name;

class User {

    private int $id;
    private Name $username;
    private string $login;

    public function __construct(int $id, Name $name, string $login)
    {
        $this->id = $id;
        $this->username = $name;
        $this->login = $login;
    }

    public function __toString(): string
    {
        return $this->username;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}