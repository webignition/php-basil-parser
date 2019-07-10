<?php

namespace webignition\BasilParser\Resolver\Test;

use webignition\BasilModel\PageUrlReference\PageUrlReference;
use webignition\BasilModel\Test\Configuration;
use webignition\BasilModel\Test\ConfigurationInterface;
use webignition\BasilParser\Exception\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Provider\Page\PageProviderInterface;

class ConfigurationResolver
{
    /**
     * @param ConfigurationInterface $configuration
     * @param PageProviderInterface $pageProvider
     *
     * @return ConfigurationInterface
     *
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievablePageException
     * @throws UnknownPageException
     */
    public function resolve(
        ConfigurationInterface $configuration,
        PageProviderInterface $pageProvider
    ): ConfigurationInterface {
        $url = $configuration->getUrl();

        $pageUrlReference = new PageUrlReference($url);
        if ($pageUrlReference->isValid()) {
            $page = $pageProvider->findPage($pageUrlReference->getImportName());
            $url = (string) $page->getUri();
        }

        return new Configuration($configuration->getBrowser(), $url);
    }
}
