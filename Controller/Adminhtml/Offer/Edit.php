<?php

namespace Smile\RetailerOffer\Controller\Adminhtml\Offer;

use Magento\Framework\Exception\NoSuchEntityException;
use Smile\Offer\Api\Data\OfferInterface;
use Smile\RetailerOffer\Controller\Adminhtml\AbstractOffer;

/**
 * Retailer Offer Adminhtml Edit controller.
 */
class Edit extends AbstractOffer
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $offerId = (int) $this->getRequest()->getParam(OfferInterface::OFFER_ID);
        $isExistingOffer = (bool) $offerId;

        if ($isExistingOffer) {
            try {
                $offer = $this->offerRepository->getById($offerId);
                $this->coreRegistry->register('current_offer', $offer);

                $resultPage = $this->createPage();
                $resultPage->getConfig()->getTitle()->prepend(
                    __('Edit Offer %1 ', $offer->getId())
                );
                $resultPage->addBreadcrumb(__('Offer'), __('Offer'));

                return $resultPage;
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while editing the offer.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('*/*/index');

                return $resultRedirect;
            }
        }

        $this->_forward('create');
    }
}
