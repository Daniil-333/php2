<?php

namespace Geekbrains\App\Http\Actions\Users;

use Geekbrains\App\Blog\Exceptions\HttpException;
use Geekbrains\App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use Geekbrains\App\Blog\Exceptions\UserNotFoundException;
use Geekbrains\App\Http\Actions\ActionInterface;
use Geekbrains\App\Http\ErrorResponse;
use Geekbrains\App\Http\Request;
use Geekbrains\App\Http\Response;
use Geekbrains\App\Http\SuccessfulResponse;
use Psr\Log\LoggerInterface;

class FindByUsername implements ActionInterface
{
    // Нам понадобится репозиторий пользователей,
    // внедряем его контракт в качестве зависимости
    public function __construct(
        private UsersRepositoryInterface $usersRepository
    )
    {
    }

    public function handle(Request $request): Response
    {
        try {
        // Пытаемся получить искомое имя пользователя из запроса
            $username = $request->query('username');
        } catch (HttpException $e) {
        // Если в запросе нет параметра username -
        // возвращаем неуспешный ответ,
        // сообщение об ошибке берём из описания исключения
            return new ErrorResponse($e->getMessage());
        }


        try {
        // Пытаемся найти пользователя в репозитории
            $user = $this->usersRepository->getByUsername($username);
        } catch (UserNotFoundException $e) {
        // Если пользователь не найден -
        // возвращаем неуспешный ответ
            return new ErrorResponse($e->getMessage());
        }


    // Возвращаем успешный ответ
        return new SuccessfulResponse([
            'username' => $user->username(),
            'name' => $user->name()->first() . ' ' . $user->name()->last(),
        ]);
    }
}