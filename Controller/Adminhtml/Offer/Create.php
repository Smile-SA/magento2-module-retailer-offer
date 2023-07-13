<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Controller\Adminhtml\Offer;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Smile\RetailerOffer\Controller\Adminhtml\AbstractOffer;

/**
 * Retailer Offer Creation Controller.
 */
class Create extends AbstractOffer implements HttpGetActionInterface
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->coreRegistry->register("current_offer", $this->offerFactory->create([]));

        $resultPage = $this->createPage();

        $resultPage->setActiveMenu('Smile_Seller::retailer_offers');
        $resultPage->getConfig()->getTitle()->prepend(__('New Retailer Offer'));

        return $resultPage;
    }
}
