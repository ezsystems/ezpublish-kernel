<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\MVC\Symfony\Templating\Tests;

use eZ\Publish\Core\MVC\Symfony\Templating\RenderOptions;
use PHPUnit\Framework\TestCase;

class RenderOptionsTest extends TestCase
{
    public function testInitialOptions(): void
    {
        $renderOptions = new RenderOptions([
            'a' => 'value_a',
            'b' => null,
        ]);

        $this->assertTrue($renderOptions->has('a'));
        $this->assertSame('value_a', $renderOptions->get('a'));
        $this->assertFalse($renderOptions->has('b'));
        $this->assertSame([
            'a' => 'value_a',
            'b' => null,
        ], $renderOptions->all());
    }

    public function testSettingOptions(): void
    {
        $renderOptions = new RenderOptions();

        $renderOptions->set('a', 'value_a');
        $this->assertTrue($renderOptions->has('a'));
        $this->assertSame('value_a', $renderOptions->get('a'));

        $this->assertTrue($renderOptions->has('a'));
        $renderOptions->set('a', 'different_value_a');
        $this->assertSame('different_value_a', $renderOptions->get('a'));

        $this->assertFalse($renderOptions->has('b'));
        $renderOptions->set('b', null);
        $this->assertFalse($renderOptions->has('b'));
    }

    public function testGettingDefaultOptions(): void
    {
        $renderOptions = new RenderOptions([
            'a' => null,
            'b' => 'default_value_b',
        ]);

        $this->assertFalse($renderOptions->has('a'));
        $this->assertSame('some_default_value', $renderOptions->get('a', 'some_default_value'));

        $this->assertTrue($renderOptions->has('b'));
        $this->assertSame('default_value_b', $renderOptions->get('b', 'other_default_value'));

        $this->assertFalse($renderOptions->has('c'));
        $this->assertSame('default_value_c', $renderOptions->get('c', 'default_value_c'));
    }

    public function testUnsettingOptions(): void
    {
        $renderOptions = new RenderOptions([
            'a' => 'value_a',
            'b' => 'value_b',
            'c' => 'value_c',
        ]);

        $renderOptions->set('a', null);
        $this->assertFalse($renderOptions->has('a'));

        $renderOptions->remove('b');
        $this->assertFalse($renderOptions->has('b'));

        $this->assertTrue($renderOptions->has('c'));
    }
}
