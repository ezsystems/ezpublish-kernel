<?php
use ezp\Base\ServiceContainer,
    ezp\Base\Configuration;

$sc = new ServiceContainer( Configuration::getInstance('service')->getAll() );
$sectionService = $sc->getRepository()->getSectionService();

$sectionId = 1;

try
{
    $section = $sectionService->load( $sectionId );
    $section->name = "New section name";
    $sectionService->update( $section );
}
catch ( ezp\Base\Exception\NotFound $e )
{
    echo "Section #{$sectionId} not found !";
    exit;
}
catch ( ValidationException $e )
{
    echo "An error occurred during section update: {$e->getMessage()}";
    exit;
}

?>
