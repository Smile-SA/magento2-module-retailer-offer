<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteCatalog
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Plugin;

/**
 * Add filtering for the current offer to the catalog.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteCatalog
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 */
class LayerPlugin
{
    /**
     * @var \Smile\RetailerOffer\Api\CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * LayerPlugin constructor.
     *
     * @param \Smile\RetailerOffer\Api\CollectionProcessorInterface $collectionProcessor Collection Processor
     */
    public function __construct(\Smile\RetailerOffer\Api\CollectionProcessorInterface $collectionProcessor)
    {
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * {@inheritDoc}
     */
    public function beforePrepareProductCollection(
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection $collection
    ) {
        $this->collectionProcessor->applyStoreSortOrders($collection);
    }
}
