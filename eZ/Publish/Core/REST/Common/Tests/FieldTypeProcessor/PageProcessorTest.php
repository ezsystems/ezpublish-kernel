<?php
/**
 * File containing the PageProcessorTest class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Common\Tests\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor\PageProcessor;
use eZ\Publish\Core\FieldType\Page\Parts\Base;
use PHPUnit_Framework_TestCase;

class PageProcessorTest extends PHPUnit_Framework_TestCase
{
    protected $incomingValue;
    protected $outgoingValue;

    protected function setUp()
    {
        parent::setUp();

        $this->incomingValue = array(
            "zones" => array(
                array(
                    "action" => "ACTION_ADD",
                    "blocks" => array(
                        array(
                            "action" => "ACTION_MODIFY",
                            "items" => array(
                                array(
                                    "action" => "ACTION_REMOVE"
                                )
                            )
                        )
                    )
                )
            ),
        );

        $this->outgoingValue = array(
            "zones" => array(
                array(
                    "action" => Base::ACTION_ADD,
                    "blocks" => array(
                        array(
                            "action" => Base::ACTION_MODIFY,
                            "items" => array(
                                array(
                                    "action" => Base::ACTION_REMOVE
                                )
                            )
                        )
                    )
                )
            )
        );
    }

    /**
     * @covers \eZ\Publish\Core\REST\Common\FieldTypeProcessor\PageProcessor::preProcessValueHash
     */
    public function testPreProcessValueHash()
    {
        $processor = $this->getProcessor();

        $this->assertEquals(
            $this->outgoingValue,
            $processor->preProcessValueHash( $this->incomingValue )
        );
    }

    /**
     * @covers \eZ\Publish\Core\REST\Common\FieldTypeProcessor\PageProcessor::postProcessValueHash
     */
    public function testPostProcessValueHash()
    {
        $processor = $this->getProcessor();

        $this->assertEquals(
            $this->incomingValue,
            $processor->postProcessValueHash( $this->outgoingValue )
        );
    }

    /**
     * @return \eZ\Publish\Core\REST\Common\FieldTypeProcessor\PageProcessor
     */
    protected function getProcessor()
    {
        return new PageProcessor;
    }
}
