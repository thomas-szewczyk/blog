<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\RSS\RSSFeed;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RssController extends AbstractController
{
    /**
     * @Route("/rss", name="rss")
     * @param PostRepository $postRepository
     * @return Response
     */
    public function index(PostRepository $postRepository): Response
    {
        RSSFeed::generateRssFeed($postRepository->findAll());
        return $this->render('rss/index.html.twig', [
            'controller_name' => 'RssController',
        ]);
    }
}
