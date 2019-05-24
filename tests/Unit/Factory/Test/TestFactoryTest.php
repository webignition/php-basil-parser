<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Factory\Test;

use Symfony\Component\Yaml\Parser as YamlParser;
use webignition\BasilParser\Builder\StepBuilder;
use webignition\BasilParser\Factory\PageFactory;
use webignition\BasilParser\Factory\StepFactory;
use webignition\BasilParser\Factory\Test\ConfigurationFactory;
use webignition\BasilParser\Factory\Test\TestFactory;
use webignition\BasilParser\Loader\PageLoader;
use webignition\BasilParser\Loader\StepLoader;
use webignition\BasilParser\Loader\YamlLoader;
use webignition\BasilParser\Model\Action\ActionTypes;
use webignition\BasilParser\Model\Action\InteractionAction;
use webignition\BasilParser\Model\Assertion\Assertion;
use webignition\BasilParser\Model\Assertion\AssertionComparisons;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;
use webignition\BasilParser\Model\Step\Step;
use webignition\BasilParser\Model\Test\Configuration;
use webignition\BasilParser\Model\Test\Test;
use webignition\BasilParser\Model\Test\TestInterface;
use webignition\BasilParser\Model\Value\Value;
use webignition\BasilParser\Model\Value\ValueTypes;
use webignition\BasilParser\Tests\Services\FixturePathFinder;

class TestFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var TestFactory
     */
    private $testFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $configurationFactory = new ConfigurationFactory();
        $stepFactory = new StepFactory();

        $yamlParser = new YamlParser();
        $yamlLoader = new YamlLoader($yamlParser);
        $stepLoader = new StepLoader($yamlLoader, $stepFactory);

        $pageFactory = new PageFactory();
        $pageLoader = new PageLoader($yamlLoader, $pageFactory);

        $stepBuilder = new StepBuilder($stepFactory, $stepLoader, $yamlLoader);

        $this->testFactory = new TestFactory($configurationFactory, $pageLoader, $stepBuilder);
    }

    /**
     * @dataProvider createFromTestDataDataProvider
     */
    public function testCreateFromTestData(array $testData, TestInterface $expectedTest)
    {
        $test = $this->testFactory->createFromTestData($testData);

        $this->assertEquals($expectedTest, $test);
    }

    public function createFromTestDataDataProvider(): array
    {
        $configurationData = [
            ConfigurationFactory::KEY_BROWSER => 'chrome',
            ConfigurationFactory::KEY_URL => 'http://example.com',
        ];

        $expectedConfiguration = new Configuration('chrome', 'http://example.com');

        return [
            'empty' => [
                'testData' => [],
                'expectedTest' => new Test(
                    new Configuration('', ''),
                    []
                ),
            ],
            'configuration only' => [
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => $configurationData,
                ],
                'expectedTest' => new Test($expectedConfiguration, []),
            ],
            'invalid inline steps only' => [
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => $configurationData,
                    'invalid' => [
                        StepFactory::KEY_ACTIONS => true,
                        StepFactory::KEY_ASSERTIONS => [
                            '',
                            false,
                        ],
                    ],
                ],
                'expectedTest' => new Test($expectedConfiguration, [
                    'invalid' => new Step([], []),
                ]),
            ],
            'inline steps only' => [
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => $configurationData,
                    'verify page is open' => [
                        StepFactory::KEY_ASSERTIONS => [
                            '$page.url is "http://example.com"',
                        ],
                    ],
                    'query "example"' => [
                        StepFactory::KEY_ACTIONS => [
                            'click ".form .submit"',
                        ],
                        StepFactory::KEY_ASSERTIONS => [
                            '$page.title is "example - Example Domain"',
                        ],
                    ],
                ],
                'expectedTest' => new Test($expectedConfiguration, [
                    'verify page is open' => new Step([], [
                        new Assertion(
                            '$page.url is "http://example.com"',
                            new Identifier(
                                IdentifierTypes::PAGE_OBJECT_PARAMETER,
                                '$page.url'
                            ),
                            AssertionComparisons::IS,
                            new Value(
                                ValueTypes::STRING,
                                'http://example.com'
                            )
                        ),
                    ]),
                    'query "example"' => new Step(
                        [
                            new InteractionAction(
                                ActionTypes::CLICK,
                                new Identifier(
                                    IdentifierTypes::CSS_SELECTOR,
                                    '.form .submit'
                                ),
                                '".form .submit"'
                            ),
                        ],
                        [
                            new Assertion(
                                '$page.title is "example - Example Domain"',
                                new Identifier(
                                    IdentifierTypes::PAGE_OBJECT_PARAMETER,
                                    '$page.title'
                                ),
                                AssertionComparisons::IS,
                                new Value(
                                    ValueTypes::STRING,
                                    'example - Example Domain'
                                )
                            ),
                        ]
                    ),
                ]),
            ],
            'invalid page import path for unused import' => [
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => $configurationData,
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_PAGES => [
                            'invalid' => '../page/file-does-not-exist.yml',
                        ],
                    ],
                ],
                'expectedTest' => new Test($expectedConfiguration, []),
            ],
            'invalid step import path for unused import' => [
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => $configurationData,
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_STEPS => [
                            'invalid' => '../step/file-does-not-exist.yml',
                        ],
                    ],
                ],
                'expectedTest' => new Test($expectedConfiguration, []),
            ],
            'invalid data provider import path for unused import' => [
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => $configurationData,
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_DATA_PROVIDERS => [
                            'invalid' => '../data-provider/file-does-not-exist.yml',
                        ],
                    ],
                ],
                'expectedTest' => new Test($expectedConfiguration, []),
            ],
            'step import, no parameters' => [
                'testData' => [
                    TestFactory::KEY_CONFIGURATION => $configurationData,
                    TestFactory::KEY_IMPORTS => [
                        TestFactory::KEY_IMPORTS_STEPS => [
                            'step_import_name' => FixturePathFinder::find('Step/no-parameters.yml'),
                        ],
                    ],
                    'step_name' => [
                        'use' => 'step_import_name',
                    ],
                ],
                'expectedTest' => new Test(
                    $expectedConfiguration,
                    [
                        'step_name' => new Step(
                            [
                                new InteractionAction(
                                    ActionTypes::CLICK,
                                    new Identifier(
                                        IdentifierTypes::CSS_SELECTOR,
                                        '.button'
                                    ),
                                    '".button"'
                                )
                            ],
                            [
                                new Assertion(
                                    '".heading" includes "Hello World"',
                                    new Identifier(
                                        IdentifierTypes::CSS_SELECTOR,
                                        '.heading'
                                    ),
                                    AssertionComparisons::INCLUDES,
                                    new Value(
                                        ValueTypes::STRING,
                                        'Hello World'
                                    )
                                ),
                            ]
                        ),
                    ]
                ),
            ],
        ];
    }
}