<?php

namespace Geekbrains\App\Http\Actions\Auth;

use DateTimeImmutable;
use Geekbrains\App\Blog\AuthToken;
use Geekbrains\App\Blog\Exceptions\AuthException;
use Geekbrains\App\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use Geekbrains\App\Http\Actions\ActionInterface;
use Geekbrains\App\Http\Auth\PasswordAuthenticationInterface;
use Geekbrains\App\Http\Auth\TokenAuthenticationInterface;
use Geekbrains\App\Http\Request;
use Geekbrains\App\Http\Response;
use Geekbrains\App\Http\SuccessfulResponse;
use Geekbrains\App\Http\ErrorResponse;

class LogOut implements ActionInterface
{

    public function __construct(
        // Авторизация по токену
        private TokenAuthenticationInterface $tokenAuthentication,
        // Репозиторий токенов
        private AuthTokensRepositoryInterface $authTokensRepository
    ) {
    }

    /**
     * @throws \Exception
     */
    public function handle(Request $request): Response
    {

        try {
            $user = $this->tokenAuthentication->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $token = $this->tokenAuthentication->getToken($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $authToken = new AuthToken(
            $token,
            $user->uuid(),
            (new DateTimeImmutable())->modify('today')
        );

        $this->authTokensRepository->save($authToken);

        return new SuccessfulResponse([
            'token' => (string)$authToken,
            'logout' => 'All done'
        ]);
    }
}