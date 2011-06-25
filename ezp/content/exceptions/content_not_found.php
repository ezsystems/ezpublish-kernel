<?php
/**
 * File containing ezp\content\ContentNotFoundException class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package ezp
 * @subpackage content
 */
namespace ezp\content;
class ContentNotFoundException extends \ezp\base\Exception
{
    /**
     * @var int
     */
    protected $id;

    /**
     * Constructs a new ezp\base\Exception with $message
     *
     * @param int $contentId
     */
    public function __construct( $contentId )
    {
        $this->id = $contentId;
        parent::__construct( "Could not find content with id: {$contentId}" );
    }
}
?>
