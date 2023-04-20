<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @author   Maxime Leclercq <maxime.leclercq@smile.fr>
 * @copyright 2021 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Plugin;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Layer\ItemCollectionProviderInterface;
use Magento\Catalog\Model\Product\Visibility;
use Smile\ElasticsuiteCatalog\Model\Category\Filter\Provider as FilterProvider;
use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Using the collection and filter provider to retrieve Category Product Count if browsing for a given retailer
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 * @author   Maxime Leclercq <maxime.leclercq@smile.fr>
 */
class CategoryPlugin
{
    /**
     * @var CurrentStore
     */
    private CurrentStore $currentStore;

    /**
     * @var ItemCollectionProviderInterface
     */
    private ItemCollectionProviderInterface $collectionProvider;

    /**
     * @var FilterProvider
     */
    private FilterProvider $filterProvider;

    /**
     * CategoryPlugin constructor.
     *
     * @param CurrentStore                      $currentStore           The current Store provider.
     * @param ItemCollectionProviderInterface   $collectionProvider     The category collection provider.
     * @param FilterProvider                    $filterProvider         The category filter provider.
     */
    public function __construct(
        CurrentStore $currentStore,
        ItemCollectionProviderInterface $collectionProvider,
        FilterProvider $filterProvider
    ) {
        $this->currentStore  = $currentStore;
        $this->collectionProvider = $collectionProvider;
        $this->filterProvider = $filterProvider;

    }

    /**
     * Use collection and filter provider to retrieve category product count
     *
     * @param Category $category The Category
     * @param \Closure $proceed  The initial getProductCount() of category object
     *
     * @return int|null
     */
    public function aroundGetProductCount(Category $category, \Closure $proceed): int|null
    {
        if (!$this->currentStore->getRetailer() || !$this->currentStore->getRetailer()->getId()) {
            return $proceed();
        }

        $collection = $this->collectionProvider->getCollection($category);
        $collection->setVisibility([Visibility::VISIBILITY_IN_CATALOG, Visibility::VISIBILITY_BOTH]);
        $query = $this->filterProvider->getQueryFilter($category);
        if ($query !== null) {
            $collection->addQueryFilter($query);
        }

        return $collection->getSize();
    }
}
