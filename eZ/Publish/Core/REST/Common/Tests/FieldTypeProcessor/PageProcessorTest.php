<?php

/**
 * File containing the PageProcessorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 *
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

    public function fieldValueHashes()
    {
        return array(
            array(null, null),
            array(array(), array()),
            array(
                array(
                    'zones' => array(
                        array(
                            'action' => 'ACTION_ADD',
                        ),
                    ),
                ),
                array(
                    'zones' => array(
                        array(
                            'action' => Base::ACTION_ADD,
                        ),
                    ),
                ),
            ),
            array(
                array(
                    'zones' => array(
                        array(
                            'action' => 'ACTION_ADD',
                            'blocks' => array(
                                array(
                                    'action' => 'ACTION_MODIFY',
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'zones' => array(
                        array(
                            'action' => Base::ACTION_ADD,
                            'blocks' => array(
                                array(
                                    'action' => Base::ACTION_MODIFY,
                                ),
                            ),
                        ),
                    ),
                ),
            ),
            array(
                array(
                    'zones' => array(
                        array(
                            'action' => 'ACTION_ADD',
                            'blocks' => array(
                                array(
                                    'action' => 'ACTION_MODIFY',
                                    'items' => array(
                                        array(
                                            'action' => 'ACTION_REMOVE',
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'zones' => array(
                        array(
                            'action' => Base::ACTION_ADD,
                            'blocks' => array(
                                array(
                                    'action' => Base::ACTION_MODIFY,
                                    'items' => array(
                                        array(
                                            'action' => Base::ACTION_REMOVE,
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
    }

    /**
     * @covers \eZ\Publish\Core\REST\Common\FieldTypeProcessor\PageProcessor::preProcessValueHash
     * @dataProvider fieldValueHashes
     */
    public function testPreProcessValueHash($inputValue, $outputValue)
    {
        $processor = $this->getProcessor();

        $this->assertEquals(
            $outputValue,
            $processor->preProcessValueHash($inputValue)
        );
    }

    /**
     * @covers \eZ\Publish\Core\REST\Common\FieldTypeProcessor\PageProcessor::postProcessValueHash
     * @dataProvider fieldValueHashes
     */
    public function testPostProcessValueHash($outputValue, $inputValue)
    {
        $processor = $this->getProcessor();

        $this->assertEquals(
            $outputValue,
            $processor->postProcessValueHash($inputValue)
        );
    }

    /**
     * @return \eZ\Publish\Core\REST\Common\FieldTypeProcessor\PageProcessor
     */
    protected function getProcessor()
    {
        return new PageProcessor();
    }
}
