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
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getAuthor(): User
    {
        return $this->author;
    }

    /**
     * @return Post
     */
    public function getPost(): Post
    {
        return $this->post;
    }

    /**
     * @param User $author
     */
    public function setAuthor(User $author): void
    {
        $this->author = $author;
    }

    /**
     * @param Post $post
     */
    public function setPost(Post $post): void
    {
        $this->post = $post;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

}