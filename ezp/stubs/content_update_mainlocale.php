<?php

/**
 * Assume that:
 * $content's mainLocale is eng-GB
 */

use ezp\content;


$contentService = \ezp\base\Repository::get()->getContentService();
$content = $contentService->load( 2 );

$content->mainLocale = \ezp\base\Locale::get( 'fre-FR' );


$contentService->update( $content );
