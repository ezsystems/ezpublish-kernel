<?php
use ezp\Content\Repository as ContentRepository;
use ezp\Content\Section;

$sectionIdentifier = 'content';
$sectionService = ContentRepository::get()->getSectionService();

$section = $sectionService->loadByIdentifier( $sectionIdentifier );

try
{
    $sectionService->delete( $section );
}
catch( ezp\Content\ValidationException $e )
{
    echo "Can not remove section ({$sectionIdentifier}) because {$e->getMessage()}";
}


?>
