<?php

namespace Geekbrains\App\Http\Auth;

use Geekbrains\App\Blog\Exceptions\AuthException;
use Geekbrains\App\Blog\Exceptions\HttpException;
use Geekbrains\App\Blog\Exceptions\UserNotFoundException;
use Geekbrains\App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use Geekbrains\App\http\Request;
use Geekbrains\App\Blog\User;

class JsonBodyUsernameIdentification implements IdentificationInterface
{
    public function __construct(
        private UsersRepositoryInterface $usersRepository
    )
    {
    }

    /**
     * @throws AuthException
     */
    public function user(Request $request): User
    {
        try {
            // Получаем имя пользователя из JSON-тела запроса;
            // ожидаем, что имя пользователя находится в поле username
            $username = $request->jsonBodyField('username');
        } catch (HttpException $e) {
            // Если невозможно получить имя пользователя из запроса -
            // бросаем исключение
            throw new AuthException($e->getMessage());
        }
        try {
            // Ищем пользователя в репозитории и возвращаем его
            return $this->usersRepository->getByUsername($username);
        } catch (UserNotFoundException $e) {
            // Если пользователь не найден -
            // бросаем исключение
            throw new AuthException($e->getMessage());
        }

    }
}