<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\RSS\RSSFeed;
use App\Service\RssFeedUpdater;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class RssController
 * @package App\Controller
 * @Route("/", name="rss_")
 */
class RssController extends AbstractController
{
    /**
     * @Route("/rss", name="generate")
     * @param PostRepository $postRepository
     * @param Request $request
     * @param RssFeedUpdater $feedUpdater
     * @return Response
     */
    public function generateRss(PostRepository $postRepository, Request $request, RssFeedUpdater $feedUpdater): Response
    {
        $feedUpdater->updateRSSFeed($request, $postRepository);

        return $this->render('rss/index.html.twig', [
            'controller_name' => 'RssController',
        ]);
    }

}
