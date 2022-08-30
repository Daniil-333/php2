<?php

namespace Geekbrains\App\Blog;

class Comment {

    private UUID $uuid;
    private User $user;
    private Post $post;
    private string $text;

    public function __construct(UUID $uuid, User $user, Post $post, string $text)
    {
        $this->uuid = $uuid;
        $this->user = $user;
        $this->post = $post;
        $this->text = $text;
    }

    public function __toString(): string
    {
        return $this->user . " пишет комментарий " . $this->text;
    }

    /**
     * @return UUID
     */
    public function uuid(): UUID
    {
        return $this->uuid;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @return Post
     */
    public function getPost(): Post
    {
        return $this->post;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
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