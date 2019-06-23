<?php

/**
 * File containing a test class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Tests\Input\Parser;

use eZ\Publish\Core\Repository\SectionService;
use eZ\Publish\Core\REST\Server\Input\Parser\SectionInput;
use eZ\Publish\API\Repository\Values\Content\SectionCreateStruct;

class SectionInputTest extends BaseTest
{
    /**
     * Tests the SectionInput parser.
     */
    public function testParse()
    {
        $inputArray = [
            'name' => 'Name Foo',
            'identifier' => 'Identifier Bar',
        ];

        $sectionInput = $this->getParser();
        $result = $sectionInput->parse($inputArray, $this->getParsingDispatcherMock());

        $this->assertEquals(
            new SectionCreateStruct($inputArray),
            $result,
            'SectionCreateStruct not created correctly.'
        );
    }

    /**
     * Test SectionInput parser throwing exception on missing identifier.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'identifier' attribute for SectionInput.
     */
    public function testParseExceptionOnMissingIdentifier()
    {
        $inputArray = [
            'name' => 'Name Foo',
        ];

        $sectionInput = $this->getParser();
        $sectionInput->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Test SectionInput parser throwing exception on missing name.
     *
     * @expectedException \eZ\Publish\Core\REST\Common\Exceptions\Parser
     * @expectedExceptionMessage Missing 'name' attribute for SectionInput.
     */
    public function testParseExceptionOnMissingName()
    {
        $inputArray = [
            'identifier' => 'Identifier Bar',
        ];

        $sectionInput = $this->getParser();
        $sectionInput->parse($inputArray, $this->getParsingDispatcherMock());
    }

    /**
     * Returns the section input parser.
     *
     * @return \eZ\Publish\Core\REST\Server\Input\Parser\SectionInput
     */
    protected function internalGetParser()
    {
        return new SectionInput(
            $this->getSectionServiceMock()
        );
    }

    /**
     * Get the section service mock object.
     *
     * @return \eZ\Publish\API\Repository\SectionService
     */
    protected function getSectionServiceMock()
    {
        $sectionServiceMock = $this->createMock(SectionService::class);

        $sectionServiceMock->expects($this->any())
            ->method('newSectionCreateStruct')
            ->will(
                $this->returnValue(new SectionCreateStruct())
            );

        return $sectionServiceMock;
    }
}
