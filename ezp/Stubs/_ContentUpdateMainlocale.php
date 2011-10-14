<?php

/**
 * Assume that:
 * $content's mainLocale is eng-GB
 */

use ezp\Base\ServiceContainer,
    ezp\Base\Configuration;

$sc = new ServiceContainer( Configuration::getInstance('service')->getAll() );
$contentService = $sc->getRepository()->getContentService();
$content = $contentService->load( 2 );

$content->mainLocale = ezp\Base\Locale::get( 'fre-FR' );

$contentService->update( $content );
