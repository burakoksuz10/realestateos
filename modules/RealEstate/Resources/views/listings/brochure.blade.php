<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>{{ $listing->title }} — {{ $office->name ?? 'Broşür' }}</title>
    <style>
        @page { margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #1e293b; }

        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 0;
            page-break-after: always;
            position: relative;
        }
        .page:last-child { page-break-after: auto; }

        /* === HERO (kapak) === */
        .hero {
            background: linear-gradient(135deg, #0c4a6e 0%, #0ea5e9 100%);
            color: white;
            padding: 14mm 16mm 12mm;
            position: relative;
        }
        .hero-top { display: table; width: 100%; }
        .hero-top-left { display: table-cell; vertical-align: middle; }
        .hero-top-right { display: table-cell; vertical-align: middle; text-align: right; }
        .office-name { font-size: 16pt; font-weight: 800; letter-spacing: -0.3pt; }
        .office-meta { font-size: 8.5pt; opacity: 0.85; margin-top: 1mm; }
        .ref-badge {
            display: inline-block;
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.3);
            padding: 1.5mm 4mm;
            border-radius: 8mm;
            font-size: 9pt;
            letter-spacing: 0.3pt;
        }

        .hero-title {
            font-size: 26pt;
            font-weight: 800;
            line-height: 1.15;
            margin-top: 10mm;
            margin-bottom: 3mm;
        }
        .hero-location {
            font-size: 11.5pt;
            opacity: 0.9;
            margin-bottom: 6mm;
        }
        .hero-bottom { display: table; width: 100%; margin-top: 8mm; }
        .price-tag {
            display: table-cell;
            font-size: 22pt;
            font-weight: 800;
            letter-spacing: -0.3pt;
        }
        .price-meta { font-size: 8pt; opacity: 0.8; font-weight: normal; display: block; margin-top: 0.5mm; }
        .type-badges { display: table-cell; vertical-align: bottom; text-align: right; }
        .type-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 1.5mm 4mm;
            border-radius: 3mm;
            font-size: 9pt;
            font-weight: 600;
            margin-left: 2mm;
        }

        /* === HERO PHOTO === */
        .hero-photo {
            width: 100%;
            height: 90mm;
            background: #0c4a6e;
            object-fit: cover;
            display: block;
        }

        /* === DETAYLAR (key-value grid) === */
        .section {
            padding: 8mm 16mm;
        }
        .section-title {
            font-size: 13pt;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 4mm;
            padding-left: 3mm;
            border-left: 3.5pt solid #0ea5e9;
        }
        .detail-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 2mm;
        }
        .detail-grid td {
            background: #f1f5f9;
            border-radius: 2mm;
            padding: 3mm 4mm;
            width: 33.33%;
            vertical-align: top;
        }
        .detail-label {
            font-size: 7.5pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.4pt;
            margin-bottom: 1mm;
        }
        .detail-value {
            font-size: 11pt;
            font-weight: 700;
            color: #0f172a;
        }

        /* === DESCRIPTION === */
        .description {
            font-size: 10pt;
            line-height: 1.65;
            color: #334155;
            text-align: justify;
        }

        /* === FEATURES === */
        .feature-list { font-size: 0; }
        .feature-tag {
            display: inline-block;
            background: #ecfdf5;
            color: #047857;
            padding: 1.5mm 4mm;
            border-radius: 4mm;
            font-size: 9pt;
            font-weight: 600;
            margin-right: 2mm;
            margin-bottom: 2mm;
            border: 0.5pt solid #a7f3d0;
        }

        /* === PHOTO GALLERY === */
        .gallery {
            width: 100%;
            border-collapse: separate;
            border-spacing: 2mm;
        }
        .gallery td {
            padding: 0;
            vertical-align: top;
        }
        .gallery img {
            width: 100%;
            height: 50mm;
            object-fit: cover;
            border-radius: 2mm;
            display: block;
        }

        /* === MAP === */
        .map-box {
            background: #f1f5f9;
            border-radius: 3mm;
            padding: 4mm;
            text-align: center;
        }
        .map-box img {
            width: 100%;
            max-height: 70mm;
            object-fit: cover;
            border-radius: 2mm;
        }
        .map-address {
            font-size: 9.5pt;
            color: #334155;
            margin-top: 3mm;
        }

        /* === AGENT CARD === */
        .agent-card {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: white;
            padding: 6mm 8mm;
            border-radius: 3mm;
            display: table;
            width: 100%;
            margin-top: 4mm;
        }
        .agent-avatar {
            display: table-cell;
            vertical-align: middle;
            width: 16mm;
        }
        .agent-avatar-circle {
            width: 14mm;
            height: 14mm;
            border-radius: 50%;
            background: #0ea5e9;
            color: white;
            text-align: center;
            font-weight: 800;
            font-size: 16pt;
            line-height: 14mm;
        }
        .agent-info { display: table-cell; vertical-align: middle; padding-left: 3mm; }
        .agent-name { font-size: 13pt; font-weight: 800; }
        .agent-meta { font-size: 9.5pt; opacity: 0.85; margin-top: 1mm; }

        /* === FOOTER === */
        .footer {
            position: absolute;
            bottom: 6mm;
            left: 16mm;
            right: 16mm;
            border-top: 0.5pt solid #cbd5e1;
            padding-top: 2mm;
            font-size: 7.5pt;
            color: #94a3b8;
            display: table;
            width: calc(100% - 32mm);
        }
        .footer-left { display: table-cell; text-align: left; }
        .footer-right { display: table-cell; text-align: right; }
    </style>
</head>
<body>

{{-- ============ PAGE 1: HERO ============ --}}
<div class="page">
    <div class="hero">
        <div class="hero-top">
            <div class="hero-top-left">
                <div class="office-name">{{ $office->name ?? 'RE-OS' }}</div>
                @if (!empty($office?->phone) || !empty($office?->website))
                    <div class="office-meta">
                        {{ $office->phone ?? '' }}{{ $office->phone && $office->website ? ' · ' : '' }}{{ $office->website ?? '' }}
                    </div>
                @endif
            </div>
            <div class="hero-top-right">
                <span class="ref-badge">Ref: {{ $listing->reference_no ?? 'REF-' . $listing->id }}</span>
            </div>
        </div>

        <div class="hero-title">{{ $listing->title }}</div>
        <div class="hero-location">
            📍 {{ trim(($listing->neighborhood ?? '') . ' ' . ($listing->district ?? '') . ', ' . ($listing->city ?? ''), ', ') }}
        </div>

        <div class="hero-bottom">
            <div class="price-tag">
                {{ $priceFormatted }}
                <span class="price-meta">
                    {{ $listing->listing_type === 'sale' ? 'Satılık' : ($listing->listing_type === 'rent' ? 'Aylık Kira' : ucfirst($listing->listing_type ?? '')) }}
                    @if ($listing->is_negotiable) · Pazarlık payı var @endif
                </span>
            </div>
            <div class="type-badges">
                @if ($listing->category)
                    <span class="type-badge">{{ ucfirst($listing->category) }}</span>
                @endif
                @if ($listing->type)
                    <span class="type-badge">{{ ucfirst($listing->type) }}</span>
                @endif
            </div>
        </div>
    </div>

    @if (!empty($photos[0]['path']))
        <img class="hero-photo" src="{{ $photos[0]['path'] }}" alt="Ana fotoğraf">
    @elseif (!empty($photos[0]['url']))
        <img class="hero-photo" src="{{ $photos[0]['url'] }}" alt="Ana fotoğraf">
    @endif

    {{-- KEY DETAILS --}}
    <div class="section">
        <div class="section-title">Temel Bilgiler</div>
        <table class="detail-grid">
            <tr>
                @if ($listing->gross_sqm)
                    <td><div class="detail-label">Brüt Alan</div><div class="detail-value">{{ $listing->gross_sqm }} m²</div></td>
                @endif
                @if ($listing->net_sqm)
                    <td><div class="detail-label">Net Alan</div><div class="detail-value">{{ $listing->net_sqm }} m²</div></td>
                @endif
                @if ($listing->room_count)
                    <td><div class="detail-label">Oda</div><div class="detail-value">{{ $listing->room_count }}+{{ $listing->living_room_count ?? 1 }}</div></td>
                @endif
            </tr>
            <tr>
                @if ($listing->bathroom_count)
                    <td><div class="detail-label">Banyo</div><div class="detail-value">{{ $listing->bathroom_count }}</div></td>
                @endif
                @if ($listing->floor_number !== null)
                    <td><div class="detail-label">Kat</div><div class="detail-value">{{ $listing->floor_number }}{{ $listing->total_floors ? ' / ' . $listing->total_floors : '' }}</div></td>
                @endif
                @if ($listing->building_age !== null)
                    <td><div class="detail-label">Bina Yaşı</div><div class="detail-value">{{ $listing->building_age }} yıl</div></td>
                @endif
            </tr>
            <tr>
                @if ($listing->heating)
                    <td><div class="detail-label">Isınma</div><div class="detail-value">{{ ucfirst($listing->heating) }}</div></td>
                @endif
                @if ($listing->facade)
                    <td><div class="detail-label">Cephe</div><div class="detail-value">{{ ucfirst($listing->facade) }}</div></td>
                @endif
                @if ($listing->furnishings)
                    <td><div class="detail-label">Eşya Durumu</div><div class="detail-value">{{ ucfirst($listing->furnishings) }}</div></td>
                @endif
            </tr>
        </table>
    </div>

    {{-- DESCRIPTION --}}
    @if ($description)
        <div class="section">
            <div class="section-title">Açıklama</div>
            <div class="description">{!! nl2br(e(\Illuminate\Support\Str::limit(strip_tags($description), 1200))) !!}</div>
        </div>
    @endif

    <div class="footer">
        <div class="footer-left">{{ $office->name ?? 'RE-OS' }} · {{ now()->format('d.m.Y') }}</div>
        <div class="footer-right">Sayfa 1 / 2</div>
    </div>
</div>

{{-- ============ PAGE 2: GALLERY + MAP + AGENT ============ --}}
<div class="page">
    @if (count($photos) > 1)
        <div class="section" style="padding-top: 14mm;">
            <div class="section-title">Fotoğraflar</div>
            <table class="gallery">
                @php $rest = array_slice($photos, 1, 6); @endphp
                @foreach (array_chunk($rest, 2) as $row)
                    <tr>
                        @foreach ($row as $photo)
                            <td>
                                @if (!empty($photo['path']))
                                    <img src="{{ $photo['path'] }}" alt="">
                                @elseif (!empty($photo['url']))
                                    <img src="{{ $photo['url'] }}" alt="">
                                @endif
                            </td>
                        @endforeach
                        @if (count($row) === 1)<td></td>@endif
                    </tr>
                @endforeach
            </table>
        </div>
    @endif

    @if (!empty($features))
        <div class="section">
            <div class="section-title">Özellikler</div>
            <div class="feature-list">
                @foreach ($features as $feature)
                    <span class="feature-tag">{{ $feature }}</span>
                @endforeach
            </div>
        </div>
    @endif

    @if ($mapUrl)
        <div class="section">
            <div class="section-title">Konum</div>
            <div class="map-box">
                <img src="{{ $mapUrl }}" alt="Konum">
                @if ($listing->address)
                    <div class="map-address">{{ $listing->address }}</div>
                @endif
            </div>
        </div>
    @endif

    {{-- AGENT --}}
    @if ($agent)
        <div class="section">
            <div class="section-title">İletişim</div>
            <div class="agent-card">
                <div class="agent-avatar">
                    <div class="agent-avatar-circle">{{ strtoupper(mb_substr($agent->name ?? 'D', 0, 1)) }}</div>
                </div>
                <div class="agent-info">
                    <div class="agent-name">{{ $agent->name ?? '—' }}</div>
                    <div class="agent-meta">
                        @if ($agent->phone)📞 {{ $agent->phone }}@endif
                        @if ($agent->phone && $agent->email) · @endif
                        @if ($agent->email)✉ {{ $agent->email }}@endif
                    </div>
                    @if ($office)
                        <div class="agent-meta" style="margin-top: 1.5mm;">
                            {{ $office->name }}{{ $office->phone ? ' · ' . $office->phone : '' }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <div class="footer">
        <div class="footer-left">{{ $office->name ?? 'RE-OS' }} · {{ $generatedAt->format('d.m.Y') }} · Ref: {{ $listing->reference_no ?? 'REF-' . $listing->id }}</div>
        <div class="footer-right">Sayfa 2 / 2</div>
    </div>
</div>

</body>
</html>
