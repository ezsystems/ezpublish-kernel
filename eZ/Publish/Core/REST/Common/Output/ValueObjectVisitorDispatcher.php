<?php

/**
 * File containing the ValueObjectVisitorDispatcher class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace eZ\Publish\Core\REST\Common\Output;

/**
 * Dispatches value objects to a visitor depending on the class name.
 */
class ValueObjectVisitorDispatcher
{
    /** @var ValueObjectVisitor[] */
    private $visitors;

    /** @var \eZ\Publish\Core\REST\Common\Output\Visitor */
    private $outputVisitor;

    /** @var \eZ\Publish\Core\REST\Common\Output\Generator */
    private $outputGenerator;

    public function setOutputVisitor(Visitor $outputVisitor)
    {
        $this->outputVisitor = $outputVisitor;
    }

    public function setOutputGenerator(Generator $outputGenerator)
    {
        $this->outputGenerator = $outputGenerator;
    }

    /**
     * @param string $visitedClassName The FQN of the visited class
     * @param \eZ\Publish\Core\REST\Common\Output\ValueObjectVisitor $visitor The visitor object
     */
    public function addVisitor($visitedClassName, ValueObjectVisitor $visitor)
    {
        $this->visitors[$visitedClassName] = $visitor;
    }

    /**
     * @param object $data The visited object
     *
     * @throws \eZ\Publish\Core\REST\Common\Output\Exceptions\NoVisitorFoundException
     * @throws \eZ\Publish\Core\REST\Common\Output\Exceptions\InvalidTypeException
     *
     * @return mixed
     */
    public function visit($data)
    {
        if (!is_object($data)) {
            throw new Exceptions\InvalidTypeException($data);
        }
        $checkedClassNames = [];

        $className = get_class($data);
        do {
            $checkedClassNames[] = $className;
            if (isset($this->visitors[$className])) {
                return $this->visitors[$className]->visit($this->outputVisitor, $this->outputGenerator, $data);
            }
        } while ($className = get_parent_class($className));

        throw new Exceptions\NoVisitorFoundException($checkedClassNames);
    }
}
