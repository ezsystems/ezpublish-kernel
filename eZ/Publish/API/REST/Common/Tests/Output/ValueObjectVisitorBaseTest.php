<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2012 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\API\REST\Common\Tests\Output;
use eZ\Publish\API\REST\Server\Tests;

use eZ\Publish\API\REST\Common\Output\Generator;

abstract class ValueObjectVisitorBaseTest extends Tests\BaseTest
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $visitorMock;

    /**
     * @var \eZ\Publish\API\REST\Common\Output\Generator\Xml
     */
    protected $generator;

    /**
     * @var \eZ\Publish\API\REST\Common\Input\ParsingDispatcher
     */
    protected function getVisitorMock()
    {
        if ( !isset( $this->visitorMock ) )
        {
            $this->visitorMock = $this->getMock(
                '\\eZ\\Publish\\API\\REST\\Common\\Output\\Visitor',
                array(),
                array(),
                '',
                false
            );
        }
        return $this->visitorMock;
    }

    /**
     * @return \eZ\Publish\API\REST\Common\Output\Generator\Xml
     */
    protected function getGenerator()
    {
        if ( !isset( $this->generator ) )
        {
            $this->generator = new Generator\Xml();
        }
        return $this->generator;
    }
}
