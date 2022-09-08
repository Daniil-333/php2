<?php

namespace Geekbrains\App\UnitTests\Http\Actions\Posts;

use Geekbrains\App\Blog\Exceptions\AuthException;
use Geekbrains\App\Blog\Exceptions\HttpException;
use Geekbrains\App\Blog\Exceptions\JsonException;
use Geekbrains\App\Blog\Exceptions\PostNotFoundException;
use Geekbrains\App\Blog\Exceptions\UserNotFoundException;
use Geekbrains\App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use Geekbrains\App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use Geekbrains\App\Blog\Post;
use Geekbrains\App\Blog\User;
use Geekbrains\App\Blog\UUID;
use Geekbrains\App\Http\Actions\Posts\CreatePost;
use Geekbrains\App\Http\Auth\IdentificationInterface;
use Geekbrains\App\Http\ErrorResponse;
use Geekbrains\App\Http\Request;
use Geekbrains\App\Http\SuccessfulResponse;
use Geekbrains\App\Person\Name;
use Geekbrains\App\UnitTests\DummyLogger;
use PHPUnit\Framework\TestCase;

class CreatePostActionTest extends TestCase
{

    // Функция, создающая стаб репозитория статей,
    // принимает массив "существующих" статей
    private function postsRepository(array $posts): PostsRepositoryInterface
    {
        return new class($posts) implements PostsRepositoryInterface {
            private bool $called = false;
            public function __construct()
            {
            }

            public function save(Post $post): void
            {
                $this->called = true;
            }

            public function get(UUID $uuid): Post
            {
                throw new PostNotFoundException("Not found");
            }

            public function getByTitle(string $title): Post
            {
                throw new PostNotFoundException('Not found');
            }

            public function getCalled(): bool
            {
                return $this->called;
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
                    if ($user instanceof User && (string)$uuid == $user->uuid()) {
                        return $user;
                    }
                }
                throw new UserNotFoundException("Not found");
            }

            public function getByUsername(string $username): User
            {
                throw new UserNotFoundException('Not found');
            }
        };
    }

    private function identification(UsersRepositoryInterface $usersRepository): IdentificationInterface
    {
        return new class($usersRepository) implements IdentificationInterface {
            public function __construct(
                private UsersRepositoryInterface $usersRepository
            )
            {
            }
            public function user(Request $request): User
            {
                try {
                    $userUuid = $request->jsonBodyField('author_uuid');
                } catch (HttpException $e) {
                    throw new AuthException($e->getMessage());
                }
                try {
                    return $this->usersRepository->get(new UUID($userUuid));
                } catch (UserNotFoundException $e) {
                    throw new AuthException($e->getMessage());
                }
            }
        };
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws /JsonException
     */
    public function testItReturnsSuccessfulResponse(): void
    {
        $request = new Request([], [], '{
            "author_uuid": "c9e6813e-bae2-4140-96ac-8ddac672e13a",
            "title": "title_test",
            "text": "text_test"
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
                'text_test'
            ),
        ]);

        $identification = $this->identification($usersRepository);

        $action = new CreatePost($postRepository, $identification);

        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);

        $this->setOutputCallback(function ($data){
            $dataDecode = json_decode(
                $data,
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );

            $dataDecode['data']['uuid'] = "a1e3253e-bae2-4140-96ac-8ddac672e55b";
            return json_encode(
                $dataDecode,
                JSON_THROW_ON_ERROR
            );
        });

        $this->expectOutputString('{"success":true,"data":{"uuid":"a1e3253e-bae2-4140-96ac-8ddac672e55b"}}');
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testItReturnsErrorResponseIfNotFoundUser(): void
    {
        $request = new Request([], [], '{"author_uuid":"10373537-0805-4d7a-830e-22b481b4859c","title":"title","text":"text"}');

        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([]);
        $identification = $this->identification($usersRepository);

        $action = new CreatePost($postsRepository, $identification);

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Cannot find user: 10373537-0805-4d7a-830e-22b481b4859c"}');

        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @throws JsonException
     */
    public function testItReturnsErrorResponseIfNoTextProvided(): void
    {
        $request = new Request([], [], '{"author_uuid":"10373537-0805-4d7a-830e-22b481b4859c","title":"title"}');

        $postsRepository = $this->postsRepository([]);
        $usersRepository = $this->usersRepository([
            new User(
                new UUID('10373537-0805-4d7a-830e-22b481b4859c'),
                new Name('Ivan', 'Nikitin'), 'ivan',
            ),
        ]);
        $identification = $this->identification($usersRepository);

        $action = new CreatePost($postsRepository, $identification);

        $response = $action->handle($request);

        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"No such field: text"}');

        $response->send();
    }
}