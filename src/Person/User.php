<?php

namespace Geekbrains\Person;

class User {

    protected int $id;
    protected string $name;
    protected string $surname;

    public function __construct(string $name, string $surname)
    {
        $this->name = $name;
        $this->surname = $surname;
    }

    public function __toString(): string
    {
        return $this->name . ' ' .  $this->surname;
    }

}