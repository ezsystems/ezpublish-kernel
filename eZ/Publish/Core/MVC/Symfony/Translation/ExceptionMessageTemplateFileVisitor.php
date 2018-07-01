<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Translation;

use JMS\TranslationBundle\Translation\Extractor\File\DefaultPhpFileExtractor;

class ExceptionMessageTemplateFileVisitor extends DefaultPhpFileExtractor
{
    protected $methodsToExtractFrom = ['setmessagetemplate' => -1];

    protected $defaultDomain = 'repository_exceptions';
}
