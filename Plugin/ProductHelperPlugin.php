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
     * @var \Smile\RetailerOffer\Helper\Settings
     */
    private $settingsHelper;

    /**
     * @var \Smile\RetailerOffer\Helper\Offer
     */
    private $offerHelper;

    /**
     * @var \Magento\Catalog\Api\Data\ProductInterfaceFactory
     */
    private $productFactory;

    /**
     * HelperProductPlugin constructor.
     *
     * @param \Smile\RetailerOffer\Helper\Settings              $settingsHelper          Settings Helper
     * @param \Smile\RetailerOffer\Helper\Offer                 $offerHelper             Offer Helper
     * @param \Magento\Catalog\Api\Data\ProductInterfaceFactory $productInterfaceFactory Product Factory
     */
    public function __construct(
        \Smile\RetailerOffer\Helper\Settings $settingsHelper,
        \Smile\RetailerOffer\Helper\Offer $offerHelper,
        \Magento\Catalog\Api\Data\ProductInterfaceFactory $productInterfaceFactory
    ) {
        $this->settingsHelper = $settingsHelper;
        $this->offerHelper    = $offerHelper;
        $this->productFactory = $productInterfaceFactory;
    }

    /**
     * Prevent accessing product if !available in offer
     *
     * @param \Magento\Catalog\Helper\Product      $productHelper Product helper
     * @param int                                  $productId     Product id
     * @param \Magento\Framework\App\Action\Action $controller    Controller
     * @param \Magento\Framework\DataObject        $params        Params
     *
     * @return array
     * @SuppressWarnings("PMD.UnusedFormalParameter")
     * @throws \Exception
     */
    public function beforeInitProduct(
        \Magento\Catalog\Helper\Product $productHelper,
        $productId,
        $controller,
        $params = null
    ) {
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
