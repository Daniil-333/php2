<?php

use Geekbrains\App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Geekbrains\App\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Geekbrains\App\Blog\Repositories\CommentsRepository\SqliteCommentsRepository;

use Geekbrains\App\Blog\UUID;
use Geekbrains\App\Blog\Post;
use Geekbrains\App\Blog\Comment;
use Geekbrains\App\Blog\User;
use Geekbrains\App\Person\Name;

require_once __DIR__ . "/vendor/autoload.php";

//Создаём объект подключения к SQLite
$connection = new PDO('sqlite:' . __DIR__ . '/blog.sqlite');

$usersRepository = new SqliteUsersRepository($connection);
//
//$command = new CreateUserCommand($usersRepository);
//
//try {
//    $command->handle(Arguments::fromArgv($argv));
//} catch (Exception $e) {
//    echo $e->getMessage();
//}

$postRepository = new SqlitePostsRepository($connection);
$commentRepository = new SqliteCommentsRepository($connection);

$post = $postRepository->get(new UUID('5625e275-8cfd-4573-82af-28b07401db61'));
$user1 = $usersRepository->get(new UUID('3b697686-01bf-433a-bf17-53ce84cb987b'));
$user2 = $usersRepository->get(new UUID('c9e6813e-bae2-4140-96ac-8ddac672e13a'));

try {
    /* Работа со статьёй */

//    $postRepository->save(
//        new Post(
//            new UUID(uuid_create(UUID_TYPE_RANDOM)),
//            $user1,
//            'title1',
//            'text1')
//    );
//    $post1 = $postRepository->get(new UUID('5625e275-8cfd-4573-82af-28b07401db61'));
//    print_r($post1);

    /* Работа с комментарием */

//    $commentRepository->save(
//        new Comment(
//            new UUID(uuid_create(UUID_TYPE_RANDOM)),
//            $user2,
//            $post,
//            'text1')
//    );
    $comment1 = $commentRepository->get(new UUID('c8813cda-cd7c-42cd-b6a2-5a5416e77b77'));
    var_dump($comment1);
} catch (\Exception $e) {
    echo $e->getMessage();
}