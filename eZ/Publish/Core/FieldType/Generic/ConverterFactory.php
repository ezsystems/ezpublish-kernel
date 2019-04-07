<?php

declare(strict_types=1);

namespace eZ\Publish\Core\FieldType\Generic;

use Symfony\Component\Serializer\Serializer;
use eZ\Publish\Core\Persistence\Legacy\Content\FieldValue\Converter as ConverterInterface;

class ConverterFactory
{
    /** @var \Symfony\Component\Serializer\Serializer */
    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function createForFieldType(?string $settingsClass = null): ConverterInterface
    {
        return new Converter($this->serializer, $settingsClass);
    }
}
