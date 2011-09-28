<?php
/**
 * File containing the BaseContentTest class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\Tests;
use ezp\Content\Concrete as ConcreteContent,
    ezp\Content\Type\Concrete as ConcreteType,
    ezp\Content\Type\FieldDefinition,
    ezp\Content\FieldType\TextLine\Value as TextLineValue,
    ezp\Content\FieldType\Keyword\Value as KeywordValue,
    ezp\User\Proxy as ProxyUser,
    ezp\Base\Service\Container,
    ezp\Base\Collection\ReadOnly,
    PHPUnit_Framework_TestCase;

/**
 * Base class for all test cases relying on Content domain object
 */
abstract class BaseContentTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \ezp\Content\Type
     */
    protected $contentType;

    /**
     * @var \ezp\Content
     */
    protected $content;

    /**
     * @var \ezp\Base\Repository
     */
    protected $repository;

    protected function setUp()
    {
        parent::setUp();

        $sc = new Container;
        $this->repository = $sc->getRepository();

        // setup a content type & content object of use by tests
        $this->contentType = new ConcreteType;
        $this->contentType->identifier = 'article';

        // Add some fields
        $aFieldDefData = array(
            'title' => array( 'ezstring', new TextLineValue( 'New Article' ) ),
            'tags' => array( 'ezkeyword', new KeywordValue() )
        );
        $fieldDefCollection = array();
        foreach ( $aFieldDefData as $identifier => $data )
        {
            $fieldDef = new FieldDefinition( $this->contentType, $data[0] );
            $fieldDef->identifier = $identifier;
            $fieldDef->setDefaultValue( $data[1] );
            $fieldDefCollection[] = $fieldDef;
        }
         $this->contentType->setState( array( 'fields' => new ReadOnly( $fieldDefCollection ) ) );

        $this->content = new ConcreteContent( $this->contentType, new ProxyUser( 10, $this->repository->getUserService() ) );
    }
}
