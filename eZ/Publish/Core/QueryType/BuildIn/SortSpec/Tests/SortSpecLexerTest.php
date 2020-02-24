<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
declare(strict_types=1);

namespace eZ\Publish\Core\QueryType\BuildIn\SortSpec;

use PHPUnit\Framework\TestCase;

final class SortSpecLexerTest extends TestCase
{
    /**
     * @dataProvider dataProviderForTokenize
     */
    public function testTokenize(string $input, iterable $expectedTokens): void
    {
        $lexer = new SortSpecLexer();
        $lexer->tokenize($input);

        $this->assertEquals($expectedTokens, $lexer->getAll());
    }

    public function dataProviderForTokenize(): iterable
    {
        yield 'keyword: asc' => [
            'asc',
            [
                new Token(Token::TYPE_ASC, 'asc', 0),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'keyword: desc' => [
            'desc',
            [
                new Token(Token::TYPE_DESC, 'desc', 0),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'id: simple' => [
            'foo',
            [
                new Token(Token::TYPE_ID, 'foo', 0),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'id: full alphabet' => [
            'fO0_bA9',
            [
                new Token(Token::TYPE_ID, 'fO0_bA9', 0),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'int: < 0' => [
            '-10',
            [
                new Token(Token::TYPE_INT, '-10', 0),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'int: 0' => [
            '0',
            [
                new Token(Token::TYPE_INT, '0', 0),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'int: > 0' => [
            '100',
            [
                new Token(Token::TYPE_INT, '100', 0),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'float: 0.0' => [
            '0.0',
            [
                new Token(Token::TYPE_FLOAT, '0.0', 0),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'float: 0.0 < x < 1.0' => [
            '0.5',
            [
                new Token(Token::TYPE_FLOAT, '0.5', 0),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'float: -1.0 < x < 0.0' => [
            '-0.25',
            [
                new Token(Token::TYPE_FLOAT, '-0.25', 0),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'float: > 1.0' => [
            '40.67',
            [
                new Token(Token::TYPE_FLOAT, '40.67', 0),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'float: < -1.0' => [
            '-25.00',
            [
                new Token(Token::TYPE_FLOAT, '-25.00', 0),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'dot' => [
            '.',
            [
                new Token(Token::TYPE_DOT, '.', 0),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'comma' => [
            ',',
            [
                new Token(Token::TYPE_COMMA, ',', 0),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'unknown' => [
            '???',
            [
                new Token(Token::TYPE_NONE, '???', 0),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];

        yield 'empty input' => [
            '',
            [new Token(Token::TYPE_EOF, '')],
        ];

        yield 'sequence' => [
            'asc desc id 0 0.0 . , ???',
            [
                new Token(Token::TYPE_ASC, 'asc', 0),
                new Token(Token::TYPE_DESC, 'desc', 4),
                new Token(Token::TYPE_ID, 'id', 9),
                new Token(Token::TYPE_INT, '0', 12),
                new Token(Token::TYPE_FLOAT, '0.0', 14),
                new Token(Token::TYPE_DOT, '.', 18),
                new Token(Token::TYPE_COMMA, ',', 20),
                new Token(Token::TYPE_NONE, '???', 21),
                new Token(Token::TYPE_EOF, ''),
            ],
        ];
    }

    public function testConsume(): void
    {
        $lexer = new SortSpecLexer();
        $lexer->tokenize('foo, asc');

        $output = [];
        while (!$lexer->isEOF()) {
            $output[] = $lexer->consume();
        }

        $this->assertEquals([
            new Token(Token::TYPE_ID, 'foo', 0),
            new Token(Token::TYPE_COMMA, ',', 3),
            new Token(Token::TYPE_ASC, 'asc', 5),
        ], $output);
    }
}
