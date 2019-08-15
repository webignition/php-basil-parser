<?php

namespace webignition\BasilParser\Resolver;

use webignition\BasilModel\Identifier\ElementIdentifierInterface;
use webignition\BasilModel\Identifier\IdentifierCollectionInterface;
use webignition\BasilModel\Identifier\IdentifierInterface;
use webignition\BasilModel\Identifier\IdentifierTypes;
use webignition\BasilModel\Value\ObjectValue;
use webignition\BasilModelFactory\InvalidPageElementIdentifierException;
use webignition\BasilModelFactory\MalformedPageElementReferenceException;
use webignition\BasilParser\Exception\NonRetrievablePageException;
use webignition\BasilParser\Exception\UnknownElementException;
use webignition\BasilParser\Exception\UnknownPageElementException;
use webignition\BasilParser\Exception\UnknownPageException;
use webignition\BasilParser\Provider\Page\PageProviderInterface;

class IdentifierResolver
{
    private $pageElementReferenceResolver;

    public function __construct(PageElementReferenceResolver $pageElementReferenceResolver)
    {
        $this->pageElementReferenceResolver = $pageElementReferenceResolver;
    }

    public static function createResolver(): IdentifierResolver
    {
        return new IdentifierResolver(
            PageElementReferenceResolver::createResolver()
        );
    }

    /**
     * @param IdentifierInterface $identifier
     * @param PageProviderInterface $pageProvider
     *
     * @return IdentifierInterface
     *
     * @throws InvalidPageElementIdentifierException
     * @throws MalformedPageElementReferenceException
     * @throws NonRetrievablePageException
     * @throws UnknownPageElementException
     * @throws UnknownPageException
     */
    public function resolvePageElementReference(
        IdentifierInterface $identifier,
        PageProviderInterface $pageProvider
    ): IdentifierInterface {
        if (IdentifierTypes::PAGE_ELEMENT_REFERENCE !== $identifier->getType()) {
            return $identifier;
        }

        $value = $identifier->getValue();

        if (!$value instanceof ObjectValue) {
            return $identifier;
        }

        return $this->pageElementReferenceResolver->resolve($value, $pageProvider);
    }

    /**
     * @param IdentifierInterface $identifier
     * @param IdentifierCollectionInterface $identifierCollection
     *
     * @return IdentifierInterface
     *
     * @throws UnknownElementException
     */
    public function resolveElementParameter(
        IdentifierInterface $identifier,
        IdentifierCollectionInterface $identifierCollection
    ): IdentifierInterface {
        if (IdentifierTypes::ELEMENT_PARAMETER !== $identifier->getType()) {
            return $identifier;
        }

        $value = $identifier->getValue();

        if (!$value instanceof ObjectValue) {
            return $identifier;
        }

        $elementName = $value->getObjectProperty();
        $resolvedIdentifier = $identifierCollection->getIdentifier($elementName);

        if ($resolvedIdentifier instanceof ElementIdentifierInterface) {
            return $resolvedIdentifier;
        }

        throw new UnknownElementException($elementName);
    }
}
