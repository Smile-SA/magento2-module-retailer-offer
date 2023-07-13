<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection;
use Smile\ElasticsuiteCore\Search\Request\Query\QueryFactory;
use Smile\Retailer\Api\Data\RetailerInterface;
use Smile\RetailerOffer\Api\CollectionProcessorInterface;
use Smile\RetailerOffer\Helper\Settings;
use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Collection Processor.
 * Used to filter product collection according current store configuration.
 * Also used to build proper sort orders for collection according to offers data.
 */
class CollectionProcessor implements CollectionProcessorInterface
{
    public function __construct(
        private CurrentStore $currentStore,
        protected Settings $settingsHelper,
        protected QueryFactory $queryFactory,
        protected ScopeConfigInterface $scopeConfig
    ) {
    }

    /**
     * @inheritdoc
     */
    public function applyStoreSortOrders(Collection $collection): void
    {
        if ($this->settingsHelper->isDriveMode()) {
            $retailerId = $this->getRetailerId();
            if ($retailerId) {
                $collection->addSortFilterParameters(
                    'price',
                    'offer.price',
                    'offer',
                    ['offer.seller_id' => $retailerId]
                );
            }
        }
    }

    /**
     * Retrieve currently chosen retailer id
     */
    private function getRetailerId(): ?int
    {
        $retailerId = null;
        if ($this->getRetailer()) {
            $retailerId = (int) $this->getRetailer()->getId();
        }

        return $retailerId;
    }

    /**
     * Retrieve current retailer.
     */
    private function getRetailer(): ?RetailerInterface
    {
        $retailer = null;
        if ($this->currentStore->getRetailer() && $this->currentStore->getRetailer()->getId()) {
            /** @var RetailerInterface $retailer */
            $retailer = $this->currentStore->getRetailer();
        }

        return $retailer;
    }
}
