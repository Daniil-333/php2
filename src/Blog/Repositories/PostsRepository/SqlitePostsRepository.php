<?php

namespace Geekbrains\App\Blog\Repositories\PostsRepository;

use Geekbrains\App\Blog\Exceptions\InvalidArgumentException;
use Geekbrains\App\Blog\Exceptions\PostNotFoundException;
use Geekbrains\App\Blog\Exceptions\UserNotFoundException;
use Geekbrains\App\Blog\Post;
use Geekbrains\App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Geekbrains\App\Blog\UUID;
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
            'INSERT INTO posts (
                        uuid, 
                       user_id, 
                       title, 
                       text
                   ) VALUES (
                         :uuid, 
                         :user_id, 
                         :title, 
                         :text
                             )
                    ON CONFLICT (uuid) DO UPDATE SET uuid = :uuid'

        );

        // Выполняем запрос с конкретными значениями
//        var_dump($post->getUser()->);
        $statement->execute([
            ':uuid' => (string)$post->uuid(),
            ':user_id' => (string)$post->getUser()->uuid(),
            ':title' => $post->getTitle(),
            ':text' => $post->getText(),
        ]);

    }

    // Метод для получения статьи по её UUID

    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     * @throws PostNotFoundException
     */
    public function get(UUID $uuid): Post
    {

        $statement = $this->connection->prepare(
            'SELECT * FROM posts WHERE uuid = ?'
        );

        $statement->execute([(string)$uuid]);

        return $this->getPost($statement, $uuid);
    }

    /**
     * @throws PostNotFoundException
     * @throws InvalidArgumentException|UserNotFoundException
     */
    private function getPost(PDOStatement $statement, string $errorString): Post
    {

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
            throw new PostNotFoundException(
                "Cannot find post: $errorString"
            );
        }

        $userRepository = new SqliteUsersRepository($this->connection);
        $user = $userRepository->get(new UUID($result['user_id']));

        return new Post(
            new UUID($result['uuid']),
            $user,
            $result['title'],
            $result['text'],
        );
    }

    /**
     * @throws PostNotFoundException
     */
    public function delete(UUID $uuid): void
    {
        $statement = $this->connection->prepare(
            'DELETE FROM posts WHERE uuid = ?'
        );

        $statement->execute([(string)$uuid]);
    }
}