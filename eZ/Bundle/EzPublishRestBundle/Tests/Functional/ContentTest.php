<?php

/**
 * File containing the Functional\ContentTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use Buzz\Message\Response;
use eZ\Bundle\EzPublishRestBundle\Tests\Functional\TestCase as RESTFunctionalTestCase;
use Psr\Http\Message\ResponseInterface;

class ContentTest extends RESTFunctionalTestCase
{
    /**
     * Covers POST /content/objects.
     *
     * @return string REST content ID
     */
    public function testCreateContent()
    {
        $string = $this->addTestSuffix(__FUNCTION__);
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentCreate>
  <ContentType href="/api/ezp/v2/content/types/1" />
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <LocationCreate>
    <ParentLocation href="/api/ezp/v2/content/locations/1/2" />
    <priority>0</priority>
    <hidden>false</hidden>
    <sortField>PATH</sortField>
    <sortOrder>ASC</sortOrder>
  </LocationCreate>
  <Section href="/api/ezp/v2/content/sections/1" />
  <alwaysAvailable>true</alwaysAvailable>
  <remoteId>{$string}</remoteId>
  <User href="/api/ezp/v2/user/users/14" />
  <modificationDate>2012-09-30T12:30:00</modificationDate>
  <fields>
    <field>
      <fieldDefinitionIdentifier>name</fieldDefinitionIdentifier>
      <languageCode>eng-GB</languageCode>
      <fieldValue>{$string}</fieldValue>
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

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location')[0];
        $this->addCreatedElement($href);

        return $href;
    }

    /**
     * @depends testCreateContent
     * Covers PUBLISH /content/objects/<contentId>/versions/<versionNumber>
     *
     * @return string REST content ID
     */
    public function testPublishContent($restContentHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('PUBLISH', "$restContentHref/versions/1")
        );
        self::assertHttpResponseCodeEquals($response, 204);

        return $restContentHref;
    }

    /**
     * @depends testPublishContent
     * Covers GET /content/objects?remoteId=<remoteId>
     */
    public function testRedirectContent($restContentHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', '/api/ezp/v2/content/objects?remoteId=' . $this->addTestSuffix('testCreateContent'))
        );

        self::assertHttpResponseCodeEquals($response, 307);
        self::assertEquals($response->getHeader('Location')[0], $restContentHref);
    }

    /**
     * @depends testPublishContent
     */
    public function testLoadContent($restContentHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $restContentHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
        // @todo test data a bit ?
    }

    /**
     * @depends testPublishContent
     */
    public function testUpdateContentMetadata($restContentHref)
    {
        $string = $this->addTestSuffix(__FUNCTION__);
        $content = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentUpdate>
  <Owner href="/api/ezp/v2/user/users/10"/>
  <remoteId>{$string}</remoteId>
</ContentUpdate>
XML;
        $request = $this->createHttpRequest(
            'PATCH',
            $restContentHref,
            'ContentUpdate+xml',
            'ContentInfo+json',
            $content
        );
        $response = $this->sendHttpRequest($request);
        self::assertHttpResponseCodeEquals($response, 200);

        // @todo test data
    }

    /**
     * @depends testPublishContent
     *
     * @param string $restContentHref
     *
     * @return string ContentVersion REST ID
     */
    public function testCreateDraftFromVersion(string $restContentHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('COPY', "{$restContentHref}/versions/1")
        );

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertEquals($response->getHeader('Location')[0], "{$restContentHref}/versions/2");

        return $response->getHeader('Location')[0];
    }

    /**
     * @depends testPublishContent
     * Covers GET /content/objects/<contentId>/currentversion
     * @covers \eZ\Publish\Core\REST\Server\Controller\Content::redirectCurrentVersion
     *
     * @param string $restContentHref
     *
     * @throws \Psr\Http\Client\ClientException
     */
    public function testRedirectCurrentVersion(string $restContentHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$restContentHref/currentversion")
        );

        self::assertHttpResponseCodeEquals($response, 307);

        self::assertHttpResponseHasHeader($response, 'Location', "$restContentHref/versions/1");
    }

    /**
     * @depends testCreateDraftFromVersion
     * Covers GET /content/objects/<contentId>/versions/<versionNumber>
     *
     * @param string $restContentVersionHref
     */
    public function testLoadContentVersion($restContentVersionHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $restContentVersionHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);
        $this->assertVersionResponseContainsExpectedFields($response);
        // @todo test filtering (language, fields, etc)
    }

    /**
     * Covers COPY /content/objects/<contentId>.
     * @depends testPublishContent
     *
     * @return string the copied content href
     */
    public function testCopyContent($restContentHref)
    {
        $testContent = $this->loadContent($restContentHref);

        $request = $this->createHttpRequest(
            'COPY',
            $restContentHref,
            '',
            '',
            '',
            ['Destination' => $testContent['MainLocation']['_href']]
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertStringStartsWith(
            '/api/ezp/v2/content/objects/',
            $response->getHeader('Location')[0]
        );

        $this->addCreatedElement($response->getHeader('Location')[0]);

        return $response->getHeader('Location')[0];
    }

    /**
     * Covers DELETE /content/objects/<versionNumber>.
     * @depends testCopyContent
     */
    public function testDeleteContent($restContentHref)
    {
        self::markTestSkipped("Fails as the content created by copyContent isn't found");
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $restContentHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * @depends testPublishContent
     * Covers GET /content/objects/<contentId>/versions
     */
    public function testLoadContentVersions($restContentHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$restContentHref/versions", '', 'VersionList')
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testPublishContent
     *
     * @param string $restContentHref /content/objects/<contentId>
     * Covers COPY /content/objects/<contentId>/currentversion
     *
     * @return string the ID of the created version (/content/objects/<contentId>/versions/<versionNumber>
     */
    public function testCreateDraftFromCurrentVersion($restContentHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('COPY', "$restContentHref/currentversion")
        );

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        return $response->getHeader('Location')[0];
    }

    /**
     * @depends testCreateDraftFromCurrentVersion
     *
     * @param string $restContentVersionHref /api/ezp/v2/content/objects/<contentId>/versions>/<versionNumber>
     * Covers DELETE /api/ezp/v2/content/objects/<contentId>/versions>/<versionNumber>
     */
    public function testDeleteContentVersion($restContentVersionHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $restContentVersionHref)
        );

        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * @depends testCreateDraftFromVersion
     * Covers PATCH /content/objects/<contentId>/versions>/<versionNumber>
     *
     * @param string $restContentVersionHref /content/objects/<contentId>/versions>/<versionNumber>
     */
    public function testUpdateVersion($restContentVersionHref)
    {
        $xml = <<< XML
<VersionUpdate>
    <fields>
        <field>
            <fieldDefinitionIdentifier>name</fieldDefinitionIdentifier>
            <languageCode>eng-GB</languageCode>
            <fieldValue>testUpdateVersion</fieldValue>
        </field>
    </fields>
</VersionUpdate>
XML;

        $request = $this->createHttpRequest(
            'PATCH',
            $restContentVersionHref,
            'VersionUpdate+xml',
            'Version+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testPublishContent
     * Covers GET /content/objects/<contentId>/relations
     */
    public function testRedirectCurrentVersionRelations($restContentHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$restContentHref/relations")
        );

        self::assertHttpResponseCodeEquals($response, 307);

        // @todo Fix, see EZP-21059. Meanwhile, the test is skipped if it fails as expected
        // self::assertHttpResponseHasHeader( $response, 'Location', "$restContentHref/versions/1/relations" );
        self::assertHttpResponseHasHeader($response, 'Location', "$restContentHref/relations?versionNumber=1");
        self::markTestIncomplete('@todo Fix issue EZP-21059');
    }

    /**
     * @depends testCreateDraftFromVersion
     * Covers GET /content/objects/<contentId>/versions/<versionNumber>/relations
     */
    public function testLoadVersionRelations($restContentVersionHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$restContentVersionHref/relations")
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * @depends testCreateDraftFromVersion
     * Covers POST /content/objects/<contentId>/versions/<versionNumber>/relations/<relationId>
     *
     * @return string created relation HREF (/content/objects/<contentId>/versions/<versionNumber>/relations/<relationId>
     */
    public function testCreateRelation($restContentVersionHref)
    {
        $content = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<RelationCreate>
  <Destination href="/api/ezp/v2/content/objects/10"/>
</RelationCreate>
XML;

        $request = $this->createHttpRequest(
            'POST',
            "$restContentVersionHref/relations",
            'RelationCreate+xml',
            'Relation+json',
            $content
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);

        $response = json_decode($response->getBody(), true);

        return $response['Relation']['_href'];
    }

    /**
     * @depends testCreateRelation
     * Covers GET /content/objects/<contentId>/versions/<versionNo>/relations/<relationId>
     */
    public function testLoadVersionRelation($restContentRelationHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $restContentRelationHref)
        );

        self::assertHttpResponseCodeEquals($response, 200);

        // @todo test data
    }

    /**
     * Returns the Content key from the decoded JSON of $restContentId's contentInfo.
     *
     *
     * @throws \InvalidArgumentException
     *
     * @param string $restContentHref /api/ezp/v2/content/objects/<contentId>
     *
     * @return array
     */
    private function loadContent($restContentHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $restContentHref, '', 'ContentInfo+json')
        );

        if ($response->getStatusCode() != 200) {
            throw new \InvalidArgumentException("Content with ID $restContentHref could not be loaded");
        }

        $array = json_decode($response->getBody(), true);
        if ($array === null) {
            self::fail('Error loading content. Response: ' . $response->getBody());
        }

        return $array['Content'];
    }

    public function testCreateView()
    {
        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ViewInput>
  <identifier>testCreateView</identifier>
  <Query>
    <Criteria>
      <ContentTypeIdentifierCriterion>folder</ContentTypeIdentifierCriterion>
    </Criteria>
    <limit>10</limit>
    <offset>0</offset>
  </Query>
</ViewInput>
XML;
        $request = $this->createHttpRequest(
            'POST',
            '/api/ezp/v2/content/views',
            'ViewInput+xml',
            'View+json',
            $body
        );
        $response = $this->sendHttpRequest($request);

        // Returns 301 since 6.0 (deprecated in favour of /views)
        self::assertHttpResponseCodeEquals($response, 301);
        self::assertHttpResponseHasHeader($response, 'Location');
    }

    /**
     * Covers DELETE /content/objects/<contentId>/versions/<versionNo>/translations/<languageCode>.
     *
     * @depends testCreateDraftFromVersion
     *
     * @param string $restContentVersionHref
     */
    public function testDeleteTranslationFromDraft($restContentVersionHref)
    {
        // create pol-PL Translation
        $translationToDelete = 'pol-PL';
        $this->createVersionTranslation($restContentVersionHref, $translationToDelete, 'Polish');

        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', $restContentVersionHref . "/translations/{$translationToDelete}")
        );
        self::assertHttpResponseCodeEquals($response, 204);

        // check that the Translation was deleted by reloading Version
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $restContentVersionHref, '', 'Version+json')
        );

        $version = json_decode($response->getBody(), true);
        self::assertNotContains($translationToDelete, $version['Version']['VersionInfo']['languageCodes']);
    }

    /**
     * Test that VersionInfo loaded in VersionList contains working DeleteTranslation resource link.
     *
     * Covers DELETE /content/objects/<contentId>/versions/<versionNo>/translations/<languageCode>.
     * Covers GET /content/objects/<contentId>/versions
     *
     * @depends testCreateDraftFromVersion
     *
     * @param string $restContentVersionHref
     */
    public function testLoadContentVersionsProvidesDeleteTranslationFromDraftResourceLink($restContentVersionHref)
    {
        $translationToDelete = 'pol-PL';
        // create Version Draft containing pol-PL Translation
        $this->createVersionTranslation($restContentVersionHref, $translationToDelete, 'Polish');

        // load Version
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $restContentVersionHref, '', 'Version+json')
        );
        self::assertHttpResponseCodeEquals($response, 200);
        $version = json_decode($response->getBody(), true);

        // load all Versions
        self::assertNotEmpty($version['Version']['VersionInfo']['Content']['_href']);
        $restLoadContentVersionsHref = $version['Version']['VersionInfo']['Content']['_href'] . '/versions';
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $restLoadContentVersionsHref, '', 'VersionList+json')
        );
        self::assertHttpResponseCodeEquals($response, 200);

        // load Version list
        $versionList = json_decode($response->getBody(), true);
        $version = $this->getVersionInfoFromJSONVersionListByStatus(
            $versionList['VersionList'],
            'DRAFT'
        );

        // validate VersionTranslationInfo structure
        self::assertNotEmpty($version['VersionTranslationInfo']['Language']);
        foreach ($version['VersionTranslationInfo']['Language'] as $versionTranslationInfo) {
            // Other Translation, as the main one, shouldn't be deletable
            if ($versionTranslationInfo['languageCode'] !== $translationToDelete) {
                // check that endpoint is not provided for non-deletable Translation
                self::assertTrue(empty($versionTranslationInfo['DeleteTranslation']['_href']));
            } else {
                // check that provided endpoint works
                self::assertNotEmpty($versionTranslationInfo['DeleteTranslation']['_href']);
                $response = $this->sendHttpRequest(
                    $this->createHttpRequest(
                        'DELETE',
                        $versionTranslationInfo['DeleteTranslation']['_href']
                    )
                );
                self::assertHttpResponseCodeEquals($response, 204);
            }
        }
    }

    /**
     * Covers DELETE /content/objects/<contentId>/translations/<languageCode>.
     */
    public function testDeleteTranslation()
    {
        // create independent Content
        $content = $this->createContentDraft(
            '/api/ezp/v2/content/types/1',
            '/api/ezp/v2/content/locations/1/2',
            '/api/ezp/v2/content/sections/1',
            '/api/ezp/v2/user/users/14',
            [
                'name' => [
                    'eng-GB' => $this->addTestSuffix(__FUNCTION__),
                ],
            ]
        );
        $restContentHref = $content['_href'];
        $restContentVersionHref = "{$content['Versions']['_href']}/{$content['currentVersionNo']}";
        $this->publishContentVersionDraft($restContentVersionHref);
        $restContentVersionHref = $this->createDraftFromVersion($content['CurrentVersion']['_href']);

        // create pol-PL Translation
        $translationToDelete = 'pol-PL';
        $this->createVersionTranslation($restContentVersionHref, $translationToDelete, 'Polish');
        $this->publishContentVersionDraft($restContentVersionHref);

        // delete Translation
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', "{$restContentHref}/translations/{$translationToDelete}")
        );
        self::assertHttpResponseCodeEquals($response, 204);

        // check that deleted Translation no longer exists
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$restContentHref/versions", '', 'VersionList+json')
        );
        self::assertHttpResponseCodeEquals($response, 200);
        $versionList = json_decode($response->getBody(), true);
        foreach ($versionList['VersionList']['VersionItem'] as $versionItem) {
            self::assertNotContains($translationToDelete, $versionItem['VersionInfo']['languageCodes']);
            foreach ($versionItem['VersionInfo']['names']['value'] as $name) {
                self::assertNotEquals($translationToDelete, $name['_languageCode']);
            }
        }

        return $restContentHref;
    }

    /**
     * Test that deleting content which has Version(s) with single Translation being deleted is supported.
     *
     * Covers DELETE /content/objects/<contentId>/translations/<languageCode>.
     *
     * @depends testDeleteTranslation
     *
     * @param string $restContentHref
     */
    public function testDeleteTranslationOfContentWithSingleTranslationVersion($restContentHref)
    {
        // create draft independent from other tests
        $restContentVersionHref = $this->createDraftFromVersion("$restContentHref/versions/1");

        // create pol-PL Translation to have more than one Translation
        $this->createVersionTranslation($restContentVersionHref, 'pol-PL', 'Polish');
        $this->publishContentVersionDraft($restContentVersionHref);

        // change Main Translation to just created pol-PL
        $this->updateMainTranslation($restContentHref, 'pol-PL');

        // delete eng-GB Translation
        $translationToDelete = 'eng-GB';
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('DELETE', "{$restContentHref}/translations/{$translationToDelete}")
        );
        self::assertHttpResponseCodeEquals($response, 204);

        // check that deleted Translation no longer exists
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', "$restContentHref/versions", '', 'VersionList+json')
        );
        self::assertHttpResponseCodeEquals($response, 200);
        $versionList = json_decode($response->getBody(), true);
        foreach ($versionList['VersionList']['VersionItem'] as $versionItem) {
            self::assertNotEmpty($versionItem['VersionInfo']['languageCodes']);
            self::assertNotContains($translationToDelete, $versionItem['VersionInfo']['languageCodes']);
            foreach ($versionItem['VersionInfo']['names']['value'] as $name) {
                self::assertNotEquals($translationToDelete, $name['_languageCode']);
            }
        }
    }

    /**
     * Publish another Version with new Translation.
     *
     * @param string $restContentVersionHref
     *
     * @param string $languageCode
     * @param string $languageName
     *
     * @return string
     */
    private function createVersionTranslation($restContentVersionHref, $languageCode, $languageName)
    {
        // @todo Implement EZP-21171 to check if Language exists and add it
        // for now adding is done by ez:behat:create-language command executed in Travis job

        $xml = <<< XML
<VersionUpdate>
    <fields>
        <field>
            <fieldDefinitionIdentifier>name</fieldDefinitionIdentifier>
            <languageCode>{$languageCode}</languageCode>
            <fieldValue>{$languageName} translated name</fieldValue>
        </field>
    </fields>
</VersionUpdate>
XML;

        $request = $this->createHttpRequest(
            'PATCH',
            $restContentVersionHref,
            'VersionUpdate+xml',
            'Version+json',
            $xml
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Iterate through Version Items returned by REST view for ContentType: VersionList+json
     * and return first VersionInfo data matching given status.
     *
     * @param array $versionList
     * @param string $status uppercase string representation of Version status
     *
     * @return array
     */
    private function getVersionInfoFromJSONVersionListByStatus(array $versionList, $status)
    {
        foreach ($versionList['VersionItem'] as $versionItem) {
            if ($versionItem['VersionInfo']['status'] === $status) {
                return $versionItem['VersionInfo'];
            }
        }

        throw new \RuntimeException("Test internal error: Version with status {$status} not found");
    }

    /**
     * Assert that Version REST Response contains proper fields.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     */
    private function assertVersionResponseContainsExpectedFields(ResponseInterface $response)
    {
        self::assertHttpResponseHasHeader($response, 'Content-Type');
        $contentType = $response->getHeader('Content-Type')[0];
        self::assertNotEmpty($contentType);

        $responseBody = $response->getBody();

        // check if response is of an expected Content-Type
        self::assertEquals('Version+xml', $this->getMediaFromTypeString($contentType));

        // validate by custom XSD
        $document = new \DOMDocument();
        $document->loadXML($responseBody);
        $document->schemaValidate(__DIR__ . '/xsd/Version.xsd');
    }

    /**
     * Create new Content Draft.
     *
     * @param string $restContentTypeHref Content Type REST resource link
     * @param string $restParentLocationHref Parent Location REST resource link
     * @param string $restSectionHref Section REST resource link
     * @param string $restUserHref User REST resource link
     * @param array $fieldValues multilingual field values <code>['fieldIdentifier' => ['languageCode' => 'value']]</code>
     *
     * @return array Content structure decoded from JSON
     */
    private function createContentDraft($restContentTypeHref, $restParentLocationHref, $restSectionHref, $restUserHref, array $fieldValues)
    {
        $remoteId = md5(microtime() . uniqid());
        $modificationDate = new \DateTime();

        $fieldsXML = '';
        foreach ($fieldValues as $fieldIdentifier => $multilingualValues) {
            foreach ($multilingualValues as $languageCode => $fieldValue) {
                $fieldsXML .= <<< XML
<field>
  <fieldDefinitionIdentifier>{$fieldIdentifier}</fieldDefinitionIdentifier>
  <languageCode>{$languageCode}</languageCode>
  <fieldValue>{$fieldValue}</fieldValue>
</field>
XML;
            }
        }

        $body = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentCreate>
  <ContentType href="{$restContentTypeHref}" />
  <mainLanguageCode>eng-GB</mainLanguageCode>
  <LocationCreate>
    <ParentLocation href="{$restParentLocationHref}" />
    <priority>0</priority>
    <hidden>false</hidden>
    <sortField>PATH</sortField>
    <sortOrder>ASC</sortOrder>
  </LocationCreate>
  <Section href="{$restSectionHref}" />
  <alwaysAvailable>true</alwaysAvailable>
  <remoteId>{$remoteId}</remoteId>
  <User href="{$restUserHref}" />
  <modificationDate>{$modificationDate->format('c')}</modificationDate>
  <fields>
    {$fieldsXML}
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

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location')[0];
        $this->addCreatedElement($href);

        $content = json_decode($response->getBody(), true);
        self::assertNotEmpty($content['Content']);

        return $content['Content'];
    }

    /**
     * Create Draft of a given Content and versionNo.
     *
     * @param string $restContentVersionHref REST resource link of Content Version
     *
     * @return string Content Version Draft REST resource link
     */
    private function createDraftFromVersion($restContentVersionHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('COPY', $restContentVersionHref)
        );
        self::assertHttpResponseCodeEquals($response, 201);

        $href = $response->getHeader('Location')[0];
        self::assertNotEmpty($href);

        return $href;
    }

    /**
     * Publish Content Version Draft given by REST resource link.
     *
     * @param string $restContentVersionHref REST resource link of Version Draft
     */
    private function publishContentVersionDraft($restContentVersionHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('PUBLISH', $restContentVersionHref)
        );
        self::assertHttpResponseCodeEquals($response, 204);
    }

    /**
     * Update Main Translation of a Content.
     *
     * @param string $restContentHref REST resource link of Content
     * @param string $languageCode new Main Translation language code
     */
    private function updateMainTranslation($restContentHref, $languageCode)
    {
        $content = <<< XML
<?xml version="1.0" encoding="UTF-8"?>
<ContentUpdate>
  <mainLanguageCode>{$languageCode}</mainLanguageCode>
</ContentUpdate>
XML;

        $request = $this->createHttpRequest(
            'PATCH',
            $restContentHref,
            'ContentUpdate+xml',
            'ContentInfo+json',
            $content
        );
        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 200);
    }
}
