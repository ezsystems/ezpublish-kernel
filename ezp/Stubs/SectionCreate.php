<?php
use ezp\Content\Section,
    ezp\Base\ServiceContainer,
    ezp\Base\Configuration;

$sc = new ServiceContainer( Configuration::getInstance('service')->getAll() );
$sectionService = $sc->getRepository()->getSectionService();

$sectionIdentifier = 'content';
$sectionName = "Content section";

$section = new Section();
$section->identifier = $sectionIdentifier;
$section->name = $sectionName;
try
{
    $sectionService->create( $section );
}
catch ( ValidationException $e )
{
    echo "An error occurred while updating the section: {$e->getMessage()}";
    exit;
}

?>
