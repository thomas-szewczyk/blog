<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="home")
     * @param PostRepository $postRepository
     * @param CategoryRepository $categoryRepository
     * @return Response
     */
    public function index(PostRepository $postRepository, CategoryRepository $categoryRepository): Response
    {
        $latestPost = $postRepository->findLatestPosts(1);
        $latestPosts = $postRepository->findLatestPosts();

        $rss = $this->loadRssFile();

        return $this->render('main/index.html.twig', [
            'latest_post' => $latestPost,
            'latest_posts' => $latestPosts,
            'rss' => $rss,
            'categories' => $categoryRepository->findAll()
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

    /**
     * @Route("news/category/{id}", name="show_category")
     * @param int $id
     * @param PostRepository $postRepository
     * @param CategoryRepository $categoryRepository
     * @return Response
     */
    public function showCategory(int $id, PostRepository $postRepository, CategoryRepository $categoryRepository): Response
    {
        $category = $categoryRepository->find($id);
        if (!$category) {
            throw $this->createNotFoundException('The category does not exist');
        } else {
            $postsByCategory = $postRepository-> findAllPostsByCategory($id);
        }
        return $this->render('main/category.html.twig', [
            'postsByCategory' => $postsByCategory,
            'category' => $category->getName()
        ]);
    }

    /**
     * @param string $path
     * @return \SimpleXMLElement|string
     */
    public function loadRssFile(string $path = '../public/rss.xml')
    {
        return simplexml_load_file($path);
    }
}
