@php
    $d = $data;
    $brand = config('esg.brand', 'Greidefûgels');
@endphp
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Biodiversiteits-bewijs — {{ $d['partner']['company'] }}</title>
@if($preview ?? false)
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
@endif
<style>
  :root{--green:#2f6b2e;--green-d:#1f4a1f;--green-l:#eaf2e6;--gold:#d98a2b;--gold-d:#b06d1c;--ink:#22301c;--muted:#5b6b52;--line:#e3e8dd;--cream:#f7f5ee;}
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Poppins','DejaVu Sans',sans-serif;color:var(--ink);background:{{ ($preview ?? false) ? '#cdd6cf' : '#fff' }};line-height:1.55;-webkit-font-smoothing:antialiased}
  .toolbar{max-width:210mm;margin:18px auto 10px;display:flex;gap:12px;align-items:center;justify-content:center;flex-wrap:wrap}
  .toolbar a,.toolbar button{font-family:inherit;font-weight:700;border:none;cursor:pointer;border-radius:999px;padding:.7em 1.5em;background:var(--gold);color:#fff;text-decoration:none;display:inline-block}
  .toolbar span{font-size:.85rem;color:var(--green-d);font-weight:600}
  .page{width:210mm;min-height:297mm;background:#fff;margin:0 auto 16px;padding:0;box-shadow:0 10px 40px rgba(0,0,0,.2);position:relative;overflow:hidden}
  .pad{padding:16mm 16mm 14mm}
  h1,h2,h3{line-height:1.12;font-weight:700}
  .eyebrow{font-weight:700;text-transform:uppercase;letter-spacing:.14em;font-size:8.5pt;color:var(--gold)}
  .hd{background:var(--green);color:#fff;padding:12mm 16mm 9mm;display:flex;justify-content:space-between;align-items:flex-start;gap:16px}
  .hd .brand{display:flex;align-items:center;gap:10px;font-weight:800;font-size:12pt}
  .hd .brand .mark{width:34px;height:34px;border-radius:50%;background:#fff;display:inline-grid;place-items:center}
  .hd .brand small{display:block;font-weight:600;font-size:6.5pt;letter-spacing:.1em;color:#cfe0c8;text-transform:uppercase}
  .hd .doc{text-align:right;font-size:8.5pt;color:#dbe7d2}
  .hd .doc b{display:block;font-size:14pt;color:#fff;font-weight:700}
  .proof{margin:-7mm 16mm 0;background:#fff;border:1.5px solid var(--line);border-radius:12px;box-shadow:0 8px 24px rgba(31,74,31,.10);padding:18px 22px;display:flex;gap:18px;align-items:center}
  .proof .seal{flex:none;width:74px;height:74px}
  .proof .ptxt h2{font-size:15pt;color:var(--green-d)}
  .proof .ptxt p{font-size:9.5pt;color:var(--muted);margin-top:3px}
  .proof .ptxt b{color:var(--ink)}
  .kpis{display:table;width:100%;border-collapse:separate;border-spacing:10px;margin:6px 0 4px}
  .kpis .k{display:table-cell;width:20%;background:var(--green-l);border-radius:10px;padding:12px;text-align:center;vertical-align:middle}
  .kpis .k b{display:block;font-size:17pt;color:var(--green-d);line-height:1}
  .kpis .k span{font-size:7.5pt;color:var(--muted)}
  .sec{margin-top:9mm}
  .sec h3{font-size:11.5pt;color:var(--green-d);margin-bottom:6px;border-bottom:2px solid var(--green-l);padding-bottom:4px}
  table.sp{width:100%;border-collapse:collapse;font-size:9pt}
  table.sp th{text-align:left;color:var(--muted);font-size:7.5pt;text-transform:uppercase;letter-spacing:.04em;border-bottom:1px solid var(--line);padding:6px 4px}
  table.sp td{padding:7px 4px;border-bottom:1px solid var(--line)}
  table.sp .nm b{color:var(--ink)} table.sp .nm i{color:var(--muted);font-style:normal;font-size:8pt}
  .trend{font-weight:700;font-size:8.5pt;border-radius:999px;padding:1px 8px}
  .up{background:#e7f3e0;color:var(--green-d)} .same{background:#eef0ea;color:var(--muted)} .down{background:#fdeede;color:var(--gold-d)} .new{background:#e7eef7;color:#37618e}
  .bars{display:table;width:100%;border-spacing:10px}
  .bar{display:table-cell;width:25%;background:var(--cream);border:1px solid var(--line);border-radius:10px;padding:12px;vertical-align:top}
  .bar span{font-size:7.5pt;color:var(--muted);display:block}
  .bar b{font-size:12pt;color:var(--green-d)}
  .track{height:8px;background:var(--line);border-radius:999px;margin-top:6px;overflow:hidden}
  .track i{display:block;height:100%;background:var(--green)}
  .score{display:flex;align-items:center;gap:14px;background:var(--green);color:#fff;border-radius:12px;padding:14px 18px;margin-top:6px}
  .score .big{font-size:24pt;font-weight:800;line-height:1}
  .score .lbl{font-size:8.5pt;color:#dbe7d2}
  .score .meter{flex:1;height:10px;background:rgba(255,255,255,.25);border-radius:999px;overflow:hidden}
  .score .meter i{display:block;height:100%;background:var(--gold)}
  .two{display:table;width:100%;border-spacing:12px}
  .col{display:table-cell;width:50%;vertical-align:top;background:var(--cream);border:1px solid var(--line);border-radius:10px;padding:14px}
  .col h4{font-size:9.5pt;color:var(--green-d);margin-bottom:4px}
  .col p,.col li{font-size:8.5pt;color:var(--muted)} .col ul{margin:4px 0 0 14px}
  .share{background:var(--green-l);border:1px dashed var(--green);border-radius:10px;padding:12px 14px;font-size:8.5pt;color:var(--ink);margin-top:6px}
  .share b{color:var(--green-d)}
  .disclaim{font-size:7pt;color:var(--muted);margin-top:8mm;border-top:1px solid var(--line);padding-top:6px}
  .pgfoot{position:absolute;bottom:8mm;left:16mm;right:16mm;display:flex;justify-content:space-between;font-size:7pt;color:var(--muted)}
  @media print{
    body{background:#fff}
    .toolbar{display:none}
    .page{box-shadow:none;margin:0;width:auto;min-height:auto;page-break-after:always;break-after:page}
    .page:last-child{page-break-after:auto;break-after:auto}
    @page{size:A4;margin:0}
  }
</style>
</head>
<body>
@if($preview ?? false)
<div class="toolbar">
    <a href="{{ route('admin.esg-reports.pdf', ['partnerSlug' => \App\Support\PartnerSlug::fromCompany($d['partner']['company']), 'season' => $d['report']['season']]) }}">PDF downloaden</a>
    <a href="{{ route('admin.esg-reports.index', ['season' => $d['report']['season']]) }}" style="background:var(--green)">← Terug naar beheer</a>
    <span>Geverifieerd biodiversiteits-bewijs · {{ $d['report']['nr'] }}</span>
</div>
@endif

<div class="page">
  <div class="hd">
    <div class="brand">
      <span class="mark"><svg viewBox="0 0 48 48" width="22" height="22" fill="none"><path d="M6 34c8-2 14 2 18-2s10-8 18-6c-3 8-12 12-20 12S10 36 6 34z" fill="#2f6b2e"/><path d="M30 14c4-3 9-3 11 0-3 2-8 2-11 0z" fill="#d98a2b"/></svg></span>
      <span>{{ $brand }}<small>Weidevogels · Fryslân</small></span>
    </div>
    <div class="doc">
      <b>Biodiversiteits-bewijs</b>
      Seizoen {{ $d['report']['season'] }} · rapport-nr. {{ $d['report']['nr'] }}<br>
      Gegenereerd {{ $d['report']['generatedAt'] }}
    </div>
  </div>

  <div class="proof">
    <svg class="seal" viewBox="0 0 100 100"><circle cx="50" cy="50" r="46" fill="none" stroke="#d98a2b" stroke-width="3" stroke-dasharray="4 5"/><path d="M22 56c10-4 18 2 24-3s14-9 30-6c-5 12-19 18-32 18S30 60 22 56z" fill="#2f6b2e"/><path d="M62 34c5-4 11-4 14 0-4 3-10 3-14 0z" fill="#d98a2b"/><text x="50" y="86" text-anchor="middle" font-family="Poppins,sans-serif" font-size="7" font-weight="700" fill="#1f4a1f">GEVERIFIEERD</text></svg>
    <div class="ptxt">
      <h2>{{ $d['partner']['company'] }} is Greide-partner</h2>
      <p>
        adopteert <b>{{ $d['partner']['adopted_m2_formatted'] }} m²</b> weidevogelgebied in
        <b>{{ $d['area']['name'] }}</b>@if(filled($d['area']['subtitle'])) ({{ $d['area']['subtitle'] }})@endif.
        In seizoen {{ $d['report']['season'] }} zijn op dit gebied
        <b>{{ $d['totals']['species'] }} soorten weidevogels</b> en
        <b>{{ $d['totals']['birds'] }} vogels</b> vastgesteld via beeldherkenning en expertverificatie.
      </p>
    </div>
  </div>

  <div class="pad" style="padding-top:8mm">
    <div class="kpis">
      <div class="k"><b>{{ $d['totals']['species'] }}</b><span>soorten</span></div>
      <div class="k"><b>{{ $d['totals']['birds'] }}</b><span>vogels geteld</span></div>
      <div class="k"><b>{{ $d['totals']['nests'] ?? '—' }}</b><span>nesten</span></div>
      <div class="k"><b>{{ $d['totals']['chicks'] ?? '—' }}</b><span>kuikens</span></div>
      <div class="k"><b>{{ $d['totals']['delta_label'] }}</b><span>t.o.v. {{ $d['report']['season'] - 1 }}</span></div>
    </div>

    <div class="sec">
      <h3>Waargenomen soorten</h3>
      <table class="sp">
        <tr><th style="width:42%">Soort</th><th>Aantal</th><th>Nesten</th><th>Trend vs {{ $d['report']['season'] - 1 }}</th></tr>
        @forelse($d['species'] as $species)
        <tr>
          <td class="nm">
            <b>{{ $species['nl'] }}</b>
            @if(filled($species['fy']))<i>· {{ $species['fy'] }}</i>@endif
          </td>
          <td>{{ $species['count'] }}</td>
          <td>{{ $species['nests'] ?? '—' }}</td>
          <td>
            @if(filled($species['trend_class']))
              <span class="trend {{ $species['trend_class'] }}">{{ $species['trend_label'] }}</span>
            @else
              —
            @endif
          </td>
        </tr>
        @empty
        <tr><td colspan="4">Geen geverifieerde soorten in dit seizoen.</td></tr>
        @endforelse
      </table>
    </div>

    <div class="sec">
      <h3>Soortenrijkdom-score</h3>
      <div class="score">
        <div class="big">{{ $d['richnessScore'] }}</div>
        <div style="flex:1">
          <div class="lbl">van de 100 — "rijk weidevogelgebied"</div>
          <div class="meter"><i style="width:{{ $d['richnessScore'] }}%"></i></div>
        </div>
      </div>
    </div>

    <div class="sec">
      <h3>Beheer &amp; leefgebied</h3>
      <div class="bars">
        @php
          $habitatRows = [
            ['label' => 'Waterpeil', 'value' => $d['habitat']['waterpeil_cm'], 'display' => filled($d['habitat']['waterpeil_cm']) ? '+'.$d['habitat']['waterpeil_cm'].' cm' : '—', 'pct' => $d['habitat']['waterpeil_cm'] ?? 0],
            ['label' => 'Kruidenrijk grasland', 'value' => $d['habitat']['kruidenrijk_pct'], 'display' => filled($d['habitat']['kruidenrijk_pct']) ? $d['habitat']['kruidenrijk_pct'].'%' : '—', 'pct' => $d['habitat']['kruidenrijk_pct'] ?? 0],
            ['label' => 'Laat gemaaid', 'value' => $d['habitat']['laat_gemaaid_pct'], 'display' => filled($d['habitat']['laat_gemaaid_pct']) ? $d['habitat']['laat_gemaaid_pct'].'%' : '—', 'pct' => $d['habitat']['laat_gemaaid_pct'] ?? 0],
            ['label' => 'Plas-dras', 'value' => $d['habitat']['plasdras_ha'], 'display' => filled($d['habitat']['plasdras_ha']) ? str_replace('.', ',', (string) $d['habitat']['plasdras_ha']).' ha' : '—', 'pct' => filled($d['habitat']['plasdras_ha']) ? min(100, (int) round($d['habitat']['plasdras_ha'] * 70)) : 0],
          ];
        @endphp
        @foreach($habitatRows as $row)
        <div class="bar">
          <span>{{ $row['label'] }}</span>
          <b>{{ $row['display'] }}</b>
          <div class="track"><i style="width:{{ $row['pct'] }}%"></i></div>
        </div>
        @endforeach
      </div>
    </div>

    <div class="pgfoot"><span>{{ $brand }} · Biodiversiteits-bewijs {{ $d['report']['nr'] }}</span><span>Pagina 1 / 2</span></div>
  </div>
</div>

<div class="page">
  <div class="pad">
    <span class="eyebrow">Verantwoording &amp; gebruik</span>
    <h2 style="font-size:16pt;color:var(--green-d);margin:2px 0 10px">Hoe deze gegevens tot stand kwamen</h2>

    <div class="two">
      <div class="col">
        <h4>Methode &amp; herkomst</h4>
        <ul>
          <li>{{ $d['method']['photos'] }} ingestuurde foto's uit {{ $d['method']['teamdays'] }} teamdag(en) ({{ $d['method']['period'] }})</li>
          <li>Eerste herkenning via AI-beeldherkenning</li>
          <li>Annotatie &amp; verificatie door ecologen / vogelwacht</li>
          <li>Elke waarneming {{ $d['method']['geotagged'] ? 'geotag + tijdstempel (provenance)' : 'met tijdstempel (provenance)' }}</li>
          <li>Tellingen op gebiedsniveau, geen individuele tracking</li>
        </ul>
      </div>
      <div class="col">
        <h4>Gebied</h4>
        <p>
          <b>{{ $d['area']['name'] }}</b>
          @if(filled($d['area']['subtitle'])) — "{{ $d['area']['subtitle'] }}"@endif,
          {{ $d['area']['ha'] }} ha greppelland.
          @if(filled($d['area']['description'])) {{ $d['area']['description'] }} @else Beheer in samenwerking met de boer en de vogelwacht. @endif
        </p>
        <p style="margin-top:6px">
          Adoptie: {{ $d['partner']['adopted_m2_formatted'] }} m² · pakket "{{ $d['partner']['package'] }}"
          @if(filled($d['partner']['partnerSince'])) · partner sinds {{ $d['partner']['partnerSince'] }}@endif.
        </p>
      </div>
    </div>

    <div class="sec">
      <h3>Gebruik in uw duurzaamheidsverhaal</h3>
      <div class="two">
        <div class="col">
          <h4>Waarvoor bruikbaar</h4>
          <ul>
            <li>Onderbouwing van vrijwillige biodiversiteitsacties</li>
            <li>Input voor de E4-paragraaf (biodiversiteit) van uw rapportage</li>
            <li>Verhaal &amp; beeld voor jaarverslag, website en social</li>
            <li>Aantoonbaar lokaal, meetbaar en geverifieerd resultaat</li>
          </ul>
        </div>
        <div class="col">
          <h4>Belangrijk (eerlijk)</h4>
          <p>Dit is een <b>onderbouwend bewijs- en verhaaldocument</b>, geen gecertificeerd CSRD-/ESRS-compliancerapport en geen wettelijke biodiversiteitscompensatie. Gebruik het als bron en illustratie binnen uw eigen rapportageproces.</p>
        </div>
      </div>
    </div>

    <div class="sec">
      <h3>Deel dit</h3>
      <div class="share"><b>Kant-en-klaar voor uw kanalen:</b> "{{ $d['share'] }}"</div>
    </div>

    <p class="disclaim">
      {{ $brand }} · Agrarisch Natuurfonds Fryslân. Methode: AI-beeldherkenning + menselijke expertverificatie;
      waarnemingen op gebiedsniveau met geotag en tijdstempel waar beschikbaar.
      Rapport gebaseerd op {{ count($d['observation_ids']) }} geverifieerde momenten (ID's: {{ implode(', ', $d['observation_ids']) }}).
      Vragen over methodiek of herkomst: via uw partnercontact bij ANF.
    </p>

    <div class="pgfoot"><span>{{ $brand }} · Biodiversiteits-bewijs {{ $d['report']['nr'] }}</span><span>Pagina 2 / 2</span></div>
  </div>
</div>
</body>
</html>
