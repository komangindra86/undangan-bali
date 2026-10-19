<?php

return [
    'minimum_amount' => (int) env('WEDDING_GIFT_MINIMUM_AMOUNT', 10000),

    'payout_minimum_amount' => (int) env('WEDDING_GIFT_PAYOUT_MINIMUM_AMOUNT', 50000),

    // Platform fee taken when gift money is paid out, as a percentage of each gift. Gifts paid from
    // payout_fee_new_from onwards (a WITA date and time) use payout_fee_new_percent; earlier gifts keep
    // payout_fee_percent, the rate that applied when they came in. Leave payout_fee_new_from empty until the
    // new rate starts.
    'payout_fee_percent' => (float) env('WEDDING_GIFT_PAYOUT_FEE_PERCENT', 1),
    'payout_fee_new_percent' => (float) env('WEDDING_GIFT_PAYOUT_FEE_NEW_PERCENT', 10),
    'payout_fee_new_from' => env('WEDDING_GIFT_PAYOUT_FEE_NEW_FROM'),
];
