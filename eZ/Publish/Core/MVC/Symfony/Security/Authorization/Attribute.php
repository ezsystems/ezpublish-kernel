<?php

/**
 * File containing the authorization Attribute class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\MVC\Symfony\Security\Authorization;

/**
 * Authorization attribute class to be used with SecurityContextInterface::isGranted().
 *
 * $module represents the global scope you want to check access to (e.g. "content")
 * $function represents the feature inside $module (e.g. "read")
 * $limitations are optional limitations to check against (e.g. array( 'valueObject' => $contentInfo )).
 *              Supported keys are "valueObject" and "targets".
 *              "valueObject": ValueObject you want to check access to (e.g. ContentInfo)
 *              "targets": Location, parent or "assignment" (e.g. Section) value object, or an array of the same
 *
 * Usage example:
 * <code>
 * use eZ\Publish\Core\MVC\Symfony\Security\Authorization\Attribute as AuthorizationAttribute;
 *
 * // From inside a controller
 * // Will check if current user can assign a content to a section, $section being a Section value object.
 * $hasAccess = $this->isGranted(
 *     new AuthorizationAttribute( 'content', 'read', array( 'valueObject' => $contentInfo, 'targets' => $section ) )
 * );
 * </code>
 */
class Attribute
{
    /** @var string */
    public $module;

    /** @var string */
    public $function;

    /** @var array */
    public $limitations;

    public function __construct($module = null, $function = null, array $limitations = [])
    {
        $this->module = $module;
        $this->function = $function;
        $this->limitations = $limitations;
    }

    /**
     * String representation so that it's understandable by basic voters.
     *
     * @return string
     */
    public function __toString()
    {
        return "EZ_ROLE_{$this->module}_{$this->function}";
    }
}
