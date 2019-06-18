<?php

/**
 * File containing the PageProcessorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Tests\FieldTypeProcessor;

use eZ\Publish\Core\REST\Common\FieldTypeProcessor\PageProcessor;
use eZ\Publish\Core\FieldType\Page\Parts\Base;
use PHPUnit\Framework\TestCase;

class PageProcessorTest extends TestCase
{
    protected $incomingValue;
    protected $outgoingValue;

    public function fieldValueHashes()
    {
        return [
            [null, null],
            [[], []],
            [
                [
                    'zones' => [
                        [
                            'action' => 'ACTION_ADD',
                        ],
                    ],
                ],
                [
                    'zones' => [
                        [
                            'action' => Base::ACTION_ADD,
                        ],
                    ],
                ],
            ],
            [
                [
                    'zones' => [
                        [
                            'action' => 'ACTION_ADD',
                            'blocks' => [
                                [
                                    'action' => 'ACTION_MODIFY',
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'zones' => [
                        [
                            'action' => Base::ACTION_ADD,
                            'blocks' => [
                                [
                                    'action' => Base::ACTION_MODIFY,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                [
                    'zones' => [
                        [
                            'action' => 'ACTION_ADD',
                            'blocks' => [
                                [
                                    'action' => 'ACTION_MODIFY',
                                    'items' => [
                                        [
                                            'action' => 'ACTION_REMOVE',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'zones' => [
                        [
                            'action' => Base::ACTION_ADD,
                            'blocks' => [
                                [
                                    'action' => Base::ACTION_MODIFY,
                                    'items' => [
                                        [
                                            'action' => Base::ACTION_REMOVE,
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
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
