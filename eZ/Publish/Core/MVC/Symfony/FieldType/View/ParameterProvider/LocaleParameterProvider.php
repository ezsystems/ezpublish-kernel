<?php
/**
 * File containing the LocaleParameterProvider class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 * @version //autogentag//
 */

namespace eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProvider;

use eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderInterface;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Locale view parameter provider.
 */
class LocaleParameterProvider implements ParameterProviderInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @var \eZ\Publish\Core\MVC\Symfony\Locale\LocaleConverterInterface
     */
    protected $localeConverter;

    public function __construct( LocaleConverterInterface $localeConverter )
    {
        $this->localeConverter = $localeConverter;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     */
    public function setRequest( Request $request = null )
    {
        $this->request = $request;
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

        if ( $this->request && $this->request->attributes->has( '_locale' ) )
        {
            $parameters['locale'] = $this->request->attributes->get( '_locale' );
        }
        else
        {
            $parameters['locale'] = $this->localeConverter->convertToPOSIX( $field->languageCode );
        }

        return $parameters;
    }
}
