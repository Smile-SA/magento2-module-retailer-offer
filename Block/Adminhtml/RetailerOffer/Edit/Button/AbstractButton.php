<?php

namespace Smile\RetailerOffer\Block\Adminhtml\RetailerOffer\Edit\Button;

use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Smile\Offer\Api\Data\OfferInterface;

/**
 * Abstract RetailerOffer edit button.
 */
class AbstractButton implements ButtonProviderInterface
{
    public function __construct(
        protected Context $context,
        protected Registry $registry
    ) {
    }

    /**
     * Generate url by route and parameters.
     */
    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrl($route, $params);
    }

    /**
     * Get current offer.
     */
    public function getRetailerOffer(): OfferInterface
    {
        return $this->registry->registry('current_offer');
    }

    /**
     * @inheritdoc
     */
    public function getButtonData()
    {
        return [];
    }
}
