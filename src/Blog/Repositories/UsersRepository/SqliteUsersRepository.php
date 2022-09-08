<?php

namespace Geekbrains\App\Blog\Repositories\UsersRepository;

use Geekbrains\App\Blog\Exceptions\InvalidArgumentException;
use Geekbrains\App\Blog\Exceptions\UserNotFoundException;
use Geekbrains\App\Blog\User;
use Geekbrains\App\Blog\UUID;
use Geekbrains\App\Person\Name;
use \PDO;
use \PDOStatement;
use Psr\Log\LoggerInterface;

class SqliteUsersRepository implements UsersRepositoryInterface
{
    private PDO $connection;
    private LoggerInterface $logger;

    public function __construct(PDO $connection, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->logger = $logger;
    }

    public function save(User $user): void
    {
        $this->logger->info("Create user command started");

        $statement = $this->connection->prepare(
            'INSERT INTO users (
                       first_name, 
                       last_name, 
                       uuid, 
                       username) 
                   VALUES (
                           :first_name, 
                           :last_name, 
                           :uuid, 
                           :username
                           )
                   ON CONFLICT (uuid) DO UPDATE SET
                   first_name = :first_name,
                   last_name = :last_name'

        );

        $newUserUuid = (string)$user->uuid();

        $statement->execute([
            ':first_name' => $user->name()->first(),
            ':last_name' => $user->name()->last(),
            ':uuid' => $newUserUuid,
            ':username' => $user->username(),
        ]);

        $this->logger->info("User created: $newUserUuid");
    }

    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    public function get(UUID $uuid): User
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM users WHERE uuid = ?'
        );

        $statement->execute([(string)$uuid]);

        return $this->getUser($statement, $uuid);
    }

    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    public function getByUsername(string $username): User
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM users WHERE username = :username'
        );
        $statement->execute([
            ':username' => $username,
        ]);

       return $this->getUser($statement, $username);
    }

    /**
     * @throws UserNotFoundException
     * @throws InvalidArgumentException
     */
    private function getUser(PDOStatement $statement, string $errorString): User
    {

        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        if ($result === false) {
            $this->logger->warning("User with username {$errorString} not found");

            throw new UserNotFoundException(
                "Cannot find user: $errorString"
            );
        }

        return new User(
            new UUID($result['uuid']),
            new Name($result['first_name'], $result['last_name']),
            $result['username'],
        );
    }
}