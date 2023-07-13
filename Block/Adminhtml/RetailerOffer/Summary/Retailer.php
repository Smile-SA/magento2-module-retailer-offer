<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Block\Adminhtml\RetailerOffer\Summary;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Smile\Retailer\Api\Data\RetailerInterface;
use Smile\Retailer\Api\RetailerRepositoryInterface;
use Smile\RetailerOffer\Block\Adminhtml\RetailerOffer\Summary;
use Smile\Seller\Api\Data\SellerInterface;

/**
 * Panel to display retailer's summary in the offer edit form.
 */
class Retailer extends Summary
{
    // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
    protected $_template = 'retailer-offer/summary/retailer.phtml';

    public function __construct(
        Context $context,
        Registry $registry,
        private RetailerRepositoryInterface $retailerRepository,
        array $data = []
    ) {
        parent::__construct($context, $registry, $data);
    }

    /**
     * Get current retailer : retailer of the current offer.
     */
    public function getRetailer(): RetailerInterface|SellerInterface|null
    {
        $retailer = null;
        $offer = $this->getRetailerOffer();

        if ($offer && $offer->getSellerId()) {
            $retailer = $this->retailerRepository->get((int) $offer->getSellerId());
        }

        return $retailer;
    }

    /**
     * Retrieve Product Status Label.
     */
    public function getRetailerStatusLabel(): Phrase|string
    {
        $statusesLabels = [0 => __('Inactive'), 1 => __('Active')];

        return $statusesLabels[(int) $this->getRetailer()->getIsActive()] ?? '';
    }

    /**
     * @inheritdoc
     */
    protected function _prepareLayout()
    {
        return $this;
    }
}
