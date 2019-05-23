<?php
/** @noinspection PhpDocSignatureInspection */

namespace webignition\BasilParser\Tests\Unit\Model\Step;

use webignition\BasilParser\Model\Action\WaitAction;
use webignition\BasilParser\Model\Assertion\Assertion;
use webignition\BasilParser\Model\Assertion\AssertionComparisons;
use webignition\BasilParser\Model\DataSet\DataSet;
use webignition\BasilParser\Model\Identifier\Identifier;
use webignition\BasilParser\Model\Identifier\IdentifierTypes;
use webignition\BasilParser\Model\Step\Step;
use webignition\BasilParser\Model\Step\StepInterface;

class StepTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider createDataProvider
     */
    public function testCreate(array $actions, array $assertions, array $expectedActions, array $expectedAssertions)
    {
        $step = new Step($actions, $assertions);

        $this->assertEquals($expectedActions, $step->getActions());
        $this->assertEquals($expectedAssertions, $step->getAssertions());
    }

    public function createDataProvider(): array
    {
        return [
            'no actions, no assertions' => [
                'actions' => [],
                'assertions' => [],
                'expectedActions' => [],
                'expectedAssertions' => [],
            ],
            'all non-actions, all non-assertions' => [
                'actions' => [
                    'foo',
                    'bar',
                ],
                'assertions' => [
                    1,
                    2,
                ],
                'expectedActions' => [],
                'expectedAssertions' => [],
            ],
            'has actions, has assertions, some not correct types' => [
                'actions' => [
                    'foo',
                    new WaitAction('5'),
                    'bar',
                ],
                'assertions' => [
                    1,
                    2,
                    new Assertion(
                        '".selector" is "foo"',
                        new Identifier(
                            IdentifierTypes::CSS_SELECTOR,
                            '.selector'
                        ),
                        AssertionComparisons::IS
                    ),
                ],
                'expectedActions' => [
                    new WaitAction('5'),
                ],
                'expectedAssertions' => [
                    new Assertion(
                        '".selector" is "foo"',
                        new Identifier(
                            IdentifierTypes::CSS_SELECTOR,
                            '.selector'
                        ),
                        AssertionComparisons::IS
                    ),
                ],
            ],
        ];
    }

    /**
     * @dataProvider withDataSetsDataProvider
     */
    public function testWithDataSets(
        StepInterface $step,
        array $dataSets,
        array $expectedDataSets
    ) {
        $currentDataSets = $step->getDataSets();

        $mutatedStep = $step->withDataSets($dataSets);

        $this->assertNotSame($mutatedStep, $step);
        $this->assertEquals($expectedDataSets, $mutatedStep->getDataSets());
        $this->assertSame($currentDataSets, $step->getDataSets());
    }

    public function withDataSetsDataProvider(): array
    {
        return [
            'no existing data sets, empty data sets' => [
                'step' => new Step([], []),
                'dataSets' => [],
                'expectedDataSets' => [],
            ],
            'no existing data sets, non-empty data sets' => [
                'step' => new Step([], []),
                'dataSets' => [
                    'one' => 1,
                    'two' => 'two',
                    'three' => new DataSet([]),
                ],
                'expectedDataSets' => [
                    'three' => new DataSet([]),
                ],
            ],
            'has existing data sets, empty data sets' => [
                'step' => (new Step([], []))->withDataSets([
                    'one' => new DataSet([]),
                ]),
                'dataSets' => [],
                'expectedDataSets' => [],
            ],
            'has existing data sets, non-empty data sets' => [
                'step' => (new Step([], []))->withDataSets([
                    'one' => new DataSet([]),
                ]),
                'dataSets' => [
                    'two' => new DataSet([]),
                ],
                'expectedDataSets' => [
                    'two' => new DataSet([]),
                ],
            ],
        ];
    }

    /**
     * @dataProvider withElementIdentifiersDataProvider
     */
    public function testWithElementIdentifiers(
        StepInterface $step,
        array $elementIdentifiers,
        array $expectedElementIdentifiers
    ) {
        $currentElementIdentifiers = $step->getElementIdentifiers();

        $mutatedStep = $step->withElementIdentifiers($elementIdentifiers);

        $this->assertNotSame($mutatedStep, $step);
        $this->assertEquals($expectedElementIdentifiers, $mutatedStep->getElementIdentifiers());
        $this->assertSame($currentElementIdentifiers, $step->getElementIdentifiers());
    }

    public function withElementIdentifiersDataProvider(): array
    {
        return [
            'no existing element references, empty element references' => [
                'step' => new Step([], []),
                'elementIdentifiers' => [],
                'expectedElementIdentifiers' => [],
            ],
            'no existing element references, non-empty element references' => [
                'step' => new Step([], []),
                'elementIdentifiers' => [
                    'input' => new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.input'
                    ),
                ],
                'expectedElementIdentifiers' => [
                    'input' => new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.input'
                    ),
                ],
            ],
            'has existing element references, empty element references' => [
                'step' => (new Step([], []))->withElementIdentifiers([
                    'input' => new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.input'
                    ),
                ]),
                'elementIdentifiers' => [],
                'expectedElementIdentifiers' => [],
            ],
            'has existing element references, non-empty element references' => [
                'step' => (new Step([], []))->withElementIdentifiers([
                    'input' => new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.input'
                    ),
                ]),
                'elementIdentifiers' => [
                    'button' => new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.button'
                    ),
                ],
                'expectedElementIdentifiers' => [
                    'button' => new Identifier(
                        IdentifierTypes::CSS_SELECTOR,
                        '.button'
                    ),
                ],
            ],
        ];
    }
}