<?php

namespace Geekbrains\App\Http\Actions\Comments;

use Geekbrains\App\Blog\Comment;
use Geekbrains\App\Blog\Exceptions\HttpException;
use Geekbrains\App\Blog\Exceptions\InvalidArgumentException;
use Geekbrains\App\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use Geekbrains\App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use Geekbrains\App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use Geekbrains\App\Blog\UUID;
use Geekbrains\App\Http\Actions\ActionInterface;
use Geekbrains\App\Http\ErrorResponse;
use Geekbrains\App\Http\Request;
use Geekbrains\App\Http\Response;
use Geekbrains\App\Http\SuccessfulResponse;
use Psr\Log\LoggerInterface;

class CreateComment implements ActionInterface
{
    public function __construct(
        private CommentsRepositoryInterface $commentsRepository,
        private UsersRepositoryInterface $usersRepository,
        private PostsRepositoryInterface $postsRepository
    ) {
    }

    public function handle(Request $request): Response
    {
        try {
            $authorUuid = new UUID($request->jsonBodyField('author_uuid'));
            $post_uuid = new UUID($request->jsonBodyField('post_uuid'));
        } catch (HttpException | InvalidArgumentException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $user = $this->usersRepository->get($authorUuid);
        } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }

        try {
            $post = $this->postsRepository->get($post_uuid);
        } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $newCommentUuid = UUID::random();

        $comment = new Comment(
            $newCommentUuid,
            $user,
            $post,
            $request->jsonBodyField('text')
        );

        $this->commentsRepository->save($comment);

        return new SuccessfulResponse([
            'uuid' => (string)$newCommentUuid,
        ]);
    }
}