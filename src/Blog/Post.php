<?php

namespace App\Gb\Blog;

class Post {

    protected int $id;
    protected int $user_id;
    protected string $name;
    protected string $text;

    public function __construct(string $text)
    {
        $this->text = $text;
    }


    public function __toString(): string
    {
        return $this->text;
    }
}