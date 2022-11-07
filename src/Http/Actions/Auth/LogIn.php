<?php

namespace Geekbrains\App\Http\Actions\Auth;

use DateTimeImmutable;
use Geekbrains\App\Blog\AuthToken;
use Geekbrains\App\Blog\Exceptions\AuthException;
use Geekbrains\App\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use Geekbrains\App\Http\Actions\ActionInterface;
use Geekbrains\App\Http\Auth\PasswordAuthenticationInterface;
use Geekbrains\App\Http\Request;
use Geekbrains\App\Http\Response;
use Geekbrains\App\Http\SuccessfulResponse;
use Geekbrains\App\Http\ErrorResponse;

class LogIn implements ActionInterface
{
    public function __construct(
        // Авторизация по паролю
        private PasswordAuthenticationInterface $passwordAuthentication,
        // Репозиторий токенов
        private AuthTokensRepositoryInterface $authTokensRepository
    ) {
    }

    /**
     * @throws \Exception
     */
    public function handle(Request $request): Response
    {
        // Аутентифицируем пользователя
        try {
            $user = $this->passwordAuthentication->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        // Генерируем токен
        $authToken = new AuthToken(
        // Случайная строка длиной 40 символов
            bin2hex(random_bytes(40)),
            $user->uuid(),
        // Срок годности - 1 день
            (new DateTimeImmutable())->modify('+1 day')
        );

        // Сохраняем токен в репозиторий
        $this->authTokensRepository->save($authToken);

        // Возвращаем токен
        return new SuccessfulResponse([
            'token' => (string)$authToken,
        ]);
    }
}