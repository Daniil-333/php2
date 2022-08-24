<?php

namespace Geekbrains\App\Blog\Repositories\PostsRepository;

use Geekbrains\App\Blog\Exceptions\InvalidArgumentException;
use Geekbrains\App\Blog\Exceptions\PostNotFoundException;
use Geekbrains\App\Blog\Post;
use Geekbrains\App\Blog\User;
use Geekbrains\App\Blog\UUID;
use Geekbrains\App\Person\Name;
use \PDO;
use \PDOStatement;

class SqlitePostsRepository implements PostsRepositoryInterface
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }


    public function save(Post $post): void
    {

        // Подготавливаем запрос
        $statement = $this->connection->prepare(
            'INSERT INTO posts (uuid, user_id, title, text) VALUES (:uuid, :user_id, :title, :text)'
        );
        // Выполняем запрос с конкретными значениями
        $statement->execute([
            ':uuid' => (string)$post->uuid(),
            ':user_id' => $post->getUser()->uuid(),
            ':title' => $post->getTitle(),
            ':text' => $post->getText(),
        ]);

    }

    // Метод для получения статьи по её UUID
    /**
     * @throws PostNotFoundException
     * @throws InvalidArgumentException
     */
    public function get(UUID $uuid): Post
    {

        $statement = $this->connection->prepare(
            'SELECT * FROM posts WHERE uuid = ?'
        );

        $statement->execute([(string)$uuid]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);


        // Бросаем исключение, если статья не найдена
        if ($result === false) {
            throw new PostNotFoundException(
                "Cannot get post: $uuid"
            );
        }
        return $this->getPost($statement, $uuid);
    }

    /**
     * @throws PostNotFoundException
     * @throws InvalidArgumentException
     */
    private function getPost(PDOStatement $statement, string $errorString): Post
    {

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
            throw new PostNotFoundException(
                "Cannot find post: $errorString"
            );
        }
        $user = $this->getUser($result['user_id']);
        var_dump($user);die();
        // Создаём объект статьи с полем username
        return new Post(
            new UUID($result['uuid']),
            $user,
            $result['title'],
            $result['text'],
        );
    }

    private function getUser($user_id): User
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM users WHERE uuid = :uuid'
        );
        $statement->execute([
            ':uuid' => $user_id,
        ]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return new User(
            new UUID($result['uuid']),
            $result['username'],
            new Name($result['first_name'], $result['last_name']),
        );
    }
}