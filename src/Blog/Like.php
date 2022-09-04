<?php

namespace Geekbrains\App\Blog;

class Like {

    private UUID $uuid;
    private UUID $uuidPost;
    private UUID $uuidUser;

    /**
     * @param UUID $uuid
     * @param UUID $uuidPost
     * @param UUID $uuidUser
     */
    public function __construct(UUID $uuid, UUID $uuidPost, UUID $uuidUser)
    {
        $this->uuid = $uuid;
        $this->uuidPost = $uuidPost;
        $this->uuidUser = $uuidUser;
    }

    public function __toString(): string
    {
        return 'Добавлен лайк к посту ' . $this->uuidPost . ' юзером ' . $this->uuidUser;
    }

    /**
     * @return UUID
     */
    public function uuid(): UUID
    {
        return $this->uuid;
    }

    /**
     * @return UUID
     */
    public function getUuidPost(): UUID
    {
        return $this->uuidPost;
    }

    /**
     * @return UUID
     */
    public function getUuidUser(): UUID
    {
        return $this->uuidUser;
    }
}