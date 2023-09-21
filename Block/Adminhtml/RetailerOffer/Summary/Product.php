<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Block\Adminhtml\RetailerOffer\Summary;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\Product\Attribute\Source\Status as StatusSource;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Registry;
use Smile\RetailerOffer\Block\Adminhtml\RetailerOffer\Summary;

/**
 * Panel to display product's summary in the offer edit form.
 */
class Product extends Summary
{
    // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
    protected $_template = 'retailer-offer/summary/product.phtml';

    public function __construct(
        Context $context,
        Registry $registry,
        private ProductRepositoryInterface $productRepository,
        private ProductHelper $productHelper,
        private PriceHelper $priceHelper,
        private StatusSource $statusSource,
        array $data = []
    ) {
        parent::__construct($context, $registry, $data);
    }

    /**
     * Get current Product : product of the current offer.
     */
    public function getProduct(): ?ProductInterface
    {
        $product = null;
        $offer = $this->getRetailerOffer();

        if ($offer && $offer->getProductId()) {
            $product = $this->productRepository->getById((int) $offer->getProductId());
        }

        return $product;
    }

    /**
     * Retrieve Product Small Image.
     */
    public function getProductImage(): ?string
    {
        $image = null;
        /** @var ?ProductModel $product */
        $product = $this->getProduct();

        if ($product) {
            $image = (string) $this->productHelper->getSmallImageUrl($product);
        }

        return $image;
    }

    /**
     * Retrieve Product Price
     */
    public function getProductPrice(): ?string
    {
        $price = null;
        /** @var ?ProductModel $product */
        $product = $this->getProduct();

        if ($product) {
            $price = (string) $this->priceHelper->currency($product->getPrice());
        }

        return $price;
    }

    /**
     * Retrieve Product Special Price
     */
    public function getProductSpecialPrice(): ?string
    {
        $price = null;
        /** @var ?ProductModel $product */
        $product = $this->getProduct();

        if ($product) {
            $price = (string) $this->priceHelper->currency($product->getSpecialPrice());
        }

        return $price;
    }

    /**
     * Retrieve Product Special Price.
     */
    public function getProductStockLabel(): ?Phrase
    {
        $label = __('In stock');
        /** @var ProductModel $product */
        $product = $this->getProduct();

        if (!$product->isAvailable()) {
            $label = __('Out of stock');
        }

        return $label;
    }

    /**
     * Retrieve Product Status Label.
     */
    public function getProductStatusLabel(): Phrase|string
    {
        return $this->statusSource->getOptionText((string) $this->getProduct()->getStatus());
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        return $this;
    }
}
