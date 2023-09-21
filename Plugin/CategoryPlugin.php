<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Plugin;

use Closure;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\ItemCollectionProviderInterface;
use Magento\Catalog\Model\Product\Visibility;
use Smile\ElasticsuiteCatalog\Model\Category\Filter\Provider as FilterProvider;
use Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection;
use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Using the collection and filter provider to retrieve Category Product Count if browsing for a given retailer.
 */
class CategoryPlugin
{
    public function __construct(
        private CurrentStore $currentStore,
        private ItemCollectionProviderInterface $collectionProvider,
        private FilterProvider $filterProvider
    ) {
    }

    /**
     * Use collection and filter provider to retrieve category product count.
     */
    public function aroundGetProductCount(Category $category, Closure $proceed): ?int
    {
        if (!$this->currentStore->getRetailer() || !$this->currentStore->getRetailer()->getId()) {
            return $proceed();
        }

        /** @var Collection $collection */
        $collection = $this->collectionProvider->getCollection($category);
        $collection->setVisibility([Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH]);
        $query = $this->filterProvider->getQueryFilter($category);
        if ($query !== null) {
            $collection->addQueryFilter($query);
        }

        return $collection->getSize();
    }
}
