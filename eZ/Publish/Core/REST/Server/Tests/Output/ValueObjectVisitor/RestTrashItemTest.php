<?php
/**
 * File containing a test class
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\REST\Server\Tests\Output\ValueObjectVisitor;

use eZ\Publish\Core\REST\Common\Tests\Output\ValueObjectVisitorBaseTest;

use eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Server\Values\RestTrashItem;
use eZ\Publish\Core\Repository\Values\Content\TrashItem;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\Core\REST\Common;

class RestTrashItemTest extends ValueObjectVisitorBaseTest
{
    /**
     * Test the TrashItem visitor
     *
     * @return string
     */
    public function testVisit()
    {
        $visitor   = $this->getTrashItemVisitor();
        $generator = $this->getGenerator();

        $generator->startDocument( null );

        $trashItem = new RestTrashItem(
            new TrashItem(
                array(
                    'id' => 42,
                    'priority' => 0,
                    'hidden' => false,
                    'invisible' => true,
                    'remoteId' => 'remote-id',
                    'parentLocationId' => 21,
                    'pathString' => '/1/2/21/42/',
                    'depth' => 3,
                    'contentInfo' => new ContentInfo(
                        array(
                            'id' => 84
                        )
                    ),
                    'sortField' => TrashItem::SORT_FIELD_NAME,
                    'sortOrder' => TrashItem::SORT_ORDER_DESC
                )
            ),
            // Dummy value for ChildCount
            0
        );

        $visitor->visit(
            $this->getVisitorMock(),
            $generator,
            $trashItem
        );

        $result = $generator->endDocument( null );

        $this->assertNotNull( $result );

        return $result;
    }

    /**
     * Test if result contains TrashItem element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsTrashItemElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'TrashItem',
                'children' => array(
                    'count' => 12
                )
            ),
            $result,
            'Invalid <TrashItem> element.',
            false
        );
    }

    /**
     * Test if result contains TrashItem element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsTrashItemAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'TrashItem',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.TrashItem+xml',
                    'href'       => '/content/trash/42',
                )
            ),
            $result,
            'Invalid <TrashItem> attributes.',
            false
        );
    }

    /**
     * Test if result contains id value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsIdValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'id',
                'content'  => '42'
            ),
            $result,
            'Invalid or non-existing <TrashItem> id value element.',
            false
        );
    }

    /**
     * Test if result contains priority value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPriorityValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'priority',
                'content'  => '0'
            ),
            $result,
            'Invalid or non-existing <TrashItem> priority value element.',
            false
        );
    }

    /**
     * Test if result contains hidden value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsHiddenValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'hidden',
                'content'  => 'false'
            ),
            $result,
            'Invalid or non-existing <TrashItem> hidden value element.',
            false
        );
    }

    /**
     * Test if result contains invisible value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsInvisibleValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'invisible',
                'content'  => 'true'
            ),
            $result,
            'Invalid or non-existing <TrashItem> invisible value element.',
            false
        );
    }

    /**
     * Test if result contains remoteId value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsRemoteIdValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'remoteId',
                'content'  => 'remote-id'
            ),
            $result,
            'Invalid or non-existing <TrashItem> remoteId value element.',
            false
        );
    }

    /**
     * Test if result contains ParentLocation element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsParentLocationElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'ParentLocation'
            ),
            $result,
            'Invalid <ParentLocation> element.',
            false
        );
    }

    /**
     * Test if result contains ParentLocation element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsParentLocationAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'ParentLocation',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Location+xml',
                    'href'       => '/content/locations/1/2/21',
                )
            ),
            $result,
            'Invalid <ParentLocation> attributes.',
            false
        );
    }

    /**
     * Test if result contains pathString value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsPathStringValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'pathString',
                'content'  => '/1/2/21/42/'
            ),
            $result,
            'Invalid or non-existing <TrashItem> pathString value element.',
            false
        );
    }

    /**
     * Test if result contains depth value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsDepthValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'depth',
                'content'  => '3'
            ),
            $result,
            'Invalid or non-existing <TrashItem> depth value element.',
            false
        );
    }

    /**
     * Test if result contains childCount value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsChildCountValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'childCount',
                'content'  => '0'
            ),
            $result,
            'Invalid or non-existing <TrashItem> childCount value element.',
            false
        );
    }

    /**
     * Test if result contains Content element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Content'
            ),
            $result,
            'Invalid <Content> element.',
            false
        );
    }

    /**
     * Test if result contains Content element attributes
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsContentAttributes( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'Content',
                'attributes' => array(
                    'media-type' => 'application/vnd.ez.api.Content+xml',
                    'href'       => '/content/objects/84',
                )
            ),
            $result,
            'Invalid <Content> attributes.',
            false
        );
    }

    /**
     * Test if result contains sortField value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSortFieldValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'sortField',
                'content'  => 'NAME'
            ),
            $result,
            'Invalid or non-existing <TrashItem> sortField value element.',
            false
        );
    }

    /**
     * Test if result contains sortOrder value element
     *
     * @param string $result
     *
     * @depends testVisit
     */
    public function testResultContainsSortOrderValueElement( $result )
    {
        $this->assertTag(
            array(
                'tag'      => 'sortOrder',
                'content'  => 'DESC'
            ),
            $result,
            'Invalid or non-existing <TrashItem> sortOrder value element.',
            false
        );
    }

    /**
     * Get the TrashItem visitor
     *
     * @return \eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor\RestTrashItem
     */
    protected function getTrashItemVisitor()
    {
        return new ValueObjectVisitor\RestTrashItem(
            new Common\UrlHandler\eZPublish()
        );
    }
}
