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
use Magento\Framework\App\State;
use Smile\RetailerOffer\Helper\Offer as OfferHelper;
use Smile\RetailerOffer\Helper\Settings;
use Smile\StoreLocator\CustomerData\CurrentStore;

/**
 * Using the Layer to retrieve Category Product Count if browsing for a given retailer/date
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class CategoryPlugin extends AbstractPlugin
{
    /**
     * @var Resolver
     */
    private $layerResolver;

    /**
     * LayerPlugin constructor.
     *
     * @param OfferHelper  $offerHelper    The offer Helper
     * @param CurrentStore $currentStore   The Retailer Data Object
     * @param State        $state          The Application State
     * @param Settings     $settingsHelper Settings Helper
     * @param Resolver     $layerResolver  Layer Resolver
     */
    public function __construct(
        OfferHelper $offerHelper,
        CurrentStore $currentStore,
        State $state,
        Settings $settingsHelper,
        Resolver $layerResolver
    ) {
        parent::__construct($offerHelper, $currentStore, $state, $settingsHelper);
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
        if (!$this->getRetailerId()) {
            return $proceed();
        }

        $layer = $this->layerResolver->get();

        return $layer->setCurrentCategory($category)->getProductCollection()->getSize();
    }
}
