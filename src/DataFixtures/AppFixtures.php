<?php

namespace App\DataFixtures;

use App\Entity\Post;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
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

        $manager->flush();
    }
}
