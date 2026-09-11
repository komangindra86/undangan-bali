<?php

return [
    'minimum_amount' => (int) env('WEDDING_GIFT_MINIMUM_AMOUNT', 10000),

    'payout_minimum_amount' => (int) env('WEDDING_GIFT_PAYOUT_MINIMUM_AMOUNT', 50000),
    'payout_fee_percent' => (float) env('WEDDING_GIFT_PAYOUT_FEE_PERCENT', 1),
];
