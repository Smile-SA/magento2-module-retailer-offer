<?php

namespace Smile\RetailerOffer\Ui\Component\Offer\Listing;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFieldToCollectionInterface;

/**
 * Add field strategy for Product SKU Field.
 */
class AddProductNameFieldToCollection implements AddFieldToCollectionInterface
{
    private const MAGENTO_EE_EDITION_NAME = 'B2B';

    public function __construct(private ProductMetadataInterface $productMetadata)
    {
    }

    /**
     * @inheritdoc
     */
    public function addField(Collection $collection, $field, $alias = null)
    {
        $entityType = ProductInterface::class;
        $skuField = Product::SKU;
        $join = null;

        if ($this->productMetadata->getEdition() == self::MAGENTO_EE_EDITION_NAME) {
            $foreignKey = $collection->getForeignKeyByEntityType($entityType);
            $join['name'] = ['catalog_product_entity' => 'catalog_product_entity'];
            $join['cond'] = "catalog_product_entity.entity_id = main_table.$foreignKey";
            $join['cols'] = ['catalog_product_entity.row_id', $skuField];
            $join['foreignKeyCondition'] = "catalog_product_entity.row_id";
        }

        $collection->addEntityAttributeToSelect(
            $entityType,
            'name',
            $field,
            null,
            $join
        );
    }
}
