<?php

/**
 * File containing the ContentTypeGroup context class for RestBundle.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishRestBundle\Features\Context\SubContext;

use EzSystems\BehatBundle\Helper\Gherkin as GherkinHelper;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert as Assertion;

trait ContentTypeGroup
{
    /**
     * @When I create a Content Type Group with identifier :identifier
     */
    public function createContentTypeGroupWithIdentifier($identifier)
    {
        $this->createRequest('post', '/content/typegroups');
        $this->setHeaderWithObject('content-type', 'ContentTypeGroupInput');
        $this->setHeaderWithObject('accept', 'ContentTypeGroup');
        $this->makeObject('ContentTypeGroupCreateStruct');
        $this->setFieldToValue('identifier', $identifier);
        $this->sendRequest();
    }

    /**
     * @When I get (the) Content Type Group with id :id
     */
    public function getContentTypeGroupWithId($id)
    {
        $this->createRequest('get', "/content/typegroups/$id");
        $this->setHeaderWithObject('accept', 'ContentTypeGroup');
        $this->sendRequest();
    }

    /**
     * @When I get (the) Content Type Group with identifier :identifier
     */
    public function getContentTypeGroup($identifier)
    {
        $this->createRequest('get', "/content/typegroups?identifier=$identifier");
        $this->setHeaderWithObject('accept', 'ContentTypeGroup');
        $this->sendRequest();
    }

    /**
     * @When I get Content Type Groups list
     */
    public function getContentTypeGroupsList()
    {
        $this->createRequest('get', '/content/typegroups');
        $this->setHeaderWithObject('accept', 'ContentTypeGroupList');
        $this->sendRequest();
    }

    /**
     * @When I update Content Type Group with identifier :old to :new
     */
    public function updateContentTypeGroupIdentifier($old, $new)
    {
        // load the ContentTypeGroup to be updated
        $contentTypeGroup = $this
            ->getRepository()
            ->getContentTypeService()
            ->loadContentTypeGroupByIdentifier($old);

        $this->createRequest('patch', '/content/typegroups/' . $contentTypeGroup->id);
        $this->setHeaderWithObject('content-type', 'ContentTypeGroupInput');
        $this->setHeaderWithObject('accept', 'ContentTypeGroup');
        $this->makeObject('ContentTypeGroupUpdateStruct');
        $this->setFieldToValue('identifier', $new);
        $this->sendRequest();
    }

    /**
     * @Then response has/contains (a) Content Type Group with identifier :identifier
     */
    public function responseHasContentTypeGroupWithIdentifier($identifier)
    {
        $this->assertHeaderHasObject('content-type', 'ContentTypeGroup');
        $this->assertResponseObject('eZ\\Publish\\Core\\REST\\Client\\Values\\ContentType\\ContentTypeGroup');
        $this->assertObjectFieldHasValue('identifier', $identifier);
    }

    /**
     * @Then response has/contains the following Content Type Groups:
     */
    public function responseHasFollowingContentTypeGroups(TableNode $table)
    {
        // get groups
        $groups = GherkinHelper::convertTableToArrayOfData($table);

        // verify if the expects objects are in the list
        foreach ($this->getResponseObject() as $ContentTypeGroup) {
            $found = array_search($ContentTypeGroup->identifier, $groups);
            if ($found !== false) {
                unset($groups[$found]);
            }
        }

        // verify if all the expected groups were found
        Assertion::assertEmpty(
            $groups,
            "Expected to find all groups but couldn't find: " . print_r($groups, true)
        );
    }
}
