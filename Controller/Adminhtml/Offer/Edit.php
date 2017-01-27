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

use Magento\Framework\Exception\NoSuchEntityException;
use Smile\Offer\Api\Data\OfferInterface;
use Smile\RetailerOffer\Controller\Adminhtml\AbstractOffer;

/**
 * Retailer Offer Adminhtml Edit controller.
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Edit extends AbstractOffer
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $offerId = (int) $this->getRequest()->getParam(OfferInterface::OFFER_ID);

        $offer = null;
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
