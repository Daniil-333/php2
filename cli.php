<?php

use Geekbrains\App\Blog\Command\Arguments;
use Geekbrains\App\Blog\Command\CreateUserCommand;
use Geekbrains\App\Blog\Exceptions\AppException;
use Geekbrains\App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Geekbrains\App\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Geekbrains\App\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;
use Geekbrains\App\Blog\UUID;
use Psr\Log\LoggerInterface;

$container = require __DIR__ . "./bootstrap.php";

$command = $container->get(CreateUserCommand::class);

// Получаем объект логгера из контейнера
$logger = $container->get(LoggerInterface::class);

try {
    $command->handle(Arguments::fromArgv($argv));
} catch (AppException $e) {
    $logger->error($e->getMessage(), ['exception' => $e]);
}


/*$usersRepository = new SqliteUsersRepository($connection);
$postRepository = new SqlitePostsRepository($connection);
$commentRepository = new SqliteCommentsRepository($connection);
try {
    $post = $postRepository->get(new UUID('5625e275-8cfd-4573-82af-28b07401db61'));
    $user1 = $usersRepository->get(new UUID('3b697686-01bf-433a-bf17-53ce84cb987b'));
    $user2 = $usersRepository->get(new UUID('c9e6813e-bae2-4140-96ac-8ddac672e13a'));
} catch (\Exception $e) {
    echo $e->getMessage();
}*/