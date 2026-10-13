<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $invitation->exists ? 'Ubah' : 'Buat' }} Undangan Pelanggan - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-stone-950 text-stone-100 min-h-screen">
    @php
        $live = $invitation->exists && $invitation->status === 'published' && ! $invitation->isTakenDown();
        $order = old('order', $invitation->order_details ?? []);
        $gift = $invitation->giftSetting;
        $story = old('story', $invitation->exists ? $invitation->moments->map(fn ($moment) => [
            'title' => $moment->title,
            'when' => $moment->occurred_at?->format('Y-m'),
            'body' => $moment->body,
        ])->all() : []);
        $field = 'w-full bg-stone-950 border border-stone-700 rounded-xl px-3 py-3 text-stone-100';
        $label = 'block text-sm text-stone-300 mb-2';
        $card = 'bg-stone-900 border border-stone-800 rounded-3xl p-4 md:p-6 mb-6';
        $value = fn (string $name) => old($name, $invitation->{$name});
    @endphp
    @include('admin.partials.nav', [
        'title' => $invitation->exists ? 'Ubah undangan pelanggan' : 'Buat undangan pelanggan',
        'subtitle' => 'Isi data pelanggan, pilih template, lalu terbitkan. Pelanggan cukup menerima link-nya.',
    ])

    <main class="max-w-4xl mx-auto p-4 md:p-6">
        <a href="{{ route('admin.custom.index') }}" class="inline-block text-amber-300 text-sm mb-4 hover:underline">Kembali ke daftar pesanan</a>

        @if (session('message'))
            <p class="bg-emerald-950 border border-emerald-800 text-emerald-200 rounded-2xl p-4 mb-6">{{ session('message') }}</p>
        @endif

        @if ($errors->any())
            <div class="bg-red-950 border border-red-800 text-red-100 rounded-2xl p-4 mb-6">
                <p class="font-semibold">Belum tersimpan. Periksa isian berikut:</p>
                <ul class="list-disc list-inside text-sm mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($invitation->exists && $invitation->isTakenDown())
            <p class="bg-red-950 border border-red-800 text-red-100 rounded-2xl p-4 mb-6">Undangan ini sedang diturunkan. Pulihkan dulu di menu Undangan agar link-nya bisa dibuka.</p>
        @endif

        @if ($live)
            <section class="{{ $card }} border-emerald-700/50">
                <h2 class="font-serif text-xl">Link untuk pelanggan</h2>
                <p class="text-stone-400 text-sm mt-1">Kirim keduanya: link undangan, dan halaman bagikan tempat pelanggan menulis nama tamu.</p>
                <label class="{{ $label }} mt-4">Link undangan</label>
                <input readonly value="{{ $invitation->public_url }}" onclick="this.select()" class="{{ $field }} text-sm">
                <label class="{{ $label }} mt-4">Halaman bagikan (isi nama tamu)</label>
                <input readonly value="{{ route('invitations.share', $invitation->slug) }}" onclick="this.select()" class="{{ $field }} text-sm">
            </section>
        @endif

        <form method="POST" enctype="multipart/form-data" action="{{ $invitation->exists ? route('admin.custom.update', $invitation) : route('admin.custom.store') }}">
            @csrf
            @if ($invitation->exists)
                @method('PUT')
            @endif

            <section class="{{ $card }}">
                <h2 class="font-serif text-xl">Template</h2>
                <label class="{{ $label }} mt-4" for="template_id">Desain undangan</label>
                <select id="template_id" name="template_id" required class="{{ $field }}">
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}" @selected((int) $value('template_id') === $template->id)>{{ $template->name }}{{ $template->is_premium ? ' (premium)' : '' }}</option>
                    @endforeach
                </select>
            </section>

            <section class="{{ $card }}">
                <h2 class="font-serif text-xl">Mempelai</h2>
                @foreach (['groom' => 'Mempelai pria', 'bride' => 'Mempelai wanita'] as $who => $title)
                    <fieldset class="mt-5">
                        <legend class="text-amber-300 font-semibold">{{ $title }}</legend>
                        <div class="grid sm:grid-cols-2 gap-4 mt-3">
                            <div>
                                <label class="{{ $label }}" for="{{ $who }}_full_name">Nama lengkap</label>
                                <input id="{{ $who }}_full_name" name="{{ $who }}_full_name" required maxlength="80" value="{{ $value($who.'_full_name') }}" class="{{ $field }}">
                            </div>
                            <div>
                                <label class="{{ $label }}" for="{{ $who }}_nickname">Nama panggilan (maks. 18 huruf)</label>
                                <input id="{{ $who }}_nickname" name="{{ $who }}_nickname" required maxlength="18" value="{{ $value($who.'_nickname') }}" class="{{ $field }}">
                            </div>
                            <div>
                                <label class="{{ $label }}" for="{{ $who }}_father_name">Nama ayah</label>
                                <input id="{{ $who }}_father_name" name="{{ $who }}_father_name" maxlength="80" value="{{ $value($who.'_father_name') }}" class="{{ $field }}">
                            </div>
                            <div>
                                <label class="{{ $label }}" for="{{ $who }}_mother_name">Nama ibu</label>
                                <input id="{{ $who }}_mother_name" name="{{ $who }}_mother_name" maxlength="80" value="{{ $value($who.'_mother_name') }}" class="{{ $field }}">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="{{ $label }}" for="{{ $who }}_photo">Foto{{ $invitation->{$who.'_photo'} ? ' (unggah lagi untuk mengganti)' : '' }}</label>
                                @if ($invitation->{$who.'_photo'})
                                    <img src="{{ Storage::url($invitation->{$who.'_photo'}) }}" alt="" class="h-24 w-24 object-cover rounded-xl mb-3">
                                @endif
                                <input type="file" id="{{ $who }}_photo" name="{{ $who }}_photo" accept="image/*" class="block w-full text-sm text-stone-300">
                            </div>
                        </div>
                    </fieldset>
                @endforeach
                <label class="{{ $label }} mt-5" for="opening_quote">Kalimat pembuka (opsional)</label>
                <textarea id="opening_quote" name="opening_quote" maxlength="1000" rows="3" class="{{ $field }}">{{ $value('opening_quote') }}</textarea>
            </section>

            <section class="{{ $card }}">
                <h2 class="font-serif text-xl">Acara</h2>
                <div class="grid sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="{{ $label }}" for="event_type">Jenis acara</label>
                        <select id="event_type" name="event_type" required class="{{ $field }}">
                            @foreach ($eventTypes as $type)
                                <option value="{{ $type }}" @selected($value('event_type') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $label }}" for="event_date">Tanggal</label>
                        <input type="date" id="event_date" name="event_date" required value="{{ old('event_date', $invitation->event_date?->format('Y-m-d')) }}" class="{{ $field }}">
                    </div>
                    <div>
                        <label class="{{ $label }}" for="start_time">Jam mulai (WITA)</label>
                        <input type="time" id="start_time" name="start_time" required value="{{ old('start_time', substr((string) $invitation->start_time, 0, 5)) }}" class="{{ $field }}">
                    </div>
                    <div>
                        <label class="{{ $label }}" for="end_time">Jam selesai (opsional)</label>
                        <input type="time" id="end_time" name="end_time" value="{{ old('end_time', substr((string) $invitation->end_time, 0, 5)) }}" class="{{ $field }}">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}" for="venue_name">Nama tempat</label>
                        <input id="venue_name" name="venue_name" required maxlength="120" value="{{ $value('venue_name') }}" class="{{ $field }}">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}" for="venue_address">Alamat lengkap</label>
                        <textarea id="venue_address" name="venue_address" required maxlength="1000" rows="2" class="{{ $field }}">{{ $value('venue_address') }}</textarea>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}" for="google_maps_url">Link Google Maps (opsional)</label>
                        <input type="url" id="google_maps_url" name="google_maps_url" value="{{ $value('google_maps_url') }}" placeholder="https://maps.app.goo.gl/..." class="{{ $field }}">
                    </div>
                </div>
            </section>

            <section class="{{ $card }}">
                <h2 class="font-serif text-xl">Galeri dan musik</h2>
                <p class="text-stone-400 text-sm mt-1">Paling banyak {{ $maxGallery }} foto. Foto pertama dipakai sebagai foto sampul.</p>
                @if ($invitation->gallery_photos)
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-3 mt-4">
                        @foreach ($invitation->gallery_photos as $path)
                            <label class="block text-xs text-stone-400">
                                <img src="{{ Storage::url($path) }}" alt="" class="aspect-square w-full object-cover rounded-xl mb-2">
                                <input type="checkbox" name="remove_gallery[]" value="{{ $path }}"> Hapus
                            </label>
                        @endforeach
                    </div>
                @endif
                <label class="{{ $label }} mt-4" for="gallery_photos">Tambah foto galeri</label>
                <input type="file" id="gallery_photos" name="gallery_photos[]" accept="image/*" multiple class="block w-full text-sm text-stone-300">

                <label class="{{ $label }} mt-5" for="music_id">Musik</label>
                <select id="music_id" name="music_id" class="{{ $field }}">
                    <option value="">Tanpa musik</option>
                    @foreach ($musics as $music)
                        <option value="{{ $music->id }}" @selected((int) $value('music_id') === $music->id)>{{ $music->title }}{{ $music->artist ? ' - '.$music->artist : '' }}</option>
                    @endforeach
                </select>
            </section>

            <section class="{{ $card }}">
                <h2 class="font-serif text-xl">Kisah pasangan (opsional)</h2>
                <p class="text-stone-400 text-sm mt-1">Tampil sebagai garis waktu pada template premium. Baris tanpa judul diabaikan.</p>
                @for ($index = 0; $index < $maxStory; $index++)
                    <div class="grid sm:grid-cols-[1fr_170px] gap-3 mt-4">
                        <input name="story[{{ $index }}][title]" maxlength="100" value="{{ $story[$index]['title'] ?? '' }}" placeholder="Judul, contoh: Pertama bertemu" aria-label="Judul kisah {{ $index + 1 }}" class="{{ $field }}">
                        <input type="month" name="story[{{ $index }}][when]" value="{{ $story[$index]['when'] ?? '' }}" aria-label="Bulan dan tahun kisah {{ $index + 1 }}" class="{{ $field }}">
                        <textarea name="story[{{ $index }}][body]" maxlength="600" rows="2" placeholder="Cerita singkat" aria-label="Cerita {{ $index + 1 }}" class="{{ $field }} sm:col-span-2">{{ $story[$index]['body'] ?? '' }}</textarea>
                    </div>
                @endfor
            </section>

            <section class="{{ $card }}">
                <h2 class="font-serif text-xl">Tanda kasih (QRIS)</h2>
                <p class="text-stone-400 text-sm mt-1">Pembayaran tamu masuk ke akun iPaymu dan tampil di menu Transaksi Gift. Transfer ke pelanggan Anda lakukan manual ke rekening di bagian bawah.</p>
                <label class="flex items-center gap-3 mt-4">
                    <input type="hidden" name="gift_active" value="0">
                    <input type="checkbox" name="gift_active" value="1" @checked(old('gift_active', $gift?->is_active)) class="h-5 w-5">
                    <span>Aktifkan tanda kasih di undangan ini</span>
                </label>
                <div class="grid sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="{{ $label }}" for="gift_receiver_name">Nama penerima yang tampil</label>
                        <input id="gift_receiver_name" name="gift_receiver_name" maxlength="80" value="{{ old('gift_receiver_name', $gift?->receiver_name) }}" placeholder="Contoh: Wira & Ayu" class="{{ $field }}">
                    </div>
                    <div>
                        <label class="{{ $label }}" for="gift_minimum_amount">Nominal minimal (Rp)</label>
                        <input type="number" id="gift_minimum_amount" name="gift_minimum_amount" min="10000" step="1000" value="{{ old('gift_minimum_amount', $gift?->minimum_amount ?? config('wedding_gift.minimum_amount')) }}" class="{{ $field }}">
                    </div>
                </div>
            </section>

            <section class="{{ $card }}">
                <h2 class="font-serif text-xl">Catatan pesanan (hanya untuk Anda)</h2>
                <p class="text-stone-400 text-sm mt-1">Tidak tampil di undangan dan tidak terlihat oleh siapa pun selain admin.</p>
                <div class="grid sm:grid-cols-2 gap-4 mt-4">
                    @foreach (['customer_name' => 'Nama pelanggan', 'customer_whatsapp' => 'WhatsApp pelanggan', 'bank_name' => 'Bank', 'account_number' => 'Nomor rekening', 'account_holder' => 'Atas nama'] as $key => $text)
                        <div>
                            <label class="{{ $label }}" for="order_{{ $key }}">{{ $text }}</label>
                            <input id="order_{{ $key }}" name="order[{{ $key }}]" maxlength="300" value="{{ $order[$key] ?? '' }}" class="{{ $field }}">
                        </div>
                    @endforeach
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}" for="order_note">Catatan lain (harga, permintaan khusus)</label>
                        <textarea id="order_note" name="order[note]" maxlength="300" rows="2" class="{{ $field }}">{{ $order['note'] ?? '' }}</textarea>
                    </div>
                </div>
            </section>

            <div class="flex flex-col gap-3 sm:flex-row sm:justify-end">
                @unless ($invitation->exists && $invitation->status === 'published')
                    <button name="intent" value="draft" class="border border-stone-600 text-stone-200 rounded-xl px-6 py-3 hover:border-amber-400">Simpan sebagai draft</button>
                @endunless
                <button name="intent" value="publish" class="bg-amber-400 text-stone-950 font-semibold rounded-xl px-6 py-3 hover:bg-amber-300">{{ $invitation->exists && $invitation->status === 'published' ? 'Simpan perubahan' : 'Simpan dan terbitkan' }}</button>
            </div>
        </form>
    </main>
</body>
</html>
