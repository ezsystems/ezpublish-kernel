<?php

/**
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Bundle\EzPublishCoreBundle\EventListener;

use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\Helper\TranslationHelper;
use eZ\Publish\Core\MVC\Symfony\Event\RouteReferenceGenerationEvent;
use eZ\Publish\Core\MVC\Symfony\MVCEvents;
use InvalidArgumentException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContentDownloadRouteReferenceListener implements EventSubscriberInterface
{
    const ROUTE_NAME = 'ez_content_download';
    const OPT_FIELD_IDENTIFIER = 'fieldIdentifier';
    const OPT_CONTENT = 'content';
    const OPT_CONTENT_ID = 'contentId';
    const OPT_DOWNLOAD_NAME = 'filename';
    const OPT_DOWNLOAD_LANGUAGE = 'inLanguage';
    const OPT_SITEACCESS_LANGUAGE = 'language';
    const OPT_SITEACCESS = 'siteaccess';
    const OPT_VERSION = 'version';

    /** @var \eZ\Publish\Core\Helper\TranslationHelper */
    private $translationHelper;

    public function __construct(TranslationHelper $translationHelper)
    {
        $this->translationHelper = $translationHelper;
    }

    public static function getSubscribedEvents()
    {
        return [
            MVCEvents::ROUTE_REFERENCE_GENERATION => 'onRouteReferenceGeneration',
        ];
    }

    /**
     * @throws \InvalidArgumentException If the required arguments are not correct
     */
    public function onRouteReferenceGeneration(RouteReferenceGenerationEvent $event)
    {
        $routeReference = $event->getRouteReference();

        if ($routeReference->getRoute() != self::ROUTE_NAME) {
            return;
        }

        $options = $routeReference->getParams();

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);
        $options = $resolver->resolve($options);

        if (isset($options[self::OPT_DOWNLOAD_LANGUAGE])) {
            $routeReference->set(self::OPT_DOWNLOAD_LANGUAGE, $options[self::OPT_DOWNLOAD_LANGUAGE]);
        }

        if (isset($options[self::OPT_VERSION])) {
            $routeReference->set(self::OPT_VERSION, $options[self::OPT_VERSION]);
        }

        $routeReference->set(self::OPT_CONTENT_ID, $options[self::OPT_CONTENT_ID]);
        $routeReference->set(self::OPT_DOWNLOAD_NAME, $options[self::OPT_DOWNLOAD_NAME]);
    }

    /**
     * @param $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired([self::OPT_CONTENT, self::OPT_FIELD_IDENTIFIER]);

        $resolver->setDefaults(
            [
                self::OPT_VERSION => null,
                self::OPT_DOWNLOAD_LANGUAGE => null,
                self::OPT_SITEACCESS_LANGUAGE => null,
                self::OPT_SITEACCESS => null,
            ]
        );

        $resolver->setAllowedTypes(self::OPT_CONTENT, 'eZ\Publish\API\Repository\Values\Content\Content');
        $resolver->setAllowedTypes(self::OPT_FIELD_IDENTIFIER, 'string');

        $resolver->setDefault(
            self::OPT_CONTENT_ID,
            function (Options $options) {
                return $options[self::OPT_CONTENT]->id;
            }
        );

        $resolver->setDefault(
            self::OPT_DOWNLOAD_NAME,
            function (Options $options) {
                $field = $this->translationHelper->getTranslatedField(
                    $options[self::OPT_CONTENT],
                    $options[self::OPT_FIELD_IDENTIFIER],
                    $options[self::OPT_DOWNLOAD_LANGUAGE]
                );
                if (!$field instanceof Field) {
                    throw new InvalidArgumentException(
                        sprintf(
                            "The '%s' parameter did not match a known Field",
                            self::OPT_FIELD_IDENTIFIER
                        )
                    );
                }

                return $field->value->fileName;
            }
        );
    }
}
