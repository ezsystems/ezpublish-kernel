<?php
use ezp\Content\Repository as ContentRepository;
use ezp\Content;

$contentId = 60;
$sectionId = 2;
$contentService = ContentRepository::get()->getContentService();
$sectionService = ContentRepository::get()->getSectionService();

try
{
    $section = $sectionService->load( $sectionId );
    $content = $contentService->load( $contentId );
    $content->section = $section;
    $contentService->update( $content );
}
catch ( ContentNotFoundException $e )
{
    echo "Content ($contentId) does not exist";
    exit;
}
catch ( SectionNotFoundException $e )
{
    echo "Section ($sectionId) does not exist";
    exit;
}
catch ( ValidationException $e )
{
    echo "An error occurred while updating the content: {$e->getMessage()}";
    exit;
}
?>
