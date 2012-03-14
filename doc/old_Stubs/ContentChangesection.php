<?php
use ezp\Base\ServiceContainer,
    ezp\Base\Configuration;

$sc = new ServiceContainer( Configuration::getInstance('service')->getAll() );
$repository = $sc->getRepository();
$contentService = $repository->getContentService();
$sectionService = $repository->getSectionService();

$contentId = 60;
$sectionId = 2;

try
{
    $section = $sectionService->load( $sectionId );
    $content = $contentService->load( $contentId );
    $sectionService->assign( $section, $content );
}
catch ( \Exception $e )
{
    echo "An error occurred while updating the content: {$e->getMessage()}";
    exit;
}
