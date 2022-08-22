<?php

namespace Geekbrains\App\Blog;

class Comment {

    private int $id;
    private User $author;
    private Post $post;
    private string $text;

    public function __construct(int $id, User $author, Post $post, string $text)
    {
        $this->id = $id;
        $this->author = $author;
        $this->post = $post;
        $this->text = $text;
    }

    public function __toString(): string
    {
        return $this->text;
    }

    /**
     * @return int
     */
    public function getAuthorId(): int
    {
        return $this->author->getId();
    }

    /**
     * @return int
     */
    public function getPostId(): int
    {
        return $this->post->getId();
    }


}