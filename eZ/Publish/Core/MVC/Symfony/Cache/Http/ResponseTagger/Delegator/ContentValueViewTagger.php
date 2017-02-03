<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\Delegator;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseConfigurator\ResponseCacheConfigurator;
use eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger;
use eZ\Publish\Core\MVC\Symfony\View\ContentValueView;
use Symfony\Component\HttpFoundation\Response;

class ContentValueViewTagger implements ResponseTagger
{
    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Cache\Http\ResponseTagger\ResponseTagger
     */
    private $contentInfoTagger;

    public function __construct(ResponseTagger $contentInfoTagger)
    {
        $this->contentInfoTagger = $contentInfoTagger;
    }

    public function tag(ResponseCacheConfigurator $configurator, Response $response, $view)
    {
        if (!$view instanceof ContentValueView || !($content = $view->getContent()) instanceof Content) {
            return $this;
        }

        $contentInfo = $content->getVersionInfo()->getContentInfo();
        $this->contentInfoTagger->tag($configurator, $response, $contentInfo);

        return $this;
    }
}
