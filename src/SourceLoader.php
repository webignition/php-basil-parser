<?php

declare(strict_types=1);

namespace webignition\BasilLoader;

use webignition\BasilLoader\Exception\EmptyTestException;
use webignition\BasilLoader\Exception\InvalidPageException;
use webignition\BasilLoader\Exception\InvalidTestException;
use webignition\BasilLoader\Exception\NonRetrievableImportException;
use webignition\BasilLoader\Exception\ParseException;
use webignition\BasilLoader\Exception\UnknownTestException;
use webignition\BasilLoader\Exception\YamlLoaderException;
use webignition\BasilModelProvider\Exception\UnknownItemException;
use webignition\BasilModels\TestSuite\TestSuiteInterface;
use webignition\BasilResolver\CircularStepImportException;
use webignition\BasilResolver\UnknownElementException;
use webignition\BasilResolver\UnknownPageElementException;

class SourceLoader
{
    public function __construct(
        private YamlLoader $yamlLoader,
        private TestSuiteLoader $testSuiteLoader
    ) {
    }

    public static function createLoader(): SourceLoader
    {
        return new SourceLoader(
            YamlLoader::createLoader(),
            TestSuiteLoader::createLoader()
        );
    }

    /**
     * @throws CircularStepImportException
     * @throws EmptyTestException
     * @throws InvalidPageException
     * @throws InvalidTestException
     * @throws NonRetrievableImportException
     * @throws ParseException
     * @throws UnknownElementException
     * @throws UnknownItemException
     * @throws UnknownPageElementException
     * @throws UnknownTestException
     * @throws YamlLoaderException
     */
    public function load(string $path): TestSuiteInterface
    {
        $basePath = dirname($path) . '/';
        $data = $this->yamlLoader->loadArray($path);

        if ([] === $data) {
            throw new EmptyTestException($path);
        }

        if (!$this->isTestPathList($data)) {
            $data = [
                0 => $path,
            ];
        }

        return $this->testSuiteLoader->loadFromTestPathList($path, $basePath, $data);
    }

    /**
     * @param array<mixed> $data
     */
    private function isTestPathList(array $data): bool
    {
        if ([] === $data) {
            return false;
        }

        $keysAreAllIntegers = array_reduce(array_keys($data), function ($result, $value) {
            return false === $result ? false : is_int($value);
        });

        if (false === $keysAreAllIntegers) {
            return false;
        }

        return (bool) array_reduce(array_values($data), function ($result, $value) {
            return false === $result ? false : is_string($value);
        });
    }
}
