<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Jadi Fotografer — {{ $event->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-white antialiased">
    <main class="mx-auto max-w-2xl px-4 py-6"
          data-guest
          data-store-url="{{ $storeUrl }}">
        <header class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Jadi Fotografer</p>
                <h1 class="mt-1 text-xl font-semibold">{{ $event->name }}</h1>
            </div>
            <a href="{{ route('events.landing', $event->slug) }}" class="rounded-full border border-white/20 px-3 py-1.5 text-xs text-slate-300">Kembali</a>
        </header>

        <div class="relative mt-5 overflow-hidden rounded-2xl bg-black">
            <video id="camera" autoplay playsinline muted class="aspect-[3/4] w-full object-cover"></video>
            <div id="camera-message" class="absolute inset-0 flex items-center justify-center bg-slate-900/90 px-6 text-center text-sm text-slate-300">
                Nyalakan kamera untuk mulai memotret acara.
            </div>
        </div>

        <div class="mt-3 grid grid-cols-2 gap-2">
            <button id="start-camera" type="button" class="rounded-xl bg-white px-4 py-3 text-sm font-semibold text-slate-900">Nyalakan Kamera</button>
            <button id="capture" type="button" disabled class="rounded-xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-white disabled:opacity-40">Ambil Foto</button>
        </div>

        <label class="mt-3 block rounded-xl border border-dashed border-white/20 px-4 py-3 text-center text-sm text-slate-300">
            Atau unggah dari galeri HP
            <input id="file-photo" type="file" accept="image/*" class="mt-2 block w-full text-xs text-slate-400">
        </label>

        <p id="status" class="mt-3 text-center text-sm text-slate-400"></p>
        <p id="error" class="mt-3 hidden rounded-xl border border-rose-400/30 bg-rose-400/10 px-4 py-3 text-sm text-rose-200"></p>

        <section class="mt-8">
            <h2 class="font-semibold">Foto yang Kamu Unggah</h2>
            <div id="gallery" class="mt-3 grid grid-cols-2 gap-2 sm:grid-cols-3"></div>
            <p id="empty-gallery" class="mt-3 text-sm text-slate-500">Belum ada foto di sesi ini. Jadilah yang pertama!</p>
        </section>
    </main>

    <script>
        (function () {
            var root = document.querySelector('[data-guest]');
            if (!root) return;

            var storeUrl = root.dataset.storeUrl;
            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            var video = document.getElementById('camera');
            var startButton = document.getElementById('start-camera');
            var captureButton = document.getElementById('capture');
            var fileInput = document.getElementById('file-photo');
            var statusText = document.getElementById('status');
            var errorBox = document.getElementById('error');
            var gallery = document.getElementById('gallery');
            var emptyGallery = document.getElementById('empty-gallery');
            var cameraMessage = document.getElementById('camera-message');

            var stream = null;
            var canvas = document.createElement('canvas');
            var uploading = false;

            function fingerprint() {
                var key = 'photobooth-device-id';
                var value = localStorage.getItem(key);
                if (!value) {
                    value = (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : String(Date.now()) + Math.random().toString(16).slice(2);
                    localStorage.setItem(key, value);
                }
                return value;
            }

            function showError(message) {
                errorBox.textContent = message;
                errorBox.classList.remove('hidden');
            }

            function clearError() {
                errorBox.classList.add('hidden');
            }

            async function startCamera() {
                clearError();
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: 'environment', width: { ideal: 1280 }, height: { ideal: 1600 } },
                        audio: false,
                    });
                    video.srcObject = stream;
                    cameraMessage.classList.add('hidden');
                    captureButton.disabled = false;
                    startButton.disabled = true;
                    startButton.textContent = 'Kamera Aktif';
                } catch (error) {
                    showError('Tidak bisa mengakses kamera. Berikan izin kamera dan buka via HTTPS atau localhost.');
                }
            }

            function grabPhoto() {
                var width = video.videoWidth || 720;
                var height = video.videoHeight || 960;
                var scale = Math.min(1, 1280 / Math.max(width, height));
                canvas.width = Math.round(width * scale);
                canvas.height = Math.round(height * scale);
                canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

                return new Promise(function (resolve) {
                    canvas.toBlob(resolve, 'image/jpeg', 0.9);
                });
            }

            async function upload(blob, filename) {
                if (uploading) return;
                uploading = true;
                clearError();
                statusText.textContent = 'Mengunggah foto...';

                try {
                    var form = new FormData();
                    form.append('photo', blob, filename || 'foto-tamu.jpg');
                    form.append('device_fingerprint', fingerprint());

                    var response = await fetch(storeUrl, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                        body: form,
                    });
                    var payload = await response.json();

                    if (!response.ok) {
                        throw new Error(payload.message || 'Gagal mengunggah foto.');
                    }

                    var image = document.createElement('img');
                    image.src = payload.url;
                    image.alt = 'Foto tamu';
                    image.className = 'aspect-square w-full rounded-xl object-cover';
                    gallery.prepend(image);
                    emptyGallery.classList.add('hidden');
                    statusText.textContent = 'Foto berhasil diunggah.';
                } catch (error) {
                    showError(error.message);
                    statusText.textContent = '';
                } finally {
                    uploading = false;
                }
            }

            async function capture() {
                clearError();
                captureButton.disabled = true;
                statusText.textContent = 'Memotret...';
                var blob = await grabPhoto();
                await upload(blob, 'foto-tamu.jpg');
                captureButton.disabled = false;
                statusText.textContent = 'Foto siap. Ambil foto lagi jika perlu.';
            }

            startButton.addEventListener('click', startCamera);
            captureButton.addEventListener('click', capture);
            fileInput.addEventListener('change', function () {
                if (fileInput.files && fileInput.files[0]) {
                    upload(fileInput.files[0], fileInput.files[0].name);
                    fileInput.value = '';
                }
            });
        })();
    </script>
</body>
</html>
