<?php

namespace Geekbrains\App\Blog;

class Post {

    public function __construct(
        private UUID $uuid,
        private User $user,
        private string $title,
        private string $text
    ) {
    }

    public function __toString(): string
    {
        return $this->user . ' пишет: ' . $this->title . PHP_EOL . $this->text  . PHP_EOL;
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
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }
}