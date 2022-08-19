<?php

namespace Geekbrains\Blog;

class Comment {

    protected int $id;
    protected int $user_id;
    protected int $id_post;
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