<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Controller\Content;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\MVC\Symfony\Controller\Controller;
use eZ\Publish\Core\MVC\Symfony\Routing\Generator\RouteReferenceGenerator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class DownloadRedirectionController extends Controller
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;

    /** @var \Symfony\Component\Routing\RouterInterface */
    private $router;

    /** @var \eZ\Publish\Core\MVC\Symfony\Routing\Generator\RouteReferenceGenerator */
    private $routeReferenceGenerator;

    public function __construct(ContentService $contentService, RouterInterface $router, RouteReferenceGenerator $routeReferenceGenerator)
    {
        $this->contentService = $contentService;
        $this->router = $router;
        $this->routeReferenceGenerator = $routeReferenceGenerator;
    }

    /**
     * Used by the REST API to reference downloadable files.
     * It redirects (permanently) to the standard ez_content_download route, based on the language of the field
     * passed as an argument, using the language switcher.
     *
     * @param mixed $contentId
     * @param int $fieldId
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToContentDownloadAction($contentId, $fieldId, Request $request)
    {
        $content = $this->contentService->loadContent($contentId);
        $field = $this->findFieldInContent($fieldId, $content);

        $params = [
            'content' => $content,
            'fieldIdentifier' => $field->fieldDefIdentifier,
            'language' => $field->languageCode,
        ];

        if ($request->query->has('version')) {
            $params['version'] = $request->query->get('version');
        }

        $downloadUrl = $this->router->generate(
            $this->routeReferenceGenerator->generate(
                'ez_content_download',
                $params
            )
        );

        return new RedirectResponse($downloadUrl, 302);
    }

    /**
     * Finds the field with id $fieldId in $content.
     *
     * @param int $fieldId
     * @param Content $content
     *
     * @return Field
     */
    protected function findFieldInContent($fieldId, Content $content)
    {
        foreach ($content->getFields() as $field) {
            if ($field->id == $fieldId) {
                return $field;
            }
        }
        throw new InvalidArgumentException("Field with id $fieldId not found in Content with id {$content->id}");
    }
}
