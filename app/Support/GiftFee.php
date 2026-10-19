<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * The platform fee on paid-out gift money. Each gift is charged the rate in force when it was paid, so a change
 * of rate never reaches gifts that came in under the old one.
 */
class GiftFee
{
    public static function newFrom(): ?Carbon
    {
        $from = config('wedding_gift.payout_fee_new_from');

        return filled($from) ? Carbon::parse($from, 'Asia/Makassar') : null;
    }

    public static function oldPercent(): float
    {
        return (float) config('wedding_gift.payout_fee_percent');
    }

    public static function newPercent(): float
    {
        return (float) config('wedding_gift.payout_fee_new_percent');
    }

    public static function percentFor(?Carbon $paidAt): float
    {
        $from = self::newFrom();

        return $from && ($paidAt ?? now())->greaterThanOrEqualTo($from) ? self::newPercent() : self::oldPercent();
    }

    /** The rate a gift paid right now would carry. */
    public static function currentPercent(): float
    {
        return self::percentFor(now());
    }

    /** One sentence for terms, landing page and admin pages, naming the date when a new rate starts. */
    public static function description(): string
    {
        $from = self::newFrom();
        if (! $from) {
            return 'Biaya platform '.self::format(self::oldPercent()).' dipotong dari nominal yang diajukan saat pemilik undangan mencairkan saldo.';
        }

        return 'Biaya platform '.self::format(self::newPercent()).' untuk tanda kasih yang diterima sejak '.$from->translatedFormat('j F Y').
            ' dan '.self::format(self::oldPercent()).' untuk tanda kasih yang diterima sebelumnya, dipotong saat pemilik undangan mencairkan saldo.';
    }

    public static function format(float $percent): string
    {
        return rtrim(rtrim(number_format($percent, 2, ',', ''), '0'), ',').'%';
    }

    public static function toArray(): array
    {
        return [
            'payout_fee_percent' => self::currentPercent(),
            'payout_fee_old_percent' => self::oldPercent(),
            'payout_fee_new_percent' => self::newPercent(),
            'payout_fee_new_from' => self::newFrom()?->toIso8601String(),
            'payout_fee_description' => self::description(),
        ];
    }
}
