<?php

use Geekbrains\App\Blog\Exceptions\AppException;
use Geekbrains\App\Blog\Exceptions\HttpException;
use Geekbrains\App\Http\Actions\Auth\LogIn;
use Geekbrains\App\Http\Actions\Auth\LogOut;
use Geekbrains\App\Http\Actions\Comments\CreateComment;
use Geekbrains\App\Http\Actions\Likes\CreateLike;
use Geekbrains\App\Http\Actions\Posts\CreatePost;
use Geekbrains\App\Http\Actions\Posts\DeletePost;
use Geekbrains\App\Http\Actions\Posts\FindByUuid;
use Geekbrains\App\Http\Actions\Users\CreateUser;
use Geekbrains\App\Http\Actions\Users\FindByUsername;
use Geekbrains\App\Http\ErrorResponse;
use Geekbrains\App\Http\Request;
use Psr\Log\LoggerInterface;

$container = require __DIR__ . '/bootstrap.php';

$request = new Request(
    $_GET,
    $_SERVER,
    file_get_contents('php://input')
);

// Получаем объект логгера из контейнера
$logger = $container->get(LoggerInterface::class);

try {
    $path = $request->path();
} catch (HttpException $e) {
    // Логируем сообщение с уровнем WARNING
    $logger->warning($e->getMessage());
    (new ErrorResponse)->send();
    return;
}

try {
// Пытаемся получить HTTP-метод запроса
    $method = $request->method();
} catch (HttpException $e) {
    // Логируем сообщение с уровнем WARNING
    $logger->warning($e->getMessage());
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
        '/login' => LogIn::class,
        '/logout' => LogOut::class,
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
if (!array_key_exists($method, $routes) ||
    !array_key_exists($path, $routes[$method])) {
    // Логируем сообщение с уровнем NOTICE
    $message = "Route not found: $method $path";
    $logger->notice($message);
    (new ErrorResponse('Not found'))->send();
    return;
}

// Получаем имя класса действия для маршрута
$actionClassName = $routes[$method][$path];


try {
    // С помощью контейнера создаём объект нужного действия
    $action = $container->get($actionClassName);
    $response = $action->handle($request);
} catch (AppException $e) {
    // Логируем сообщение с уровнем ERROR
    $logger->error($e->getMessage(), ['exception' => $e]);
    // Больше не отправляем пользователю
    // конкретное сообщение об ошибке,
    // а только логируем его
    (new ErrorResponse($e->getMessage()))->send();
    return;
}

$response->send();