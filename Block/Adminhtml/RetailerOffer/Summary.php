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
namespace Smile\RetailerOffer\Block\Adminhtml\RetailerOffer;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as StatusSource;
use Magento\Framework\Registry;
use Smile\Offer\Api\Data\OfferInterface;
use Smile\Retailer\Api\Data\RetailerInterface;
use Smile\Retailer\Api\RetailerRepositoryInterface;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

/**
 * Panel to display offer's summary in the offer edit form.
 * Offer summary is a reminder for :
 *
 *  - concerned product id
 *  - concerned retailer
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Summary extends Template
{
    /**
     * @var string
     */
    protected $_template = 'retailer-offer/summary.phtml';

    /**
     * Registry
     *
     * @var Registry
     */
    private $registry;

    /**
     * Retailer Repository
     *
     * @var RetailerRepositoryInterface
     */
    private $retailerRepository;

    /**
     * Product Repository
     *
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    private $productHelper;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $priceHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    private $statusSourceModel;

    /**
     * Summary constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context            Application context
     * @param \Magento\Framework\Registry             $registry           Application registry
     * @param RetailerRepositoryInterface             $retailerRepository Retailer Repository
     * @param ProductRepositoryInterface              $productRepository  Product Repository
     * @param ProductHelper                           $productHelper      Product Helper
     * @param PriceHelper                             $priceHelper        Price Helper
     * @param StatusSource                            $statusSource       Source Model for product's status.
     * @param array                                   $data               Block's data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        RetailerRepositoryInterface $retailerRepository,
        ProductRepositoryInterface $productRepository,
        ProductHelper $productHelper,
        PriceHelper $priceHelper,
        StatusSource $statusSource,
        array $data = []
    ) {
        $this->registry           = $registry;
        $this->productRepository  = $productRepository;
        $this->retailerRepository = $retailerRepository;
        $this->productHelper      = $productHelper;
        $this->priceHelper        = $priceHelper;
        $this->statusSourceModel  = $statusSource;

        parent::__construct($context, $data);
    }

    /**
     * Get current offer
     *
     * @return OfferInterface
     */
    public function getRetailerOffer()
    {
        $offer = $this->registry->registry('current_offer');
        if (null !== $offer && $offer->getId()) {
            return $offer;
        }

        return null;
    }

    /**
     * Get current retailer : retailer of the current offer.
     *
     * @return RetailerInterface
     */
    public function getRetailer()
    {
        $retailer = null;

        if ($offer = $this->getRetailerOffer()) {
            if ($offer->getSellerId()) {
                $retailer = $this->retailerRepository->get((int) $offer->getSellerId());
            }
        }

        return $retailer;
    }

    /**
     * Get current Product : product of the current offer.
     *
     * @return ProductInterface
     */
    public function getProduct()
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
    public function getProductImage()
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
    public function getProductPrice()
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
    public function getProductSpecialPrice()
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
    public function getProductStockLabel()
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
    public function getProductStatusLabel()
    {
        return $this->statusSourceModel->getOptionText((int) $this->getProduct()->getStatus());
    }

    /**
     * Retrieve Product Status Label
     *
     * @return string
     */
    public function getRetailerStatusLabel()
    {
        $statusesLabels = [0 => __("Inactive"), 1 => __("Active")];

        return isset($statusesLabels[(int) $this->getRetailer()->getIsActive()]) ? $statusesLabels[(int) $this->getRetailer()->getIsActive()] : "";
    }
}
