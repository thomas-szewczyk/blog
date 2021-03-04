<?php

namespace App\DataFixtures;

use App\Entity\Post;
use App\Entity\PostLike;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {

        // Demo Posts
        $firstPost = new Post();
        $firstPost->setTitle('The first great post!');
        $firstPost->setDescription('This is the first post that was made for test purposes');
        $firstPost->setContent('The content does not matter so far')
            ->setImageFile('orionthemes-placeholder-image.png');

        $manager->persist($firstPost);

        $secondPost = new Post();
        $secondPost->setTitle('The second great post!');
        $secondPost->setDescription('This is the second post that was made for test purposes');
        $secondPost->setContent('The content does not matter so far')
            ->setImageFile('orionthemes-placeholder-image.png');

        $manager->persist($secondPost);

        $thirdPost = new Post();
        $thirdPost->setTitle('The third great post!');
        $thirdPost->setDescription('This is the third post that was made for test purposes');
        $thirdPost->setContent('The content does not matter so far')
            ->setImageFile('orionthemes-placeholder-image.png');

        $manager->persist($thirdPost);

        // Demo Users
        $rootUser = new User();
        $rootUser->setUsername('admin');
        $rootUser->setPassword(
            $this->passwordEncoder->encodePassword(
                $rootUser,
                'admin'
            )
        );
        $rootUser->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

        $manager->persist($rootUser);

        $regularUser = new User();
        $regularUser->setUsername('user');
        $regularUser->setPassword(
            $this->passwordEncoder->encodePassword(
                $regularUser,
                'user'
            )
        );

        $manager->persist($regularUser);

        $author = new User();
        $author->setUsername('author');
        $author->setPassword(
            $this->passwordEncoder->encodePassword(
                $author,
                'author'
            )
        );
        $author->setRoles(['ROLE_AUTHOR', 'ROLE_USER']);

        $manager->persist($author);

        $manager->flush();
    }
}
