<?php

namespace Smile\RetailerOffer\Ui\Component\Offer\Listing;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;

/**
 * Add field strategy for Product SKU Field.
 */
class AddProductSkuFieldToCollection implements AddFieldToCollectionInterface
{
    /**
     * @inheritdoc
     */
    public function addField(Collection $collection, $field, $alias = null)
    {
        $collection->addProductSkuToSelect();
    }
}
