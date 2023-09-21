<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Model\Product\Search\Request\Container\Filter;

use RuntimeException;
use Smile\ElasticsuiteCore\Api\Search\Request\Container\FilterInterface;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\ElasticsuiteCore\Search\Request\QueryInterface;
use Smile\RetailerOffer\Helper\Settings;
use Smile\StoreLocator\CustomerData\CurrentStore as CustomerCurrentStore;

/**
 * Class CurrentStore
 * Append the offer filter in the elasticsearch request if the drive mode is enabled.
 */
class CurrentStore implements FilterInterface
{
    public function __construct(
        private QueryFactory $queryFactory,
        private CustomerCurrentStore $currentStore,
        private Settings $settingsHelper,
        private array $retailerStockFilters = []
    ) {
    }

    /**
     * Append offer filter if the drive mode is enabled.
     */
    public function getFilterQuery(): ?QueryInterface
    {
        $retailer = $this->currentStore->getRetailer();
        if (!$this->settingsHelper->isDriveMode() || !$retailer) {
            return null;
        }

        $sellerIdFilter = $this->queryFactory->create(
            QueryInterface::TYPE_TERM,
            ['field' => 'offer.seller_id', 'value' => $retailer->getId()]
        );
        $mustClause = ['must' => [$sellerIdFilter]];

        // If out of stock products must be shown, just keep filter on product having an offer for current
        // retailer, wether the offer is available or not.
        if (false === $this->settingsHelper->isEnabledShowOutOfStock()) {
            foreach ($this->retailerStockFilters as $retailerStockFilter) {
                if (!$retailerStockFilter instanceof FilterInterface) {
                    throw new RuntimeException('The stock filter is not an FilterInterface');
                }
                $currentStockFilter = $retailerStockFilter->getFilterQuery();
                if ($currentStockFilter !== null) {
                    $mustClause['must'][] = $currentStockFilter;
                }
            }
        }

        $boolFilter = $this->queryFactory->create(QueryInterface::TYPE_BOOL, $mustClause);

        return $this->queryFactory->create(
            QueryInterface::TYPE_NESTED,
            ['path' => 'offer', 'query' => $boolFilter]
        );
    }
}
