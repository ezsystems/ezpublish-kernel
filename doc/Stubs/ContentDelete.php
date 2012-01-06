<?php
/**
 * Delete a Content
 */
use ezp\Base\ServiceContainer,
    ezp\Base\Configuration;

$sc = new ServiceContainer( Configuration::getInstance('service')->getAll() );
$contentService = $sc->getRepository()->getContentService();
try
{
    $content = $contentService->load( 60 );
    $contentService->delete( $content );
}
catch ( ezp\Base\Exception\NotFound $e )
{
    echo "Content could not be found in the repository !\n";
    exit;
}
catch ( ezp\Base\Exception\Forbidden $e )
{
    echo "A permission issue occurred while deleting content: {$e->getMessage()}\n";
    exit;
}
?>
