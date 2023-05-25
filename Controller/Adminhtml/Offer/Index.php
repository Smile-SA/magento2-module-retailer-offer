<?php

namespace Smile\RetailerOffer\Controller\Adminhtml\Offer;

use Magento\Backend\Model\View\Result\Page;
use Smile\RetailerOffer\Controller\Adminhtml\AbstractOffer;

/**
 * Abstract Controller for retailer offer management.
 */
class Index extends AbstractOffer
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->createPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Retailer Offers List'));

        return $resultPage;
    }
}
