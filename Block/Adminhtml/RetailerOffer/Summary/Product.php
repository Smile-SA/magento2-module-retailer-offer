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
namespace Smile\RetailerOffer\Block\Adminhtml\RetailerOffer\Summary;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Catalog\Model\Product\Attribute\Source\Status as StatusSource;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Registry;
use Smile\Offer\Api\Data\OfferInterface;
use Smile\RetailerOffer\Block\Adminhtml\RetailerOffer\Summary;

/**
 * Panel to display product's summary in the offer edit form
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Product extends Summary
{
    /**
     * @var string
     */
    protected $_template = 'retailer-offer/summary/product.phtml';

    /**
     * Product Repository
     *
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var ProductHelper
     */
    private ProductHelper $productHelper;

    /**
     * @var PriceHelper
     */
    private PriceHelper $priceHelper;

    /**
     * @var StatusSource
     */
    private StatusSource $statusSourceModel;

    /**
     * Summary constructor.
     *
     * @param Context                     $context           Application context
     * @param Registry                    $registry          Application registry
     * @param ProductRepositoryInterface  $productRepository Product Repository
     * @param ProductHelper               $productHelper     Product Helper
     * @param PriceHelper                 $priceHelper       Price Helper
     * @param StatusSource                $statusSource      Source Model for product's status.
     * @param array                       $data              Block's data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProductRepositoryInterface $productRepository,
        ProductHelper $productHelper,
        PriceHelper $priceHelper,
        StatusSource $statusSource,
        array $data = []
    ) {
        $this->productRepository  = $productRepository;
        $this->productHelper      = $productHelper;
        $this->priceHelper        = $priceHelper;
        $this->statusSourceModel  = $statusSource;

        parent::__construct($context, $registry, $data);
    }

    /**
     * Get current Product : product of the current offer.
     *
     * @return ProductInterface
     */
    public function getProduct(): ProductInterface
    {
        $product = null;

        if ($offer = $this->getRetailerOffer()) {
            if ($offer->getProductId()) {
                $product = $this->productRepository->getById((int) $offer->getProductId());
            }
        }

        return $product;
    }

    /**
     * Retrieve Product Small Image
     *
     * @return null|string
     */
    public function getProductImage(): null|string
    {
        $image = null;

        if ($this->getProduct()) {
            $image = $this->productHelper->getSmallImageUrl($this->getProduct());
        }

        return $image;
    }

    /**
     * Retrieve Product Price
     *
     * @return null|string
     */
    public function getProductPrice(): null|string
    {
        $price = null;

        if ($this->getProduct()) {
            $price = $this->priceHelper->currency($this->getProduct()->getPrice());
        }

        return $price;
    }

    /**
     * Retrieve Product Special Price
     *
     * @return null|string
     */
    public function getProductSpecialPrice(): null|string
    {
        $price = null;

        if ($this->getProduct()) {
            $price = $this->priceHelper->currency($this->getProduct()->getSpecialPrice());
        }

        return $price;
    }

    /**
     * Retrieve Product Special Price
     *
     * @return null|string
     */
    public function getProductStockLabel(): null|string
    {
        $label = __('In stock');

        if (!$this->getProduct()->isAvailable()) {
            $label = __('Out of stock');
        }

        return $label;
    }

    /**
     * Retrieve Product Status Label
     *
     * @return string
     */
    public function getProductStatusLabel(): string
    {
        return $this->statusSourceModel->getOptionText((int) $this->getProduct()->getStatus());
    }

    /**
     * Prepare Layout. Overridden to prevent triggering the parent one.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName) Method is inherited
     *
     * @return $this
     */
    protected function _prepareLayout(): self
    {
        return $this;
    }
}
