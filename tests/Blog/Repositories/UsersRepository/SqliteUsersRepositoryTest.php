<?php

namespace Geekbrains\App\UnitTests\Blog\Repositories\UsersRepository;

use Geekbrains\App\Blog\Exceptions\UserNotFoundException;
use Geekbrains\App\Blog\User;
use Geekbrains\App\Blog\UUID;
use Geekbrains\App\Person\Name;
use Geekbrains\App\UnitTests\DummyLogger;
use PHPUnit\Framework\TestCase;
use Geekbrains\App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use PDO;
use PDOStatement;

class SqliteUsersRepositoryTest extends TestCase
{


    // Тест, проверяющий, что SQLite-репозиторий бросает исключение,
    // когда запрашиваемый пользователь не найден
    public function testItThrowsAnExceptionWhenUserNotFound(): void
    {
        // 2. Создаём стаб подключения
        $connectionMock = $this->createStub(PDO::class);

        // 4. Стаб запроса
        $connectionStub = $this->createStub(PDOStatement::class);

        // 5. Стаб запроса будет возвращать false при вызове метода fetch
        $connectionStub->method('fetch')->willReturn(false);

        // 3. Стаб подключения будет возвращать другой стаб - стаб запроса - при вызове метода prepare
        $connectionMock->method('prepare')->willReturn($connectionStub);

        // 1. Передаём в репозиторий стаб подключения
        $repository = new SqliteUsersRepository($connectionMock, new DummyLogger());

        // Ожидаем, что будет брошено исключение
        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Cannot find user: Ivan');

        // Вызываем метод получения пользователя
        $repository->getByUsername('Ivan');
    }

    // Тест, проверяющий, что репозиторий сохраняет данные в БД
    public function testItSavesUserToDatabase(): void
    {
        // 2. Создаём стаб подключения
        $connectionStub = $this->createStub(PDO::class);

        // 4. Создаём мок запроса, возвращаемый стабом подключения
        $statementMock = $this->createMock(PDOStatement::class);

        // 5. Описываем ожидаемое взаимодействие
        // нашего репозитория с моком запроса
        $statementMock
            ->expects($this->once()) // Ожидаем, что будет вызван один раз
            ->method('execute') // метод execute
            ->with([ // с единственным аргументом - массивом
                ':uuid' => 'c9e6813e-bae2-4140-96ac-8ddac672e13a',
                ':username' => 'admin',
                ':first_name' => 'Ivan',
                ':last_name' => 'Nikitin',
            ]);

        // 3. При вызове метода prepare стаб подключения
        // возвращает мок запроса
        $connectionStub->method('prepare')->willReturn($statementMock);

        // 1. Передаём в репозиторий стаб подключения
        $repository = new SqliteUsersRepository($connectionStub, new DummyLogger());

        // Вызываем метод сохранения пользователя
        $repository->save(
            new User(   // Свойства пользователя точно такие, как и в описании мока
                new UUID('c9e6813e-bae2-4140-96ac-8ddac672e13a'),
                new Name('Ivan', 'Nikitin'),
            'admin'
            )
        );
    }

}