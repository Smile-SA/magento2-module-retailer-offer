<?php

namespace Smile\RetailerOffer\Ui\Component\Offer\Listing;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

/**
 * Add filter strategy for Product SKU Field.
 */
class AddProductSkuFilterToCollection implements AddFilterToCollectionInterface
{
    /**
     * @inheritdoc
     */
    public function addFilter(Collection $collection, $field, $condition = null)
    {
        $collection->setSkuFilter($condition);
    }
}
