<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Model\Page;

use Nyholm\Psr7\Uri;
use Psr\Http\Message\UriInterface;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;
use webignition\BasilParser\Model\Page\Page;

class PageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(UriInterface $uri, array $elementIdentifiers, Page $expectedPage)
    {
        $page = new Page($uri, $elementIdentifiers);

        $this->assertEquals($expectedPage, $page);
    }

    public function createDataProvider(): array
    {
        return [
            'no elements' => [
                'uri' => new Uri('http://example.com/'),
                'elementIdentifiers' => [],
                'expectedPage' => new Page(new Uri('http://example.com/'), []),
            ],
            'no valid elements' => [
                'uri' => new Uri('http://example.com/'),
                'elementIdentifiers' => [
                    'foo',
                    'bar',
                ],
                'expectedPage' => new Page(new Uri('http://example.com/'), []),
            ],
            'valid elements' => [
                'uri' => new Uri('http://example.com/'),
                'elementIdentifiers' => [
                    'foo' => new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.foo'
                    ),
                    'bar' => new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.bar'
                    ),
                ],
                'expectedPage' => new Page(
                    new Uri('http://example.com/'),
                    [
                        'foo' => new Identifier(
                            IdentifierTypes::CSS_SELECTOR,
                            '.foo'
                        ),
                        'bar' => new Identifier(
                            IdentifierTypes::CSS_SELECTOR,
                            '.bar'
                        ),
                    ]
                ),
            ],
        ];
    }

    public function testGetElementIdentifier()
    {
        $fooIdentifier = new Identifier(
            IdentifierTypes::CSS_SELECTOR,
            '.foo'
        );

        $barIdentifier = new Identifier(
            IdentifierTypes::CSS_SELECTOR,
            '.bar'
        );

        $page = new Page(
            new Uri('http://example.com/'),
            [
                'foo' => $fooIdentifier,
                'bar' => $barIdentifier,
            ]
        );

        $this->assertSame($fooIdentifier, $page->getElementIdentifier('foo'));
        $this->assertSame($barIdentifier, $page->getElementIdentifier('bar'));
        $this->assertNull($page->getElementIdentifier('non-existent'));
    }
}
