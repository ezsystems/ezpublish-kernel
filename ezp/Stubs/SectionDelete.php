<?php
use ezp\Base\Service\Container;

$sc = new Container();
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
catch ( ValidationException $e )
{
    echo "Can not remove section ({$sectionIdentifier}) because {$e->getMessage()}";
    exit;
}

?>
