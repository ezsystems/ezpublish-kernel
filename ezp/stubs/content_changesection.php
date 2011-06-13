<?php
use ezp\Content\Repository as ContentRepository;

$contentId = 60;
$sectionId = 2;
$contentService = ContentRepository::get()->getContentService();
$sectionService = ContentRepository::get()->getSectionService();

$section = $sectionService->load( $sectionId );
$content = $contentService->load( $contentId );
$content->section = $section;
$contentService->update( $section );
?>
