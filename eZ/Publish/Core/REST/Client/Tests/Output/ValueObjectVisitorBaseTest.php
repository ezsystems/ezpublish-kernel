<?php
/**
 * File containing ValueObjectVisitorBaseTest class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Client\Tests\Output;

use eZ\Publish\Core\REST\Server\Tests;

use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\RequestParser\eZPublish as EzPublishRequestParser;
use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest as CommonValueObjectVisitorBaseTest;

abstract class ValueObjectVisitorBaseTest extends CommonValueObjectVisitorBaseTest
{
    /**
     * @var \eZ\Publish\Core\REST\Common\RequestParser\eZPublish
     */
    protected $requestParser;

    /**
     * @return \eZ\Publish\Core\REST\Common\RequestParser\eZPublish
     */
    protected function getRequestParser()
    {
        if ( !isset( $this->requestParser ) )
        {
            $this->requestParser = new EzPublishRequestParser();
        }
        return $this->requestParser;
    }
}
