<?php

namespace Geekbrains\App\Blog\Repositories\PostsRepository;

use Geekbrains\App\Blog\Post;
use Geekbrains\App\Blog\UUID;

interface PostsRepositoryInterface
{
    public function save(Post $post): void;
    public function get(UUID $uuid): Post;
    public function delete(UUID $uuid): void;
    public function clearData(): void;
}