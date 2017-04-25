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
 * Product Autocomplete Plugin. Filter Autocomplete results according to current Store if needed.
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class ProductAutocompletePlugin extends AbstractPlugin
{
    /**
     * Apply Store limitation to autocomplete products results if needed.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param \Smile\ElasticsuiteCatalog\Model\Autocomplete\Product\Collection\Filter    $filter     Collection Filter
     * @param \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection $collection Product Collection
     */
    public function beforePrepareCollection(
        \Smile\ElasticsuiteCatalog\Model\Autocomplete\Product\Collection\Filter $filter,
        \Smile\ElasticsuiteCatalog\Model\ResourceModel\Product\Fulltext\Collection $collection
    ) {
        $this->applyStoreLimitationToCollection($collection);
    }
}
