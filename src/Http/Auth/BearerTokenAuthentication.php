<?php

namespace Geekbrains\App\Http\Auth;

use DateTimeImmutable;
use Geekbrains\App\Blog\AuthToken;
use Geekbrains\App\Blog\Exceptions\AuthException;
use Geekbrains\App\Blog\Exceptions\AuthTokenNotFoundException;
use Geekbrains\App\Blog\Exceptions\HttpException;
use Geekbrains\App\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use Geekbrains\App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use GeekBrains\App\Blog\User;
use GeekBrains\App\Http\Request;

class BearerTokenAuthentication implements TokenAuthenticationInterface
{
    private const HEADER_PREFIX = 'Bearer ';

    public function __construct(
        // Репозиторий токенов
        private AuthTokensRepositoryInterface $authTokensRepository,
        // Репозиторий пользователей
        private UsersRepositoryInterface $usersRepository,
    ) {
    }

    /**
     * @throws AuthException
     */
    public function user(Request $request): User
    {

        // Получаем токен из HTTP-заголовока
        $token = $this->getToken($request);

        // Ищем токен в репозитории
        try {
            $authToken = $this->authTokensRepository->get($token);
        } catch (AuthTokenNotFoundException) {
            throw new AuthException("Bad token: [$token]");
        }

        // Проверяем срок годности токена
        if ($authToken->expiresOn() <= new DateTimeImmutable()) {
            throw new AuthException("Token expired: [$token]");
        }

        // Получаем UUID пользователя из токена
        $userUuid = $authToken->userUuid();

        // Ищем и возвращаем пользователя
        return $this->usersRepository->get($userUuid);
    }

    public function getToken(Request $request): string
    {
        // Получаем HTTP-заголовок
        try {
            $header = $request->header('Authorization');
        } catch (HttpException $e) {
            throw new AuthException($e->getMessage());
        }

        // Проверяем, что заголовок имеет правильный формат
        if (!str_starts_with($header, self::HEADER_PREFIX)) {
            throw new AuthException("Malformed token: [$header]");
        }

        // Отрезаем префикс Bearer
        return mb_substr($header, strlen(self::HEADER_PREFIX));
    }
}