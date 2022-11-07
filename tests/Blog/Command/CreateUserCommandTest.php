<?php

namespace Geekbrains\App\UnitTests\Blog\Command;

use Geekbrains\App\Blog\Command\Arguments;
use Geekbrains\App\Blog\Command\CreateUserCommand;
use Geekbrains\App\Blog\Command\Users\CreateUser;
use Geekbrains\App\Blog\Exceptions\ArgumentsException;
use Geekbrains\App\Blog\Exceptions\UserNotFoundException;
use Geekbrains\App\Blog\Repositories\UsersRepository\DummyUsersRepository;
use Geekbrains\App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use Geekbrains\App\Blog\Exceptions\CommandException;
use Geekbrains\App\UnitTests\DummyLogger;
use Geekbrains\App\Blog\User;
use Geekbrains\App\Blog\UUID;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class CreateUserCommandTest extends TestCase
{

    public function testItRequiresPassword(): void
    {
        $command = new CreateUserCommand(
            $this->makeUsersRepository(),
            new DummyLogger()
        );
        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: password');
        $command->handle(new Arguments([
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
            'username' => 'Ivan',
        ]));
    }

    // Проверяем, что команда создания пользователя бросает исключение,
    // если пользователь с таким именем уже существует
    public function testItThrowsAnExceptionWhenUserAlreadyExists(): void
    {
        // Создаём объект команды
        // У команды одна зависимость - UsersRepositoryInterface
        $command = new CreateUserCommand(new DummyUsersRepository(), new DummyLogger());

        // Описываем тип ожидаемого исключения
        $this->expectException(CommandException::class);

        // и его сообщение
        $this->expectExceptionMessage('User already exists: admin');

        // Запускаем команду с аргументами
        $command->handle(new Arguments([
            'username' => 'admin',
            'password' => '123'
        ]));
    }

    public function testItRequiresFirstName(): void
    {
        // $usersRepository - это объект анонимного класса,
        // реализующего контракт UsersRepositoryInterface
        $usersRepository = new class implements UsersRepositoryInterface {
            public function save(User $user): void
            {}

            public function get(UUID $uuid): User
            {
        // И здесь ничего не делаем
                throw new UserNotFoundException("Not found");
            }

            public function getByUsername(string $username): User
            {
                // И здесь ничего не делаем
                throw new UserNotFoundException("Not found");
            }

            public function clearData(): void
            {
            }
        };

        // Передаём объект анонимного класса
        // в качестве реализации UsersRepositoryInterface
        $command = new CreateUserCommand($usersRepository, new DummyLogger());
        // Ожидаем, что будет брошено исключение
        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: first_name');
        // Запускаем команду
        $command->handle(new Arguments([
            'username' => 'Ivan',
            'password' => '123'
        ]));
    }

    // Функция возвращает объект типа UsersRepositoryInterface
    private function makeUsersRepository(): UsersRepositoryInterface
    {
        return new class implements UsersRepositoryInterface {
            public function save(User $user): void {}

            public function get(UUID $uuid): User
            {
                throw new UserNotFoundException("Not found");
            }

            public function getByUsername(string $username): User
            {
                throw new UserNotFoundException("Not found");
            }

            public function clearData(): void
            {
            }
        };
    }

    // Тест проверяет, что команда действительно требует фамилию пользователя
    public function testItRequiresLastName(): void
    {
        // Передаём в конструктор команды объект, возвращаемый нашей функцией
        $command = new CreateUserCommand(
            $this->makeUsersRepository(),
            // Тестовая реализация логгера
            new DummyLogger()
        );
        $this->expectException(ArgumentsException::class);
        $this->expectExceptionMessage('No such argument: last_name');
        $command->handle(new Arguments([
            'username' => 'Ivan',
        // Нам нужно передать имя пользователя,
        // чтобы дойти до проверки наличия фамилии
            'first_name' => 'Ivan',
            'password' => '123'
        ]));
    }


    // Тест, проверяющий, что команда сохраняет пользователя в репозитории
    public function testItSavesUserToRepository(): void
    {
        // Создаём объект анонимного класса
        $usersRepository = new class implements UsersRepositoryInterface {

            // В этом свойстве мы храним информацию о том,
            // был ли вызван метод save
            private bool $called = false;

            public function save(User $user): void
            {
                // Запоминаем, что метод save был вызван
                $this->called = true;
            }

            public function get(UUID $uuid): User
            {
                throw new UserNotFoundException("Not found");
            }

            public function getByUsername(string $username): User
            {
                throw new UserNotFoundException("Not found");
            }

            // Этого метода нет в контракте UsersRepositoryInterface,
            // но ничто не мешает его добавить.
            // С помощью этого метода мы можем узнать,
            // был ли вызван метод save
            public function wasCalled(): bool
            {
                return $this->called;
            }

            public function clearData(): void
            {
            }
        };

        // Передаём наш мок в команду
        $command = new CreateUserCommand($usersRepository, new DummyLogger());

        // Запускаем команду
        $command = new CreateUser(
            $usersRepository
        );
        $command->run(
            new ArrayInput([
                'username' => 'Ivan',
                'password' => 'some_password',
                'first_name' => 'Ivan',
                'last_name' => 'Nikitin',
            ]),
            new NullOutput()
        );

        // Проверяем утверждение относительно мока,
        // а не утверждение относительно команды
        $this->assertTrue($usersRepository->wasCalled());
    }
}