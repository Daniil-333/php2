<?php

use Geekbrains\App\Blog\Exceptions\AppException;
use Geekbrains\App\Blog\Exceptions\HttpException;
use Geekbrains\App\Http\Actions\Comments\CreateComment;
use Geekbrains\App\Http\Actions\Likes\CreateLike;
use Geekbrains\App\Http\Actions\Posts\CreatePost;
use Geekbrains\App\Http\Actions\Posts\DeletePost;
use Geekbrains\App\Http\Actions\Posts\FindByUuid;
use Geekbrains\App\Http\Actions\Users\CreateUser;
use Geekbrains\App\Http\Actions\Users\FindByUsername;
use Geekbrains\App\Http\ErrorResponse;
use Geekbrains\App\Http\Request;

$container = require __DIR__ . '/bootstrap.php';

$request = new Request(
    $_GET,
    $_SERVER,
    file_get_contents('php://input')
);

try {
    $path = $request->path();
} catch (HttpException) {
    (new ErrorResponse)->send();
    return;
}

try {
// Пытаемся получить HTTP-метод запроса
    $method = $request->method();
} catch (HttpException) {
// Возвращаем неудачный ответ, если по какой-то причине не можем получить метод
    (new ErrorResponse)->send();
    return;
}

$routes = [
    'GET' => [
        '/users/show' => FindByUsername::class,
        '/posts/show' => FindByUuid::class,
    ],
    'POST' => [
        '/users/create' => CreateUser::class,
        '/posts/create' => CreatePost::class,
        '/posts/comment' => CreateComment::class,
        '/likes/create' => CreateLike::class
    ],
    'DELETE' => [
        '/posts' => DeletePost::class
    ]

];

// Если у нас нет маршрутов для метода запроса - возвращаем неуспешный ответ
if (!array_key_exists($method, $routes)) {
    (new ErrorResponse('Not found'))->send();
    return;
}

// Ищем маршрут среди маршрутов для этого метода
if (!array_key_exists($path, $routes[$method])) {
    (new ErrorResponse('Not found'))->send();
    return;
}

// Получаем имя класса действия для маршрута
$actionClassName = $routes[$method][$path];

// С помощью контейнера создаём объект нужного действия
$action = $container->get($actionClassName);

try {
    $response = $action->handle($request);
} catch (AppException $e) {
    (new ErrorResponse($e->getMessage()))->send();
}

$response->send();