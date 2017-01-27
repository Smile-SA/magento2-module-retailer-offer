<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Plugin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\State;
use Smile\Offer\Api\Data\OfferInterface;
use Smile\StoreLocator\CustomerData\CurrentStore;
use Smile\RetailerOffer\Helper\Offer as OfferHelper;
use Smile\RetailerOffer\Helper\Settings;

/**
 * Abstract plugin, contains common methods
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class AbstractPlugin
{
    /**
     * @var OfferHelper
     */
    private $helper;

    /**
     * @var CurrentStore
     */
    private $currentStore;

    /**
     * @var \Smile\RetailerOffer\Helper\Settings
     */
    protected $settingsHelper;

    /**
     * ProductPlugin constructor.
     *
     * @param OfferHelper  $offerHelper    The offer Helper
     * @param CurrentStore $currentStore   The Retailer Data Object
     * @param State        $state          The Application State
     * @param Settings     $settingsHelper Settings Helper
     */
    public function __construct(OfferHelper $offerHelper, CurrentStore $currentStore, State $state, Settings $settingsHelper)
    {
        $this->currentStore   = $currentStore;
        $this->helper         = $offerHelper;
        $this->state          = $state;
        $this->settingsHelper = $settingsHelper;
    }

    /**
     * Retrieve Current Offer for the product.
     *
     * @param ProductInterface $product The product
     *
     * @return OfferInterface
     */
    protected function getCurrentOffer($product)
    {
        $offer      = null;
        $retailerId = $this->getRetailerId();

        if ($retailerId) {
            $offer = $this->helper->getOffer($product, $retailerId);
        }

        return $offer;
    }

    /**
     * Retrieve currently chosen retailer id
     *
     * @return int|null
     */
    protected function getRetailerId()
    {
        $retailerId = null;
        if ($this->getRetailer()) {
            $retailerId = $this->currentStore->getRetailer()->getId();
        }

        return $retailerId;
    }

    /**
     * Retrieve current retailer
     *
     * @return null|\Smile\Retailer\Api\Data\RetailerInterface
     */
    protected function getRetailer()
    {
        $retailer = null;
        if ($this->currentStore->getRetailer() && $this->currentStore->getRetailer()->getId()) {
            $retailer = $this->currentStore->getRetailer();
        }

        return $retailer;
    }
}
