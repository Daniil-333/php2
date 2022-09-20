<?php

namespace Geekbrains\App\Blog\Repositories\CommentsRepository;

use Geekbrains\App\Blog\Exceptions\CommentNotFoundException;
use Geekbrains\App\Blog\Exceptions\InvalidArgumentException;
use Geekbrains\App\Blog\Exceptions\UserNotFoundException;
use Geekbrains\App\Blog\Exceptions\PostNotFoundException;
use Geekbrains\App\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Geekbrains\App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Geekbrains\App\Blog\Comment;
use Geekbrains\App\Blog\UUID;
use \PDO;
use \PDOStatement;
use Psr\Log\LoggerInterface;

class SqliteCommentsRepository implements CommentsRepositoryInterface
{

    private PDO $connection;
    private LoggerInterface $logger;


    public function __construct(PDO $connection, LoggerInterface $logger)
    {
        $this->connection = $connection;
        $this->logger = $logger;
    }

    public function save(Comment $comment): void
    {
        $this->logger->info("Create comment command started");

        $statement = $this->connection->prepare(
            'INSERT INTO comments (uuid, user_id, post_id, text) 
                        VALUES (:uuid, :user_id, :post_id, :text)'
        );

        $newCommentUuid = (string)$comment->uuid();

        $statement->execute([
            ':uuid' => $newCommentUuid,
            ':user_id' => $comment->getUser()->uuid(),
            ':post_id' => $comment->getPost()->uuid(),
            ':text' => $comment->getText(),
        ]);

        $this->logger->info("Comment created with UUID: $newCommentUuid");
    }

    /**
     * @throws PostNotFoundException
     * @throws InvalidArgumentException
     * @throws UserNotFoundException
     */
    public function get(UUID $uuid): Comment
    {
        $statement = $this->connection->prepare(
            'SELECT * FROM comments WHERE uuid = ?'
        );

        $statement->execute([(string)$uuid]);

        return $this->getComment($statement, $uuid);
    }

    /**
     * @throws CommentNotFoundException
     * @throws InvalidArgumentException
     */
    private function getComment(PDOStatement $statement, string $errorString): Comment
    {

        $result = $statement->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
            $this->logger->warning("Cannot find comment: $errorString");

            throw new CommentNotFoundException(
                "Cannot find comment: $errorString"
            );
        }
        $user = (new SqliteUsersRepository($this->connection, $this->logger))->get(new UUID($result['user_id']));
        $post = (new SqlitePostsRepository($this->connection, $this->logger))->get(new UUID($result['post_id']));

        return new Comment(
            new UUID($result['uuid']),
            $user,
            $post,
            $result['text'],
        );
    }
}