<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param PostRepository $postRepository
     * @return Response
     */
    public function index(PostRepository $postRepository): Response
    {
        $latestPosts = $this->getLatestPosts($postRepository, 3);
        return $this->render('main/index.html.twig', [
            'latest_post' => $latestPosts[0],
            'latest_posts' => $latestPosts
        ]);
    }

    public function getLatestPosts(PostRepository $postRepository, $numOfPosts): array
    {
        $posts = $postRepository->findAll();

        // Array with the 3 most recent posts

        return array_reverse(array_slice($posts, (count($posts) - 3), $numOfPosts));
    }

    /**
     * @Route ("/post/{id}", name="show")
     * @param $id
     * @param PostRepository $postRepository
     */
    public function show($id, PostRepository $postRepository): Response
    {
        return $this->render('post/post.html.twig', [
            'post' => $postRepository->find($id)
        ]);
    }

    /**
     * @Route("/news", name="news")
     * @param PostRepository $postRepository
     * @return Response
     */
    public function news(PostRepository $postRepository): Response
    {
        return $this->render('main/news.html.twig', [
            'news' => array_reverse($postRepository->findAll())
        ]);
    }
}
