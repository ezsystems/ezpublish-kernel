<?php
/**
 * Load a Content based on its Id (1)
 */
$repository = ezp\Content\Repository::get();
$content = $repository->loadContent( 1 );
?>