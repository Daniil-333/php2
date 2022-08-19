<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Blog\Post;
use App\Blog\Comment;
use App\Person\User;
use Faker\Factory;

$faker = Factory::create();

$result = match ($argv[1]) {
    'user' => (string)new User($faker->name, $faker->lastName),
    'comment' => (string)new Comment($faker->text),
    'post' => (string)new Post($faker->text),
    default => throw new \Exception('Совпадений не найдено'),
};

print $result;