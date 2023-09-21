<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Controller\Adminhtml\Offer;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Smile\RetailerOffer\Controller\Adminhtml\AbstractOffer;

/**
 * Abstract Controller for retailer offer management.
 */
class Index extends AbstractOffer implements HttpGetActionInterface
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
