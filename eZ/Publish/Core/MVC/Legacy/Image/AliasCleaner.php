<?php
/**
 * File containing the AliasCleaner class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Legacy\Image;

use eZ\Publish\Core\FieldType\Image\AliasCleanerInterface;
use eZ\Publish\Core\IO\UrlRedecoratorInterface;

class AliasCleaner implements AliasCleanerInterface
{
    /**
     * @var AliasCleanerInterface
     */
    private $innerAliasCleaner;

    /**
     * @var UrlRedecoratorInterface
     */
    private $urlRedecorator;

    public function __construct(
        AliasCleanerInterface $innerAliasCleaner,
        UrlRedecoratorInterface $urlRedecorator
    )
    {
        $this->innerAliasCleaner = $innerAliasCleaner;
        $this->urlRedecorator = $urlRedecorator;
    }

    public function removeAliases( $originalPath )
    {
        $this->innerAliasCleaner->removeAliases(
            $this->urlRedecorator->redecorateFromTarget( $originalPath )
        );
    }
}
