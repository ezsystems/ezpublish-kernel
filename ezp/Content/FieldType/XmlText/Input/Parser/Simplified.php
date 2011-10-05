<?php
/**
 * File containing the ezp\Content\FieldType\XmlText\Input\Parser\Simplified class.
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace ezp\Content\FieldType\XmlText\Input\Parser;

use ezp\Content\FieldType\XmlText\Input\Parser as InputParser,
    ezp\Content\FieldType\XmlText\Input\Parser\Raw as RawParser,
    ezp\Content\FieldType\XmlText\Input\Handler,
    ezp\Base\Configuration,
    DOMElement;

/**
 * Simplified (native) XmlText input parser
 */
class Simplified extends RawParser implements InputParser
{
    protected $InputTags = array(
        'b'       => array( 'name' => 'strong' ),
        'bold'    => array( 'name' => 'strong' ),
        'i'       => array( 'name' => 'emphasize' ),
        'em'      => array( 'name' => 'emphasize' ),
        'h'       => array( 'name' => 'header' ),
        'p'       => array( 'name' => 'paragraph' ),
        'para'    => array( 'name' => 'paragraph' ),
        'br'      => array( 'name' => 'br',
                            'noChildren' => true ),
        'a'       => array( 'name' => 'link' ),
        'h1'     => array( 'nameHandler' => 'tagNameHeader' ),
        'h2'     => array( 'nameHandler' => 'tagNameHeader' ),
        'h3'     => array( 'nameHandler' => 'tagNameHeader' ),
        'h4'     => array( 'nameHandler' => 'tagNameHeader' ),
        'h5'     => array( 'nameHandler' => 'tagNameHeader' ),
        'h6'     => array( 'nameHandler' => 'tagNameHeader' ),
        );

    /**
     * Tag Name handlers (init handlers)
     */
    protected function tagNameHeader( $tagName, &$attributes )
    {
        switch ( $tagName )
        {
            case 'h1':
            {
                $attributes['level'] = '1';
            } break;
            case 'h2':
            {
                $attributes['level'] = '2';
            } break;
            case 'h3':
            {
                $attributes['level'] = '3';
            } break;
            case 'h4':
            {
                $attributes['level'] = '4';
            } break;
            case 'h5':
            {
                $attributes['level'] = '5';
            } break;
            case 'h6':
            {
                $attributes['level'] = '6';
            } break;
            default :
            {
                return '';
            } break;
        }
        return 'header';
    }


    protected function getRelatedObjectIDArray()
    {
        return $this->relatedObjectIDArray;
    }

    protected function getLinkedObjectIDArray()
    {
        return $this->linkedObjectIDArray;
    }

    protected function getUrlIDArray()
    {
        return $this->urlIDArray;
    }

    public $urlIDArray = array();
    public $relatedObjectIDArray = array();
    public $linkedObjectIDArray = array();

    // needed for self-embedding protection
    public $contentObjectID = 0;
    /**
     * Input handler
     * @var \ezp\Content\FieldType\XmlText\Input\Handler
     */
    protected $handler;
}
?>