<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Plugin;

/**
 * Plugin to ensure the attributes results in autocomplete are properly filtered amongst current store if needed.
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class AttributesAutocompletePlugin
{
    /**
     * @var \Smile\RetailerOffer\Api\CollectionFilterInterface
     */
    private $collectionFilter;

    /**
     * AttributesAutocompletePlugin constructor.
     *
     * @param \Smile\RetailerOffer\Api\CollectionFilterInterface $collectionFilter Collection Filter
     */
    public function __construct(\Smile\RetailerOffer\Api\CollectionFilterInterface $collectionFilter)
    {
        $this->collectionFilter = $collectionFilter;
    }

    /**
     * Apply Store limitation to autocomplete products results if needed.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param \Smile\ElasticsuiteCatalog\Model\Autocomplete\Product\Attribute\DataProvider $dataProvider Data provider
     * @param \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection   $collection   Product Collection
     */
    public function beforePrepareProductCollection(
        \Smile\ElasticsuiteCatalog\Model\Autocomplete\Product\Attribute\DataProvider $dataProvider,
        \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection $collection
    ) {
        $this->collectionFilter->applyStoreLimitation($collection);
    }
}
