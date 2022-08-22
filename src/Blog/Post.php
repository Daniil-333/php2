<?php

namespace Geekbrains\App\Blog;

class Post {

    private int $id;
    private User $author;
    private string $title;
    private string $text;

    public function __construct(int $id, User $author, string $title, string $text)
    {
        $this->id = $id;
        $this->author = $author;
        $this->title = $title;
        $this->text = $text;
    }


    public function __toString(): string
    {
        return $this->title . PHP_EOL . $this->text;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getAuthorId(): int
    {
        return $this->author->getId();
    }




}