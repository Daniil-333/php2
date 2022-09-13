<?php

namespace Geekbrains\App\Http\Actions\Posts;

use Geekbrains\App\Blog\Exceptions\AuthException;
use Geekbrains\App\Blog\Exceptions\HttpException;
use Geekbrains\App\Blog\Exceptions\InvalidArgumentException;
use Geekbrains\App\Blog\Exceptions\PostNotFoundException;
use Geekbrains\App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use Geekbrains\App\Blog\UUID;
use Geekbrains\App\Http\Actions\ActionInterface;
use Geekbrains\App\Http\Auth\TokenAuthenticationInterface;
use Geekbrains\App\Http\ErrorResponse;
use Geekbrains\App\Http\Request;
use Geekbrains\App\Http\Response;
use Geekbrains\App\Http\SuccessfulResponse;
use Psr\Log\LoggerInterface;

class DeletePost implements ActionInterface
{
    public function __construct(
        private PostsRepositoryInterface $postsRepository,
        private TokenAuthenticationInterface $authentication
    ) {
    }

    public function handle(Request $request): Response
    {
        try {
            $this->authentication->user($request);
        }catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $postUuid = new UUID($request->query('uuid'));
        } catch (HttpException | InvalidArgumentException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $this->postsRepository->get($postUuid);
        } catch (PostNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $this->postsRepository->delete($postUuid);

        return new SuccessfulResponse([
            'uuid' => (string)$postUuid,
        ]);
    }
}