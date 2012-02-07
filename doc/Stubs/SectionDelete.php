<?php
use ezp\Base\ServiceContainer,
    ezp\Base\Configuration;

$sc = new ServiceContainer( Configuration::getInstance('service')->getAll() );
$sectionService = $sc->getRepository()->getSectionService();

$sectionIdentifier = 'content';

try
{
    $section = $sectionService->loadByIdentifier( $sectionIdentifier );
    $sectionService->delete( $section );
}
catch ( ezp\Base\Exception\NotFound $e )
{
    echo "Section ({$sectionIdentifier}) not found !";
    exit;
}
catch ( \Exception $e )
{
    echo "Can not remove section ({$sectionIdentifier}) because {$e->getMessage()}";
    exit;
}
