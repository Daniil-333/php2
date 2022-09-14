<?php

namespace Geekbrains\App\Blog\Command\FakeData;

use Geekbrains\App\Blog\Comment;
use Geekbrains\App\Blog\Post;
use Geekbrains\App\Blog\Repositories\CommentsRepository\CommentsRepositoryInterface;
use Geekbrains\App\Blog\Repositories\PostsRepository\PostsRepositoryInterface;
use Geekbrains\App\Blog\Repositories\UsersRepository\UsersRepositoryInterface;
use Geekbrains\App\Blog\User;
use Geekbrains\App\Blog\UUID;
use Geekbrains\App\Person\Name;
use Symfony\Component\Console\Command\Command;
use Faker\Generator;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputInterface;

class PopulateDB extends Command
{
    public function __construct(
        private Generator $faker,
        private UsersRepositoryInterface $usersRepository,
        private PostsRepositoryInterface $postsRepository,
        private CommentsRepositoryInterface $commentsRepository
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('fake-data:populate-db')
            ->addOption('users-number', 'un', InputOption::VALUE_OPTIONAL, 'Users number')
            ->addOption('posts-number', 'pn', InputOption::VALUE_OPTIONAL, 'Posts number')
            ->setDescription('Populates DB with fake data');
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {

        $this->clearTables();

        $usersNumber = ($input->getOption('users-number')) ?: 10;
        $postsNumber = ($input->getOption('posts-number')) ?: 20;

        // Создаём пользователей
        $users = [];
        for ($i = 0; $i < $usersNumber; $i++) {
            $user = $this->createFakeUser();
            $users[] = $user;
            $output->writeln('User created: ' . $user->username());
        }

        // От имени каждого пользователя создаём статьи
        $posts = [];
        foreach ($users as $user) {
            for ($i = 0; $i < $postsNumber; $i++) {
                $post = $this->createFakePost($user);
                $posts[] = $post;
                $output->writeln('Post created: ' . $post->getTitle());
            }
        }

        // Для каждой статьи создаём комментарий
        foreach ($posts as $key => $post) {
            $random = random_int(0, $usersNumber - 1);

            for ($i = 0; $i < 1; $i++) {
                $comment = $this->createFakeComment($post, $users[$random]);
                $output->writeln('Comment created: ' . $comment->getText());
            }
        }

        return Command::SUCCESS;
    }

    private function clearTables(): void
    {
        $this->usersRepository->clearData();
        $this->postsRepository->clearData();
        $this->commentsRepository->clearData();
    }

    private function createFakeUser(): User
    {
        $user = User::createFrom(
        // Генерируем имя пользователя
            new Name(
            // Генерируем имя
                $this->faker->firstName,
                // Генерируем фамилию
                $this->faker->lastName
            ),
            $this->faker->userName,
            // Генерируем пароль
            $this->faker->password
        );

        // Сохраняем пользователя в репозиторий
        $this->usersRepository->save($user);
        return $user;
    }

    private function createFakePost(User $author): Post
    {
        $post = new Post(
            UUID::random(),
            $author,
            // Генерируем предложение не длиннее шести слов
            $this->faker->sentence(6, true),
            // Генерируем текст
            $this->faker->realText
        );

        // Сохраняем статью в репозиторий
        $this->postsRepository->save($post);
        return $post;
    }

    private function createFakeComment(Post $post, User $author): Comment
    {
        $comment = new Comment(
            UUID::random(),
            $author,
            $post,
            $this->faker->realText
        );

        $this->commentsRepository->save($comment);
        return $comment;
    }
}