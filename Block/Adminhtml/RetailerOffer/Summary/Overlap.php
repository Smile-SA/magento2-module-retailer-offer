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
use Magento\Framework\Registry;
use Smile\Offer\Api\Data\OfferInterface;
use Smile\RetailerOffer\Block\Adminhtml\RetailerOffer\Summary;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;

/**
 * Panel to display retailer's summary in the offer edit form
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Overlap extends Summary
{
    /**
     * @var string
     */
    protected $_template = 'retailer-offer/summary/overlap.phtml';

    /**
     * @var null|OfferInterface[]
     */
    private $overlappingOffers = null;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    private $priceHelper;

    /**
     * Overlap constructor.
     *
     * @param Context     $context     Application Context
     * @param Registry    $registry    Application Registry
     * @param PriceHelper $priceHelper Price Helper
     * @param array       $data        Block Data
     */
    public function __construct(Context $context, Registry $registry, PriceHelper $priceHelper, array $data = [])
    {
        $this->priceHelper = $priceHelper;
        parent::__construct($context, $registry, $data);
    }

    /**
     * Get current offer
     *
     * @return OfferInterface[]
     */
    public function getOverlapOffers()
    {
        $offer = $this->getRetailerOffer();

        if (null === $this->overlappingOffers) {
            $this->overlappingOffers = $offer->getOverlapOffers();
        }

        return $this->overlappingOffers;
    }

    /**
     * Convert and format price value for current application store
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag) Inherited from Price Helper
     *
     * @param float $value            The value
     * @param bool  $format           If should append format
     * @param bool  $includeContainer If should include container
     *
     * @return string
     */
    public function formatPrice($value, $format = true, $includeContainer = true)
    {
        return $this->priceHelper->currency($value, $format, $includeContainer);
    }

    /**
     * Retrieve Edit Url for a given Offer
     *
     * @param \Smile\Offer\Api\Data\OfferInterface $offer The offer
     *
     * @return string
     */
    public function getEditUrl(OfferInterface $offer)
    {
        return $this->getUrl("smile_retailer/offer/edit", ["offer_id" => $offer->getId()]);
    }

    /**
     * Prepare Layout. Overridden to prevent triggering the parent one.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName) Method is inherited
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        return $this;
    }
}
