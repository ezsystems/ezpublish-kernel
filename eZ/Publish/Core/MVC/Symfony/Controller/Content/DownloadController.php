<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Controller\Content;

use eZ\Bundle\EzPublishIOBundle\BinaryStreamResponse;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\IO\IOService;
use eZ\Publish\Core\MVC\Symfony\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class DownloadController extends Controller
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \eZ\Publish\Core\IO\IOService */
    private $ioService;

    /** @var \eZ\Publish\Core\Helper\TranslationHelper */
    private $translationHelper;

    public function __construct(ContentService $contentService, IOService $ioService, TranslationHelper $translationHelper)
    {
        $this->contentService = $contentService;
        $this->ioService = $ioService;
        $this->translationHelper = $translationHelper;
    }

    /**
     * Download binary file identified by field ID.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the field $fieldId can't be found, or the translation can't be found.
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the content is trashed, or can't be found.
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the user has no access to read content and in case of un-published content: read versions.
     */
    public function downloadBinaryFileByIdAction(Request $request, int $contentId, int $fieldId): BinaryStreamResponse
    {
        $content = $this->contentService->loadContent($contentId);
        try {
            $field = $this->findFieldInContent($fieldId, $content);
        } catch (InvalidArgumentException $e) {
            throw new NotFoundException('File', $fieldId);
        }

        return $this->downloadBinaryFileAction($contentId, $field->fieldDefIdentifier, $field->value->fileName, $request);
    }

    /**
     * Finds the field with id $fieldId in $content.
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the field $fieldId can't be found, or the translation can't be found.
     */
    protected function findFieldInContent(int $fieldId, Content $content): Field
    {
        foreach ($content->getFields() as $field) {
            if ($field->getId() === $fieldId) {
                return $field;
            }
        }

        throw new InvalidArgumentException(
            '$fieldId', "Field with id $fieldId not found in Content with id {$content->id}"
        );
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException If the field $fieldIdentifier can't be found, or the translation can't be found.
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException If the content is trashed, or can't be found.
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException If the user has no access to read content and in case of un-published content: read versions.
     */
    public function downloadBinaryFileAction(int $contentId, string $fieldIdentifier, string $filename, Request $request): BinaryStreamResponse
    {
        if ($request->query->has('version')) {
            $content = $this->contentService->loadContent($contentId, null, $request->query->get('version'));
        } else {
            $content = $this->contentService->loadContent($contentId);
        }

        if ($content->contentInfo->isTrashed()) {
            throw new NotFoundException('File', $filename);
        }

        $field = $this->translationHelper->getTranslatedField(
            $content,
            $fieldIdentifier,
            $request->query->has('inLanguage') ? $request->query->get('inLanguage') : null
        );
        if (!$field instanceof Field) {
            throw new InvalidArgumentException(
                '$fieldIdentifier', "'{$fieldIdentifier}' field not present on content #{$content->contentInfo->id} '{$content->contentInfo->name}'"
            );
        }

        $response = new BinaryStreamResponse($this->ioService->loadBinaryFile($field->value->id), $this->ioService);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $field->value->fileName);

        return $response;
    }
}
