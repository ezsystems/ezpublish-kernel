<?php
/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\View\Builder;

use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\Core\FieldType\Page\PageService;
use eZ\Publish\Core\FieldType\Page\Parts\Block;
use eZ\Publish\Core\MVC\Symfony\View\BlockView;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Builds BlockView objects.
 */
class BlockViewBuilder implements ViewBuilder
{
    /** @var PageService */
    private $pageService;

    public function __construct(PageService $pageService)
    {
        $this->pageService = $pageService;
    }

    public function matches($argument)
    {
        return strpos($argument, 'ez_page:') !== false;
    }

    public function buildView(array $parameters)
    {
        $view = new BlockView();

        if (isset($parameters['id'])) {
            try {
                $view->addParameters(['id' => $parameters['id']]);
                $view->setBlock(
                    $this->pageService->loadBlock($parameters['id'])
                );
            } catch (UnauthorizedException $e) {
                throw new AccessDeniedException();
            }
        } elseif ($parameters['block'] instanceof Block) {
            $view->setBlock($parameters['block']);
        }

        return $view;
    }
}
