<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Wedding Gift Service Fee
    |--------------------------------------------------------------------------
    |
    | Fee is controlled by the platform and is presented transparently to
    | guests before a QRIS transaction is created.
    |
    */
    'fee' => [
        'type' => env('WEDDING_GIFT_FEE_TYPE', 'flat'),
        'value' => (float) env('WEDDING_GIFT_FEE_VALUE', 2000),
        'flat_below_amount' => (int) env('WEDDING_GIFT_FEE_FLAT_BELOW_AMOUNT', 100000),
        'flat_value' => (int) env('WEDDING_GIFT_FEE_FLAT_VALUE', 2000),
        'percent_value' => (float) env('WEDDING_GIFT_FEE_PERCENT_VALUE', 2),
    ],

    'minimum_amount' => (int) env('WEDDING_GIFT_MINIMUM_AMOUNT', 10000),

    'payout_minimum_amount' => (int) env('WEDDING_GIFT_PAYOUT_MINIMUM_AMOUNT', 50000),
];
