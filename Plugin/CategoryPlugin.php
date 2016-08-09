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

use Magento\Catalog\Model\Layer\Resolver;
use Smile\Retailer\CustomerData\RetailerData;

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
     * @var RetailerData
     */
    private $retailerData;

    /**
     * @var Resolver
     */
    private $layerResolver;

    /**
     * LayerPlugin constructor.
     *
     * @param RetailerData $retailerData  The retailer Data object
     * @param Resolver     $layerResolver Layer Resolver
     */
    public function __construct(RetailerData $retailerData, Resolver $layerResolver)
    {
        $this->retailerData  = $retailerData;
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
        if (!$this->retailerData->getRetailerId() || !$this->retailerData->getPickupDate()) {
            return $proceed();
        }

        $layer = $this->layerResolver->get();

        return $layer->setCurrentCategory($category)->getProductCollection()->getSize();
    }
}
