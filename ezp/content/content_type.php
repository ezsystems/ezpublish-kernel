<?php
/**
 * File containing ContentType class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 * @package API
 * @subpackage content
 */
namespace ezp\content;
class ContentType extends \ezp\base\AbstractModel
{
    /**
     * Returns an instance of ContentType by its $identifier (e.g. "folder")
     * <code>
     * use \ezp\content\ContentType;
     * use \ezp\content\Content;
     *
     * $contentType = ContentType::byIdentifier( "folder" );
     * $content = new Content( $contentType );
     * </code>
     * @param string $identifier The content type identifier
     * @return ezp\content\ContentType
     */
    public static function byIdentifier( $identifier )
    {
        $contentType = Repository::get()->getContentService()->loadContentTypeByIdentifier( $identifier );
        return $contentType;
    }
}
?>
