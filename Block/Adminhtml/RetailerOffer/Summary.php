<?php

namespace Smile\RetailerOffer\Block\Adminhtml\RetailerOffer;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\BlockInterface;
use Smile\Offer\Api\Data\OfferInterface;

/**
 * Panel to display offer's summary in the offer edit form.
 * Offer summary is a reminder for:
 *
 * - related product id
 * - related retailer
 */
class Summary extends Template
{
    // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
    protected $_template = 'retailer-offer/summary.phtml';

    public function __construct(
        Context $context,
        private Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get current offer.
     */
    public function getRetailerOffer(): ?OfferInterface
    {
        $offer = $this->registry->registry('current_offer');

        if (null !== $offer && $offer->getId()) {
            return $offer;
        }

        return null;
    }

    /**
     * Retrieve Product Summary Block.
     *
     * @throws LocalizedException
     */
    public function getProductSummaryBlock(): BlockInterface
    {
        return $this->getLayout()->createBlock(self::class . '\Product', "product.summary");
    }

    /**
     * Retrieve Retailer Summary Block.
     *
     * @throws LocalizedException
     */
    public function getRetailerSummaryBlock(): BlockInterface
    {
        return $this->getLayout()->createBlock(self::class . '\Retailer', "retailer.summary");
    }
}
