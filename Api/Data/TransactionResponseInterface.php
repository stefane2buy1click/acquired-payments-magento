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

namespace Acquired\Payments\Api\Data;

interface TransactionResponseInterface
{

    public const TRANSACTION_ID = 'transaction_id';

    public const STATUS = 'status';

    public const REASON = 'reason';

    public const MID = 'mid';

    public const PAYMENT_METHOD = 'payment_method';

    public const TRANSACTION = 'transaction';

    public const CHECK = 'check';

    public const TDS = 'tds';

    public const ISSUER_RESPONSE_CODE = 'issuer_response_code';

    public const AUTHORISATION_CODE = 'authorisation_code';

    public const ACQUIRER_REFERENCE_NUMBER = 'acquirer_reference_number';

    public const SCHEME_REFERENCE_DATA = 'scheme_reference_data';

    public const CARD_ID = 'card_id';

    public const PAYMENT = 'payment';

    public const PAYER = 'payer';

    public const CARD = 'card';

    public const BIN = 'bin';

    public const CUSTOM1 = 'custom1';

    public const CUSTOM2 = 'custom2';

    public const CUSTOMER = 'customer';

    public const CREATED = 'created';

}