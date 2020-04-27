<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @author    Fanny DECLERCK <fadec@smile.fr>
 * @copyright 2020 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Ui\Component\Offer\Listing;

use Magento\Ui\DataProvider\AddFieldToCollectionInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Add field strategy for Product SKU Field
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 * @author   Fanny DECLERCK <fadec@smile.fr>
 */
class AddProductNameFieldToCollection implements AddFieldToCollectionInterface
{
    const MAGENTO_EE_EDITION_NAME = 'B2B';

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * AddProductNameFieldToCollection constructor.
     *
     * @param ProductMetadataInterface $productMetadata  Magento Product metadata
     */
    public function __construct(ProductMetadataInterface $productMetadata)
    {
        $this->productMetadata = $productMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function addField(Collection $collection, $field, $alias = null)
    {
        $entityType = \Magento\Catalog\Api\Data\ProductInterface::class;
        $skuField = \Magento\Catalog\Model\Product::SKU;
        $join = null;

        if ($this->productMetadata->getEdition() == self::MAGENTO_EE_EDITION_NAME) {
            $foreignKey = $collection->getForeignKeyByEntityType($entityType);
            $join['name']                = ['catalog_product_entity' => 'catalog_product_entity'];
            $join['cond']                = "catalog_product_entity.entity_id = main_table.$foreignKey";
            $join['cols']                = ['catalog_product_entity.row_id', $skuField];
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
