<?php

namespace Geekbrains\App\Blog\Command;

use Geekbrains\App\Blog\Exceptions\ArgumentsException;
use Geekbrains\App\Blog\Exceptions\CommandException;
use Geekbrains\App\Blog\Exceptions\InvalidArgumentException;
use Geekbrains\App\Blog\Exceptions\UserNotFoundException;
use Geekbrains\App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use Geekbrains\App\Blog\User;
use Geekbrains\App\Blog\UUID;
use Geekbrains\App\Person\Name;
use Psr\Log\LoggerInterface;

//php cli.php username=ivan first_name=Ivan last_name=Nikitin

class CreateUserCommand
{

// Команда зависит от контракта репозитория пользователей,
// а не от конкретной реализации
    public function __construct(
        private UsersRepositoryInterface $usersRepository,
        private LoggerInterface $logger
    )
    {
    }

    /**
     * @throws CommandException
     * @throws InvalidArgumentException|ArgumentsException
     */
    public function handle(Arguments $arguments): void
    {
        // Логируем информацию о том, что команда запущена
        // Уровень логирования – INFO
        $this->logger->info("Create user command started");

        $username = $arguments->get('username');

        if ($this->userExists($username)) {
            // Логируем сообщение с уровнем WARNING
            $this->logger->warning("User already exists: $username");
            // Вместо выбрасывания исключения просто выходим из функции
            return;
        }

        $uuid = UUID::random();
        $this->usersRepository->save(new User(
            $uuid,
            new Name(
                $arguments->get('first_name'),
                $arguments->get('last_name')),
            $username,
        ));

        // Логируем информацию о новом пользователе
        $this->logger->info("User created: $uuid");
    }

    private function userExists(string $username): bool
    {
        try {
        // Пытаемся получить пользователя из репозитория
            $this->usersRepository->getByUsername($username);
        } catch (UserNotFoundException) {
            return false;
        }
        return true;
    }
}