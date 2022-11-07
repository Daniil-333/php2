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
use Psr\Log\LoggerInterface;

class SqlitePostsRepository implements PostsRepositoryInterface
{
    private PDO $connection;
    private LoggerInterface $logger;

    public function __construct(PDO $connection, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->logger = $logger;
    }


    public function save(Post $post): void
    {
        $this->logger->info("Create post command started");

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

        $newPostUuid = (string)$post->uuid();

        $statement->execute([
            ':uuid' => $newPostUuid,
            ':user_id' => (string)$post->getUser()->uuid(),
            ':title' => $post->getTitle(),
            ':text' => $post->getText(),
        ]);

        $this->logger->info("Post created with UUID: $newPostUuid");
    }

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
            $this->logger->warning("Post not found with UUID: $errorString");

            throw new PostNotFoundException(
                "Cannot find post: $errorString"
            );
        }

        $userRepository = new SqliteUsersRepository($this->connection, $this->logger);
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
        $this->logger->info("Delete post command started");

        $statement = $this->connection->prepare(
            'DELETE FROM posts WHERE uuid = ?'
        );

        $statement->execute([(string)$uuid]);

        $this->logger->info("Post deleted: $uuid");
    }

    public function clearData(): void
    {
        $this->logger->info("Clear table Posts command started");

        $this->connection->query(
            "DELETE FROM posts"
        );

        $this->logger->info("Table Posts is clear");
    }
}