<?php

declare(strict_types=1);

/**
 * Acquired.com Payments Integration for Magento2
 *
 * Copyright (c) 2024 Acquired Limited (https://acquired.com/)
 *
 * This file is open source under the MIT license.
 * Please see LICENSE file for more details.
 */

namespace Acquired\Payments\Model\Api;

use Acquired\Payments\Api\MultishippingRepositoryInterface;
use Acquired\Payments\Model\MultishippingFactory;
use Acquired\Payments\Service\MultishippingService;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Webapi\Rest\Request;
use Acquired\Payments\Ui\Method\CardProvider;
use Acquired\Payments\Ui\Method\PayByBankProvider;


class CreateMultiShipping
{
    /**
     * @param CheckoutSession $checkoutSession
     * @param MultishippingFactory $multishippingFactory
     * @param MultishippingRepositoryInterface $multishippingRepository
     * @param Request $request
     * @param CustomerSession $customerSession
     */
    public function __construct(
        private readonly CheckoutSession $checkoutSession,
        private readonly MultishippingFactory $multishippingFactory,
        private readonly MultishippingRepositoryInterface $multishippingRepository,
        private readonly Request $request,
        private readonly HttpContext $httpContext,
        private readonly CustomerSessionFactory $customerSessionFactory,
        private readonly MultishippingService $multishippingService
    ) {
    }

    /**
     * Initialize multishipping
     *
     * @return bool
     * @throws LocalizedException
     */
    public function execute()
    {
        $session = $this->customerSessionFactory->create();
        if ($this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH) || $session->isLoggedIn()) {
            $quote  = $this->checkoutSession->getQuote();
            $quotePayment = $quote->getPayment();

            if(!$quote->getId() || count($quote->getAllItems()) == 0) {
                throw new LocalizedException(__('Cart is empty'));
            }

            $body = $this->request->getBodyParams();

            if (isset($body['sessionId']) && isset($body['transactionId'])) {
                $reservedIds = $this->multishippingService->reserveOrderIds($quote, $body['sessionId'], $body['transactionId']);

                if(count($reservedIds) == 0) {
                    throw new LocalizedException(__('Invalid request: %1', $quote->getId()) );
                }

                $this->checkoutSession->setMultishippingTransactionId($body['transactionId']);
                $quotePayment->setAdditionalInformation([
                    'transaction_id' => $body['transactionId'],
                    'session_id' => $body['sessionId'],
                    'order_id' => $body['orderId'],
                    'timestamp' => $body['timestamp'],
                    'hash' => isset($body['hash']) ? $body['hash'] : null,
                    'multishipping' => true
                ]);
                $quotePayment->save();

                return true;
            } else {
                if($quotePayment->getMethod() == CardProvider::CODE) {
                    throw new LocalizedException( __('Invalid request') );
                }
                if($quotePayment->getMethod() == PayByBankProvider::CODE) {
                    // transaction id is unknown at this point as payment will be processed after order creation
                    $reservedIds = $this->multishippingService->reserveOrderIds($quote, null, 'ACQM-' . $quote->getId());
                    $quotePayment->setAdditionalInformation([
                        'transaction_id' => $body['transactionId'],
                        'session_id' => $body['sessionId'],
                        'order_id' => $body['orderId'],
                        'timestamp' => $body['timestamp'],
                        'hash' => isset($body['hash']) ? $body['hash'] : null,
                        'multishipping' => true
                    ]);
                    $quotePayment->save();
                }
                return true;
            }

        }

        throw new LocalizedException(__('Customer logged out'));
    }
}
