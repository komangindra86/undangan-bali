import { useEffect, useState } from 'react';
import { api } from '../services/api';

// The server decides the platform fee, including a new rate and the date it starts; ask it once per app session.
let cached = null;

export const GIFT_FEE_FALLBACK_TEXT = 'Biaya platform dipotong dari nominal yang diajukan saat pencairan.';

export function useGiftFee() {
  const [fee, setFee] = useState(cached);

  useEffect(() => {
    if (cached) return undefined;
    let active = true;
    api.giftFee()
      .then((response) => {
        cached = response.data;
        if (active) setFee(cached);
      })
      .catch(() => {});
    return () => { active = false; };
  }, []);

  return fee;
}

export function formatPercent(value) {
  return `${String(Number(value)).replace('.', ',')}%`;
}

export function feeLabel(fee) {
  return fee ? `${formatPercent(fee.payout_fee_percent)} saat pencairan` : 'Dipotong saat pencairan';
}

export function feeDescription(fee) {
  return fee?.payout_fee_description || GIFT_FEE_FALLBACK_TEXT;
}
