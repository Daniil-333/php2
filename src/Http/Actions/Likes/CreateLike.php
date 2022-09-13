<?php

namespace Geekbrains\App\Http\Actions\Likes;

use Geekbrains\App\Blog\Exceptions\AuthException;
use Geekbrains\App\Blog\Exceptions\HttpException;
use Geekbrains\App\Blog\Exceptions\InvalidArgumentException;
use Geekbrains\App\Blog\Exceptions\LikeFoundException;
use Geekbrains\App\Blog\Exceptions\PostNotFoundException;
use Geekbrains\App\Blog\Exceptions\UserNotFoundException;
use Geekbrains\App\Blog\Like;
use Geekbrains\App\Blog\Repositories\LikesRepository\LikesRepositoryInterface;
use Geekbrains\App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use Geekbrains\App\Blog\UUID;
use Geekbrains\App\Http\Actions\ActionInterface;
use Geekbrains\App\Http\Auth\TokenAuthenticationInterface;
use Geekbrains\App\Http\ErrorResponse;
use Geekbrains\App\Http\Request;
use Geekbrains\App\Http\Response;
use Geekbrains\App\Http\SuccessfulResponse;
use Psr\Log\LoggerInterface;

class CreateLike implements ActionInterface
{
    public function __construct(
        private LikesRepositoryInterface $likesRepository,
        private PostsRepositoryInterface $postsRepository,
        private TokenAuthenticationInterface $authentication
    ) {
    }

    public function handle(Request $request): Response
    {

        try {
            $user = $this->authentication->user($request);
        }catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $postUuid = new UUID($request->jsonBodyField('uuid_post'));
        } catch (HttpException | InvalidArgumentException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $this->postsRepository->get($postUuid);
        } catch (PostNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $userUuid = $user->uuid();
        } catch (UserNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $newLikeUuid = UUID::random();
        try {
            $like = new Like(
                $newLikeUuid,
                $postUuid,
                $userUuid
            );
        }catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $this->likesRepository->getByUserUuid($userUuid, $postUuid);
        }catch (LikeFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $this->likesRepository->save($like);

        return new SuccessfulResponse([
            'uuid' => (string)$like,
        ]);
    }
}