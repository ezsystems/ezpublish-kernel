<?php

/**
 * File containing the DateAndTime converter.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;

use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use eZ\Publish\Core\FieldType\DateAndTime\Type as DateAndTimeType;
use eZ\Publish\Core\FieldType\FieldSettings;
use DateTime;
use DateInterval;
use DOMDocument;
use SimpleXMLElement;

class DateAndTimeConverter implements Converter
{
    /**
     * Factory for current class.
     *
     * Note: Class should instead be configured as service if it gains dependencies.
     *
     * @deprecated since 6.8, will be removed in 7.x, use default constructor instead.
     *
     * @return \eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter\DateAndTimeConverter
     */
    public static function create()
    {
        return new self();
    }

    /**
     * Converts data from $value to $storageFieldValue.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $value
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $storageFieldValue
     */
    public function toStorageValue(FieldValue $value, StorageFieldValue $storageFieldValue)
    {
        // @todo: One should additionally store the timezone here. This could
        // be done in a backwards compatible way, I think…
        $storageFieldValue->dataInt = ($value->data !== null ? $value->data['timestamp'] : null);
        $storageFieldValue->sortKeyInt = (int)$value->sortKey;
    }

    /**
     * Converts data from $value to $fieldValue.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldValue $value
     * @param \eZ\Publish\SPI\Persistence\Content\FieldValue $fieldValue
     */
    public function toFieldValue(StorageFieldValue $value, FieldValue $fieldValue)
    {
        if ($value->dataInt === null) {
            return;
        }

        $fieldValue->data = [
            'rfc850' => null,
            'timestamp' => $value->dataInt,
        ];
        $fieldValue->sortKey = $value->sortKeyInt;
    }

    /**
     * Converts field definition data in $fieldDef into $storageFieldDef.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     */
    public function toStorageFieldDefinition(FieldDefinition $fieldDef, StorageFieldDefinition $storageDef)
    {
        $storageDef->dataInt2 = $fieldDef->fieldTypeConstraints->fieldSettings['useSeconds'] ? 1 : 0;
        $storageDef->dataInt1 = $fieldDef->fieldTypeConstraints->fieldSettings['defaultType'];

        if ($fieldDef->fieldTypeConstraints->fieldSettings['defaultType'] === DateAndTimeType::DEFAULT_CURRENT_DATE_ADJUSTED) {
            $storageDef->dataText5 = $this->generateDateIntervalXML(
                $fieldDef->fieldTypeConstraints->fieldSettings['dateInterval']
            );
        }
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef.
     *
     * @param \eZ\Publish\Core\Persistence\Legacy\Content\StorageFieldDefinition $storageDef
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDef
     */
    public function toFieldDefinition(StorageFieldDefinition $storageDef, FieldDefinition $fieldDef)
    {
        $useSeconds = (bool)$storageDef->dataInt2;
        $dateInterval = $this->getDateIntervalFromXML($storageDef->dataText5);

        $fieldDef->fieldTypeConstraints->fieldSettings = new FieldSettings(
            [
                'defaultType' => $storageDef->dataInt1,
                'useSeconds' => $useSeconds,
                'dateInterval' => $dateInterval,
            ]
        );

        // Building default value
        switch ($fieldDef->fieldTypeConstraints->fieldSettings['defaultType']) {
            case DateAndTimeType::DEFAULT_CURRENT_DATE:
                $data = [
                    'rfc850' => null,
                    'timestamp' => time(), // @deprecated timestamp is no longer used and will be removed in a future version.
                    'timestring' => 'now',
                ];
                break;

            case DateAndTimeType::DEFAULT_CURRENT_DATE_ADJUSTED:
                if (!$useSeconds) {
                    $dateInterval->s = 0;
                }
                $date = new DateTime();
                $date->add($dateInterval);
                $data = [
                    'rfc850' => null,
                    'timestamp' => $date->getTimestamp(), // @deprecated timestamp is no longer used and will be removed in a future version.
                    'timestring' => $dateInterval->format('%y years, %m months, %d days, %h hours, %i minutes, %s seconds'),
                ];
                break;

            default:
                $data = null;
        }

        $fieldDef->defaultValue->data = $data;
    }

    /**
     * Returns the name of the index column in the attribute table.
     *
     * Returns the name of the index column the datatype uses, which is either
     * "sort_key_int" or "sort_key_string". This column is then used for
     * filtering and sorting for this type.
     *
     * @return string
     */
    public function getIndexColumn()
    {
        return 'sort_key_int';
    }

    /**
     * Generates the internal XML structure for $dateInterval, used for date adjustment.
     *
     * @param \DateInterval $dateInterval
     *
     * @return string The generated XML string
     */
    protected function generateDateIntervalXML(DateInterval $dateInterval)
    {
        // Constructing XML structure
        $doc = new DOMDocument('1.0', 'utf-8');
        $root = $doc->createElement('adjustment');

        $year = $doc->createElement('year');
        $year->setAttribute('value', $dateInterval->format('%y'));
        $root->appendChild($year);
        unset($year);

        $month = $doc->createElement('month');
        $month->setAttribute('value', $dateInterval->format('%m'));
        $root->appendChild($month);
        unset($month);

        $day = $doc->createElement('day');
        $day->setAttribute('value', $dateInterval->format('%d'));
        $root->appendChild($day);
        unset($day);

        $hour = $doc->createElement('hour');
        $hour->setAttribute('value', $dateInterval->format('%h'));
        $root->appendChild($hour);
        unset($hour);

        $minute = $doc->createElement('minute');
        $minute->setAttribute('value', $dateInterval->format('%i'));
        $root->appendChild($minute);
        unset($minute);

        $second = $doc->createElement('second');
        $second->setAttribute('value', $dateInterval->format('%s'));
        $root->appendChild($second);
        unset($second);

        $doc->appendChild($root);

        return $doc->saveXML();
    }

    /**
     * Generates a DateInterval object from $xmlText.
     *
     * @param string $xmlText
     *
     * @return \DateInterval
     */
    protected function getDateIntervalFromXML($xmlText)
    {
        if (empty($xmlText)) {
            return;
        }

        $xml = new SimpleXMLElement($xmlText);
        $aIntervalString = [
            (int)$xml->year['value'] . ' years',
            (int)$xml->month['value'] . ' months',
            (int)$xml->day['value'] . ' days',
            (int)$xml->hour['value'] . ' hours',
            (int)$xml->minute['value'] . ' minutes',
            (int)$xml->second['value'] . ' seconds',
        ];

        return DateInterval::createFromDateString(implode(', ', $aIntervalString));
    }
}
