<?php


namespace App\Service;

use App\Repository\PostRepository;
use App\RSS\RSSFeed;
use Symfony\Component\HttpFoundation\Request;

class RssFeedUpdater
{
    public function updateRSSFeed(Request $request, PostRepository $postRepository)
    {
        $uri = $request->getBaseUrl();
        RSSFeed::generateRssFeed($postRepository->findAllPostsAsEntities(), $uri);
    }
}