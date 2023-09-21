<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Plugin;

use Exception;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Helper\Product;
use Magento\Framework\App\Action\Action;
use Magento\Framework\DataObject;
use Smile\RetailerOffer\Helper\Offer;
use Smile\RetailerOffer\Helper\Settings;

class ProductHelperPlugin
{
    public function __construct(
        private Settings $settingsHelper,
        private Offer $offerHelper,
        private ProductInterfaceFactory $productFactory
    ) {
    }

    /**
     * Prevent accessing product if !available in offer.
     *
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     * @throws Exception
     */
    public function beforeInitProduct(
        Product $productHelper,
        int $productId,
        Action $controller,
        ?DataObject $params = null
    ): array {
        if ($this->settingsHelper->isDriveMode() && (false === $this->settingsHelper->isEnabledShowOutOfStock())) {
            $productMock = $this->productFactory->create([])->setId($productId);
            $offer = $this->offerHelper->getCurrentOffer($productMock);

            if ($offer && !$offer->isAvailable()) {
                $productId = false;
            }
        }

        return [$productId, $controller, $params];
    }
}
