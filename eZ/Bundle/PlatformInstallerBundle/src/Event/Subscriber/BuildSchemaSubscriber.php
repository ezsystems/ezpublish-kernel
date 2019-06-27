<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace EzSystems\PlatformInstallerBundle\Event\Subscriber;

use EzSystems\DoctrineSchema\API\Event\SchemaBuilderEvent;
use EzSystems\DoctrineSchema\API\Event\SchemaBuilderEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BuildSchemaSubscriber implements EventSubscriberInterface
{
    /** @var string */
    private $schemaFilePath;

    /**
     * @param string $schemaFilePath Path to Yaml schema definition supported by SchemaBuilder
     */
    public function __construct(string $schemaFilePath)
    {
        $this->schemaFilePath = $schemaFilePath;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            SchemaBuilderEvents::BUILD_SCHEMA => ['onBuildSchema', 200],
        ];
    }

    /**
     * @param \EzSystems\DoctrineSchema\API\Event\SchemaBuilderEvent $event
     */
    public function onBuildSchema(SchemaBuilderEvent $event): void
    {
        $event
            ->getSchemaBuilder()
            ->importSchemaFromFile($this->schemaFilePath);
    }
}
