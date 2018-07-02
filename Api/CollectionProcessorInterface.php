<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2018 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Api;

/**
 * Collection Processor : Used to filter product collection according store configuration.
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
interface CollectionProcessorInterface
{
    /**
     * Apply store limitation to a product collection.
     *
     * @param \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection|\Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection Product Collection
     *
     * @return void
     */
    public function applyStoreLimitation($collection);

    /**
     * Apply store sort orders to a product collection.
     *
     * @param \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection|\Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection Product Collection
     *
     * @return void
     */
    public function applyStoreSortOrders($collection);
}
