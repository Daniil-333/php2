<?php

namespace  Geekbrains\App\Blog\Repositories\PostsRepository\UnitTest\PostsRepository;

use Geekbrains\App\Blog\Exceptions\InvalidArgumentException;
use Geekbrains\App\Blog\Exceptions\PostNotFoundException;
use Geekbrains\App\Blog\Exceptions\UserNotFoundException;
use Geekbrains\App\Blog\Repositories\PostsRepository\SqlitePostsRepository;
use Geekbrains\App\Blog\User;
use Geekbrains\App\Blog\Post;
use Geekbrains\App\Blog\UUID;
use Geekbrains\App\Person\Name;
use PHPUnit\Framework\TestCase;
use PDO;
use PDOStatement;

class SqlitePostsRepositoryTest extends TestCase
{


    public function testItSavesPostToDatabase(): void
    {

        $uuidPost = UUID::random();
        $uuidUser = '690bc4b2-efcf-4a96-8ffa-978e9796bb0c';

        $connectionStub = $this->createStub(PDO::class);
        $statementMock = $this->createMock(PDOStatement::class);

        $user = new User(
            new UUID($uuidUser),
            new Name('Ivan', 'Nikitin'),
            'ivan',
        );

        $statementMock
            ->expects($this->once())
            ->method('execute')
            ->with([
                ':uuid' => $uuidPost,
                ':user_id' => $uuidUser,
                ':title' => 'title',
                ':text' => 'text',
            ]);
        $connectionStub->method('prepare')->willReturn($statementMock);

        $repository = new SqlitePostsRepository($connectionStub);

        $repository->save(
            new Post(
                new UUID($uuidPost),
                $user,
                'title',
                'text'
            )
        );
    }

    public function testItGetPostByUuid(): void
    {
        $connectionStub = $this->createStub(\PDO::class);
        $statementStubPost = $this->createStub(\PDOStatement::class);
        $statementStubUser = $this->createStub(\PDOStatement::class);

        //TODO соединить
        $statementStubPost->method('fetch')->willReturn([
            'uuid' => '6090c267-410f-456e-bd05-df6bb254c0a1',
            'user_id' => '3b697686-01bf-433a-bf17-53ce84cb987b',
            'title' => 'title2',
            'text' => 'text2',
        ]);

        $statementStubUser->method('fetch')->willReturn([
            'uuid' => '3b697686-01bf-433a-bf17-53ce84cb987b',
            'username' => 'ivan2',
            'first_name' => 'Ivan',
            'last_name' => 'Nikitin',
        ]);

        $connectionStub->method('prepare')->willReturn($statementStubPost, $statementStubUser);


        $postRepository = new SqlitePostsRepository($connectionStub);
        $post = $postRepository->get(new UUID('6090c267-410f-456e-bd05-df6bb254c0a1'));

        $this->assertSame('6090c267-410f-456e-bd05-df6bb254c0a1', (string)$post->uuid());
    }


    public function testItThrowsAnExceptionWhenPostNotFound(): void
    {
        $connectionStub = $this->createStub(PDO::class);
        $statementStub = $this->createStub(PDOStatement::class);
        $statementStub->method('fetch')->willReturn(false);
        $connectionStub->method('prepare')->willReturn($statementStub);

        $repository = new SqlitePostsRepository($connectionStub);
        $this->expectException(PostNotFoundException::class);
        $this->expectExceptionMessage('Cannot find post: 5625e275-8cfd-4573-82af-28b07401db60');
        $repository->get(new UUID('5625e275-8cfd-4573-82af-28b07401db60'));
    }
}