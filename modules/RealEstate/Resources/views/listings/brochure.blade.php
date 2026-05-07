<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $listing->title }} - Broşür</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Arial, sans-serif; color: #1e293b; background: white; }
        .page { width: 210mm; min-height: 297mm; padding: 20mm; }
        .header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 8mm; border-bottom: 2px solid #0ea5e9; margin-bottom: 8mm; }
        .logo { font-size: 20pt; font-weight: 800; color: #0ea5e9; }
        .ref { font-size: 9pt; color: #64748b; }
        .title { font-size: 22pt; font-weight: 700; color: #0f172a; margin-bottom: 2mm; }
        .location { font-size: 11pt; color: #64748b; margin-bottom: 6mm; }
        .price-box { background: #0ea5e9; color: white; padding: 4mm 8mm; border-radius: 3mm; display: inline-block; font-size: 18pt; font-weight: 800; margin-bottom: 8mm; }
        .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 6mm; margin-bottom: 8mm; }
        .detail-item { background: #f8fafc; padding: 3mm 4mm; border-radius: 2mm; }
        .detail-label { font-size: 8pt; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5pt; }
        .detail-value { font-size: 11pt; font-weight: 600; color: #0f172a; }
        .section-title { font-size: 12pt; font-weight: 700; color: #0f172a; margin-bottom: 3mm; margin-top: 6mm; border-left: 3px solid #0ea5e9; padding-left: 3mm; }
        .description { font-size: 10pt; line-height: 1.6; color: #334155; }
        .features { display: flex; flex-wrap: wrap; gap: 2mm; margin-top: 3mm; }
        .feature-tag { background: #e0f2fe; color: #0369a1; padding: 1mm 3mm; border-radius: 2mm; font-size: 9pt; }
        .footer { position: fixed; bottom: 15mm; left: 20mm; right: 20mm; border-top: 1px solid #e2e8f0; padding-top: 3mm; display: flex; justify-content: space-between; font-size: 8pt; color: #94a3b8; }
        .agent-box { background: #f8fafc; border: 1px solid #e2e8f0; padding: 4mm; border-radius: 3mm; margin-top: 6mm; display: flex; align-items: center; gap: 4mm; }
        .agent-avatar { width: 12mm; height: 12mm; background: #0ea5e9; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14pt; flex-shrink: 0; }
    </style>
</head>
<body>
<div class="page">
    <!-- Header -->
    <div class="header">
        <div class="logo">RE-OS</div>
        <div class="ref">Ref: {{ $listing->reference_no ?? 'REF-' . $listing->id }}</div>
    </div>

    <!-- Title & Price -->
    <div class="title">{{ $listing->title }}</div>
    <div class="location">📍 {{ $listing->district ?? '' }}{{ $listing->district && $listing->city ? ', ' : '' }}{{ $listing->city ?? '' }}</div>
    <div class="price-box">₺{{ number_format($listing->price ?? 0, 0, '.', '.') }}</div>

    <!-- Key Details -->
    <div class="grid">
        @if($listing->gross_sqm)
        <div class="detail-item">
            <div class="detail-label">Brüt Alan</div>
            <div class="detail-value">{{ $listing->gross_sqm }} m²</div>
        </div>
        @endif
        @if($listing->net_sqm)
        <div class="detail-item">
            <div class="detail-label">Net Alan</div>
            <div class="detail-value">{{ $listing->net_sqm }} m²</div>
        </div>
        @endif
        @if($listing->room_count)
        <div class="detail-item">
            <div class="detail-label">Oda Sayısı</div>
            <div class="detail-value">{{ $listing->room_count }}+{{ $listing->living_room_count ?? 1 }}</div>
        </div>
        @endif
        @if($listing->bathroom_count)
        <div class="detail-item">
            <div class="detail-label">Banyo</div>
            <div class="detail-value">{{ $listing->bathroom_count }}</div>
        </div>
        @endif
        @if($listing->floor_number)
        <div class="detail-item">
            <div class="detail-label">Kat</div>
            <div class="detail-value">{{ $listing->floor_number }} / {{ $listing->total_floors ?? '?' }}</div>
        </div>
        @endif
        @if($listing->building_age)
        <div class="detail-item">
            <div class="detail-label">Bina Yaşı</div>
            <div class="detail-value">{{ $listing->building_age }} Yıl</div>
        </div>
        @endif
        <div class="detail-item">
            <div class="detail-label">İlan Tipi</div>
            <div class="detail-value">{{ $listing->listing_type === 'sale' ? 'Satılık' : ($listing->listing_type === 'rent' ? 'Kiralık' : ucfirst($listing->listing_type ?? '-')) }}</div>
        </div>
        <div class="detail-item">
            <div class="detail-label">Kategori</div>
            <div class="detail-value">{{ ucfirst($listing->category ?? $listing->type ?? '-') }}</div>
        </div>
    </div>

    <!-- Description -->
    @if($listing->description)
    <div class="section-title">Açıklama</div>
    <div class="description">{{ Str::limit($listing->description, 800) }}</div>
    @endif

    <!-- Features -->
    @if($listing->features && count($listing->features) > 0)
    <div class="section-title">Özellikler</div>
    <div class="features">
        @foreach(array_slice($listing->features, 0, 20) as $feature)
        <span class="feature-tag">{{ $feature }}</span>
        @endforeach
    </div>
    @endif

    <!-- Agent -->
    @if($listing->agent)
    <div class="agent-box">
        <div class="agent-avatar">{{ strtoupper(substr($listing->agent->name ?? 'D', 0, 1)) }}</div>
        <div>
            <div style="font-weight: 700; font-size: 11pt;">{{ $listing->agent->name ?? '-' }}</div>
            <div style="font-size: 9pt; color: #64748b;">{{ $listing->agent->phone ?? '' }}{{ $listing->agent->phone && $listing->agent->email ? ' · ' : '' }}{{ $listing->agent->email ?? '' }}</div>
        </div>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <span>RE-OS Gayrimenkul Yönetim Sistemi</span>
        <span>{{ now()->format('d.m.Y') }}</span>
        <span>{{ $listing->reference_no ?? 'REF-' . $listing->id }}</span>
    </div>
</div>
</body>
</html>
