<?php

namespace App\DataFixtures;

use App\Entity\BlogPost;
use App\Entity\Comment;
use App\Entity\User;
use App\Security\TokenGenerator;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var \Faker\Factory
     */
    private $faker;

    private const USERS = [
        [
            "username" => "fozeu.jm@gmail.com",
            "email" => "fozeu.jm@gmail.com",
            "name" => "Fozeu Jean Marie",
            "password" => "Je@nm@rie1234",
            "roles" => [User::ROLE_SUPERADMIN],
            "enabled" => true
        ],
        [
            "username" => "leo.brice@gmail.com",
            "email" => "leo.brice@gmail.com",
            "name" => "Lionel Wanchie",
            "password" => "W@nchie1234",
            "roles" => [User::ROLE_ADMIN],
            "enabled" => true
        ],
        [
            "username" => "nguitanga@gmail.com",
            "email" => "nguitanga@gmail.com",
            "name" => "Nguitanaga Jaenette",
            "password" => "Nguit@ng@1234",
            "roles" => [User::ROLE_WRITER],
            "enabled" => true
        ],
        [
            "username" => "rawlings@gmail.com",
            "email" => "rawlings@gmail.com",
            "name" => "Jenny Rawlings",
            "password" => "R@wlings1234",
            "roles" => [User::ROLE_WRITER],
            "enabled" => true
        ],
        [
            "username" => "solo@gmail.com",
            "email" => "solo@gmail.com",
            "name" => "Ian Solo",
            "password" => "Solo1234",
            "roles" => [User::ROLE_EDITOR],
            "enabled" => false
        ],
        [
            "username" => "archimedes@gmail.com",
            "email" => "archimedes@gmail.com",
            "name" => "Archimedes Christopher",
            "password" => "Christ1234",
            "roles" => [User::ROLE_COMMENTATOR],
            "enabled" => true
        ]
    ];
    /**
     * @var TokenGenerator
     */
    private $tokenGenerator;


    /**
     * AppFixtures constructor.
     */
    public function __construct(
        TokenGenerator $tokenGenerator,
        UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->faker = \Faker\Factory::create();
        $this->tokenGenerator = $tokenGenerator;
    }

    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadBlogPost($manager);
        $this->loadComments($manager);
    }

    public function loadUsers(ObjectManager $manager)
    {
        foreach(self::USERS as $item){
            $user = new User();
            $user->setEmail($item["email"]);
            $user->setName($item["name"]);
            $user->setUsername($item["username"]);
            $user->setPassword($this->passwordEncoder->encodePassword(
                $user,
                $item["password"]
            ));
            $this->addReference( "user_".$item["username"], $user);
            $user->setRoles($item["roles"]);
            $user->setEnabled($item["enabled"]);
            if(!$item["enabled"]){
                $user->setConfirmationToken(
                    $this->tokenGenerator->getRandomSecureToken()
                );
            }

            $manager->persist($user);
        }
        $manager->flush();
    }

    public function loadBlogPost(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            $post = new BlogPost();
            $post->setTitle($this->faker->realText(30));
            $post->setAuthor($this->getReference($this->getRandomUserReference($post)));
            $post->setContent($this->faker->realText());
            $post->setPublished($this->faker->dateTimeThisYear);
            $post->setSlug($this->faker->slug(3, false));
            $this->setReference("blog_post_$i", $post);

            $manager->persist($post);
        }
        $manager->flush();
    }

    public function loadComments(ObjectManager $manager)
    {
        for ($i = 0; $i < 100; $i++) {
            for ($j = 0; $j < rand(1, 10); $j++) {
                $comment = new Comment();
                $comment->setContent($this->faker->realText());
                $comment->setPublished($this->faker->dateTimeThisYear);
                $authorReference = $this->getRandomUserReference($comment);
                $comment->setAuthor($this->getReference($authorReference));
                $comment->setBlogPost($this->getReference("blog_post_$i"));
                $manager->persist($comment);
            }
        }
        $manager->flush();
    }

    /**
     * @return string
     */
    public function getRandomUserReference($entity): string
    {
        $randomUser= self::USERS[rand(0, 5)];
        if($entity instanceof BlogPost &&
            !(count(array_intersect($randomUser["roles"], [User::ROLE_SUPERADMIN,User::ROLE_ADMIN,User::ROLE_WRITER])))){
            return $this->getRandomUserReference($entity);
        }

        if($entity instanceof Comment && !(count(array_intersect($randomUser["roles"], [User::ROLE_SUPERADMIN,User::ROLE_ADMIN,User::ROLE_WRITER,User::ROLE_COMMENTATOR])))){
            return $this->getRandomUserReference($entity);
        }

        return "user_".$randomUser["username"];
    }
}
