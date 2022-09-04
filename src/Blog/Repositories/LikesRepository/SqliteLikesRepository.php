<?php

namespace Geekbrains\App\Blog\Repositories\LikesRepository;

use Geekbrains\App\Blog\Exceptions\InvalidArgumentException;
use Geekbrains\App\Blog\Exceptions\UserNotFoundException;
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

/*    public function getByUsername(string $username): User
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM users WHERE username = :username'
        );
        $statement->execute([
            ':username' => $username,
        ]);

       return $this->getUser($statement, $username);
    }

    private function getUser(PDOStatement $statement, string $errorString): User
    {

        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        if ($result === false) {
            throw new UserNotFoundException(
                "Cannot find user: $errorString"
            );
        }

        return new User(
            new UUID($result['uuid']),
            new Name($result['first_name'], $result['last_name']),
            $result['username'],
        );
    }*/

    public function getByPostUuid(UUID $uuid): int
    {
        // TODO: Implement getByPostUuid() method.
    }
}