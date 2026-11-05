<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Voice Note — {{ $event->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 text-white antialiased">
    <main class="mx-auto max-w-xl px-4 py-6"
          data-voice-note
          data-store-url="{{ $storeUrl }}">
        <header class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-400">Voice Note Ucapan</p>
                <h1 class="mt-1 text-xl font-semibold">{{ $event->name }}</h1>
            </div>
            <a href="{{ route('events.landing', $event->slug) }}" class="rounded-full border border-white/20 px-3 py-1.5 text-xs text-slate-300">Kembali</a>
        </header>

        <div class="mt-6 rounded-2xl border border-white/10 bg-white/5 p-5">
            <label for="sender-name" class="mb-1 block text-sm font-medium text-slate-200">Nama Pengirim (opsional)</label>
            <input id="sender-name" type="text" maxlength="255" placeholder="Boleh anonim"
                   class="w-full rounded-xl border border-white/15 bg-white/10 px-4 py-2.5 text-sm text-white placeholder:text-slate-500">

            <div class="mt-5 text-center">
                <p id="timer" class="text-4xl font-semibold tabular-nums">00:00</p>
                <p class="mt-1 text-xs text-slate-400">Maksimal 60 detik</p>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-2">
                <button id="record" type="button" class="rounded-xl bg-rose-500 px-4 py-3 text-sm font-semibold text-white">Mulai Rekam</button>
                <button id="stop" type="button" disabled class="rounded-xl border border-white/20 px-4 py-3 text-sm font-medium text-slate-200 disabled:opacity-40">Stop</button>
            </div>

            <audio id="preview" controls class="mt-4 hidden w-full"></audio>

            <button id="submit" type="button" disabled class="mt-4 w-full rounded-xl bg-white px-4 py-3 text-sm font-semibold text-slate-900 disabled:opacity-40">
                Kirim Voice Note
            </button>

            <p id="status" class="mt-3 text-center text-sm text-slate-400"></p>
            <p id="error" class="mt-3 hidden rounded-xl border border-rose-400/30 bg-rose-400/10 px-4 py-3 text-sm text-rose-200"></p>
        </div>

        <section class="mt-8">
            <h2 class="font-semibold">Kotak Ucapan</h2>
            @if ($voiceNotes->isEmpty())
                <p class="mt-3 text-sm text-slate-500">Belum ada ucapan. Kirim yang pertama!</p>
            @else
                <ul class="mt-3 space-y-3">
                    @foreach ($voiceNotes as $voiceNote)
                        <li class="rounded-2xl border border-white/10 bg-white/5 p-4">
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-sm font-medium">{{ $voiceNote['sender_name'] ?: 'Anonim' }}</p>
                                <p class="text-xs text-slate-400">{{ gmdate('i:s', $voiceNote['duration_seconds']) }}</p>
                            </div>
                            <audio controls preload="none" src="{{ $voiceNote['url'] }}" class="mt-2 w-full"></audio>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </main>

    <script>
        (function () {
            var root = document.querySelector('[data-voice-note]');
            if (!root) return;

            var storeUrl = root.dataset.storeUrl;
            var csrf = document.querySelector('meta[name="csrf-token"]').content;

            var recordButton = document.getElementById('record');
            var stopButton = document.getElementById('stop');
            var submitButton = document.getElementById('submit');
            var preview = document.getElementById('preview');
            var timer = document.getElementById('timer');
            var statusText = document.getElementById('status');
            var errorBox = document.getElementById('error');
            var senderName = document.getElementById('sender-name');

            var recorder = null;
            var chunks = [];
            var stream = null;
            var interval = null;
            var seconds = 0;
            var blob = null;

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

            function format(seconds) {
                var minutes = Math.floor(seconds / 60).toString().padStart(2, '0');
                var rest = (seconds % 60).toString().padStart(2, '0');
                return minutes + ':' + rest;
            }

            async function start() {
                clearError();
                seconds = 0;
                chunks = [];
                blob = null;
                submitButton.disabled = true;
                preview.classList.add('hidden');
                timer.textContent = '00:00';

                try {
                    stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                } catch (error) {
                    showError('Tidak bisa mengakses mikrofon. Berikan izin mikrofon dan buka via HTTPS atau localhost.');
                    return;
                }

                var options = {};
                if (window.MediaRecorder && MediaRecorder.isTypeSupported('audio/webm')) {
                    options.mimeType = 'audio/webm';
                }

                recorder = new MediaRecorder(stream, options);
                recorder.addEventListener('dataavailable', function (event) {
                    if (event.data.size > 0) chunks.push(event.data);
                });
                recorder.addEventListener('stop', function () {
                    blob = new Blob(chunks, { type: recorder.mimeType || 'audio/webm' });
                    preview.src = URL.createObjectURL(blob);
                    preview.classList.remove('hidden');
                    submitButton.disabled = false;
                    statusText.textContent = 'Rekaman siap dikirim.';

                    if (stream) {
                        stream.getTracks().forEach(function (track) { track.stop(); });
                    }
                });

                recorder.start();
                recordButton.disabled = true;
                stopButton.disabled = false;
                statusText.textContent = 'Sedang merekam...';

                interval = setInterval(function () {
                    seconds += 1;
                    timer.textContent = format(seconds);

                    if (seconds >= 60) {
                        stop();
                    }
                }, 1000);
            }

            function stop() {
                if (!recorder || recorder.state === 'inactive') return;
                recorder.stop();
                clearInterval(interval);
                recordButton.disabled = false;
                stopButton.disabled = true;
                statusText.textContent = 'Rekaman selesai.';
            }

            async function submit() {
                if (!blob) return;
                clearError();
                submitButton.disabled = true;
                statusText.textContent = 'Mengirim voice note...';

                try {
                    var form = new FormData();
                    form.append('audio', blob, 'ucapan.webm');
                    form.append('sender_name', senderName.value);
                    form.append('duration_seconds', seconds);
                    form.append('device_fingerprint', fingerprint());

                    var response = await fetch(storeUrl, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf },
                        body: form,
                    });
                    var payload = await response.json();

                    if (!response.ok) {
                        throw new Error(payload.message || 'Gagal mengirim voice note.');
                    }

                    statusText.textContent = 'Voice note berhasil dikirim. Terima kasih!';
                    window.setTimeout(function () { window.location.reload(); }, 1200);
                } catch (error) {
                    showError(error.message);
                    submitButton.disabled = false;
                    statusText.textContent = '';
                }
            }

            recordButton.addEventListener('click', start);
            stopButton.addEventListener('click', stop);
            submitButton.addEventListener('click', submit);
        })();
    </script>
</body>
</html>
