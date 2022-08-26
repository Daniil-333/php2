<?php

namespace Geekbrains\App\Blog\Repositories\CommentsRepository;

use Geekbrains\App\Blog\Exceptions\CommentNotFoundException;
use Geekbrains\App\Blog\Exceptions\InvalidArgumentException;
use Geekbrains\App\Blog\Exceptions\UserNotFoundException;
use Geekbrains\App\Blog\Exceptions\PostNotFoundException;
use Geekbrains\App\Blog\Post;
use Geekbrains\App\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Geekbrains\App\Blog\Repositories\UsersRepository\SqliteUsersRepository;
use Geekbrains\App\Blog\User;
use Geekbrains\App\Blog\Comment;
use Geekbrains\App\Blog\UUID;
use Geekbrains\App\Person\Name;
use \PDO;
use \PDOStatement;

class SqliteCommentsRepository implements CommentsRepositoryInterface
{

    private PDO $connection;

    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    public function save(Comment $comment): void
    {
        // Подготавливаем запрос
        $statement = $this->connection->prepare(
            'INSERT INTO comments (uuid, user_id, post_id, text) VALUES (:uuid, :user_id, :post_id, :text)'
        );
        // Выполняем запрос с конкретными значениями
        $statement->execute([
            ':uuid' => (string)$comment->uuid(),
            ':user_id' => $comment->getUser()->uuid(),
            ':post_id' => $comment->getPost()->uuid(),
            ':text' => (string)$comment,
        ]);
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
            throw new CommentNotFoundException(
                "Cannot find comment: $errorString"
            );
        }
        $user = (new SqliteUsersRepository($this->connection))->get(new UUID($result['user_id']));
        $post = (new SqlitePostsRepository($this->connection))->get(new UUID($result['post_id']));

        return new Comment(
            new UUID($result['uuid']),
            $user,
            $post,
            $result['text'],
        );
    }
}