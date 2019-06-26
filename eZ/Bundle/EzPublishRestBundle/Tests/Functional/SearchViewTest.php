<?php

/**
 * File containing the Functional\SearchViewTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use DOMDocument;
use DOMElement;
use eZ\Bundle\EzPublishRestBundle\Tests\Functional\TestCase as RESTFunctionalTestCase;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion\Operator;

class SearchViewTest extends RESTFunctionalTestCase
{
    /** @var string */
    protected $contentTypeHref;

    /** @var string[] */
    protected $contentHrefList;

    /** @var string */
    private $nonSearchableContentHref;

    protected function setUp()
    {
        parent::setUp();
        $this->contentTypeHref = $this->createTestContentType();
        $this->nonSearchableContentHref = $this->createContentWithUrlField();
        $this->contentHrefList[] = $this->createTestContentWithTags('test-name', ['foo', 'bar']);
        $this->contentHrefList[] = $this->createTestContentWithTags('fancy-name', ['baz', 'foobaz']);
        $this->contentHrefList[] = $this->createTestContentWithTags('even-fancier', ['bar', 'bazfoo']);
    }

    protected function tearDown()
    {
        parent::tearDown();
        array_map([$this, 'deleteContent'], $this->contentHrefList);
        $this->deleteContent($this->contentTypeHref);
        $this->deleteContent($this->nonSearchableContentHref);
    }

    /**
     * @dataProvider xmlProvider
     * Covers POST with ContentQuery Logic on /api/ezp/v2/views.
     *
     * @param string $xmlQueryBody
     * @param int $expectedCount
     */
    public function testSimpleContentQuery(string $xmlQueryBody, int $expectedCount)
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ViewInput>
<identifier>your-query-id</identifier>
<public>false</public>
<ContentQuery>
  <Query>
    $xmlQueryBody
  </Query>  
  <limit>10</limit>  
  <offset>0</offset> 
</ContentQuery>
</ViewInput>
XML;
        $request = $this->createHttpRequest(
            'POST',
            '/api/ezp/v2/views',
            'ViewInput+xml; version=1.1',
            'ContentInfo+json',
            $body
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
        $jsonResponse = json_decode($response->getBody());
        self::assertEquals($expectedCount, $jsonResponse->View->Result->count);
    }

    private function createTestContentType(): string
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentTypeCreate>
  <identifier>tags-test</identifier>
  <names>
    <value languageCode="eng-GB">testContentQueryWithTags</value>
  </names>
  <remoteId>testContentQueryWithTags</remoteId>
  <urlAliasSchema>&lt;title&gt;</urlAliasSchema>
  <nameSchema>&lt;title&gt;</nameSchema>
  <isContainer>true</isContainer>
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <defaultAlwaysAvailable>true</defaultAlwaysAvailable>
  <defaultSortField>PATH</defaultSortField>
  <defaultSortOrder>ASC</defaultSortOrder>
  <FieldDefinitions>
    <FieldDefinition>
      <identifier>title</identifier>
      <fieldType>ezstring</fieldType>
      <fieldGroup>content</fieldGroup>
      <position>1</position>
      <isTranslatable>true</isTranslatable>
      <isRequired>true</isRequired>
      <isInfoCollector>false</isInfoCollector>
      <defaultValue>New Title</defaultValue>
      <isSearchable>true</isSearchable>
      <names>
        <value languageCode="eng-GB">Title</value>
      </names>
      <descriptions>
        <value languageCode="eng-GB">This is the title</value>
      </descriptions>
    </FieldDefinition>
    <FieldDefinition>
      <identifier>tags</identifier>
      <fieldType>ezkeyword</fieldType>
      <fieldGroup>content</fieldGroup>
      <position>2</position>
      <isTranslatable>true</isTranslatable>
      <isRequired>true</isRequired>
      <isInfoCollector>false</isInfoCollector>
      <isSearchable>true</isSearchable>
      <names>
        <value languageCode="eng-GB">Tags</value>
      </names>
      <descriptions>
        <value languageCode="eng-GB">Those are searchable tags</value>
      </descriptions>
    </FieldDefinition>
   </FieldDefinitions>
</ContentTypeCreate>
XML;

        $request = $this->createHttpRequest(
            'POST',
            '/api/ezp/v2/content/typegroups/1/types?publish=true',
            'ContentTypeCreate+xml',
            'ContentType+json',
            $body
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseHasHeader($response, 'Location');

        return $response->getHeader('Location')[0];
    }

    private function createTestContentWithTags(string $name, array $tags): string
    {
        $tagsString = implode(',', $tags);
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentCreate>
  <ContentType href="$this->contentTypeHref" />
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <LocationCreate>
    <ParentLocation href="/api/ezp/v2/content/locations/1" />
    <priority>0</priority>
    <hidden>false</hidden>
    <sortField>PATH</sortField>
    <sortOrder>ASC</sortOrder>
  </LocationCreate>
  <Section href="/api/ezp/v2/content/sections/1" />
  <alwaysAvailable>true</alwaysAvailable>
  <remoteId>$name</remoteId>
  <User href="/api/ezp/v2/user/users/14" />
  <modificationDate>2018-01-30T18:30:00</modificationDate>
  <fields>
    <field>
      <fieldDefinitionIdentifier>title</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>$name</fieldValue>
    </field>
    <field>
      <fieldDefinitionIdentifier>tags</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>$tagsString</fieldValue>
    </field>
    </fields>
</ContentCreate>
XML;
        $request = $this->createHttpRequest(
            'POST',
            '/api/ezp/v2/content/objects',
            'ContentCreate+xml',
            'ContentInfo+json',
            $body
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseHasHeader($response, 'Location');
        $href = $response->getHeader('Location')[0];
        $this->sendHttpRequest(
            $this->createHttpRequest('PUBLISH', "$href/versions/1")
        );

        return $href;
    }

    private function deleteContent($href)
    {
        $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $href)
        );
    }

    public function xmlProvider()
    {
        $fooTag = $this->buildFieldXml('tags', Operator::CONTAINS, 'foo');
        $barTag = $this->buildFieldXml('tags', Operator::CONTAINS, 'bar');
        $bazTag = $this->buildFieldXml('tags', Operator::CONTAINS, 'baz');
        $foobazTag = $this->buildFieldXml('tags', Operator::CONTAINS, 'foobaz');
        $foobazInTag = $this->buildFieldXml('tags', Operator::IN, ['foobaz']);
        $bazfooInTag = $this->buildFieldXml('tags', Operator::IN, ['bazfoo']);
        $fooAndBarInTag = $this->buildFieldXml('tags', Operator::IN, ['foo', 'bar']);

        return [
            [
                $this->getXmlString(
                    $this->wrapIn('AND', [$fooTag, $barTag])
                ),
                1,
            ],
            [
                $this->getXmlString(
                    $this->wrapIn('OR', [
                        $this->wrapIn('AND', [$fooTag, $barTag]),
                        $this->wrapIn('AND', [$bazTag, $foobazTag]),
                    ])
                ),
                2,
            ],
            [
                $this->getXmlString(
                    $this->wrapIn('AND', [
                        $this->wrapIn('NOT', [$fooTag]),
                        $barTag,
                    ])
                ),
                1,
            ],
            [
                $this->getXmlString(
                    $this->wrapIn('OR', [
                        $foobazInTag,
                        $bazfooInTag,
                    ])
                ),
                2,
            ],
            [
                $this->getXmlString($fooAndBarInTag),
                2,
            ],
        ];
    }

    /**
     * @param string $name
     * @param string $operator
     * @param string|string[] $value
     * @return DOMElement
     */
    private function buildFieldXml(string $name, string $operator, $value): DOMElement
    {
        $xml = new DOMDocument();
        $element = $xml->createElement('Field');
        $element->appendChild(new DOMElement('name', $name));
        $element->appendChild(new DOMElement('operator', $operator));

        //Force xml array with one value
        if (is_array($value)) {
            if (count($value) === 1) {
                $valueWrapper = $xml->createElement('value');
                $valueWrapper->appendChild(new DOMElement('value', $value[0]));
                $element->appendChild($valueWrapper);
            } else {
                foreach ($value as $key => $singleValue) {
                    $element->appendChild(new DOMElement('value', $singleValue));
                }
            }
        } else {
            $element->appendChild(new DOMElement('value', $value));
        }

        return $element;
    }

    private function wrapIn(string $logicalOperator, array $toWrap): DOMElement
    {
        $xml = new DOMDocument();
        $wrapper = $xml->createElement($logicalOperator);

        foreach ($toWrap as $key => $field) {
            $innerWrapper = $xml->createElement($logicalOperator);
            $innerWrapper->appendChild($xml->importNode($field, true));
            $wrapper->appendChild($innerWrapper);
        }

        return $wrapper;
    }

    private function getXmlString(DOMElement $simpleXMLElement): string
    {
        return $simpleXMLElement->ownerDocument->saveXML($simpleXMLElement);
    }

    /**
     * This is just to assure that field with same name but without legacy search engine implementation
     * does not block search in different content type.
     */
    private function createContentWithUrlField(): string
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentTypeCreate>
  <identifier>rich-text-test</identifier>
  <names>
    <value languageCode="eng-GB">urlContentType</value>
  </names>
  <remoteId>testUrlContentType</remoteId>
  <urlAliasSchema>&lt;title&gt;</urlAliasSchema>
  <nameSchema>&lt;title&gt;</nameSchema>
  <isContainer>true</isContainer>
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <defaultAlwaysAvailable>true</defaultAlwaysAvailable>
  <defaultSortField>PATH</defaultSortField>
  <defaultSortOrder>ASC</defaultSortOrder>
  <FieldDefinitions>
    <FieldDefinition>
      <identifier>title</identifier>
      <fieldType>ezurl</fieldType>
      <fieldGroup>content</fieldGroup>
      <position>1</position>
      <isTranslatable>true</isTranslatable>
      <isRequired>true</isRequired>
      <isInfoCollector>false</isInfoCollector>
      <names>
        <value languageCode="eng-GB">Title</value>
      </names>
      <descriptions>
        <value languageCode="eng-GB">This is the title but in url type</value>
      </descriptions>
    </FieldDefinition>
   </FieldDefinitions>
</ContentTypeCreate>
XML;

        $request = $this->createHttpRequest(
            'POST',
            '/api/ezp/v2/content/typegroups/1/types?publish=true',
            'ContentTypeCreate+xml',
            'ContentType+json',
            $body
        );

        $response = $this->sendHttpRequest($request);
        self::assertHttpResponseHasHeader($response, 'Location');

        return $response->getHeader('Location')[0];
    }
}
