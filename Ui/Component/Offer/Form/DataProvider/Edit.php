<?php

declare(strict_types=1);

namespace Smile\RetailerOffer\Ui\Component\Offer\Form\DataProvider;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Smile\Offer\Api\Data\OfferInterface;
use Smile\Offer\Api\OfferRepositoryInterface;

/**
 * Data Provider for Retailer Offer Edit Form.
 */
class Edit extends AbstractDataProvider
{
    private array $loadedData;

    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        mixed $collectionFactory,
        private Registry $registry,
        private RequestInterface $request,
        private OfferRepositoryInterface $offerRepository,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @inheritdoc
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
                $this->loadedData[$offer->getOfferId()] = $offerData;
            }
        }

        return $this->loadedData;
    }

    /**
     * Get current offer
     *
     * @throws NoSuchEntityException
     */
    private function getCurrentOffer(): ?OfferInterface
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
