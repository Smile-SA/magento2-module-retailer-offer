<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\RetailerOffer\Controller\Adminhtml\Offer;

use Smile\RetailerOffer\Controller\Adminhtml\AbstractOffer;

/**
 * Retailer Offer Adminhtml Save controller.
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Save extends AbstractOffer
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $data         = $this->getRequest()->getPostValue();
        $redirectBack = $this->getRequest()->getParam('back', false);

        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/rorua.log');
        $logger = new \Zend\Log\Logger();

        if ($data) {
            $identifier = $this->getRequest()->getParam('offer_id');
            $model = $this->offerFactory->create();

            if ($identifier) {
                $model->load($identifier);
                if (!$model->getId()) {
                    $this->messageManager->addErrorMessage(__('This offer no longer exists.'));

                    return $resultRedirect->setPath('*/*/');
                }
            }

            $logger->addWriter($writer);
            $logger->info(print_r($data, true));

            $model->setData($data);

            try {
                $this->offerRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the offer %1.', $model->getId()));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

                if ($redirectBack) {
                    return $resultRedirect->setPath('*/*/edit', ['offer_id' => $model->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);

                $returnParams = [
                    'id'   => $this->getRequest()->getParam('offer_id'),
                    'type' => $this->getRequest()->getParam('type'),
                ];

                return $resultRedirect->setPath('*/*/edit', $returnParams);
            }
        }

        return $resultRedirect->setPath('*/*/');
    }
}
