@csrf

@php
    $slotValues = old('slots', $frame?->slot_coordinates ?? [
        ['x' => 40, 'y' => 40, 'width' => 400, 'height' => 400],
    ]);
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label for="name" class="mb-1 block text-sm font-medium">Nama Frame</label>
        <input id="name" name="name" type="text" value="{{ old('name', $frame?->name) }}" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
        @error('name')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="photo_count" class="mb-1 block text-sm font-medium">Jumlah Foto</label>
        <input id="photo_count" name="photo_count" type="number" min="1" max="8" value="{{ old('photo_count', $frame?->photo_count ?? 1) }}" required
               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-slate-900 focus:outline-none">
        @error('photo_count')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>

    <div class="sm:col-span-2">
        <label for="frame" class="mb-1 block text-sm font-medium">File Frame (PNG transparan)</label>
        @if ($frame)
            <p class="mb-2 text-xs text-slate-500">File saat ini: {{ $frame->file_path }}</p>
        @endif
        <input id="frame" name="frame" type="file" accept="image/png" @required(! $frame)
               class="block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
        @error('frame')<p class="mt-1 text-xs text-rose-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mt-6 rounded-xl border border-slate-200 p-4">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <div>
            <p class="text-sm font-medium">Preview & Deteksi Slot</p>
            <p id="frame-dimensions" class="text-xs text-slate-500">—</p>
        </div>
        <button type="button" id="detect-slots" class="rounded-lg bg-slate-900 px-3 py-2 text-xs font-medium text-white hover:bg-slate-800">
            Deteksi Slot Otomatis
        </button>
    </div>

    <div class="mt-3 rounded-lg border border-slate-200 bg-[repeating-conic-gradient(#e5e7eb_0%_25%,#f8fafc_0%_50%)] bg-[length:20px_20px] p-3">
        <div id="frame-preview" class="relative mx-auto inline-block">
            <img id="frame-preview-image" src="{{ $frameUrl ?? '' }}" alt="Preview frame"
                 class="block max-h-[420px] max-w-full {{ empty($frameUrl) ? 'hidden' : '' }}">
            <div id="frame-slot-overlay" class="pointer-events-none absolute inset-0"></div>
        </div>
        <p id="frame-preview-empty" class="text-center text-xs text-slate-500 {{ empty($frameUrl) ? '' : 'hidden' }}">
            Pilih file PNG untuk melihat preview dan mendeteksi slot.
        </p>
    </div>

    <p id="detect-status" class="mt-2 hidden text-xs"></p>
</div>

<div class="mt-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm font-medium">Koordinat Slot Foto</p>
            <p class="text-xs text-slate-500">Nilai dalam piksel relatif terhadap ukuran file frame. Jumlah baris harus sama dengan jumlah foto.</p>
        </div>
        <button type="button" id="add-slot" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium hover:bg-slate-50">
            Tambah Slot
        </button>
    </div>

    @error('slots')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror
    @error('slots.*')<p class="mt-2 text-xs text-rose-600">{{ $message }}</p>@enderror

    <div id="slots" class="mt-3 space-y-3">
        @foreach ($slotValues as $slot)
            <div class="grid grid-cols-2 gap-2 rounded-lg border border-slate-200 p-3 sm:grid-cols-5">
                <div>
                    <label class="mb-1 block text-xs text-slate-500">X</label>
                    <input name="slots[{{ $loop->index }}][x]" type="number" step="0.01" min="0" value="{{ $slot['x'] ?? 0 }}" required class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs text-slate-500">Y</label>
                    <input name="slots[{{ $loop->index }}][y]" type="number" step="0.01" min="0" value="{{ $slot['y'] ?? 0 }}" required class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs text-slate-500">Lebar</label>
                    <input name="slots[{{ $loop->index }}][width]" type="number" step="0.01" min="1" value="{{ $slot['width'] ?? 1 }}" required class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-xs text-slate-500">Tinggi</label>
                    <input name="slots[{{ $loop->index }}][height]" type="number" step="0.01" min="1" value="{{ $slot['height'] ?? 1 }}" required class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm">
                </div>
                <div class="flex items-end">
                    <button type="button" data-remove-slot class="w-full rounded-md border border-rose-200 px-2 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50">
                        Hapus
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
        Simpan Frame
    </button>
    <a href="{{ route('admin.events.show', $event) }}" class="text-sm text-slate-600 hover:underline">Batal</a>
</div>

<script>
    (function () {
        var container = document.getElementById('slots');
        var addButton = document.getElementById('add-slot');
        var photoCountInput = document.getElementById('photo_count');
        var fileInput = document.getElementById('frame');
        var previewImage = document.getElementById('frame-preview-image');
        var previewOverlay = document.getElementById('frame-slot-overlay');
        var previewEmpty = document.getElementById('frame-preview-empty');
        var dimensions = document.getElementById('frame-dimensions');
        var detectButton = document.getElementById('detect-slots');
        var detectStatus = document.getElementById('detect-status');
        var nextIndex = {{ count($slotValues) }};
        var objectUrl = null;

        function bindRemove(button) {
            button.addEventListener('click', function () {
                button.closest('.grid').remove();
                drawOverlay();
            });
        }

        function addSlot(values) {
            values = values || {};

            var index = nextIndex;
            nextIndex += 1;

            var x = values.x !== undefined ? values.x : 0;
            var y = values.y !== undefined ? values.y : 0;
            var width = values.width !== undefined ? values.width : 400;
            var height = values.height !== undefined ? values.height : 400;

            var row = document.createElement('div');
            row.className = 'grid grid-cols-2 gap-2 rounded-lg border border-slate-200 p-3 sm:grid-cols-5';
            row.innerHTML = ''
                + '<div><label class="mb-1 block text-xs text-slate-500">X</label><input name="slots[' + index + '][x]" type="number" step="0.01" min="0" value="' + x + '" required class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm"></div>'
                + '<div><label class="mb-1 block text-xs text-slate-500">Y</label><input name="slots[' + index + '][y]" type="number" step="0.01" min="0" value="' + y + '" required class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm"></div>'
                + '<div><label class="mb-1 block text-xs text-slate-500">Lebar</label><input name="slots[' + index + '][width]" type="number" step="0.01" min="1" value="' + width + '" required class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm"></div>'
                + '<div><label class="mb-1 block text-xs text-slate-500">Tinggi</label><input name="slots[' + index + '][height]" type="number" step="0.01" min="1" value="' + height + '" required class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-sm"></div>'
                + '<div class="flex items-end"><button type="button" data-remove-slot class="w-full rounded-md border border-rose-200 px-2 py-1.5 text-xs font-medium text-rose-700 hover:bg-rose-50">Hapus</button></div>';

            container.appendChild(row);
            bindRemove(row.querySelector('[data-remove-slot]'));
        }

        function syncSlotCount() {
            var target = Math.max(1, parseInt(photoCountInput.value || '1', 10));

            while (container.children.length < target) {
                addSlot();
            }

            while (container.children.length > target) {
                container.lastElementChild.remove();
            }

            drawOverlay();
        }

        function slotsFromInputs() {
            return Array.prototype.map.call(container.children, function (row) {
                return {
                    x: parseFloat(row.querySelector('input[name$="[x]"]').value) || 0,
                    y: parseFloat(row.querySelector('input[name$="[y]"]').value) || 0,
                    width: parseFloat(row.querySelector('input[name$="[width]"]').value) || 0,
                    height: parseFloat(row.querySelector('input[name$="[height]"]').value) || 0,
                };
            });
        }

        function setSlots(slots) {
            container.innerHTML = '';
            nextIndex = 0;
            slots.forEach(function (slot) { addSlot(slot); });
            photoCountInput.value = slots.length;
            drawOverlay();
        }

        function drawOverlay() {
            previewOverlay.innerHTML = '';

            if (!previewImage.naturalWidth) {
                return;
            }

            var scale = previewImage.clientWidth / previewImage.naturalWidth;

            slotsFromInputs().forEach(function (slot, index) {
                if (slot.width <= 0 || slot.height <= 0) return;

                var box = document.createElement('div');
                box.className = 'absolute border-2 border-rose-500 bg-rose-500/15 text-[10px] font-semibold text-rose-600';
                box.style.left = (slot.x * scale) + 'px';
                box.style.top = (slot.y * scale) + 'px';
                box.style.width = (slot.width * scale) + 'px';
                box.style.height = (slot.height * scale) + 'px';
                box.textContent = String(index + 1);
                previewOverlay.appendChild(box);
            });
        }

        function updateDimensions() {
            if (previewImage.naturalWidth) {
                dimensions.textContent = previewImage.naturalWidth + ' × ' + previewImage.naturalHeight + ' px';
            } else {
                dimensions.textContent = '—';
            }
        }

        function setPreviewSource(source) {
            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }

            previewImage.src = source;
            previewImage.classList.remove('hidden');
            previewEmpty.classList.add('hidden');
        }

        function showDetectStatus(message, isError) {
            detectStatus.textContent = message;
            detectStatus.classList.remove('hidden', 'text-rose-600', 'text-emerald-700');
            detectStatus.classList.add(isError ? 'text-rose-600' : 'text-emerald-700');
        }

        function detectSlots() {
            if (!previewImage.naturalWidth) {
                showDetectStatus('Pilih file PNG frame terlebih dahulu.', true);
                return;
            }

            var maxSide = 480;
            var scale = Math.min(1, maxSide / Math.max(previewImage.naturalWidth, previewImage.naturalHeight));
            var width = Math.max(1, Math.round(previewImage.naturalWidth * scale));
            var height = Math.max(1, Math.round(previewImage.naturalHeight * scale));

            var canvas = document.createElement('canvas');
            canvas.width = width;
            canvas.height = height;
            var context = canvas.getContext('2d');
            context.clearRect(0, 0, width, height);
            context.drawImage(previewImage, 0, 0, width, height);

            var data = context.getImageData(0, 0, width, height).data;
            var visited = new Uint8Array(width * height);
            var stack = new Int32Array(width * height);
            var minimumArea = Math.max(30, Math.floor(width * height * 0.002));
            var boxes = [];

            for (var start = 0; start < width * height; start += 1) {
                if (visited[start] || data[start * 4 + 3] >= 40) continue;

                var top = 0;
                stack[top] = start;
                top += 1;
                visited[start] = 1;

                var area = 0;
                var minX = width;
                var minY = height;
                var maxX = 0;
                var maxY = 0;
                var touchesEdge = false;

                while (top > 0) {
                    top -= 1;
                    var position = stack[top];
                    var x = position % width;
                    var y = (position - x) / width;

                    area += 1;
                    if (x < minX) minX = x;
                    if (y < minY) minY = y;
                    if (x > maxX) maxX = x;
                    if (y > maxY) maxY = y;
                    if (x === 0 || y === 0 || x === width - 1 || y === height - 1) touchesEdge = true;

                    if (x > 0 && !visited[position - 1] && data[(position - 1) * 4 + 3] < 40) {
                        visited[position - 1] = 1;
                        stack[top] = position - 1;
                        top += 1;
                    }
                    if (x < width - 1 && !visited[position + 1] && data[(position + 1) * 4 + 3] < 40) {
                        visited[position + 1] = 1;
                        stack[top] = position + 1;
                        top += 1;
                    }
                    if (y > 0 && !visited[position - width] && data[(position - width) * 4 + 3] < 40) {
                        visited[position - width] = 1;
                        stack[top] = position - width;
                        top += 1;
                    }
                    if (y < height - 1 && !visited[position + width] && data[(position + width) * 4 + 3] < 40) {
                        visited[position + width] = 1;
                        stack[top] = position + width;
                        top += 1;
                    }
                }

                if (!touchesEdge && area >= minimumArea) {
                    boxes.push({
                        x: Math.round(minX / scale),
                        y: Math.round(minY / scale),
                        width: Math.round((maxX - minX + 1) / scale),
                        height: Math.round((maxY - minY + 1) / scale),
                    });
                }
            }

            boxes.sort(function (a, b) {
                return a.y === b.y ? a.x - b.x : a.y - b.y;
            });

            if (boxes.length === 0) {
                showDetectStatus('Tidak ada area transparan tertutup yang terdeteksi. Pastikan slot foto benar-benar transparan dan dikelilingi frame opaque.', true);
                return;
            }

            setSlots(boxes.slice(0, 8));
            showDetectStatus('Terdeteksi ' + Math.min(boxes.length, 8) + ' slot. Periksa kotak merah pada preview sebelum menyimpan.', false);
        }

        container.querySelectorAll('[data-remove-slot]').forEach(bindRemove);
        container.addEventListener('input', drawOverlay);
        addButton.addEventListener('click', function () { addSlot(); drawOverlay(); });
        photoCountInput.addEventListener('change', syncSlotCount);
        detectButton.addEventListener('click', detectSlots);
        previewImage.addEventListener('load', function () { updateDimensions(); drawOverlay(); });
        window.addEventListener('resize', drawOverlay);

        fileInput.addEventListener('change', function () {
            if (!fileInput.files || !fileInput.files[0]) return;

            objectUrl = URL.createObjectURL(fileInput.files[0]);
            setPreviewSource(objectUrl);
        });

        updateDimensions();
    })();
</script>
