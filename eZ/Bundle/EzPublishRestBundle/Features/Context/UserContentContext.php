<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Features\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\MinkAwareContext;
use Behat\MinkExtension\Context\RawMinkContext;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\VersionInfo;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\FieldType\User\Value as UserValue;
use eZ\Publish\Core\FieldType\TextLine\Value as TextLineValue;
use eZ\Publish\Core\REST\Client\Values\Content\Content;
use eZ\Publish\Core\REST\Client\Values\Content\VersionUpdate;
use eZ\Publish\Core\REST\Server\Values\RestContentCreateStruct;
use eZ\Publish\Core\REST\Server\Values\Version;
use PHPUnit\Framework\Assert as Assertion;

class UserContentContext extends RawMinkContext implements Context, SnippetAcceptingContext, MinkAwareContext
{
    /** @var \eZ\Bundle\EzPublishRestBundle\Features\Context\RestContext */
    private $restContext;

    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    private $contentTypeService;

    /** @var \eZ\Publish\Core\REST\Client\Values\Content\Content */
    private $currentContent;

    /** @var \eZ\Publish\API\Repository\UserService */
    private $userService;

    /** @var Version */
    private $currentDraft;

    /**
     * The new value the user account's email is set to.
     * @var string
     */
    private $newEmailValue;

    public function __construct(ContentService $contentService, ContentTypeService $contentTypeService, UserService $userService)
    {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->userService = $userService;
    }

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();
        $this->restContext = $environment->getContext('eZ\Bundle\EzPublishRestBundle\Features\Context\RestContext');
    }

    /**
     * @Given /^I set the Content to a User RestContentCreateStruct$/
     */
    public function iSetTheContentToUserContentCreateStruct()
    {
        $contentCreateStruct = $this->contentService->newContentCreateStruct(
            $this->contentTypeService->loadContentTypeByIdentifier('user'),
            'eng-GB'
        );

        $userFieldValue = new UserValue([
            'login' => 'user_content_' . time(),
            'email' => 'user_content_' . time() . '@example.com',
            'passwordHash' => 'not_a_hash',
        ]);

        $contentCreateStruct->setField('first_name', new TextLineValue('User Content'));
        $contentCreateStruct->setField('last_name', new TextLineValue('@' . microtime(true)));
        $contentCreateStruct->setField('user_account', $userFieldValue);

        $restStruct = new RestContentCreateStruct(
            $contentCreateStruct,
            new LocationCreateStruct(['parentLocationId' => 12])
        );
        $this->restContext->requestObject = $restStruct;
    }

    /**
     * @Given /^it contains a Content of ContentType "([^"]*)"$/
     */
    public function itContainsAContentOfContentType($contentTypeIdentifier)
    {
        $object = $this->restContext->getResponseObject();
        Assertion::assertInstanceOf('eZ\Publish\Core\REST\Client\Values\Content\Content', $object);
        Assertion::assertEquals(
            $contentTypeIdentifier,
            $this->contentTypeService->loadContentType($object->contentInfo->contentTypeId)->identifier
        );

        $this->currentContent = $object;
    }

    /**
     * @Given /^it contains a Version of ContentType "([^"]*)"$/
     */
    public function itContainsAVersionOfContentType($contentTypeIdentifier)
    {
        $object = $this->restContext->getResponseObject();
        Assertion::assertInstanceOf('eZ\Publish\Core\REST\Server\Values\Version', $object);
        Assertion::assertEquals(
            $contentTypeIdentifier,
            $this->contentTypeService->loadContentType($object->content->contentInfo->contentTypeId)->identifier
        );

        $this->currentContent = $this->contentService->loadContentByVersionInfo(
            $object->content->versionInfo
        );
    }

    /**
     * @Given /^a User with the same id exists$/
     */
    public function aUserWithTheSameIdExists()
    {
        Assertion::assertInstanceOf(
            'eZ\Publish\API\Repository\Values\User\User',
            $this->userService->loadUser($this->currentContent->id)
        );
    }

    /**
     * @When /^I send a publish request for this content$/
     */
    public function iSendAPublishRequestForThisContent()
    {
        Assertion::assertInstanceOf('\eZ\Publish\API\Repository\Values\Content\Content', $this->currentContent);

        $href = sprintf('/content/objects/%d/versions/1', $this->currentContent->id);
        $this->restContext->createAndSendRequest('publish', $href);
    }

    /**
     * @Given /^there is a User Content$/
     */
    public function thereIsAUserContent()
    {
        $login = 'user_content_' . microtime(true);
        $email = $login . '@example.com';

        $struct = $this->userService->newUserCreateStruct($login, $email, 'PassWord42', 'eng-GB');
        $struct->setField('first_name', 'John');
        $struct->setField('last_name', 'Doe');

        $parentGroup = $this->userService->loadUserGroup(11);
        $user = $this->userService->createUser($struct, [$parentGroup]);

        $this->currentContent = $this->contentService->loadContentByContentInfo($user->contentInfo);
    }

    /**
     * @When /^I create a delete request for this content$/
     */
    public function iCreateDeleteRequestForThisContent()
    {
        $this->restContext->createRequest('delete', '/content/objects/' . $this->currentContent->id);
    }

    /**
     * @Then /^the User this Content referred to is deleted$/
     */
    public function theUserThisContentReferredToIsDeleted()
    {
        // delete the user over HTTP as the user service will have inmemory cache
        $this->restContext->createAndSendRequest('get', '/user/users/' . $this->currentContent->id);
        Assertion::assertInstanceOf(
            'eZ\Publish\Core\REST\Common\Exceptions\NotFoundException',
            $object = $this->restContext->getResponseObject(),
            'The user the content referred to exists'
        );
    }

    /**
     * @Given /^the Content has the "([^"]*)" status$/
     */
    public function theContentHasTheStatus($statusString)
    {
        $statusCodeMap = [
            'published' => VersionInfo::STATUS_PUBLISHED,
            'draft' => VersionInfo::STATUS_DRAFT,
            'archived' => VersionInfo::STATUS_DRAFT,
        ];

        if (!isset($statusCodeMap[$statusString])) {
            throw new InvalidArgumentException('status string', "Unknown status string $statusString");
        }

        Assertion::assertEquals(
            $statusCodeMap[$statusString],
            $this->currentContent->versionInfo->status
        );
    }

    /**
     * @When /^I create a draft of this content$/
     */
    public function iCreateADraftOfThisContent()
    {
        $this->restContext->createAndSendRequest('copy', '/content/objects/' . $this->currentContent->id . '/currentversion');
        $this->currentDraft = $this->restContext->getResponseObject();
    }

    /**
     * @When /^I create an edit request for this draft$/
     */
    public function iCreateAnEditRequestForThisDraft()
    {
        $url = sprintf(
            '/content/objects/%d/versions/%d',
            $this->currentDraft->content->id,
            $this->currentDraft->content->versionInfo->versionNo
        );
        $this->restContext->createRequest('patch', $url);

        $this->restContext->requestObject = new VersionUpdate([
            'contentUpdateStruct' => $this->contentService->newContentUpdateStruct(),
            'contentType' => $this->currentDraft->contentType,
        ]);
        $this->restContext->setHeaderWithObject('content-type', 'VersionUpdate');
    }

    /**
     * @Given /^I set the email field to a new value$/
     */
    public function iSetTheEmailFieldToNewValue()
    {
        $this->newEmailValue = 'user_content_' . microtime(true) . '@example.com';
        $contentUpdateStruct = $this->restContext->requestObject->contentUpdateStruct;
        $contentUpdateStruct->setField('user_account', new UserValue(['email' => $this->newEmailValue]));
    }

    /**
     * @Given /^the User's email was updated to the new value$/
     */
    public function theUserSEmailWasUpdatedToTheNewValue()
    {
        $field = $this->currentContent->getField('user_account');
        Assertion::assertInstanceOf('eZ\Publish\Core\FieldType\User\Value', $field->value);
        Assertion::assertEquals($this->newEmailValue, $field->value->email);
    }
}
