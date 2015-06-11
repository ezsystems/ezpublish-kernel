<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Controller\Content;

use eZ\Bundle\EzPublishIOBundle\BinaryStreamResponse;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\IO\IOService;
use eZ\Publish\Core\MVC\Symfony\Controller\Controller;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DownloadController extends Controller
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \eZ\Publish\Core\IO\IOService */
    private $ioService;

    public function __construct( ContentService $contentService, IOService $ioService )
    {
        $this->contentService = $contentService;
        $this->ioService = $ioService;
    }

    /**
     * @param mixed $contentId ID of a valid Content
     * @param string $fieldId Field Definition id of the Field the file must be downloaded from
     * @param string $filename
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \eZ\Bundle\EzPublishIOBundle\BinaryStreamResponse
     */
    public function downloadBinaryFileAction( $contentId, $fieldId, $filename, Request $request )
    {
        if ( $request->query->has( 'version' ) )
        {
            $content = $this->contentService->loadContent( $contentId, null, $request->query->get( 'version' ) );
        }
        else
        {
            $content = $this->contentService->loadContent( $contentId );
        }

        $field = $this->findField( $content, $fieldId );
        if ( $field === false )
        {
            throw new InvalidArgumentException(
                "'No field with id $fieldId found in content #{$content->contentInfo->id} '{$content->contentInfo->name}'"
            );
        }

        $response = new BinaryStreamResponse( $this->ioService->loadBinaryFile( $field->value->id ), $this->ioService );
        $response->setContentDisposition( ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename );
        return $response;
    }

    /**
     * Given a Content and a Field id, returns the matching Field from the Content
     *
     * @param Content $content
     * @param int $fieldId
     *
     * @return Field|bool the Field, or false if it was not found
     */
    private function findField( Content $content, $fieldId )
    {
        foreach ( $content->getFields() as $field )
        {
            if ( $field->id == $fieldId )
            {
                return $field;
            }
        }

        return false;
    }
}
