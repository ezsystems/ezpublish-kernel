<?php
/**
 * File containing ContentHandlerInterface class
 *
 * @copyright Copyright (C) 1999-2011 eZ Systems AS. All rights reserved.
 * @license //EZP_LICENCE//
 * @version //autogentag//
 * @package ezpublish
 * @subpackage persistence
 */
namespace ezp\Content\Persistence\API;

/**
 * Interface for Content Handler
 * A content handler object is meant to manipulate content
 */
interface ContentHandlerInterface extends BaseHandlerInterface
{
	/**
	 * Creates a new version for provided content
	 * @param ContentInterface $content
	 * @return ContentVersionInterface
	 */
    public function createNewVersion( BaseContent $content );

	/**
	 * Publishes provided content
	 * Triggers every needed actions for publication like :
	 * 	- Deleting obsolete versions
	 * 	- Cache clearing
	 * 	- Indexing into search engine
	 * @param ContentInterface $content
	 * @return ContentInterface
	 */
    public function publish( BaseContent $content );

	/**
	 * Removes a content
	 * @param ContentInterface $content
	 * @param bool $moveToTrash Indicates if content has to be moved to trashed (default) or directly deleted
	 * @return void
	 */
	public function remove( BaseContent $content, $moveToTrash = true );
}

?>