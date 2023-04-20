<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Smile Elastic Suite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2018 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Plugin;

use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Helper\Product;
use Magento\Framework\App\Action\Action;
use Magento\Framework\DataObject;
use Smile\RetailerOffer\Helper\Offer;
use Smile\RetailerOffer\Helper\Settings;

/**
 * Product Helper Plugin
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class ProductHelperPlugin
{
    /**
     * @var Settings
     */
    private Settings $settingsHelper;

    /**
     * @var Offer
     */
    private Offer $offerHelper;

    /**
     * @var ProductInterfaceFactory
     */
    private ProductInterfaceFactory $productFactory;

    /**
     * HelperProductPlugin constructor.
     *
     * @param Settings                  $settingsHelper          Settings Helper
     * @param Offer                     $offerHelper             Offer Helper
     * @param ProductInterfaceFactory   $productInterfaceFactory Product Factory
     */
    public function __construct(
        Settings $settingsHelper,
        Offer $offerHelper,
        ProductInterfaceFactory $productInterfaceFactory
    ) {
        $this->settingsHelper = $settingsHelper;
        $this->offerHelper    = $offerHelper;
        $this->productFactory = $productInterfaceFactory;
    }

    /**
     * Prevent accessing product if !available in offer
     *
     * @param Product       $productHelper Product helper
     * @param int           $productId     Product id
     * @param Action        $controller    Controller
     * @param ?DataObject   $params        Params
     *
     * @return array
     * @SuppressWarnings("PMD.UnusedFormalParameter")
     * @throws \Exception
     */
    public function beforeInitProduct(
        Product $productHelper,
        int $productId,
        Action $controller,
        ?DataObject $params = null
    ): array {
        if ($this->settingsHelper->isDriveMode() && (false === $this->settingsHelper->isEnabledShowOutOfStock())) {
            $productMock = $this->productFactory->create([])->setId($productId);
            $offer       = $this->offerHelper->getCurrentOffer($productMock);

            if ($offer && !$offer->isAvailable()) {
                $productId = false;
            }
        }

        return [$productId, $controller, $params];
    }
}
