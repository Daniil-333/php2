<?php

namespace Geekbrains\App\Http\Actions\Posts;

use Geekbrains\App\Blog\Exceptions\AuthException;
use Geekbrains\App\Blog\Exceptions\HttpException;
use Geekbrains\App\Blog\Post;
use Geekbrains\App\Blog\Repositories\AuthTokensRepository\AuthTokensRepositoryInterface;
use Geekbrains\App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use Geekbrains\App\Blog\UUID;
use Geekbrains\App\Http\Actions\ActionInterface;
use Geekbrains\App\Http\Auth\TokenAuthenticationInterface;
use Geekbrains\App\Http\ErrorResponse;
use Geekbrains\App\Http\Request;
use Geekbrains\App\Http\Response;
use Geekbrains\App\Http\SuccessfulResponse;

class CreatePost implements ActionInterface
{
    public function __construct(
        private PostsRepositoryInterface $postsRepository,
        private TokenAuthenticationInterface $authentication
    ) {
    }

    public function handle(Request $request): Response
    {
        // Обрабатываем ошибки аутентификации
        // и возвращаем неудачный ответ
        // с сообщением об ошибке
        try {
            $user = $this->authentication->user($request);
        }catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }


        $newPostUuid = UUID::random();

        try {
            $post = new Post(
                $newPostUuid,
                $user,
                $request->jsonBodyField('title'),
                $request->jsonBodyField('text')
            );
        }catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $this->postsRepository->save($post);

        return new SuccessfulResponse([
            'uuid' => (string)$newPostUuid,
        ]);
    }
}