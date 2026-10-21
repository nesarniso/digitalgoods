<?php

use App\Domain\Payments\Gateways\BankTransferGateway;
use App\Domain\Payments\Gateways\CashOnDeliveryGateway;
use App\Domain\Payments\Gateways\ManualMobileGateway;

return [

    'manual_account_types' => [
        'personal' => 'Personal',
        'merchant' => 'Merchant',
        'agent' => 'Agent',
    ],

    'manual_default_instruction' => 'Send Money to this :account_type number. Then enter your payer mobile number and transaction ID below. Never share your PIN or OTP.',

    // A registry of what payment methods checkout can offer AND which
    // concrete PaymentGatewayInterface implementation backs each one.
    // `enabled` gates both checkout method selection (Phase 9) and
    // whether PaymentService will resolve a gateway for it at all —
    // flipping a method on without a working `gateway` class behind it
    // would silently break payment at the last step, not fail loudly
    // where the problem is obvious. Stripe/SSLCommerz/bKash/Nagad have no
    // `gateway` class yet — see Domain/Payments/README.md for why.
    'methods' => [
        'cash_on_delivery' => [
            'label' => 'Cash on Delivery',
            'enabled' => true,
            'gateway' => CashOnDeliveryGateway::class,
        ],
        'bank_transfer' => [
            'label' => 'Bank Transfer',
            'enabled' => true,
            'gateway' => BankTransferGateway::class,
            'bank_setting_prefix' => 'payments.bank_transfer',
            'instructions_setting' => 'payments.bank_transfer.instructions',
            'qr_setting' => 'payments.bank_transfer.qr_code',
        ],
        'stripe' => ['label' => 'Credit/Debit Card (Stripe)', 'enabled' => false, 'gateway' => null],
        'sslcommerz' => ['label' => 'SSLCommerz', 'enabled' => false, 'gateway' => null],
        'bkash' => ['label' => 'bKash', 'enabled' => true, 'gateway' => ManualMobileGateway::class, 'merchant_setting' => 'payments.bkash.merchant_number', 'account_type_setting' => 'payments.bkash.account_type', 'instructions_setting' => 'payments.bkash.instructions', 'qr_setting' => 'payments.bkash.qr_code'],
        'nagad' => ['label' => 'Nagad', 'enabled' => true, 'gateway' => ManualMobileGateway::class, 'merchant_setting' => 'payments.nagad.merchant_number', 'account_type_setting' => 'payments.nagad.account_type', 'instructions_setting' => 'payments.nagad.instructions', 'qr_setting' => 'payments.nagad.qr_code'],
        'rocket' => ['label' => 'Rocket', 'enabled' => true, 'gateway' => ManualMobileGateway::class, 'merchant_setting' => 'payments.rocket.merchant_number', 'account_type_setting' => 'payments.rocket.account_type', 'instructions_setting' => 'payments.rocket.instructions', 'qr_setting' => 'payments.rocket.qr_code'],
    ],

];
