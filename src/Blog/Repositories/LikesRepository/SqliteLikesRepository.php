<?php

namespace Geekbrains\App\Blog\Repositories\LikesRepository;

use Doctrine\Instantiator\Exception\InvalidArgumentException;
use Geekbrains\App\Blog\Exceptions\LikeFoundException;
use Geekbrains\App\Blog\Exceptions\LikeNotFoundException;
use Geekbrains\App\Blog\Like;
use Geekbrains\App\Blog\UUID;

use \PDO;
use \PDOStatement;

class SqliteLikesRepository implements LikesRepositoryInterface
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function save(Like $like): void
    {
        $statement = $this->connection->prepare(
            'INSERT INTO likes (
                       uuid, 
                       uuidPost, 
                       uuidUser) 
                   VALUES (
                           :uuid, 
                           :uuidPost, 
                           :uuidUser
                           )'
        );

        $statement->execute([
            ':uuid' => $like->uuid(),
            ':uuidPost' => $like->getUuidPost(),
            ':uuidUser' => $like->getUuidUser()
        ]);
    }

    /**
     * @throws LikeNotFoundException | InvalidArgumentException
     */
    public function getByPostUuid(UUID $uuid): array
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM likes WHERE uuidPost = :uuidPost'
        );

        $statement->execute([
            ':uuidPost' => $uuid,
        ]);

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

        if ($result === false) {
            throw new LikeNotFoundException(
                "Likes at post $uuid not found"
            );
        }

        $likes = [];

        foreach ($result as $like) {
            $likes[] = new Like(
                new UUID($like['uuid']),
                new UUID($like['uuidPost']),
                new UUID($like['uuidUser']),
            );
        }

        return $likes;
    }

    /**
     * @throws LikeFoundException
     */
    public function getByUserUuid(string $uuidUser, string $uuidPost): void
    {
        $statement = $this->connection->prepare(
            'SELECT uuidPost FROM likes 
                        WHERE uuidUser = :uuidUser 
                          AND uuidPost = :uuidPost'
        );

        $statement->execute([
            ':uuidUser' => $uuidUser,
            ':uuidPost' => $uuidPost
        ]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result !== false) {
            throw new LikeFoundException(
                "Like already exist by user $uuidUser at post {$result['uuidPost']}"
            );
        }
    }
}