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
     * Summary constructor.
     *
     * @param \Magento\Backend\Block\Template\Context $context  Application context
     * @param \Magento\Framework\Registry             $registry Application registry
     * @param array                                   $data     Block's data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;

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
     * Retrieve Product Summary Block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getProductSummaryBlock()
    {
        return $this->getLayout()->createBlock(self::class . '\Product', "product.summary");
    }

    /**
     * Retrieve Retailer Summary Block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRetailerSummaryBlock()
    {
        return $this->getLayout()->createBlock(self::class . '\Retailer', "retailer.summary");
    }
}
