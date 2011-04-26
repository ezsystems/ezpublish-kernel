<?php
use ezp\Business\Content;

$repo = new Content\Repo();
$content = $repo->findContentById( $contentId ); // Implementation of ezp\Persistence\ContentInterface
$sectionId = $content->getSection()->id; // getSection() returns implementation of ezp\Persistence\ContentSectionInterface

//

/**
 * @var \ezp\Content\Persistence\API\ContentHandlerInterface
 */
$contentHandler = $repo->getContentHandler();
$newContentVersion = $contentHandler->createVersion( $content );

?>
