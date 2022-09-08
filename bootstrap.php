<?php

use Dotenv\Dotenv;
use Geekbrains\App\Blog\Container\DIContainer;
use Geekbrains\App\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use Geekbrains\App\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use Geekbrains\App\Blog\Repositories\LikesRepository\LikesRepositoryInterface;
use Geekbrains\App\Blog\Repositories\LikesRepository\SqliteLikesRepository;
use Geekbrains\App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use Geekbrains\App\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Geekbrains\App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Geekbrains\App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use Geekbrains\App\Http\Auth\IdentificationInterface;
use Geekbrains\App\Http\Auth\JsonBodyUsernameIdentification;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;


// Подключаем автозагрузчик Composer
require_once __DIR__ . '/vendor/autoload.php';

// Загружаем переменные окружения из файла .env
Dotenv::createImmutable(__DIR__)->safeLoad();

$container = new DIContainer();

$container->bind(
    PDO::class,
    new PDO('sqlite:' . __DIR__ . '/' . $_ENV['SQLITE_DB_PATH'])
);

$logger = (new Logger('blog'));

if ('yes' === $_ENV['LOG_TO_FILES']) {
    $logger->pushHandler(new StreamHandler(
        __DIR__ . '/logs/blog.log'
    ))
        ->pushHandler(new StreamHandler(
            __DIR__ . '/logs/blog.error.log',
            level: Logger::ERROR,
            bubble: false,
        ));
}

if ('yes' === $_ENV['LOG_TO_CONSOLE']) {
    $logger->pushHandler(
        new StreamHandler("php://stdout")
    );
}

$container->bind(
    IdentificationInterface::class,
    JsonBodyUsernameIdentification::class
);

$container->bind(
    LoggerInterface::class,
    $logger
);

$container->bind(
    LikesRepositoryInterface::class,
    SqliteLikesRepository::class
);

$container->bind(
    PostsRepositoryInterface::class,
    SqlitePostsRepository::class
);

$container->bind(
    UsersRepositoryInterface::class,
    SqliteUsersRepository::class
);

$container->bind(
    CommentsRepositoryInterface::class,
    SqliteCommentsRepository::class
);

// Возвращаем объект контейнера
return $container;