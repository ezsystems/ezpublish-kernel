<?php
/**
 * File containing the LocaleParameterProvider class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProvider;

use eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderInterface;
use eZ\Publish\API\Repository\Values\Content\Field;
use Symfony\Component\DependencyInjection\ContainerInterface;
use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface;

/**
 * Locale view parameter provider.
 */
class LocaleParameterProvider implements ParameterProviderInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface
     */
    protected $localeConverter;

    public function __construct( ContainerInterface $container, LocaleConverterInterface $localeConverter )
    {
        $this->container = $container;
        $this->localeConverter = $localeConverter;
    }

    /**
     * Returns a hash with 'locale' as key and locale string in POSIX format as value.
     *
     * Locale from request object will be used as locale if set, otherwise field language code
     * will be converted to locale string.
     *
     * @param Field $field
     *
     * @return array
     */
    public function getViewParameters( Field $field )
    {
        $parameters = array();

        /** @var \Symfony\Component\HttpFoundation\Request $request */
        $request = $this->container->get( "request" );
        if ( $request->attributes->has( '_locale' ) )
        {
            $parameters['locale'] = $request->attributes->get( '_locale' );
        }
        else
        {
            $parameters['locale'] = $this->localeConverter->convertToPOSIX( $field->languageCode );
        }

        return $parameters;
    }
}
