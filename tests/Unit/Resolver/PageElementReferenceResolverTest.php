<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Resolver;

use Nyholm\Psr7\Uri;
use webignition\BasilModel\Identifier\ElementIdentifier;
use webignition\BasilModel\Identifier\IdentifierCollection;
use webignition\BasilModel\Identifier\IdentifierInterface;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ObjectValueInterface;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Page\PopulatedPageProvider;
use webignition\BasilParser\Resolver\PageElementReferenceResolver;

class PageElementReferenceResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var PageElementReferenceResolver
     */
    private $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = PageElementReferenceResolver::createResolver();
    }

    /**
     * @dataProvider resolveIsResolvedDataProvider
     */
    public function testResolveIsResolved(
        ObjectValueInterface $value,
        PageProviderInterface $pageProvider,
        IdentifierInterface $expectedIdentifier
    ) {
        $identifier = $this->resolver->resolve($value, $pageProvider);

        $this->assertEquals($expectedIdentifier, $identifier);
    }

    public function resolveIsResolvedDataProvider(): array
    {
        $cssElementIdentifier = new ElementIdentifier(
            LiteralValue::createCssSelectorValue('.selector')
        );

        $cssElementIdentifierWithName = $cssElementIdentifier->withName('element_name');

        return [
            'resolvable' => [
                'value' => new ObjectValue(
                    ValueTypes::PAGE_ELEMENT_REFERENCE,
                    'page_import_name.elements.element_name',
                    'page_import_name',
                    'element_name'
                ),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com/'),
                        new IdentifierCollection([
                            $cssElementIdentifierWithName,
                        ])
                    )
                ]),
                'expectedIdentifier' => $cssElementIdentifierWithName,
            ],
        ];
    }

    /**
     * @dataProvider resolveThrowsUnknownPageElementExceptionDataProvider
     */
    public function testResolveThrowsUnknownPageElementException(
        ObjectValueInterface $value,
        PageProviderInterface $pageProvider,
        string $expectedExceptionMessage
    ) {
        $this->expectException(UnknownPageElementException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $this->resolver->resolve($value, $pageProvider);
    }

    public function resolveThrowsUnknownPageElementExceptionDataProvider(): array
    {
        return [
            'element not present in page' => [
                'value' => new ObjectValue(
                    ValueTypes::PAGE_ELEMENT_REFERENCE,
                    'page_import_name.elements.element_name',
                    'page_import_name',
                    'element_name'
                ),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com/'),
                        new IdentifierCollection([])
                    )
                ]),
                'expectedExceptionMessage' => 'Unknown page element "element_name" in page "page_import_name"',
            ],
        ];
    }
}