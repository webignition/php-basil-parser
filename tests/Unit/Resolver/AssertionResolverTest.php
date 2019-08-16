<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Resolver;

use Nyholm\Psr7\Uri;
use webignition\BasilModel\Assertion\Assertion;
use webignition\BasilModel\Assertion\AssertionComparisons;
use webignition\BasilModel\Assertion\AssertionInterface;
use webignition\BasilModel\Identifier\AttributeIdentifier;
use webignition\BasilModel\Identifier\ElementIdentifier;
use webignition\BasilModel\Identifier\IdentifierCollection;
use webignition\BasilModel\Identifier\IdentifierCollectionInterface;
use webignition\BasilModel\Page\Page;
use webignition\BasilModel\Value\AttributeValue;
use webignition\BasilModel\Value\ElementValue;
use webignition\BasilModel\Value\LiteralValue;
use webignition\BasilModel\Value\ObjectNames;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModel\Value\ValueTypes;
use webignition\BasilModelFactory\AssertionFactory;
use webignition\BasilParser\Provider\Page\EmptyPageProvider;
use webignition\BasilParser\Provider\Page\PageProviderInterface;
use webignition\BasilParser\Provider\Page\PopulatedPageProvider;
use webignition\BasilParser\Resolver\AssertionResolver;
use webignition\BasilParser\Tests\Services\TestIdentifierFactory;

class AssertionResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AssertionResolver
     */
    private $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resolver = AssertionResolver::createResolver();
    }

    /**
     * @dataProvider resolveLeavesAssertionUnchangedDataProvider
     */
    public function testResolvePageElementReferencesLeavesAssertionUnchanged(AssertionInterface $assertion)
    {
        $this->assertEquals(
            $assertion,
            $this->resolver->resolvePageElementReferences($assertion, new EmptyPageProvider())
        );
    }

    /**
     * @dataProvider resolveLeavesAssertionUnchangedDataProvider
     */
    public function testResolveElementParameterExaminedValueLeavesAssertionUnchanged(AssertionInterface $assertion)
    {
        $this->assertEquals(
            $assertion,
            $this->resolver->resolveElementParameters($assertion, new IdentifierCollection())
        );
    }

    public function resolveLeavesAssertionUnchangedDataProvider(): array
    {
        return [
            'examined value missing' => [
                'assertion' => new Assertion(
                    '',
                    null,
                    ''
                ),
            ],
            'examined value is not object value' => [
                'assertion' => new Assertion(
                    '',
                    LiteralValue::createStringValue('literal string'),
                    ''
                ),
            ],
            'examined value is not page element reference' => [
                'assertion' => new Assertion(
                    '$page.url is "value"',
                    new ObjectValue(
                        ValueTypes::PAGE_OBJECT_PROPERTY,
                        '$page.url',
                        ObjectNames::PAGE,
                        'url'
                    ),
                    AssertionComparisons::IS,
                    LiteralValue::createStringValue('value')
                ),
            ],
            'examined value is not an element parameter' => [
                'assertion' => new Assertion(
                    '".selector" is "value"',
                    new ElementValue(
                        new ElementIdentifier(
                            LiteralValue::createStringValue('.selector')
                        )
                    ),
                    AssertionComparisons::IS,
                    LiteralValue::createStringValue('value')
                ),
            ],
        ];
    }

    /**
     * @dataProvider resolvePageElementReferencesDataProvider
     */
    public function testResolvePageElementReferences(
        AssertionInterface $assertion,
        PageProviderInterface $pageProvider,
        AssertionInterface $expectedAssertion
    ) {
        $resolvedAssertion = $this->resolver->resolvePageElementReferences($assertion, $pageProvider);

        $this->assertNotSame($assertion, $resolvedAssertion);
        $this->assertEquals($expectedAssertion, $resolvedAssertion);
    }

    public function resolvePageElementReferencesDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'examined value is page element reference' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    'page_import_name.elements.element_name exists'
                ),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com/'),
                        new IdentifierCollection([
                            TestIdentifierFactory::createCssElementIdentifier('.selector', 1, 'element_name'),
                        ])
                    )
                ]),
                'expectedAssertion' => new Assertion(
                    'page_import_name.elements.element_name exists',
                    new ElementValue(
                        TestIdentifierFactory::createCssElementIdentifier('.selector', 1, 'element_name')
                    ),
                    AssertionComparisons::EXISTS
                ),
            ],
            'expected value is page element reference' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".examined-selector" is page_import_name.elements.element_name'
                ),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com/'),
                        new IdentifierCollection([
                            TestIdentifierFactory::createCssElementIdentifier('.expected-selector', 1, 'element_name')
                        ])
                    )
                ]),
                'expectedAssertion' => new Assertion(
                    '".examined-selector" is page_import_name.elements.element_name',
                    new ElementValue(
                        TestIdentifierFactory::createCssElementIdentifier('.examined-selector')
                    ),
                    AssertionComparisons::IS,
                    new ElementValue(
                        TestIdentifierFactory::createCssElementIdentifier('.expected-selector', 1, 'element_name')
                    )
                ),
            ],
            'expected and examined values are page element reference' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    'page_import_name.elements.examined is page_import_name.elements.expected'
                ),
                'pageProvider' => new PopulatedPageProvider([
                    'page_import_name' => new Page(
                        new Uri('http://example.com/'),
                        new IdentifierCollection([
                            TestIdentifierFactory::createCssElementIdentifier('.expected-selector', 1, 'expected'),
                            TestIdentifierFactory::createCssElementIdentifier('.examined-selector', 1, 'examined'),
                        ])
                    )
                ]),
                'expectedAssertion' => new Assertion(
                    'page_import_name.elements.examined is page_import_name.elements.expected',
                    new ElementValue(
                        TestIdentifierFactory::createCssElementIdentifier('.examined-selector', 1, 'examined')
                    ),
                    AssertionComparisons::IS,
                    new ElementValue(
                        TestIdentifierFactory::createCssElementIdentifier('.expected-selector', 1, 'expected')
                    )
                ),
            ],
        ];
    }

    /**
     * @dataProvider resolveElementParametersCreatesNewAssertionDataProvider
     */
    public function testResolveElementParametersCreatesNewAssertion(
        AssertionInterface $assertion,
        IdentifierCollectionInterface $identifierCollection,
        AssertionInterface $expectedAssertion
    ) {
        $resolvedAssertion = $this->resolver->resolveElementParameters($assertion, $identifierCollection);

        $this->assertNotSame($assertion, $resolvedAssertion);
        $this->assertEquals($expectedAssertion, $resolvedAssertion);
    }

    public function resolveElementParametersCreatesNewAssertionDataProvider(): array
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'examined value is element parameter' => [
                'assertion' => $assertionFactory->createFromAssertionString('$elements.element_name exists'),
                'identifierCollection' => new IdentifierCollection([
                    TestIdentifierFactory::createCssElementIdentifier('.selector', 1, 'element_name'),
                ]),
                'expectedAssertion' => new Assertion(
                    '$elements.element_name exists',
                    new ElementValue(
                        TestIdentifierFactory::createCssElementIdentifier('.selector', 1, 'element_name')
                    ),
                    AssertionComparisons::EXISTS
                ),
            ],
            'expected value is element parameter' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".selector" is $elements.element_name'
                ),
                'identifierCollection' => new IdentifierCollection([
                    TestIdentifierFactory::createCssElementIdentifier('.expected-selector', 1, 'element_name')
                ]),
                'expectedAssertion' => new Assertion(
                    '".selector" is $elements.element_name',
                    new ElementValue(
                        TestIdentifierFactory::createCssElementIdentifier('.selector')
                    ),
                    AssertionComparisons::IS,
                    new ElementValue(
                        TestIdentifierFactory::createCssElementIdentifier('.expected-selector', 1, 'element_name')
                    )
                ),
            ],
            'expected and examined values are element references' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$elements.examined is $elements.expected'
                ),
                'identifierCollection' => new IdentifierCollection([
                    TestIdentifierFactory::createCssElementIdentifier('.expected-selector', 1, 'expected'),
                    TestIdentifierFactory::createCssElementIdentifier('.examined-selector', 1, 'examined'),
                ]),
                'expectedAssertion' => new Assertion(
                    '$elements.examined is $elements.expected',
                    new ElementValue(
                        TestIdentifierFactory::createCssElementIdentifier('.examined-selector', 1, 'examined')
                    ),
                    AssertionComparisons::IS,
                    new ElementValue(
                        TestIdentifierFactory::createCssElementIdentifier('.expected-selector', 1, 'expected')
                    )
                ),
            ],
        ];
    }

    /**
     * @dataProvider resolveAttributeParametersDataProvider
     */
    public function testResolveAttributeParameters(
        AssertionInterface $assertion,
        IdentifierCollectionInterface $identifierCollection,
        AssertionInterface $expectedAssertion
    ) {
        $resolvedAssertion = $this->resolver->resolveAttributeParameters($assertion, $identifierCollection);

        $this->assertNotSame($assertion, $resolvedAssertion);
        $this->assertEquals($expectedAssertion, $resolvedAssertion);
    }

    public function resolveAttributeParametersDataProvider()
    {
        $assertionFactory = AssertionFactory::createFactory();

        return [
            'expected value is attribute parameter' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$elements.element_name.attribute_name exists'
                ),
                'identifierCollection' => new IdentifierCollection([
                    TestIdentifierFactory::createCssElementIdentifier('.selector', 1, 'element_name'),
                ]),
                'expectedAssertion' => new Assertion(
                    '$elements.element_name.attribute_name exists',
                    new AttributeValue(
                        new AttributeIdentifier(
                            TestIdentifierFactory::createCssElementIdentifier('.selector', 1, 'element_name'),
                            'attribute_name'
                        )
                    ),
                    AssertionComparisons::EXISTS
                ),
            ],
            'examined value is attribute parameter' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '".examined-selector" is $elements.expected.attribute_name'
                ),
                'identifierCollection' => new IdentifierCollection([
                    TestIdentifierFactory::createCssElementIdentifier('.expected-selector', 1, 'expected'),
                ]),
                'expectedAssertion' => new Assertion(
                    '".examined-selector" is $elements.expected.attribute_name',
                    new ElementValue(
                        new ElementIdentifier(
                            LiteralValue::createCssSelectorValue('.examined-selector')
                        )
                    ),
                    AssertionComparisons::IS,
                    new AttributeValue(
                        new AttributeIdentifier(
                            TestIdentifierFactory::createCssElementIdentifier('.expected-selector', 1, 'expected'),
                            'attribute_name'
                        )
                    )
                ),
            ],
            'examined and expected values are attribute parameter' => [
                'assertion' => $assertionFactory->createFromAssertionString(
                    '$elements.examined.attribute_name is $elements.expected.attribute_name'
                ),
                'identifierCollection' => new IdentifierCollection([
                    TestIdentifierFactory::createCssElementIdentifier('.examined-selector', 1, 'examined'),
                    TestIdentifierFactory::createCssElementIdentifier('.expected-selector', 1, 'expected'),
                ]),
                'expectedAssertion' => new Assertion(
                    '$elements.examined.attribute_name is $elements.expected.attribute_name',
                    new AttributeValue(
                        new AttributeIdentifier(
                            TestIdentifierFactory::createCssElementIdentifier('.examined-selector', 1, 'examined'),
                            'attribute_name'
                        )
                    ),
                    AssertionComparisons::IS,
                    new AttributeValue(
                        new AttributeIdentifier(
                            TestIdentifierFactory::createCssElementIdentifier('.expected-selector', 1, 'expected'),
                            'attribute_name'
                        )
                    )
                ),
            ],
        ];
    }
}
