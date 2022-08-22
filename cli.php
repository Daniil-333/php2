<?php

use Geekbrains\App\Blog\Post;
use Geekbrains\App\Blog\Comment;
use Geekbrains\App\Person\Name;
use Geekbrains\App\Blog\User;
use GeekBrains\App\Blog\Exceptions\AppException;
use Faker\Factory;

require_once __DIR__ . '/vendor/autoload.php';

/*spl_autoload_register(function ($className)
{
//приходит GeekBrains\Person\Name
    $file = $className . ".php"; // Person/Name.php
    $file = str_replace(["\\", 'GeekBrains\App'], [DIRECTORY_SEPARATOR, "src"], $file);
    //нужно src/Person/Name.php
    if (file_exists($file)) {
        include $file;
    }
});*/

$fakerRu = Factory::create('ru_RU');
$fakerEn = Factory::create();

$name = new Name($fakerRu->firstName, $fakerRu->lastName);

$user = new User($fakerEn->randomNumber(), $name, $fakerEn->userName);

$post = new Post($fakerEn->randomNumber(), $user, $fakerRu->text(10), $fakerRu->text);

$comment = new Comment($fakerEn->randomNumber(), $user, $post, $fakerRu->text(80));


$result = match ($argv[1]) {
    'user' => (string)$user,
    'comment' => (string)$comment,
    'post' => (string)$post,
    default => throw new AppException('Совпадений не найдено'),
};

print $result;