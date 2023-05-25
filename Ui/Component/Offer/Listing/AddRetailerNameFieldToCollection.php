<?php

namespace Smile\RetailerOffer\Ui\Component\Offer\Listing;

use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;
use Smile\Retailer\Api\Data\RetailerInterface;

/**
 * Add field strategy for Product SKU Field.
 */
class AddRetailerNameFieldToCollection implements AddFieldToCollectionInterface
{
    /**
     * @inheritdoc
     */
    public function addField(Collection $collection, $field, $alias = null)
    {
        $collection->addEntityAttributeToSelect(RetailerInterface::class, "name", $field);
    }
}
