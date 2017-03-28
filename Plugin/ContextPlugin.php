<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2017 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\RetailerOffer\Plugin;

use Smile\StoreLocator\CustomerData\CurrentStore;
use Smile\RetailerOffer\Helper\Settings as SettingsHelper;

/**
 * Plugin to ensure the context properly vary according to currently selected (or not) retailer.
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class ContextPlugin
{
    /**
     * @var \Smile\StoreLocator\CustomerData\CurrentStore
     */
    private $currentStore;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var \Smile\RetailerOffer\Helper\Settings
     */
    private $settingsHelper;

    /**
     * @param \Magento\Framework\App\Http\Context $httpContext    HTTP Context
     * @param CurrentStore                        $currentStore   The Current Store
     * @param SettingsHelper                      $settingsHelper RetailerOffer Settings Helper
     */
    public function __construct(
        \Magento\Framework\App\Http\Context $httpContext,
        CurrentStore $currentStore,
        SettingsHelper $settingsHelper
    ) {
        $this->currentStore  = $currentStore;
        $this->httpContext   = $httpContext;
        $this->settingsHelper = $settingsHelper;
    }

    /**
     * Ensure proper vary on frontend according to current Retailer Id (if any)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     *
     * @param \Magento\Framework\App\ActionInterface  $subject The Action
     * @param \Closure                                $proceed The \Magento\Framework\App\Action\Action::dispatch() method
     * @param \Magento\Framework\App\RequestInterface $request HTTP Request
     *
     * @return mixed
     */
    public function aroundDispatch(
        \Magento\Framework\App\ActionInterface $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {

        if ($this->settingsHelper->isDriveMode()) {
            $retailerId = false;
            if ($this->currentStore->getRetailer() && $this->currentStore->getRetailer()->getId()) {
                $retailerId = $this->currentStore->getRetailer()->getId();
            }

            $this->httpContext->setValue(
                CurrentStore::CONTEXT_RETAILER,
                $retailerId,
                false
            );
        }

        return $proceed($request);
    }
}
