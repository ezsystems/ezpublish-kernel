<?php

namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Delegator;

use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseConfigurator\ResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger;
use Symfony\Component\HttpFoundation\Response;

/**
 * Dispatches a value to all registered ResponseTaggers.
 */
class DispatcherTagger implements ResponseTagger
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger
     */
    private $taggers = [];

    public function __construct(array $taggers = [])
    {
        $this->taggers = $taggers;
    }

    public function tag(ResponseCacheConfigurator $configurator, Response $response, $value)
    {
        foreach ($this->taggers as $tagger) {
            $tagger->tag($configurator, $response, $value);
        }
    }
}
