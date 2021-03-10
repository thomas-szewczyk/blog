<?php


namespace App\RSS;


use Symfony\Component\HttpKernel\KernelInterface;

class RSSFeed
{

    public static function generateRssFeed($posts,
                                           string $link = 'https://my-blog.com',
                                           string $title = 'My Blog Page',
                                           string $description = "Travel news from all around the world",
                                           string $lang = 'en')
    {
        $xwr = new \XMLWriter();
        $xwr->openUri('rss.xml');

        $xwr->setIndent(true);  // enable indentation
        $xwr->setIndentString('  ');    // set indentation string

        $xwr->startDocument('1.0','UTF-8');   // start xml document, version=1.0, encoding="utf-8"

        $xwr->startElement('rss');  // root element: <rss>
        $xwr->writeAttribute('version', '2.0');

        $xwr->startElement('channel');

        $xwr->writeElement('title', $title);
        $xwr->writeElement('description', $description);
        $xwr->writeElement('language', $lang);
        $xwr->writeElement('link', $link);
        $xwr->writeElement('lastBuildDate', Date("r", time()));

        // generate items form $posts
        foreach ($posts as $post) {
            $xwr->startElement('item');
            $xwr->writeElement('title', $post->getTitle());
            $xwr->writeElement('description', $post->getDescription());
            $xwr->writeElement('link', $link . '/post/' .  $post->getId());
            if ($post->getCategory()) {
                $xwr->writeElement('category', $post->getCategory()->getName());
            }
            $xwr->writeElement('pubDate', $post->getCreatedAt()->format('Y-m-d H:i:s'));
            $xwr->endElement(); // end item-element
        }

        $xwr->endElement(); // end channel-element
        $xwr->endElement(); // end rss-element

        $xwr->endDocument();    // end xml document

        $xwr->flush();
    }
}