<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Plugin;

/**
 * Using the Layer to retrieve Category Product Count if browsing for a given retailer/date
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class CategoryPlugin
{
    /**
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @var \Smile\RetailerOffer\Helper\Offer
     */
    private $offerHelper;

    /**
     * @var \Smile\StoreLocator\CustomerData\CurrentStore
     */
    private $currentStore;

    /**
     * CategoryPlugin constructor.
     *
     * @param \Smile\RetailerOffer\Helper\Offer             $offerHelper   The offer Helper
     * @param \Smile\StoreLocator\CustomerData\CurrentStore $currentStore  The current Store provider.
     * @param \Magento\Catalog\Model\Layer\Resolver         $layerResolver Layer Resolver
     */
    public function __construct(
        \Smile\RetailerOffer\Helper\Offer $offerHelper,
        \Smile\StoreLocator\CustomerData\CurrentStore $currentStore,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver
    ) {
        $this->offerHelper   = $offerHelper;
        $this->currentStore  = $currentStore;
        $this->layerResolver = $layerResolver;
    }

    /**
     * Use layer to retrieve category product count
     *
     * @param \Magento\Catalog\Model\Category $category The Category
     * @param \Closure                        $proceed  The initial getProductCount() of category object
     *
     * @return int
     */
    public function aroundGetProductCount(\Magento\Catalog\Model\Category $category, \Closure $proceed)
    {
        if (!$this->currentStore->getRetailer() || !$this->currentStore->getRetailer()->getId()) {
            return $proceed();
        }

        $layer = $this->layerResolver->get();

        return $layer->setCurrentCategory($category)->getProductCollection()->getSize();
    }
}
