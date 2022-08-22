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

    /**
     * @return Name
     */
    public function getUsername(): Name
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getLogin(): string
    {
        return $this->login;
    }


}