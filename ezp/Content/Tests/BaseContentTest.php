<?php
/**
 * File containing the BaseContentTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;
use ezp\Content,
    ezp\Content\Location,
    ezp\Content\Section,
    ezp\Content\Type,
    ezp\Content\Type\FieldDefinition,
    ezp\User,
    ezp\Content\FieldType\Value as FieldValue,
    ezp\Content\FieldType\TextLine\Value as TextLineValue,
    ezp\Content\FieldType\Keyword\Value as KeywordValue;

/**
 * Base class for all test cases relying on Content domain object
 */
abstract class BaseContentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\Content\Type
     */
    protected $contentType;

    /**
     * @var \ezp\Content
     */
    protected $content;

    protected function setUp()
    {
        parent::setUp();

        // setup a content type & content object of use by tests
        $this->contentType = new Type;
        $this->contentType->identifier = 'article';

        // Add some fields
        $aFieldDefData = array(
            'title' => array( 'ezstring', new TextLineValue( 'New Article' ) ),
            'tags' => array( 'ezkeyword', new KeywordValue() )
        );
        $fieldDefCollection = $this->contentType->getFields();
        foreach ( $aFieldDefData as $identifier => $data )
        {
            $fieldDef = new FieldDefinition( $this->contentType, $data[0] );
            $fieldDef->identifier = $identifier;
            $fieldDef->setDefaultValue( $data[1] );
            $fieldDefCollection[] = $fieldDef;
        }

        $this->content = new Content( $this->contentType, new User( 10 ) );
    }
}
