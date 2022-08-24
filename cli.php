<?php

use Geekbrains\App\Blog\Command\Arguments;
use Geekbrains\App\Blog\Command\CreateUserCommand;
use Geekbrains\App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Geekbrains\App\Blog\Repositories\PostsRepository\SqlitePostsRepository;

use Geekbrains\App\Blog\UUID;
use Geekbrains\App\Blog\Post;
use Geekbrains\App\Blog\User;
use Geekbrains\App\Person\Name;

require_once __DIR__ . "/vendor/autoload.php";

//Создаём объект подключения к SQLite
$connection = new PDO('sqlite:' . __DIR__ . '/blog.sqlite');

//$usersRepository = new SqliteUsersRepository($connection);
//
//$command = new CreateUserCommand($usersRepository);
//
//try {
//    $command->handle(Arguments::fromArgv($argv));
//} catch (Exception $e) {
//    echo $e->getMessage();
//}

$postRepository = new SqlitePostsRepository($connection);

try {
    /* Работа со статьёй */
//    $postRepository->save(
//        new Post(
//            new UUID(uuid_create(UUID_TYPE_RANDOM)),
//            new User(new UUID('c9e6813e-bae2-4140-96ac-8ddac672e13a'),
//            'admin', new Name('Ivan', 'Nikitin')),
//            'title1',
//            'text1')
//    );
//    $a = $postRepository->get(new UUID('5625e275-8cfd-4573-82af-28b07401db61'));
//    print_r($a);

    /* Работа с комментарием */

} catch (\Exception $e) {
    echo $e->getMessage();
}