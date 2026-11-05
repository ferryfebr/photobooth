<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Photobooth — {{ $event->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-white antialiased">
    <main class="mx-auto flex min-h-screen max-w-xl flex-col px-4 py-6"
          data-photobooth
          data-store-url="{{ $storeUrl }}"
          data-frames='@json($frames)'>
        <header class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Photobooth</p>
                <h1 class="mt-1 text-xl font-semibold">{{ $event->name }}</h1>
            </div>
            <a href="{{ route('events.landing', $event->slug) }}" class="rounded-full border border-white/20 px-3 py-1.5 text-xs text-slate-300">Kembali</a>
        </header>

        @unless ($isOpen)
            <div class="mt-10 rounded-2xl border border-amber-400/30 bg-amber-400/10 px-5 py-6 text-center">
                <p class="font-medium text-amber-200">Photobooth belum dibuka</p>
                <p class="mt-1 text-sm text-amber-100/70">Silakan coba lagi sesuai jadwal acara.</p>
            </div>
        @else
            @if ($frames->isEmpty())
                <div class="mt-10 rounded-2xl border border-white/10 bg-white/5 px-5 py-6 text-center">
                    <p class="font-medium">Frame belum tersedia</p>
                    <p class="mt-1 text-sm text-slate-400">Admin belum mengunggah frame untuk acara ini.</p>
                </div>
            @else
                <div class="mt-5">
                    <label class="mb-2 block text-sm text-slate-300">Pilih Frame</label>
                    <select id="frame-select" class="w-full rounded-xl border border-white/15 bg-white/10 px-4 py-3 text-sm text-white">
                        @foreach ($frames as $frame)
                            <option value="{{ $frame['id'] }}">{{ $frame['name'] }} ({{ $frame['photo_count'] }} foto)</option>
                        @endforeach
                    </select>
                </div>

                <div class="relative mt-4 overflow-hidden rounded-2xl bg-black">
                    <video id="camera" autoplay playsinline muted class="aspect-[3/4] w-full object-cover" style="transform: scaleX(-1);"></video>
                    <div id="countdown" class="absolute inset-0 hidden items-center justify-center bg-black/40 text-7xl font-bold"></div>
                    <div id="camera-message" class="absolute inset-0 flex items-center justify-center bg-slate-900/90 px-6 text-center text-sm text-slate-300">
                        Izinkan akses kamera untuk mulai mengambil foto.
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2">
                    <button id="start-camera" type="button" class="rounded-xl bg-white px-4 py-3 text-sm font-semibold text-slate-900">Nyalakan Kamera</button>
                    <button id="capture" type="button" disabled class="rounded-xl bg-emerald-500 px-4 py-3 text-sm font-semibold text-white disabled:opacity-40">Ambil Foto</button>
                </div>

                <p id="shot-status" class="mt-3 text-center text-sm text-slate-400">0 foto diambil.</p>

                <div id="previews" class="mt-3 grid grid-cols-4 gap-2"></div>

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <button id="retake" type="button" class="rounded-xl border border-white/20 px-4 py-3 text-sm font-medium text-slate-200">Ulangi</button>
                    <button id="submit" type="button" disabled class="rounded-xl bg-white px-4 py-3 text-sm font-semibold text-slate-900 disabled:opacity-40">Proses & Simpan</button>
                </div>

                <p id="error" class="mt-3 hidden rounded-xl border border-rose-400/30 bg-rose-400/10 px-4 py-3 text-sm text-rose-200"></p>

                <div id="result" class="mt-5 hidden rounded-2xl border border-white/10 bg-white/5 p-4 text-center">
                    <p class="text-sm text-slate-300">Hasil photobooth kamu</p>
                    <img id="result-image" alt="Hasil photobooth" class="mx-auto mt-3 max-h-80 rounded-xl">
                    <div class="mt-3 flex flex-wrap justify-center gap-2">
                        <a id="download" class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-900" download>Download Foto</a>
                        <button id="share" type="button" class="rounded-full border border-white/20 px-4 py-2 text-sm text-slate-200">Bagikan</button>
                    </div>
                </div>
            @endif
        @endunless
    </main>

    <script>
        (function () {
            var root = document.querySelector('[data-photobooth]');
            if (!root) return;

            var frames = JSON.parse(root.dataset.frames || '[]');
            var storeUrl = root.dataset.storeUrl;
            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            var video = document.getElementById('camera');
            var canvas = document.createElement('canvas');
            var startButton = document.getElementById('start-camera');
            var captureButton = document.getElementById('capture');
            var retakeButton = document.getElementById('retake');
            var submitButton = document.getElementById('submit');
            var frameSelect = document.getElementById('frame-select');
            var previews = document.getElementById('previews');
            var shotStatus = document.getElementById('shot-status');
            var countdown = document.getElementById('countdown');
            var cameraMessage = document.getElementById('camera-message');
            var errorBox = document.getElementById('error');
            var resultBox = document.getElementById('result');
            var resultImage = document.getElementById('result-image');
            var downloadLink = document.getElementById('download');
            var shareButton = document.getElementById('share');

            var stream = null;
            var photos = [];
            var lastResultBlob = null;

            function fingerprint() {
                var key = 'photobooth-device-id';
                var value = localStorage.getItem(key);
                if (!value) {
                    value = (window.crypto && crypto.randomUUID) ? crypto.randomUUID() : String(Date.now()) + Math.random().toString(16).slice(2);
                    localStorage.setItem(key, value);
                }
                return value;
            }

            function currentFrame() {
                return frames.find(function (frame) {
                    return String(frame.id) === String(frameSelect.value);
                });
            }

            function showError(message) {
                errorBox.textContent = message;
                errorBox.classList.remove('hidden');
            }

            function clearError() {
                errorBox.classList.add('hidden');
            }

            function updateStatus() {
                var frame = currentFrame();
                shotStatus.textContent = photos.length + ' dari ' + (frame ? frame.photo_count : 0) + ' foto diambil.';
                submitButton.disabled = !frame || photos.length !== frame.photo_count;
            }

            async function startCamera() {
                clearError();
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 1600 } },
                        audio: false,
                    });
                    video.srcObject = stream;
                    cameraMessage.classList.add('hidden');
                    startButton.textContent = 'Kamera Aktif';
                    startButton.disabled = true;
                    captureButton.disabled = false;
                } catch (error) {
                    showError('Tidak bisa mengakses kamera. Pastikan izin kamera diberikan dan halaman dibuka via HTTPS atau localhost.');
                }
            }

            function grabPhoto() {
                var width = video.videoWidth || 720;
                var height = video.videoHeight || 960;
                var max = 1280;
                var scale = Math.min(1, max / Math.max(width, height));
                canvas.width = Math.round(width * scale);
                canvas.height = Math.round(height * scale);

                var context = canvas.getContext('2d');
                context.translate(canvas.width, 0);
                context.scale(-1, 1);
                context.drawImage(video, 0, 0, canvas.width, canvas.height);

                return new Promise(function (resolve) {
                    canvas.toBlob(function (blob) {
                        resolve(blob);
                    }, 'image/jpeg', 0.9);
                });
            }

            function countdownThenCapture() {
                var frame = currentFrame();
                if (!frame || photos.length >= frame.photo_count) return;

                captureButton.disabled = true;
                var value = 3;
                countdown.textContent = value;
                countdown.classList.remove('hidden');
                countdown.classList.add('flex');

                var timer = setInterval(async function () {
                    value -= 1;

                    if (value > 0) {
                        countdown.textContent = value;
                        return;
                    }

                    clearInterval(timer);
                    countdown.classList.add('hidden');
                    countdown.classList.remove('flex');

                    var blob = await grabPhoto();
                    photos.push(blob);
                    renderPreviews();
                    updateStatus();

                    captureButton.disabled = photos.length >= frame.photo_count;
                }, 1000);
            }

            function renderPreviews() {
                previews.innerHTML = '';
                photos.forEach(function (blob, index) {
                    var image = document.createElement('img');
                    image.src = URL.createObjectURL(blob);
                    image.alt = 'Foto ' + (index + 1);
                    image.className = 'aspect-[3/4] w-full rounded-lg object-cover';
                    previews.appendChild(image);
                });
            }

            function loadImage(src) {
                return new Promise(function (resolve, reject) {
                    var image = new Image();
                    image.crossOrigin = 'anonymous';
                    image.onload = function () { resolve(image); };
                    image.onerror = reject;
                    image.src = src;
                });
            }

            function drawCover(context, image, slot) {
                var scale = Math.max(slot.width / image.width, slot.height / image.height);
                var width = image.width * scale;
                var height = image.height * scale;
                var x = slot.x + (slot.width - width) / 2;
                var y = slot.y + (slot.height - height) / 2;

                context.save();
                context.beginPath();
                context.rect(slot.x, slot.y, slot.width, slot.height);
                context.clip();
                context.drawImage(image, x, y, width, height);
                context.restore();
            }

            async function compose() {
                var frame = currentFrame();
                var frameImage = await loadImage(frame.url);
                var composeCanvas = document.createElement('canvas');
                composeCanvas.width = frameImage.naturalWidth;
                composeCanvas.height = frameImage.naturalHeight;

                var context = composeCanvas.getContext('2d');
                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, composeCanvas.width, composeCanvas.height);

                for (var index = 0; index < photos.length; index += 1) {
                    var slot = frame.slots[index];
                    if (!slot) continue;
                    var image = await createImageBitmap(photos[index]);
                    drawCover(context, image, slot);
                }

                context.drawImage(frameImage, 0, 0, composeCanvas.width, composeCanvas.height);

                return new Promise(function (resolve) {
                    composeCanvas.toBlob(function (blob) {
                        resolve(blob);
                    }, 'image/jpeg', 0.92);
                });
            }

            async function submit() {
                var frame = currentFrame();
                if (!frame || photos.length !== frame.photo_count) return;

                clearError();
                submitButton.disabled = true;
                submitButton.textContent = 'Memproses...';

                try {
                    var result = await compose();
                    lastResultBlob = result;

                    var form = new FormData();
                    form.append('frame_id', frame.id);
                    form.append('device_fingerprint', fingerprint());
                    form.append('result', result, 'hasil-photobooth.jpg');
                    photos.forEach(function (blob, index) {
                        form.append('photos[]', blob, 'foto-' + (index + 1) + '.jpg');
                    });

                    var response = await fetch(storeUrl, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                        body: form,
                    });

                    var payload = await response.json();

                    if (!response.ok) {
                        throw new Error(payload.message || 'Gagal menyimpan foto.');
                    }

                    resultImage.src = URL.createObjectURL(result);
                    downloadLink.href = payload.download_url;
                    resultBox.classList.remove('hidden');
                    resultBox.scrollIntoView({ behavior: 'smooth' });
                } catch (error) {
                    showError(error.message);
                } finally {
                    submitButton.textContent = 'Proses & Simpan';
                    updateStatus();
                }
            }

            async function share() {
                if (!lastResultBlob) return;

                var file = new File([lastResultBlob], 'photobooth.jpg', { type: 'image/jpeg' });

                if (navigator.canShare && navigator.canShare({ files: [file] })) {
                    await navigator.share({ files: [file], title: 'Photobooth {{ $event->name }}' });
                } else {
                    showError('Perangkat ini belum mendukung share langsung. Silakan download fotonya.');
                }
            }

            function retake() {
                photos = [];
                renderPreviews();
                updateStatus();
                resultBox.classList.add('hidden');
                submitButton.disabled = true;
                captureButton.disabled = false;
                clearError();
            }

            startButton.addEventListener('click', startCamera);
            captureButton.addEventListener('click', countdownThenCapture);
            submitButton.addEventListener('click', submit);
            retakeButton.addEventListener('click', retake);
            shareButton.addEventListener('click', share);
            frameSelect.addEventListener('change', function () {
                photos = [];
                renderPreviews();
                updateStatus();
            });

            updateStatus();
        })();
    </script>
</body>
</html>
