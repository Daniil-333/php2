<?php
require_once __DIR__ . '/vendor/autoload.php';

use Geekbrains\Blog\Post;
use Geekbrains\Blog\Comment;
use Geekbrains\Person\User;
use Faker\Factory;

$faker = Factory::create('RU');

$result = match ($argv[1]) {
    'user' => (string)new User($faker->name, $faker->lastName),
    'comment' => (string)new Comment($faker->text),
    'post' => (string)new Post($faker->text),
    default => throw new \Exception('Совпадений не найдено'),
};

print $result;