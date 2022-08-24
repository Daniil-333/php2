<?php

namespace Geekbrains\App\Blog\Repositories\UsersRepository;

use Geekbrains\App\Blog\Exceptions\InvalidArgumentException;
use Geekbrains\App\Blog\Exceptions\UserNotFoundException;
use Geekbrains\App\Blog\User;
use Geekbrains\App\Blog\UUID;
use Geekbrains\App\Person\Name;
use \PDO;
use \PDOStatement;

class SqliteUsersRepository implements UsersRepositoryInterface
{
    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }


    public function save(User $user): void
    {

        // Подготавливаем запрос
        $statement = $this->connection->prepare(
            'INSERT INTO users (first_name, last_name, uuid, username) 
VALUES (:first_name, :last_name, :uuid, :username)'

        );
        // Выполняем запрос с конкретными значениями
        $statement->execute([
            ':first_name' => $user->name()->first(),
            ':last_name' => $user->name()->last(),
            ':uuid' => (string)$user->uuid(),
            ':username' => $user->username(),
        ]);

    }

    // Также добавим метод для получения
        // пользователя по его UUID
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
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        // Бросаем исключение, если пользователь не найден
        if ($result === false) {
            throw new UserNotFoundException(
                "Cannot get user: $uuid"
            );
        }
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
            throw new UserNotFoundException(
                "Cannot find user: $errorString"
            );
        }
        // Создаём объект пользователя с полем username
        return new User(
            new UUID($result['uuid']),
            new Name($result['first_name'], $result['last_name']),
            $result['username'],
        );
    }
}