<?php

namespace  Geekbrains\App\Blog\Repositories\UnitTest\UsersRepository;

use Geekbrains\App\Blog\Exceptions\UserNotFoundException;
use Geekbrains\App\Blog\User;
use Geekbrains\App\Blog\UUID;
use Geekbrains\App\Person\Name;
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
        $connectionMock = $this->createStub(PDO::class);
        $connectionStub = $this->createMock(PDOStatement::class);
        $connectionStub->method('fetch')->willReturn(false);
        $connectionMock->method('prepare')->willReturn(false);

        $repository = new SqliteUsersRepository($connectionMock);
        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('Cannot find user: Ivan');
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
        $repository = new SqliteUsersRepository($connectionStub);

        // Вызываем метод сохранения пользователя
        $repository->save(
            new User(   // Свойства пользователя точно такие, как и в описании мока
                new UUID('c9e6813e-bae2-4140-96ac-8ddac672e13a'),
                new Name('Ivan', 'Nikitin'),
            'ivan123'
            )
        );
    }

}