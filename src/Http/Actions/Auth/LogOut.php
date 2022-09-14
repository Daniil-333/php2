<?php

namespace Geekbrains\App\Http\Actions\Auth;

use DateTimeImmutable;
use Geekbrains\App\Blog\AuthToken;
use Geekbrains\App\Blog\Exceptions\AuthException;
use Geekbrains\App\Blog\Exceptions\AuthTokenNotFoundException;
use Geekbrains\App\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use Geekbrains\App\Http\Actions\ActionInterface;
use Geekbrains\App\Http\Auth\BearerTokenAuthentication;
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
        private BearerTokenAuthentication $authentication,
        // Репозиторий токенов
        private AuthTokensRepositoryInterface $authTokensRepository
    ) {
    }

    /**
     * @throws \Exception
     */
    public function handle(Request $request): Response
    {
        $token = $this->authentication->getToken($request);

        try {
            $authToken = $this->authTokensRepository->get($token);
        } catch (AuthTokenNotFoundException $e) {
            throw new AuthException($e->getMessage());
        }

        $authToken->setExpiresOn(new \DateTimeImmutable("now"));

        $this->authTokensRepository->save($authToken);

        return new SuccessfulResponse([
            'token' => (string)$authToken,
            'logout' => 'All done'
        ]);
    }
}