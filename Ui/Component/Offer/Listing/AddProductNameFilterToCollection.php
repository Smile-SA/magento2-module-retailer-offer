<?php

namespace Smile\RetailerOffer\Ui\Component\Offer\Listing;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

/**
 * Filter strategy for product name.
 */
class AddProductNameFilterToCollection implements AddFilterToCollectionInterface
{
    /**
     * @inheritdoc
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        $collection->addEntityAttributeToSelect(ProductInterface::class, "name", $field);
        $collection->addEntityAttributeFilter(ProductInterface::class, "name", $condition);
    }
}
