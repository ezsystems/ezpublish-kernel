<?php
/**
 * File containing the Price Value class
 *
 * @copyright Copyright (C) 1999-2014 eZ Systems AS. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl-2.0.txt GNU General Public License v2
 * @version //autogentag//
 */
namespace eZ\Publish\Core\FieldType\Price;

use eZ\Publish\Core\FieldType\Value as BaseValue;

class Value extends BaseValue
{
    /**
     * @var float
     */
    public $price;

    public $currency;

    public $selectedVatType;

    public $vatType;

    public $vatPercent;

    public $isVatIncluded;

    public $incVatPrice;

    public $exVatPrice;

    public $discountPercent;

    public $discountPriceIncVat;

    public $discountPriceExVat;

    public $hasDiscount;

    /**
     * needed?
     *
     * @var
     */
    public $currentUser;

    /**
     * Construct a new Value object and initialize with $values
     *
     * @param array $values
     */
    public function __construct( $values = null )
    {
        if ( $values !== null )
        {
            if ( is_array( $values ) )
            {
                foreach( $values as $key => $val )
                {
                    $this->$key = $val;
                }
            }
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->price;
    }
}