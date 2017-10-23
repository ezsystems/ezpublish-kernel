<?php

/**
 * File containing the Functional\ContentTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Tests\Functional;

use eZ\Bundle\EzPublishRestBundle\Tests\Functional\TestCase as RESTFunctionalTestCase;

class ContentTest extends RESTFunctionalTestCase
{
    /**
     * Covers POST /content/objects.
     *
     * @return string REST content ID
     */
    public function testCreateContent()
    {
        $request = $this->createHttpRequest('POST', '/api/ezp/v2/content/objects', 'ContentCreate+xml', 'ContentInfo+json');
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
        $request->setContent($body);

        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertHttpResponseHasHeader($response, 'Location');

        $href = $response->getHeader('Location');
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
        self::assertEquals($response->getHeader('Location'), $restContentHref);
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
        $request = $this->createHttpRequest('PATCH', $restContentHref, 'ContentUpdate+xml', 'ContentInfo+json');
        $request->setContent($content);
        $response = $this->sendHttpRequest($request);
        self::assertHttpResponseCodeEquals($response, 200);

        // @todo test data
    }

    /**
     * @depends testPublishContent
     *
     * @return string ContentVersion REST ID
     */
    public function testCreateDraftFromVersion($restContentHref)
    {
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('COPY', "{$restContentHref}/versions/1")
        );

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertEquals($response->getHeader('Location'), "{$restContentHref}/versions/2");

        return $response->getHeader('Location');
    }

    /**
     * @depends testPublishContent
     * Covers GET /content/objects/<contentId>/currentversion
     * @covers \eZ\Publish\Core\REST\Server\Controller\Content::redirectCurrentVersion
     */
    public function testRedirectCurrentVersion($restContentHref)
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
        // @todo test data
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

        $request = $this->createHttpRequest('COPY', $restContentHref);
        $request->addHeader('Destination: ' . $testContent['MainLocation']['_href']);

        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);
        self::assertStringStartsWith('/api/ezp/v2/content/objects/', $response->getHeader('Location'));

        $this->addCreatedElement($response->getHeader('Location'));

        return $response->getHeader('Location');
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

        return $response->getHeader('Location');
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

        $request = $this->createHttpRequest('PATCH', $restContentVersionHref, 'VersionUpdate+xml', 'Version+json');
        $request->setContent($xml);
        $response = $this->sendHttpRequest(
            $request
        );

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

        $request = $this->createHttpRequest('POST', "$restContentVersionHref/relations", 'RelationCreate+xml', 'Relation+json');
        $request->setContent($content);

        $response = $this->sendHttpRequest($request);

        self::assertHttpResponseCodeEquals($response, 201);

        $response = json_decode($response->getContent(), true);

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

        $array = json_decode($response->getContent(), true);
        if ($array === null) {
            self::fail('Error loading content. Response: ' . $response->getContent());
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
        $request = $this->createHttpRequest('POST', '/api/ezp/v2/content/views', 'ViewInput+xml', 'View+json');
        $request->setContent($body);
        $response = $this->sendHttpRequest(
            $request
        );

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

        $version = json_decode($response->getContent(), true);
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
        $version = json_decode($response->getContent(), true);

        // load all Versions
        self::assertNotEmpty($version['Version']['VersionInfo']['Content']['_href']);
        $restLoadContentVersionsHref = $version['Version']['VersionInfo']['Content']['_href'] . '/versions';
        $response = $this->sendHttpRequest(
            $this->createHttpRequest('GET', $restLoadContentVersionsHref, '', 'VersionList+json')
        );
        self::assertHttpResponseCodeEquals($response, 200);

        // load Version list
        $versionList = json_decode($response->getContent(), true);
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
        $this->ensureLanguageExists($languageCode, $languageName);

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

        $request = $this->createHttpRequest('PATCH', $restContentVersionHref, 'VersionUpdate+xml', 'Version+json');
        $request->setContent($xml);
        $response = $this->sendHttpRequest(
            $request
        );

        self::assertHttpResponseCodeEquals($response, 200);
    }

    /**
     * Make REST API calls to check if the given Language exists and create it if it doesn't.
     *
     * @param string $languageCode
     * @param string $languageName
     */
    private function ensureLanguageExists($languageCode, $languageName)
    {
        self::markTestIncomplete('@todo: Implement EZP-21171');
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
}
