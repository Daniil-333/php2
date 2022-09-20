<?php

use Geekbrains\App\Blog\Container\DIContainer;
use Geekbrains\App\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use Geekbrains\App\Blog\Repositories\AuthTokensRepository\SqliteAuthTokensRepository;
use Geekbrains\App\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use Geekbrains\App\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use Geekbrains\App\Blog\Repositories\LikesRepository\LikesRepositoryInterface;
use Geekbrains\App\Blog\Repositories\LikesRepository\SqliteLikesRepository;
use Geekbrains\App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use Geekbrains\App\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Geekbrains\App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Geekbrains\App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use Geekbrains\App\Http\Auth\AuthenticationInterface;
use Geekbrains\App\Http\Auth\BearerTokenAuthentication;
use Geekbrains\App\Http\Auth\PasswordAuthentication;
use Geekbrains\App\Http\Auth\PasswordAuthenticationInterface;
use Geekbrains\App\Http\Auth\TokenAuthenticationInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Dotenv\Dotenv;
use Faker\Generator;
use Faker\Provider\Lorem;
use Faker\Provider\ru_RU\Internet;
use Faker\Provider\ru_RU\Person;
use Faker\Provider\ru_RU\Text;


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
            __DIR__ . '/logs/blog.error.log', Logger::ERROR,
            false,
        ));
}

if ('yes' === $_ENV['LOG_TO_CONSOLE']) {
    $logger->pushHandler(
        new StreamHandler("php://stdout")
    );
}

// Создаём объект генератора тестовых данных
$faker = new Generator();
// Инициализируем необходимые нам виды данных
$faker->addProvider(new Person($faker));
$faker->addProvider(new Text($faker));
$faker->addProvider(new Internet($faker));
$faker->addProvider(new Lorem($faker));

// Добавляем генератор тестовых данных
// в контейнер внедрения зависимостей
$container->bind(
    Generator::class,
    $faker
);

$container->bind(
    PasswordAuthenticationInterface::class,
    PasswordAuthentication::class
);
$container->bind(
    TokenAuthenticationInterface::class,
    BearerTokenAuthentication::class
);

$container->bind(
    AuthTokensRepositoryInterface::class,
    SqliteAuthTokensRepository::class
);

$container->bind(
    AuthenticationInterface::class,
    PasswordAuthentication::class
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