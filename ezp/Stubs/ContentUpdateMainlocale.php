<?php

/**
 * Assume that:
 * $content's mainLocale is eng-GB
 */

use ezp\Content;


$contentService = \ezp\Base\Repository::get()->getContentService();
$content = $contentService->load( 2 );

$content->mainLocale = \ezp\Base\Locale::get( 'fre-FR' );


$contentService->update( $content );
