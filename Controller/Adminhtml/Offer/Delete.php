<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Controller\Adminhtml\Offer;

use Exception;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Smile\RetailerOffer\Controller\Adminhtml\AbstractOffer;

/**
 * Delete Controller for Offer.
 */
class Delete extends AbstractOffer implements HttpGetActionInterface
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $identifier = $this->getRequest()->getParam('offer_id', false);
        $model = $this->offerFactory->create();
        if ($identifier) {
            $model = $this->offerRepository->getById((int) $identifier);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This offer no longer exists.'));

                return $resultRedirect->setPath('*/*/index');
            }
        }

        try {
            $this->offerRepository->delete($model);
            $this->messageManager->addSuccessMessage(__('You deleted the offer %1.', $model->getId()));

            return $resultRedirect->setPath('*/*/index');
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());

            return $resultRedirect->setPath('*/*/edit', ['offer_id' => $this->getRequest()->getParam('offer_id')]);
        }
    }
}
