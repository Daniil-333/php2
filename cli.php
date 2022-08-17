<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Gb\Blog\Post;
use App\Gb\Blog\Comment;
use App\Gb\Person\User;
use Faker\Factory;

$faker = Factory::create();

$result = match ($argv[1]) {
    'user' => (string)new User($faker->name, $faker->lastName),
    'comment' => (string)new Comment($faker->text),
    'post' => (string)new Post($faker->text),
    default => throw new \Exception('Совпадений не найдено'),
};

print $result;