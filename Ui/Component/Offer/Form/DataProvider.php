<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\RetailerOffer
 * @author    Romain Ruaud <romain.ruaud@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */

namespace Smile\RetailerOffer\Ui\Component\Offer\Form;

use Magento\Framework\App\RequestInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\Registry;
use Smile\Offer\Api\OfferRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Data Provider for Retailer Offer Edit Form
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    private $loadedData;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var OfferRepositoryInterface
     */
    private $offerRepository;

    /**
     * DataProvider constructor.
     *
     * @param string                   $name              The name
     * @param string                   $primaryFieldName  Primary field Name
     * @param string                   $requestFieldName  Request field Name
     * @param array                    $collectionFactory The collection factory
     * @param Registry                 $registry          The Registry
     * @param RequestInterface         $request           The Request
     * @param OfferRepositoryInterface $offerRepository   The Offer Repository
     * @param array                    $meta              Component Meta
     * @param array                    $data              Component Data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        $collectionFactory,
        Registry $registry,
        RequestInterface $request,
        OfferRepositoryInterface $offerRepository,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->registry = $registry;
        $this->offerRepository = $offerRepository;
        $this->request = $request;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Retrieve Current data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $offer = $this->getCurrentOffer();

        if ($offer) {
            $offerData = $offer->getData();
            if (!empty($offerData)) {
                $this->loadedData[$offer->getId()] = $offerData;
            }
        }

        return $this->loadedData;
    }

    /**
     * Get current offer
     *
     * @return \Smile\Offer\Api\Data\OfferInterface
     * @throws NoSuchEntityException
     */
    private function getCurrentOffer()
    {
        $offer = $this->registry->registry('current_offer');

        if ($offer) {
            return $offer;
        }

        $requestId = $this->request->getParam($this->requestFieldName);
        if ($requestId) {
            $offer = $this->offerRepository->getById($requestId);
        }

        if (!$offer || !$offer->getId()) {
            $offer = $this->collection->getNewEmptyItem();
        }

        return $offer;
    }
}
