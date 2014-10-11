<?php
/**
 * This file is part of the eZ Publish Kernel package
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributd with this source code.
 */
namespace eZ\Publish\Core\IO;

/**
 * Converts urls between two decorators
 */
class UrlRedecorator
{
    /** @var UrlDecorator */
    private $sourceDecorator;

    /** @var UrlDecorator */
    private $targetDecorator;

    public function __construct( UrlDecorator $sourceDecorator, UrlDecorator $targetDecorator )
    {
        $this->sourceDecorator = $sourceDecorator;
        $this->targetDecorator = $targetDecorator;
    }

    public function redecorateFromSource( $uri )
    {
        return $this->targetDecorator->decorate(
            $this->sourceDecorator->undecorate( $uri )
        );
    }

    public function redecorateFromTarget( $uri )
    {
        return $this->sourceDecorator->decorate(
            $this->targetDecorator->undecorate( $uri )
        );
    }
}
