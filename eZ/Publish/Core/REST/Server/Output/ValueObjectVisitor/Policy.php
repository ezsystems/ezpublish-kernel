<?php

/**
 * File containing the Policy ValueObjectVisitor class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Server\Output\ValueObjectVisitor;

use eZ\Publish\API\Repository\Values\User\PolicyDraft;
use eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor;
use eZ\Publish\Core\REST\Common\Output\Generator;
use eZ\Publish\Core\REST\Common\Output\Visitor;
use eZ\Publish\API\Repository\Values\User\Policy as PolicyValue;

/**
 * Policy value object visitor.
 */
class Policy extends ValueObjectVisitor
{
    /**
     * Visit struct returned by controllers.
     *
     * @param \eZ\Publish\Core\REST\Common\Output\Visitor $visitor
     * @param \eZ\Publish\Core\REST\Common\Output\Generator $generator
     * @param Policy|PolicyDraft $data
     */
    public function visit(Visitor $visitor, Generator $generator, $data)
    {
        $generator->startObjectElement('Policy');
        $visitor->setHeader('Content-Type', $generator->getMediaType($data instanceof PolicyDraft ? 'PolicyDraft' : 'Policy'));
        $visitor->setHeader('Accept-Patch', $generator->getMediaType('PolicyUpdate'));
        $this->visitPolicyAttributes($visitor, $generator, $data);
        $generator->endObjectElement('Policy');
    }

    protected function visitPolicyAttributes(Visitor $visitor, Generator $generator, PolicyValue $data)
    {
        $generator->startAttribute(
            'href',
            $this->router->generate('ezpublish_rest_loadPolicy', ['roleId' => $data->roleId, 'policyId' => $data->id])
        );
        $generator->endAttribute('href');

        $generator->startValueElement('id', $data->id);
        $generator->endValueElement('id');

        if ($data instanceof PolicyDraft) {
            $generator->startValueElement('originalId', $data->originalId);
            $generator->endValueElement('originalId');
        }

        $generator->startValueElement('module', $data->module);
        $generator->endValueElement('module');

        $generator->startValueElement('function', $data->function);
        $generator->endValueElement('function');

        $limitations = $data->getLimitations();
        if (!empty($limitations)) {
            $generator->startHashElement('limitations');
            $generator->startList('limitation');

            foreach ($limitations as $limitation) {
                $this->visitLimitation($generator, $limitation);
            }

            $generator->endList('limitation');
            $generator->endHashElement('limitations');
        }
    }
}
