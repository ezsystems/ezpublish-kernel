<?php
use ezp\Content\Repository as ContentRepository;
use ezp\Content\Section;

$sectionId = 1;
$sectionService = ContentRepository::get()->getSectionService();
try
{
    $section = $sectionService->load( $sectionId );
    $section->name = "New section name";
    $sectionService->update( $section );
}
catch ( SectionNotFoundException $e )
{
    echo "Section #{$sectionId} not found !"
    exit;
}
catch ( ValidationException $e )
{
    echo "An error occurred during section update: {$e->getMessage()}";
    exit;
}


?>
