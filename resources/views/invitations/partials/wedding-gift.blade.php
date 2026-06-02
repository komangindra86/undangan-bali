@php
    $giftSetting = $invitation->giftSetting;
    $isPreview = $isPreview ?? false;
    $flatBelowAmount = (int) config('wedding_gift.fee.flat_below_amount');
    $flatFee = (int) config('wedding_gift.fee.flat_value');
    $percentFee = (float) config('wedding_gift.fee.percent_value');
@endphp
<style>
    .wg-section { padding: 64px 22px; text-align: center; }
    .wg-card { background: #fffdf8; border-radius: 26px; box-shadow: 0 16px 46px #00000018; color: #392d22; padding: 32px 22px; text-align: left; }
    .wg-label { color: #ae8752; font: 700 11px Arial, sans-serif; letter-spacing: .35em; margin: 0 0 12px; text-align: center; text-transform: uppercase; }
    .wg-title { color: #392d22; font: 32px Georgia, serif; margin: 0 0 10px; text-align: center; }
    .wg-note { color: #74685b; font: 14px/1.65 Arial, sans-serif; margin: 0 auto 26px; max-width: 390px; text-align: center; }
    .wg-receiver { background: #f8f0df; border-radius: 14px; color: #594936; font: 14px/1.55 Arial, sans-serif; margin-bottom: 25px; padding: 14px; text-align: center; }
    .wg-field { display: block; margin-bottom: 15px; }
    .wg-field span { color: #56483c; display: block; font: 600 13px Arial, sans-serif; margin-bottom: 7px; }
    .wg-input { background: white; border: 1px solid #e1d4bb; border-radius: 12px; color: #392d22; font: 15px Arial, sans-serif; min-height: 48px; padding: 13px; width: 100%; }
    textarea.wg-input { min-height: 86px; resize: vertical; }
    .wg-breakdown { background: #f7f1e6; border-radius: 14px; font: 14px Arial, sans-serif; margin: 22px 0; padding: 14px 16px; }
    .wg-line { color: #665848; display: flex; justify-content: space-between; padding: 6px 0; }
    .wg-total { border-top: 1px solid #dfd0b4; color: #34281d; font-weight: 700; margin-top: 8px; padding-top: 13px; }
    .wg-button { background: #b38b50; border: 0; border-radius: 999px; color: #fff9ee; cursor: pointer; font: 700 14px Arial, sans-serif; min-height: 50px; padding: 14px 20px; width: 100%; }
    .wg-button:disabled { cursor: wait; opacity: .62; }
    .wg-status { color: #ad453e; display: none; font: 13px/1.6 Arial, sans-serif; margin: 14px 0 0; text-align: center; }
    .wg-result { border-top: 1px solid #e3d8c3; display: none; margin-top: 27px; padding-top: 27px; text-align: center; }
    .wg-result.visible, .wg-status.visible { display: block; }
    .wg-result h3 { color: #392d22; font: 25px Georgia, serif; margin: 0 0 10px; }
    .wg-qr { background: white; border: 1px solid #eadfc9; border-radius: 16px; display: block; margin: 20px auto; max-width: 260px; padding: 10px; width: 100%; }
    .wg-check { background: transparent; border: 1px solid #b38b50; border-radius: 999px; color: #8c6833; cursor: pointer; font: 600 14px Arial, sans-serif; margin-top: 16px; padding: 13px 24px; }
    .wg-paid { background: #e5f2e9; border-radius: 12px; color: #27653c; display: none; font: 600 14px Arial, sans-serif; margin-top: 18px; padding: 14px; }
    .wg-paid.visible { display: block; }
</style>
<section class="wg-section">
    <div class="wg-card">
        <p class="wg-label">Wedding Gift</p>
        <h2 class="wg-title">Kirim Tanda Kasih</h2>
        <p class="wg-note">Doa restu Anda sudah sangat berarti. Bila berkenan, tanda kasih dapat dikirim aman melalui QRIS.</p>
        <div class="wg-receiver">
            Untuk <strong>{{ $giftSetting->receiver_name }}</strong>
            @if ($giftSetting->receiver_note)
                <br>{{ $giftSetting->receiver_note }}
            @endif
        </div>
        <form data-wedding-gift-form
            data-preview="{{ $isPreview ? '1' : '0' }}"
            data-create-url="{{ url('/api/public/invitations/'.$invitation->slug.'/wedding-gift/create') }}"
            data-status-url="{{ url('/api/public/wedding-gift/__ORDER_ID__/status') }}"
            data-flat-below-amount="{{ $flatBelowAmount }}"
            data-flat-fee="{{ $flatFee }}"
            data-percent-fee="{{ $percentFee }}"
        >
            <label class="wg-field">
                <span>Nama tamu *</span>
                <input class="wg-input" name="guest_name" maxlength="255" required placeholder="Nama Anda">
            </label>
            <label class="wg-field">
                <span>Nomor HP (opsional)</span>
                <input class="wg-input" name="guest_phone" maxlength="30" inputmode="tel" placeholder="08xxxxxxxxxx">
            </label>
            <label class="wg-field">
                <span>Nominal gift *</span>
                <input class="wg-input" name="gift_amount" type="number" inputmode="numeric"
                    min="{{ $giftSetting->minimum_amount }}" step="1000" required
                    placeholder="Minimal Rp{{ number_format($giftSetting->minimum_amount, 0, ',', '.') }}">
            </label>
            @if ($giftSetting->allow_message)
                <label class="wg-field">
                    <span>Ucapan (opsional)</span>
                    <textarea class="wg-input" name="message" maxlength="500" placeholder="Doa dan ucapan untuk mempelai"></textarea>
                </label>
            @endif
            <div class="wg-breakdown">
                <div class="wg-line"><span>Nominal Gift</span><strong data-wg-amount>Rp0</strong></div>
                <div class="wg-line"><span>Biaya Layanan</span><strong data-wg-fee>Rp0</strong></div>
                <div class="wg-line wg-total"><span>Total Bayar</span><strong data-wg-total>Rp0</strong></div>
            </div>
            <p class="wg-note">Biaya layanan: Rp{{ number_format($flatFee, 0, ',', '.') }} untuk gift di bawah Rp{{ number_format($flatBelowAmount, 0, ',', '.') }}, dan {{ rtrim(rtrim(number_format($percentFee, 2, ',', '.'), '0'), ',') }}% untuk Rp{{ number_format($flatBelowAmount, 0, ',', '.') }} ke atas.</p>
            <button type="submit" class="wg-button">{{ $isPreview ? 'Lihat Simulasi QRIS' : 'Buat QRIS untuk Bayar' }}</button>
            <p class="wg-status" data-wg-error></p>
            <div class="wg-result" data-wg-result>
                <h3>Scan QRIS</h3>
                <p class="wg-note">Pindai QR berikut melalui aplikasi pembayaran Anda, lalu cek status setelah pembayaran selesai.</p>
                <div class="wg-qr" data-wg-qr-demo style="{{ $isPreview ? '' : 'display:none' }}">
                    <div style="aspect-ratio:1; background:repeating-linear-gradient(45deg,#30291f 0 8px,#fff 8px 16px); border-radius:10px; display:grid; place-items:center; color:#30291f; font:700 18px Arial,sans-serif;">QRIS<br>DEMO</div>
                </div>
                <img class="wg-qr" data-wg-qr alt="QRIS Wedding Gift" style="{{ $isPreview ? 'display:none' : '' }}">
                <div class="wg-breakdown">
                    <div class="wg-line"><span>Nominal Gift</span><strong data-result-amount></strong></div>
                    <div class="wg-line"><span>Biaya Layanan</span><strong data-result-fee></strong></div>
                    <div class="wg-line wg-total"><span>Total Bayar</span><strong data-result-total></strong></div>
                </div>
                <button type="button" class="wg-check" data-wg-check>Cek Status Pembayaran</button>
                <p class="wg-status visible" data-wg-payment-status>Menunggu pembayaran.</p>
                <p class="wg-paid" data-wg-paid>Terima kasih. Wedding Gift Anda telah berhasil diterima.</p>
            </div>
        </form>
    </div>
</section>
<script>
    (() => {
        const form = document.querySelector('[data-wedding-gift-form]');
        if (!form) return;
        const formatRupiah = (value) => 'Rp' + new Intl.NumberFormat('id-ID').format(Number(value || 0));
        const amountInput = form.elements.gift_amount;
        const flatBelowAmount = Number(form.dataset.flatBelowAmount);
        const flatFee = Number(form.dataset.flatFee);
        const percentFee = Number(form.dataset.percentFee);
        const error = form.querySelector('[data-wg-error]');
        const result = form.querySelector('[data-wg-result]');
        const isPreview = form.dataset.preview === '1';
        let orderId = null;

        function feeFor(amount) {
            return amount < flatBelowAmount ? flatFee : Math.ceil(amount * percentFee / 100);
        }

        function updateBreakdown() {
            const amount = Math.max(0, Number(amountInput.value || 0));
            const fee = amount ? feeFor(amount) : 0;
            form.querySelector('[data-wg-amount]').textContent = formatRupiah(amount);
            form.querySelector('[data-wg-fee]').textContent = formatRupiah(fee);
            form.querySelector('[data-wg-total]').textContent = formatRupiah(amount + fee);
        }

        amountInput.addEventListener('input', updateBreakdown);
        updateBreakdown();

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            error.classList.remove('visible');
            const button = form.querySelector('.wg-button');
            button.disabled = true;
            button.textContent = isPreview ? 'Menyiapkan demo...' : 'Membuat QRIS...';
            const payload = Object.fromEntries(new FormData(form).entries());
            payload.gift_amount = Number(payload.gift_amount);
            try {
                if (isPreview) {
                    const amount = payload.gift_amount || 100000;
                    const fee = feeFor(amount);
                    form.querySelector('[data-result-amount]').textContent = formatRupiah(amount);
                    form.querySelector('[data-result-fee]').textContent = formatRupiah(fee);
                    form.querySelector('[data-result-total]').textContent = formatRupiah(amount + fee);
                    form.querySelector('[data-wg-payment-status]').textContent = 'Ini hanya simulasi preview. QRIS asli dibuat setelah undangan dipublish.';
                    result.classList.add('visible');
                    result.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return;
                }
                const response = await fetch(form.dataset.createUrl, {
                    method: 'POST',
                    headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                });
                const body = await response.json();
                if (!response.ok) {
                    const validation = body.errors ? Object.values(body.errors).flat().join(' ') : body.message;
                    throw new Error(validation || 'QRIS belum berhasil dibuat.');
                }
                const data = body.data;
                orderId = data.order_id;
                const qr = form.querySelector('[data-wg-qr]');
                if (data.qr_image_url) {
                    qr.src = data.qr_image_url;
                    qr.style.display = 'block';
                } else {
                    qr.style.display = 'none';
                }
                form.querySelector('[data-result-amount]').textContent = formatRupiah(data.gift_amount);
                form.querySelector('[data-result-fee]').textContent = formatRupiah(data.service_fee);
                form.querySelector('[data-result-total]').textContent = formatRupiah(data.total_amount);
                result.classList.add('visible');
                result.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } catch (exception) {
                error.textContent = exception.message;
                error.classList.add('visible');
            } finally {
                button.disabled = false;
                button.textContent = isPreview ? 'Lihat Simulasi QRIS' : 'Buat QRIS untuk Bayar';
            }
        });

        form.querySelector('[data-wg-check]').addEventListener('click', async (event) => {
            if (isPreview) {
                form.querySelector('[data-wg-payment-status]').textContent = 'Status demo: menunggu pembayaran. Pada undangan asli, status diperiksa dari Midtrans.';
                return;
            }
            if (!orderId) return;
            const button = event.currentTarget;
            const paymentStatus = form.querySelector('[data-wg-payment-status]');
            button.disabled = true;
            try {
                const url = form.dataset.statusUrl.replace('__ORDER_ID__', encodeURIComponent(orderId));
                const response = await fetch(url, { headers: { Accept: 'application/json' } });
                const body = await response.json();
                if (!response.ok) throw new Error(body.message || 'Status belum dapat diperiksa.');
                if (body.data.transaction_status === 'paid') {
                    paymentStatus.style.display = 'none';
                    form.querySelector('[data-wg-paid]').classList.add('visible');
                    button.style.display = 'none';
                    return;
                }
                paymentStatus.textContent = 'Status: ' + body.data.transaction_status + '. Silakan cek kembali setelah membayar.';
            } catch (exception) {
                paymentStatus.textContent = exception.message;
            } finally {
                button.disabled = false;
            }
        });
    })();
</script>
