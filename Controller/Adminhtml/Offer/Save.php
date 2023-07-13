<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Controller\Adminhtml\Offer;

use Exception;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Smile\Offer\Model\Offer;
use Smile\RetailerOffer\Controller\Adminhtml\AbstractOffer;

/**
 * Retailer Offer Adminhtml Save controller.
 */
class Save extends AbstractOffer implements HttpPostActionInterface
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        /** @var Request $request */
        $request = $this->getRequest();
        $data = $request->getPostValue();
        $redirectBack = $request->getParam('back', false);

        if ($data) {
            $identifier = $request->getParam('offer_id');
            /** @var Offer $model */
            $model = $this->offerFactory->create();

            if ($identifier) {
                $model->load($identifier);
                if (!$model->getId()) {
                    $this->messageManager->addErrorMessage(__('This offer no longer exists.'));

                    return $resultRedirect->setPath('*/*/');
                }
            }

            try {
                $model->loadPost($data);
                $this->_getSession()->setPageData($data);
                $this->offerRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the offer %1.', $model->getId()));
                $this->_objectManager->get(Session::class)->setFormData(false);

                if (
                    $redirectBack
                    || (!is_null($model->getOverlapOffers()) && count($model->getOverlapOffers()))
                ) {
                    return $resultRedirect->setPath('*/*/edit', ['offer_id' => $model->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_objectManager->get(Session::class)->setFormData($data);
                $returnParams = ['offer_id' => $request->getParam('offer_id')];

                return $resultRedirect->setPath('*/*/edit', $returnParams);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
