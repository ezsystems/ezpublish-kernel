<?php
use ezp\Content\Repository as ContentRepository;
use ezp\Content;

$sectionIdentifier = 'content';
$sectionService = ContentRepository::get()->getSectionService();


try
{
    $section = $sectionService->loadByIdentifier( $sectionIdentifier );
    $sectionService->delete( $section );
}
catch ( SectionNotFoundException $e )
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
