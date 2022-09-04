<?php

namespace Geekbrains\App\Blog\Repositories\LikesRepository;

use Geekbrains\App\Blog\Exceptions\LikeFoundException;
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

    public function getByPostUuid(UUID $uuid): array
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM likes WHERE uuidPost LIKE (:uuidPost)'
        );

        $statement->execute([
            ':uuidPost' => $uuid,
        ]);

        $result = $statement->fetchAll(PDO::FETCH_ASSOC);

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

    public function getByUserUuid(string $uuidUser): bool
    {
        $statement = $this->connection->prepare(
            'SELECT uuidPost FROM likes WHERE uuidUser LIKE (:uuidUser)'
        );

        $statement->execute([
            ':uuidUser' => $uuidUser,
        ]);
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result !== false) {
            throw new LikeFoundException(
                "Like already exist by user $uuidUser at post {$result['uuidPost']}"
            );
        }

        return true;
    }
}