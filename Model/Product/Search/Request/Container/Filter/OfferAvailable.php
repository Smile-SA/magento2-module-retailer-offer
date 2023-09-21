<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Model\Product\Search\Request\Container\Filter;

use Smile\ElasticsuiteCore\Api\Search\Request\Container\FilterInterface;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;

/**
 * Class CurrentStore
 * Allows you to retrieve the filter on offers available.
 */
class OfferAvailable implements FilterInterface
{
    public function __construct(private QueryFactory $queryFactory)
    {
    }

    /**
     * Return the offer is_available filter.
     */
    public function getFilterQuery(): ?QueryInterface
    {
        return $this->queryFactory->create(
            QueryInterface::TYPE_TERM,
            ['field' => 'offer.is_available', 'value' => true]
        );
    }
}
