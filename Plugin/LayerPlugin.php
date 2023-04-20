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

use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection;
use Smile\RetailerOffer\Api\CollectionProcessorInterface;

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
     * @var CollectionProcessorInterface
     */
    private CollectionProcessorInterface $collectionProcessor;

    /**
     * LayerPlugin constructor.
     *
     * @param CollectionProcessorInterface $collectionProcessor Collection Processor
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * {@inheritDoc}
     */
    public function beforePrepareProductCollection(
        Layer $layer,
        AbstractCollection $collection
    ): void {
        $this->collectionProcessor->applyStoreSortOrders($collection);
    }
}
