<?php
/**
 * Load a Content (#1) and display one if it's field (title).
 */
$repository = ezp\Content\Repository::get();
$content = $repository->loadContent( 1 );

echo "Title of object #1 is " . $content->fields['title'] . "\n";
?>