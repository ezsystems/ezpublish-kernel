<?php
/**
 * Delete a Content
 */
$repository = ezp\Content\Repository::get();
$content = $repository->loadContent( 1 );
$repository->delete( $content );
?>