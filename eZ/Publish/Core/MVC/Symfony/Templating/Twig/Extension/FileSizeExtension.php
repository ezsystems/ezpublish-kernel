<?php
/**
 * File containing the FileSizeExtension class.
 *
 * @copyright Copyright (C) 1999-2013 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\MVC\Symfony\Templating\Twig\Extension;

use Locale;
use NumberFormatter;
use Twig_Extension;
use Twig_SimpleFilter;
use Symfony\Component\Translation\TranslatorInterface;





class FileSizeExtension extends Twig_Extension
{
    /**
     * @param TranslatorInterface $translator
     */
    protected $translator;
    /**
     * @param TranslatorInterface $translator
     */
    public function __construct( TranslatorInterface $translator )
    {
      $this->translator = $translator;
    }
    public function getFilters()
    {
        return array(
            new Twig_SimpleFilter( 'ez_file_size', array( $this, 'sizeFilter' ) ),
        );
    }
    /**
     * Returns the binary file size
     * @param integer $number
     * @param integer $precision
     *
     * @return string
     */
    public function sizeFilter( $number, $precision )
    {
        $mod = 1024;
        $suffixes = array( 'B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB' );
        if ( $number < pow( 1024, 7 ) ){
            for ( $i = 0; $number >= $mod; $i++ ) {
                $number /= $mod;
            }
        }
        else{
            $number /= pow( 1024, 6 );
            $i = 6;
        }
        $formatter = new NumberFormatter( Locale::getDefault(), NumberFormatter::PATTERN_DECIMAL );
        $formatter->setPattern( $formatter->getPattern() . " " . $this->translator->trans( $suffixes[$i] ) );
        $return = $formatter->format( round( $number, $precision ) );
        return $return;
    }
    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'twig_file_size_extension';
    }
}
