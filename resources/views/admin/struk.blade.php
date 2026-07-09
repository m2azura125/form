<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Struk #{{ $submission->id }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 13px;
            color: #111;
            background: #e5e5e5;
            margin: 0;
            padding: 24px 0;
        }
        .struk {
            width: 300px;
            margin: 0 auto;
            background: #fff;
            padding: 16px 18px;
        }
        .center { text-align: center; }
        .bold { font-weight: bold; }
        .dashed {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        .double {
            border-top: 3px double #000;
            margin: 8px 0;
        }
        .row {
            display: flex;
            justify-content: space-between;
            gap: 8px;
        }
        .total {
            font-size: 15px;
        }
        .actions {
            width: 300px;
            margin: 12px auto 0;
        }
        .actions button, .actions input {
            font-family: inherit;
            font-size: 13px;
        }
        .actions input[type="number"] {
            width: 100%;
            padding: 6px 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .actions button {
            width: 100%;
            padding: 8px;
            margin-top: 8px;
            border: none;
            border-radius: 4px;
            background: #4f46e5;
            color: #fff;
            cursor: pointer;
        }
        @media print {
            @page { size: 58mm auto; margin: 0; }
            body { background: #fff; padding: 0; }
            .struk { width: auto; padding: 4mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="struk">
        <div class="center bold">ARDUINO BALIKPAPAN</div>
        <div class="center">Jl Jendral Ahmad Yani, Wonokromo, samping Ibnu Sinar, Perum No 263, Balikpapan, Kalimantan Timur</div>
        <div class="center">Telp: 085129193206</div>

        <div class="dashed"></div>

        <div class="row"><span>No:</span><span>#{{ $submission->id }}</span></div>
        <div class="row"><span>Tgl:</span><span>{{ now()->format('d/m/y') }}</span></div>
        <div class="row"><span>Jam:</span><span>{{ now()->format('H:i') }}</span></div>
        <div class="row"><span>Kasir:</span><span>{{ auth()->user()->name }}</span></div>

        <div class="dashed"></div>

        <div>{{ $submission->judul_alat }} ({{ $submission->tipeLabel() }})</div>
        <div class="row">
            <span>1 x {{ number_format($submission->biaya_jasa, 0, ',', '.') }}</span>
        </div>
        <div class="row"><span></span><span>{{ number_format($submission->biaya_jasa, 0, ',', '.') }}</span></div>

        <div class="dashed"></div>

        <div class="bold">TOTAL:</div>
        <div class="row total bold"><span></span><span>{{ number_format($submission->biaya_jasa, 0, ',', '.') }}</span></div>

        <div class="row" style="margin-top: 8px;"><span>Bayar:</span><span id="bayarDisplay">{{ $submission->jumlah_bayar !== null ? number_format($submission->jumlah_bayar, 0, ',', '.') : '-' }}</span></div>

        <div style="margin-top: 8px;">{{ $submission->metode_pembayaran !== null ? strtoupper($submission->metodePembayaranLabel()) : 'TUNAI' }}</div>
        <div class="row"><span>{{ $submission->metode_pembayaran !== null ? $submission->metodePembayaranLabel() : 'Tunai' }}:</span><span id="tunaiDisplay">{{ $submission->jumlah_bayar !== null ? number_format($submission->jumlah_bayar, 0, ',', '.') : '-' }}</span></div>
        <div class="row"><span>Kembali:</span><span id="kembaliDisplay">{{ $submission->jumlah_bayar !== null ? number_format($submission->kembalian(), 0, ',', '.') : '-' }}</span></div>

        <div class="double"></div>

        <div class="center">Terima kasih atas kunjungan Anda!</div>
    </div>

    @if ($submission->jumlah_bayar === null)
        <div class="actions no-print">
            <label for="tunaiInput">Jumlah Tunai (Rp)</label>
            <input type="number" id="tunaiInput" min="{{ $submission->biaya_jasa }}" step="1000" placeholder="mis. 100000">
            <button type="button" onclick="window.print()">Cetak Struk</button>
        </div>

        <script>
            const total = {{ (int) $submission->biaya_jasa }};
            const tunaiInput = document.getElementById('tunaiInput');
            const bayarDisplay = document.getElementById('bayarDisplay');
            const tunaiDisplay = document.getElementById('tunaiDisplay');
            const kembaliDisplay = document.getElementById('kembaliDisplay');

            function formatRupiah(value) {
                return new Intl.NumberFormat('id-ID').format(value);
            }

            tunaiInput.addEventListener('input', () => {
                const tunai = parseInt(tunaiInput.value, 10) || 0;
                const kembali = tunai - total;

                bayarDisplay.textContent = tunai > 0 ? formatRupiah(tunai) : '-';
                tunaiDisplay.textContent = tunai > 0 ? formatRupiah(tunai) : '-';
                kembaliDisplay.textContent = tunai > 0 ? formatRupiah(Math.max(kembali, 0)) : '-';
            });
        </script>
    @else
        <div class="actions no-print">
            <button type="button" onclick="window.print()">Cetak Struk</button>
        </div>
    @endif
</body>
</html>
