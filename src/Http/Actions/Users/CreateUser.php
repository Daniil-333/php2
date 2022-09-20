<?php

namespace Geekbrains\App\Http\Actions\Users;

use Geekbrains\App\Blog\Exceptions\HttpException;
use Geekbrains\App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use Geekbrains\App\Blog\User;
use Geekbrains\App\Blog\UUID;
use Geekbrains\App\Http\Actions\ActionInterface;
use Geekbrains\App\Http\ErrorResponse;
use Geekbrains\App\Http\Request;
use Geekbrains\App\Http\Response;
use Geekbrains\App\Http\SuccessfulResponse;
use Geekbrains\App\Person\Name;
use Psr\Log\LoggerInterface;

class CreateUser implements ActionInterface
{

    public function __construct(
        private UsersRepositoryInterface $usersRepository
    ) {
    }

    public function handle(Request $request): Response
    {
        try {
            $newUserUuid = UUID::random();

            $user = new User(
                $newUserUuid,
                new Name(
                    $request->jsonBodyField('first_name'),
                    $request->jsonBodyField('last_name')
                ),
                $request->jsonBodyField('username')
            );

        } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $this->usersRepository->save($user);

        return new SuccessfulResponse([
            'uuid' => (string)$newUserUuid,
        ]);
    }
}