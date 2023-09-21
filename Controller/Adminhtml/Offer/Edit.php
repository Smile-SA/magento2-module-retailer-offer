<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Controller\Adminhtml\Offer;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Forward;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Smile\Offer\Api\Data\OfferInterface;
use Smile\RetailerOffer\Controller\Adminhtml\AbstractOffer;

/**
 * Retailer Offer Adminhtml Edit controller.
 */
class Edit extends AbstractOffer implements HttpGetActionInterface
{
    /**
     * @inheritdoc
     * @return Page|ResponseInterface|Redirect|ResultInterface|void
     * @throws LocalizedException
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

        /** @var Forward $redirect */
        $redirect = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        $redirect->forward('create');

        return $redirect;
    }
}
