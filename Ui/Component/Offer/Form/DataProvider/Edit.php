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

namespace Smile\RetailerOffer\Ui\Component\Offer\Form\DataProvider;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Smile\Offer\Api\Data\OfferInterface;
use Smile\Offer\Api\OfferRepositoryInterface;

/**
 * Data Provider for Retailer Offer Edit Form
 *
 * @category Smile
 * @package  Smile\RetailerOffer
 * @author   Romain Ruaud <romain.ruaud@smile.fr>
 */
class Edit extends AbstractDataProvider
{
    /**
     * @var array
     */
    private array $loadedData;

    /**
     * @var Registry
     */
    private Registry $registry;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var OfferRepositoryInterface
     */
    private OfferRepositoryInterface $offerRepository;

    /**
     * DataProvider constructor.
     *
     * @param string                   $name              The name
     * @param string                   $primaryFieldName  Primary field Name
     * @param string                   $requestFieldName  Request field Name
     * @param mixed                    $collectionFactory The collection factory
     * @param Registry                 $registry          The Registry
     * @param RequestInterface         $request           The Request
     * @param OfferRepositoryInterface $offerRepository   The Offer Repository
     * @param array                    $meta              Component Meta
     * @param array                    $data              Component Data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        mixed $collectionFactory,
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
    public function getData(): array
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
     * @return OfferInterface
     * @throws NoSuchEntityException
     */
    private function getCurrentOffer(): OfferInterface
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
