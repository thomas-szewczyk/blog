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
     *
     * @return Response
     */
    public function index(PostRepository $postRepository): Response
    {
        $latestPost = $postRepository->findLatestPosts(1);
        $latestPosts = $postRepository->findLatestPosts();

        $rss = $this->loadRssFile();

        return $this->render('main/index.html.twig', [
            'latest_post' => $latestPost,
            'latest_posts' => $latestPosts,
            'rss' => $rss
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
            'news' => $postRepository->findAllPosts()
        ]);
    }

    public function loadRssFile(string $path = '../public/rss.xml')
    {
        return simplexml_load_file($path);
    }
}
