<?php

namespace Geekbrains\App\Blog\Repositories\CommentsRepository;

use Geekbrains\App\Blog\Comment;
use Geekbrains\App\Blog\UUID;

interface CommentsRepositoryInterface
{
    public function save(Comment $comment): void;
    public function get(UUID $uuid): Comment;
}