{{-- Ilustrasi AWLR non-JIAT terpasang di sungai/saluran.
     Tanggul, tiang, dan sensor dari aset public/sungai/sungai-{belakang,depan}.svg;
     air dan papan peil digambar dinamis di sini. Semua angka geometri terikat ke
     ilustrasi_awlr.svg (viewBox 0 0 504 305) — ubah aset berarti ubah angka di sini. --}}
                    @php
                        // ---- Geometri artwork ----
                        $rvBaseSurfY = 236.999;  // muka air pada artwork asli (bidang depan)
                        $rvBotY      = 302.987;  // tepi bawah badan air = dasar saluran, bidang depan
                        $rvCrestY    = 224.520;  // puncak tanggul, bidang depan

                        // Bentuk air persis dari artwork; tepi bawah badan air dibuat jauh (400)
                        // lalu dipotong di dasar saluran, supaya digeser ke mana pun alasnya
                        // tetap menempel dasar.
                        $rvBodyPath = 'M95.5 400V236.999C95.5 236.999 141.429 234.999 170.875 234.999C200.321 234.999 216.804 236.999 246.25 236.999C275.696 236.999 292.179 234.999 321.625 234.999C351.071 234.999 397 236.999 397 236.999V400H95.5Z';
                        $rvSurfPath = 'M170.875 235C200.321 235 216.804 237 246.25 237C275.696 237 292.179 235 321.625 235C351.071 235 397 237 397 237C397 237 407.429 229.12 414.75 225.125C421.407 221.493 425.843 220.882 432.5 217.25C439.821 213.255 442.929 209.37 450.25 205.375C456.907 201.743 468 197.5 468 197.5C468 197.5 422.071 195.5 392.625 195.5C363.179 195.5 346.696 197.5 317.25 197.5C287.804 197.5 271.321 195.5 241.875 195.5C212.429 195.5 166.5 197.5 166.5 197.5C166.5 197.5 155.407 201.743 148.75 205.375C141.429 209.37 138.321 213.255 131 217.25C124.343 220.882 119.907 221.493 113.25 225.125C105.929 229.12 95.5 237 95.5 237C95.5 237 141.429 235 170.875 235Z';

                        // Papan peil: x 393..428,174, y 135..303,056 (rx 4) — sama dengan artwork.
                        // Air solid. Warna = hasil komposit #8EDDFA 80% di atas putih pada artwork
                        // asli (0,8*142+0,2*255 dst), jadi nadanya sama tapi dinding tanggul yang
                        // terendam tidak lagi tembus jadi bercak lebih gelap.
                        $rvWaterFill = '#A5E4FB';

                        $rvBoardPath = 'M424.174 303.056H397C394.791 303.056 393 301.265 393 299.056V139C393 136.791 394.791 135 397 135H424.174C426.384 135 428.174 136.791 428.174 139V299.056C428.174 301.265 426.384 303.056 424.174 303.056Z';
                        $rvTickX     = 395.932;
                        $rvMinorX2   = 407.656;  // panjang tick minor di artwork
                        $rvMajorX2   = 413.519;  // pita merah mayor
                        $rvMajorSkew = 411.565;  // ujung pita merah yang miring
                        $rvLabelX    = 419.7;
                        $rvBoardTopY = 144.772;  // batas atas tick (mark merah teratas artwork)
                        $rvBoardBotY = 301.600;  // batas bawah pita mayor supaya tetap di dalam papan

                        // Nol skala di dasar saluran, elevasi_max di puncak tanggul: dengan begitu
                        // seluruh rentang konfigurasi terpakai penuh oleh gambar airnya. Papan peil
                        // di artwork lebih tinggi dari saluran, jadi ticknya diteruskan ke atas
                        // puncak tanggul — itu bacaan banjir, memang di luar gambar saluran.
                        $rvScaleBotY = $rvBotY;
                        $rvScaleTopY = $rvCrestY;

                        // ---- Skala peil dari elevasi_min/elevasi_max ----
                        $rvNonjiat  = $lg->nonjiat;
                        $rvMinElev  = is_numeric($rvNonjiat?->elevasi_min) ? (float) $rvNonjiat->elevasi_min : null;
                        $rvMaxElev  = is_numeric($rvNonjiat?->elevasi_max) ? (float) $rvNonjiat->elevasi_max : null;
                        $rvScaleMin = $rvMinElev ?? 0.0;
                        $rvScaleMax = ($rvMaxElev !== null && $rvMaxElev > $rvScaleMin)
                            ? $rvMaxElev
                            : $rvScaleMin + 40.0;   // fallback kalau elevasi_max belum diset
                        $rvRange = $rvScaleMax - $rvScaleMin;
                        $rvPpu   = ($rvScaleBotY - $rvScaleTopY) / $rvRange;

                        // ---- Muka air ----
                        $rvTmaRaw = is_numeric($tma) ? (float) $tma : null;
                        // Gambar air dijepit ke rentang skala: saluran hanya bisa penuh sampai tanggul.
                        $rvTmaVal = $rvTmaRaw !== null
                            ? max($rvScaleMin, min($rvScaleMax, $rvTmaRaw))
                            : $rvScaleMin;
                        $rvSurfY  = $rvScaleBotY - ($rvTmaVal - $rvScaleMin) * $rvPpu;
                        $rvDy     = round($rvSurfY - $rvBaseSurfY, 3);

                        // Garis peil memakai bacaan mentah, jadi saat melimpas garisnya naik ke
                        // bagian papan di atas tanggul walau gambar airnya berhenti di tanggul.
                        $rvLevelY = $rvTmaRaw !== null
                            ? min($rvScaleBotY, max(141.0, $rvScaleBotY - ($rvTmaRaw - $rvScaleMin) * $rvPpu))
                            : $rvScaleBotY;
                        $rvOutOfRange = $rvTmaRaw !== null
                            && ($rvTmaRaw > $rvScaleMax + 1e-9 || $rvTmaRaw < $rvScaleMin - 1e-9);
                        $rvOverBank   = $rvTmaRaw !== null && $rvTmaRaw > $rvScaleMax + 1e-9;

                        // ---- Tick: langkah "bulat" dipilih dari jarak piksel, bukan jumlah interval ----
                        $rvNice  = [0.01, 0.02, 0.05, 0.1, 0.2, 0.25, 0.5, 1, 2, 2.5, 5, 10, 20, 25, 50, 100, 200, 250, 500];
                        $rvMajor = end($rvNice);
                        foreach ($rvNice as $rvCandidate) {
                            $rvMajor = $rvCandidate;
                            if ($rvCandidate * $rvPpu >= 30.0) {
                                break;
                            }
                        }
                        // Artwork punya 5 minor per mayor; kalau terlalu rapat turun ke 2 per mayor.
                        $rvMinor    = $rvMajor / 5 * $rvPpu >= 6.5 ? $rvMajor / 5 : $rvMajor / 2;
                        $rvPerMajor = (int) round($rvMajor / $rvMinor);

                        $rvFmt = fn ($value) => rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.') ?: '0';

                        // Tick jalan dari dasar saluran ke atas sampai ujung papan; di atas
                        // elevasi_max angkanya diteruskan, bukan dipotong.
                        $rvTicks = [];
                        for ($rvI = 0; $rvI < 400; $rvI++) {
                            $rvTickY = round($rvScaleBotY - $rvI * $rvMinor * $rvPpu, 3);
                            if ($rvTickY < $rvBoardTopY) {
                                break;
                            }
                            $rvTicks[] = [
                                'y'     => $rvTickY,
                                'major' => $rvI % $rvPerMajor === 0,
                                'label' => $rvFmt($rvScaleMin + $rvI * $rvMinor),
                            ];
                        }

                        // Lebar label dibatasi papan: 414,7..426,5. Faktor 0,62 = lebar glyph digit
                        // relatif font-size, diukur dari label pada artwork. Dihitung dari label
                        // yang benar-benar digambar, bukan cuma ujung skala — tick di atas
                        // elevasi_max dan angka desimal bisa lebih panjang.
                        $rvLabelLen = 1;
                        foreach ($rvTicks as $rvTick) {
                            if ($rvTick['major']) {
                                $rvLabelLen = max($rvLabelLen, strlen($rvTick['label']));
                            }
                        }
                        $rvLabelFont = min(8.0, max(4.2, 11.8 / ($rvLabelLen * 0.62)));

                        $rvLineClr = $rvOverBank ? '#d97706' : '#0284c7';
                        $rvUid     = $lg->id_logger;
                    @endphp

                    {{-- Di artwork asli, di atas tepi belakang air tidak ada apa-apa (putih):
                         sisi jauh saluran memang tidak digambar. Jadi saat muka air turun ruang itu
                         dibiarkan kosong — jangan diisi dinding/dasar, hasilnya seperti penutup. --}}
                    <svg viewBox="0 0 504 305" fill="none" xmlns="http://www.w3.org/2000/svg"
                        class="mx-auto h-auto w-full {{ $muted ? 'grayscale opacity-70' : '' }}">
                        <defs>
                            <clipPath id="rvAir-{{ $rvUid }}">
                                <rect x="0" y="0" width="504" height="{{ $rvBotY }}" />
                            </clipPath>
                        </defs>

                        <image href="{{ asset('sungai/sungai-belakang.svg') }}" x="0" y="0" width="504"
                            height="305" />

                        {{-- Air: bentuk dan urutan gambar dari artwork (isi + garis tepi satu path,
                             badan air dulu lalu bidang permukaan), digeser vertikal sesuai muka air. --}}
                        <g clip-path="url(#rvAir-{{ $rvUid }})">
                            <g transform="translate(0 {{ $rvDy }})">
                                <path d="{{ $rvBodyPath }}" fill="{{ $rvWaterFill }}" stroke="black"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path d="{{ $rvSurfPath }}" fill="{{ $rvWaterFill }}" stroke="black"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </g>
                        </g>
                        {{-- Alas badan air dipotong clip, jadi garis dasarnya digambar terpisah. --}}
                        <path d="M95.5 {{ $rvBotY }}H397" stroke="black" stroke-linecap="round" />

                        <image href="{{ asset('sungai/sungai-depan.svg') }}" x="0" y="0" width="504"
                            height="305" />

                        {{-- Papan peil --}}
                        <path d="{{ $rvBoardPath }}" fill="#FFD178" stroke="black" stroke-width="1.5"
                            stroke-linecap="round" stroke-linejoin="round" />
                        @foreach ($rvTicks as $rvTick)
                            @if ($rvTick['major'])
                                @if ($rvTick['y'] + 1.465 <= $rvBoardBotY)
                                    <path
                                        d="M{{ $rvTickX }} {{ round($rvTick['y'] - 1.466, 3) }}H{{ $rvMajorX2 }}L{{ $rvMajorSkew }} {{ round($rvTick['y'] + 1.465, 3) }}H{{ $rvTickX }}V{{ round($rvTick['y'] - 1.466, 3) }}Z"
                                        fill="#FF0000" />
                                @endif
                                <text x="{{ $rvLabelX }}"
                                    y="{{ round(min(max($rvTick['y'] + $rvLabelFont * 0.35, 141.0), 301.0), 3) }}"
                                    font-size="{{ round($rvLabelFont, 2) }}" text-anchor="middle"
                                    font-family="ui-sans-serif, system-ui, sans-serif" fill="black"
                                    font-weight="600">{{ $rvTick['label'] }}</text>
                            @elseif ($rvTick['y'] <= $rvBoardBotY)
                                <path d="M{{ $rvTickX }} {{ $rvTick['y'] }}H{{ $rvMinorX2 }}" stroke="black"
                                    stroke-width="1.5" stroke-linejoin="round" />
                            @endif
                        @endforeach

                        {{-- Garis muka air pada papan peil --}}
                        <line x1="383" y1="{{ round($rvLevelY, 3) }}" x2="428" y2="{{ round($rvLevelY, 3) }}"
                            stroke="{{ $rvLineClr }}" stroke-width="2"
                            stroke-dasharray="{{ $isOnline ? 'none' : '4 3' }}" />
                        <circle cx="383" cy="{{ round($rvLevelY, 3) }}" r="3.5" fill="{{ $rvLineClr }}"
                            opacity="{{ $isOnline ? '1' : '0.6' }}" />
                    </svg>

                    @if ($rvOutOfRange)
                        <p class="text-[11px] leading-snug text-amber-600">
                            Bacaan {{ $rvFmt($rvTmaRaw) }} di luar rentang elevasi
                            {{ $rvFmt($rvScaleMin) }}–{{ $rvFmt($rvScaleMax) }}; gambar air berhenti di
                            {{ $rvOverBank ? 'puncak tanggul' : 'dasar saluran' }}, garis peil tetap mengikuti
                            bacaan.
                        </p>
                    @endif
