<?php

namespace Geekbrains\App\Blog\Repositories\LikesRepository;

use Geekbrains\App\Blog\Like;
use Geekbrains\App\Blog\UUID;

interface LikesRepositoryInterface
{
    public function save(Like $like): void;
    public function getByPostUuid(UUID $uuid): int;
}