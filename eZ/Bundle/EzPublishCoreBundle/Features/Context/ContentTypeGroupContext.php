<?php
/**
 * File containing the ContentTypeGroup context.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */
namespace eZ\Bundle\EzPublishCoreBundle\Features\Context;

use Behat\Behat\Context\Context;
use EzSystems\PlatformBehatBundle\Context\RepositoryContext;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Exceptions as ApiExceptions;
use Behat\Gherkin\Node\TableNode;
use PHPUnit_Framework_Assert as Assertion;

/**
 * Sentences for ContentTypeGroups.
 */
class ContentTypeGroupContext implements Context
{
    use RepositoryContext;

    /** @var \eZ\Publish\API\Repository\ContentTypeService */
    protected $contentTypeService;
    protected $keyMap;

    /**
     * @injectService $repository @ezpublish.api.repository
     * @injectService $contentTypeService @ezpublish.api.service.content_type
     */
    public function __construct(Repository $repository, ContentTypeService $contentTypeService)
    {
        $this->setRepository($repository);
        $this->contentTypeService = $contentTypeService;
        $this->keyMap = [];
    }

    /**
     * Count total ContentTypeGroups with $identifier.
     *
     * @param string $identifier Identifier of the ContentTypeGroup
     *
     * @return int Total ContentTypeGroups with $identifier found
     */
    public function countContentTypeGroup($identifier)
    {
        $contentTypeService = $this->contentTypeService;

        // get all content type groups
        $contentTypeGroupList = $contentTypeService->loadContentTypeGroups();

        // count how many are found with $identifier
        $count = 0;
        foreach ($contentTypeGroupList as $contentTypeGroup) {
            if ($contentTypeGroup->identifier === $identifier) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @Given there is Content Type Group with id :id
     *
     * Creates a new content type group with a non-existent identifier, and maps it's id to ':id'
     */
    public function ensureContentTypeGroupWithIdExists($id)
    {
        $identifier = $this->findNonExistingContentTypeGroupIdentifier();
        $this->ensureContentTypeGroupWithIdAndIdentifierExists($id, $identifier);
    }

    /**
     * @Given there isn't a Content Type Group with id :id
     *
     * Maps id ':id' to a non-existent content type group.
     */
    public function ensureContentTypeGroupWithIdDoesntExist($id)
    {
        $randomId = $this->findNonExistingContentTypeGroupId();
        $this->addValuesToKeyMap($id, $randomId);
    }

    /**
     * @Given there is a Content Type Group with id :id and identifier :identifier
     *
     * nsures a content type group exists with identifier ':identifier', and maps it's id to ':id'
     */
    public function ensureContentTypeGroupWithIdAndIdentifierExists($id, $identifier)
    {
        $contentTypeGroup = $this->ensureContentTypeGroupExists($identifier);
        $this->addValuesToKeyMap($id, $contentTypeGroup['contentTypeGroup']->id);
    }

    /**
     * Store (map) values needed for testing that can't be passed through gherkin.
     *
     * @param string $key   (Unique) Identifier key on the array
     * @param mixed $values Any kind of value/object to store
     *
     * @throws \eZ\Publish\Core\Base\Exceptions\InvalidArgumentException if $key is empty
     */
    protected function addValuesToKeyMap($key, $values)
    {
        if (empty($key)) {
            throw new InvalidArgumentException('key', "can't be empty");
        }
        if (!empty($this->keyMap[$key])) {
            throw new InvalidArgumentException('key', 'key exists');
        }
        $this->keyMap[$key] = $values;
    }

    /**
     * @Given there is a Content Type Group with identifier :identifier
     *
     * Ensures a content type group exists, creating a new one if it doesn't.
     *
     * @return \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup
     */
    public function ensureContentTypeGroupExists($identifier)
    {
        /** @var \eZ\Publish\API\Repository\ContentTypeService */
        $contentTypeService = $this->contentTypeService;

        $found = false;
        // verify if the content type group exists
        try {
            $contentTypeGroup = $contentTypeService->loadContentTypeGroupByIdentifier($identifier);

            $found = true;
        } catch (ApiExceptions\NotFoundException $e) {
            // if not, create it
            $ContentTypeGroupCreateStruct = $contentTypeService->newContentTypeGroupCreateStruct($identifier);
            $contentTypeGroup = $contentTypeService->createContentTypeGroup($ContentTypeGroupCreateStruct);
        }

        return array(
            'found' => $found,
            'contentTypeGroup' => $contentTypeGroup,
        );
    }

    /**
     * @Given there isn't a Content Type Group with identifier :identifier
     *
     * Ensures a content type group does not exist, removing it if necessary.
     */
    public function ensureContentTypeGroupDoesntExist($identifier)
    {

        /** @var \eZ\Publish\API\Repository\ContentTypeService */
        $contentTypeService = $this->contentTypeService;

        // attempt to delete the content type group with the identifier
        try {
            $contentTypeService->deleteContentTypeGroup(
                $contentTypeService->loadContentTypeGroupByIdentifier($identifier)
            );
        } catch (ApiExceptions\NotFoundException $e) {
            // nothing to do
        }
    }

    /**
     * @Given there are the following Content Type Groups:
     *
     * Make sure that content type groups in the provided table exist, by identifier. Example:
     *      | group                 |
     *      | testContentTypeGroup1 |
     *      | testContentTypeGroup2 |
     *      | testContentTypeGroup3 |
     */
    public function ensureContentTypeGroupsExists(TableNode $table)
    {
        $contentTypeGroups = $table->getTable();

        array_shift($contentTypeGroups);
        foreach ($contentTypeGroups as $contentTypeGroup) {
            $this->ensureContentTypeGroupExists($contentTypeGroup[0]);
        }
    }

    /**
     * @Then Content Type Group with identifier :identifier exists
     * @Then Content Type Group with identifier :identifier was created
     * @Then Content Type Group with identifier :identifier wasn't deleted
     *
     * Checks that content type group with identifier $identifier exists
     */
    public function assertContentTypeGroupWithIdentifierExists($identifier)
    {
        Assertion::assertTrue(
            $this->checkContentTypeGroupExistenceByIdentifier($identifier),
            "Couldn't find ContentTypeGroup with identifier '$identifier'"
        );
    }

    /**
     * @Then Content Type Group with identifier :identifier doesn't exist (anymore)
     * @Then Content Type Group with identifier :identifier wasn't created
     * @Then Content Type Group with identifier :identifier was deleted
     *
     * Checks that content type group with identifier $identifier does not exist
     */
    public function assertContentTypeGroupWithIdentifierDoesntExist($identifier)
    {
        Assertion::assertFalse(
            $this->checkContentTypeGroupExistenceByIdentifier($identifier),
            "Unexpected ContentTypeGroup with identifer '$identifier' found"
        );
    }

    /**
     * @Then (only) :total Content Type Group(s) with identifier :identifier exists
     * @Then (only) :total Content Type Group(s) exists with identifier :identifier
     *
     * Checks that there are exactly ':total' content type groups with identifier $identifier
     */
    public function assertTotalContentTypeGroups($total, $identifier)
    {
        Assertion::assertEquals(
            $this->countContentTypeGroup($identifier),
            $total
        );
    }

    /**
     * Find an non existent ContentTypeGroup ID.
     *
     * @return int Non existing ID
     *
     * @throws \Exception Possible endless loop
     */
    private function findNonExistingContentTypeGroupId()
    {
        $i = 0;
        while ($i++ < 20) {
            $id = rand(1000, 9999);
            if (!$this->checkContentTypeGroupExistence($id)) {
                return $id;
            }
        }
        throw new \Exception('Possible endless loop when attempting to find a nonexistent contentTypeGroup id');
    }

    /**
     * Checks if the ContentTypeGroup with $identifier exists.
     *
     * @param string $identifier Identifier of the possible content
     *
     * @return bool True if it exists
     */
    public function checkContentTypeGroupExistenceByIdentifier($identifier)
    {
        /** @var \eZ\Publish\API\Repository\ContentTypeService */
        $contentTypeService = $this->contentTypeService;

        // attempt to load the content type group with the identifier
        try {
            $contentTypeService->loadContentTypeGroupByIdentifier($identifier);

            return true;
        } catch (ApiExceptions\NotFoundException $e) {
            return false;
        }
    }

    /**
     * Find a non existing ContentTypeGroup identifier.
     *
     * @return string A not used identifier
     *
     * @throws \Exception Possible endless loop
     */
    private function findNonExistingContentTypeGroupIdentifier()
    {
        $i = 0;
        while ($i++ < 20) {
            $identifier = 'ctg' . rand(10000, 99999);
            if (!$this->checkContentTypeGroupExistenceByIdentifier($identifier)) {
                return $identifier;
            }
        }

        throw new \Exception('Possible endless loop when attempting to find a new identifier to ContentTypeGroups');
    }

    /**
     * Checks if the ContentTypeGroup with $id exists.
     *
     * @param string $id Identifier of the possible content
     *
     * @return bool True if it exists
     */
    public function checkContentTypeGroupExistence($id)
    {
        $contentTypeService = $this->contentTypeService;
        try {
            $contentTypeService->loadContentTypeGroup($id);

            return true;
        } catch (ApiExceptions\NotFoundException $e) {
            return false;
        }
    }
}
