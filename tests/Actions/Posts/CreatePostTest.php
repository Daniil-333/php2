<?php

namespace Geekbrains\App\UnitTests\Actions\Posts;

use Geekbrains\App\Blog\Exceptions\PostNotFoundException;
use Geekbrains\App\Blog\Exceptions\UserNotFoundException;
use Geekbrains\App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use Geekbrains\App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use Geekbrains\App\Blog\Post;
use Geekbrains\App\Blog\User;
use Geekbrains\App\Blog\UUID;
use Geekbrains\App\Http\Actions\Posts\CreatePost;
use Geekbrains\App\Http\Request;
use Geekbrains\App\Http\SuccessfulResponse;
use Geekbrains\App\Person\Name;
use PHPUnit\Framework\TestCase;

class CreatePostTest extends TestCase
{

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws /JsonException
     */
    public function testItReturnsSuccessfulResponse(): void
    {
        $request = new Request([], [], '{
            "author_uuid": "c9e6813e-bae2-4140-96ac-8ddac672e13a",
            "text": "some text",
            "title": "some title"
            }');

        $usersRepository = $this->usersRepository([
            new User(
                new UUID('c9e6813e-bae2-4140-96ac-8ddac672e13a'),
                new Name('Ivan', 'Nikitin'),
                'admin',
            ),
        ]);

        $postRepository = $this->postsRepository([
            new Post(
                new UUID('a1e3253e-bae2-4140-96ac-8ddac672e55b'),
                $usersRepository->get(new UUID('c9e6813e-bae2-4140-96ac-8ddac672e13a')),
                'title_test',
                'text_test',
            ),
        ]);
        $action = new CreatePost($postRepository, $usersRepository);
        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);
        $this->expectOutputString('{"success":true,"data":{"uuid":"c9e3253e-bae2-4140-96ac-8ddac672e10r"}}');
        $response->send();
    }

    // Функция, создающая стаб репозитория статей,
    // принимает массив "существующих" статей
    private function postsRepository(array $posts): PostsRepositoryInterface
    {
        // В конструктор анонимного класса передаём массив пользователей
        return new class($posts) implements PostsRepositoryInterface {
            public function __construct(
                private array $posts
            )
            {
            }

            public function save(Post $post): void
            {
            }

            public function get(UUID $uuid): Post
            {
                throw new PostNotFoundException("Not found");
            }

            public function getPost(string $username): User
            {
            }

            public function delete(UUID $uuid): void
            {
            }
        };
    }

    private function usersRepository(array $users): UsersRepositoryInterface
    {
        // В конструктор анонимного класса передаём массив пользователей
        return new class($users) implements UsersRepositoryInterface {
            public function __construct(
                private array $users
            )
            {
            }

            public function save(User $user): void
            {
            }

            public function get(UUID $uuid): User
            {
                foreach ($this->users as $user) {
                    if ($user instanceof User && $uuid === $user->uuid()) {
                        return $user;
                    }
                }
                throw new UserNotFoundException("Not found");
            }

            public function getByUsername(string $username): User
            {
            }
        };
    }
}