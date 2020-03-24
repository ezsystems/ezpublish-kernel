<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Tests\RichText;

use eZ\Publish\Core\FieldType\RichText\SearchField;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Search;
use PHPUnit\Framework\TestCase;

final class SearchFieldTest extends TestCase
{
    /** @var \eZ\Publish\Core\FieldType\RichText\SearchField */
    private $searchField;

    public function getDataForTestGetIndexData(): array
    {
        return [
            'simple stub' => [
                $this->getSimpleDocBookXml(),
                [
                    new Search\Field(
                        'value',
                        'Welcome to eZ Platform',
                        new Search\FieldType\StringField()
                    ),
                    new Search\Field(
                        'fulltext',
                        "\n   Welcome to eZ Platform \n   eZ Platform  is the new generation DXP from eZ Systems. \n ",
                        new Search\FieldType\FullTextField()
                    ),
                ],
            ],
            'empty xml' => [
                $this->getEmptyXml(),
                [
                    new Search\Field(
                        'value',
                        '',
                        new Search\FieldType\StringField()
                    ),
                    new Search\Field(
                        'fulltext',
                        '',
                        new Search\FieldType\FullTextField()
                    ),
                ],
            ],
        ];
    }

    protected function setUp(): void
    {
        $this->searchField = new SearchField();
    }

    /**
     * @covers \eZ\Publish\Core\FieldType\RichText\SearchField::getIndexData
     *
     * @dataProvider getDataForTestGetIndexData
     *
     * @param array $expectedSearchFields
     */
    public function testGetIndexData(string $docBookXml, array $expectedSearchFields): void
    {
        $field = new Field(
            [
                'id' => 1,
                'type' => 'ezrichtext',
                'value' => new FieldValue(['data' => $docBookXml]),
            ]
        );
        $fieldDefinition = new FieldDefinition();

        self::assertEquals(
            $expectedSearchFields,
            $this->searchField->getIndexData($field, $fieldDefinition)
        );
    }

    private function getSimpleDocBookXml(): string
    {
        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook"
         xmlns:xlink="http://www.w3.org/1999/xlink"
         xmlns:ezxhtml="https://ezplatform.com/xmlns/docbook/xhtml">
  <title ezxhtml:level="2">Welcome to eZ Platform</title>
  <para><link xlink:href="ezurl://1" xlink:show="none">eZ Platform</link> is the new generation DXP from eZ Systems.</para>
</section>
XML;
    }

    private function getEmptyXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8"?><section></section>';
    }
}
