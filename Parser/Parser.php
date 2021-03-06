<?php
namespace MauticPlugin\MauticRssToEmailBundle\Parser;

use MauticPlugin\MauticRssToEmailBundle\Feed\Feed;
use MauticPlugin\MauticRssToEmailBundle\Traits\ParamsTrait;

class Parser
{
    use ParamsTrait;

    protected $content = null;

    public function __construct($content)
    {
        preg_match_all('/{feed([^}]*)}(.*){\/feed}/msU', $content, $matches);

        if (!empty($matches[0])) {
            foreach ($matches[0] as $key => $feedWrapper) {
                $this->parseParams($matches[1][$key]);

                $feedContent = $matches[2][$key];
                $feedUrl     = $this->getParam('url');

                if (!$this->validateFeedUrl($feedUrl)) {
                    $content = str_replace($feedWrapper, "Error: URL ({$feedUrl}) empty or not valid", $content);
                    continue;
                }

                $feed = new Feed($this->getParam('url'));

                if (is_null($feed->getFeed()->error())) {
                    $feedParser        = new FeedParser($feedContent, $feed);
                    $feedParserContent = $feedParser->getContent();
                } else {
                    $feedParserContent = "Error: {$feed->getFeed()->error()}";
                }

                // $event->addToken($feedWrapper, $parser->parse()); // Use later to do feed parsing on contact level
                $content = str_replace($feedWrapper, $feedParserContent, $content);
            }
        }

        $this->setContent($content);
    }

    public function validateFeedUrl($url)
    {
        $url = trim($url);

        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        return true;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getContent()
    {
        return $this->content;
    }
}
