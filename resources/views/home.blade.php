@extends('layouts.partner')

@section('title', 'Greidefugels')

@section('content')
<div class="note">Greidefugels.nl · Agrarisch Natuurfonds Fryslân · De Greide-scan is hoe u een greidemoment deelt en verifieert</div>

<header class="partner-header">
    <div class="wrap nav">
        <a class="logo" href="{{ route('home') }}">
            <span class="mark">
                <svg viewBox="0 0 48 48" fill="none" aria-hidden="true">
                    <path d="M6 34c8-2 14 2 18-2s10-8 18-6c-3 8-12 12-20 12S10 36 6 34z" fill="#cfe0c8"/>
                    <path d="M30 14c4-3 9-3 11 0-3 2-8 2-11 0z" fill="#d98a2b"/>
                </svg>
            </span>
            <span>Greidefugels<small>Weidevogels · Agrarisch Natuurfonds Fryslân</small></span>
        </a>
        <div class="nav-links">
            <a href="#scan">Greide-scan</a>
            <a href="#momenten">Momenten</a>
            <a href="{{ route('donate') }}">Steun ANF</a>
        </div>
    </div>
</header>

<div id="greide-scan-app" data-scan-url="{{ $scanUrl }}" data-submit-url="{{ $submitUrl }}" data-area-name="Ljippelân Workum">

<section class="hero">
    <div class="wrap">
        <span class="eyebrow" style="color:#ffd98a">Voor ondernemers in Fryslân</span>
        <h1>Bewijs, geen <em>belofte</em>.</h1>
        <p class="lead">Adopteer een echt Fries weidegebied en krijg meetbare biodiversiteit terug. Via de Greide-scan deelt u foto's, herkent AI de weidevogels en onze experts maken er geverifieerd biodiversiteits-bewijs van voor uw CSRD- en MVO-verhaal.</p>
        <div class="hero-cta">
            <a class="btn" href="#scan">Start Greide-scan</a>
            <a class="btn btn--ghost" href="#pakketten">Bekijk partnerpakketten</a>
        </div>
        @if($stats['moments'] > 0)
            <div class="hero-tags">
                <span><b>{{ $stats['moments'] }}</b> greidemomenten</span>
                <span><b>{{ $stats['donations'] }}</b> keer gesteund</span>
                <span><b>Geverifieerd</b> door experts</span>
                <span><b>CSRD-</b>bruikbaar bewijs</span>
            </div>
        @else
            <div class="hero-tags">
                <span><b>Exclusieve</b> grondwaarheid</span>
                <span><b>Geverifieerd</b> door experts</span>
                <span><b>CSRD-</b>bruikbaar bewijs</span>
                <span><b>Teamdag</b> met de vogelwacht</span>
            </div>
        @endif
    </div>
</section>

<section id="scan" class="scan">
    <div class="wrap">
        <div class="section-head">
            <span class="eyebrow">Deel uw greidemoment</span>
            <h2>De Greide-scan</h2>
            <p>Upload een foto uit een weidegebied. De scan herkent weidevogels, schrijft een verhaalregel en toelichting, en maakt een biodiversiteits-bewijs — klaar om in te zenden voor verificatie.</p>
        </div>
        <div class="scan-wrap">
            <div>
                <div class="drop" id="drop">
                    <input type="file" id="file" accept="image/*" hidden>
                    <div class="scanline"></div>
                    <div id="dropInner">
                        <div class="big" aria-hidden="true">📷</div>
                        <h3>Sleep een foto hierheen</h3>
                        <p>…of klik om een greidemoment te scannen (grutto, kievit, tureluur…)</p>
                    </div>
                </div>
                <div style="display:flex;gap:8px;margin-top:12px;flex-wrap:wrap">
                    <button type="button" class="btn btn--out" id="demoBtn" style="font-size:.9rem">Geen foto? Bekijk een voorbeeldscan</button>
                    <button type="button" class="btn btn--out" id="resetBtn" style="font-size:.9rem;display:none">Opnieuw</button>
                </div>
            </div>
            <div class="results" id="results">
                <div class="res-empty">Soorten, verhaalregel, toelichting en bewijs-kaart verschijnen hier zodra u de Greide-scan start.</div>
            </div>
        </div>
    </div>
</section>

<section>
    <div class="wrap">
        <div class="section-head">
            <span class="eyebrow">Zo werkt het partnerschap</span>
            <h2>Van teamdag naar bewijs in vier stappen</h2>
        </div>
        <div class="steps">
            <div class="step"><div class="num">1</div><h3>Adopteer een gebied</h3><p>Uw bedrijf koppelt zich aan een Friese weidevogelparel — vierkante meters met naam.</p></div>
            <div class="step"><div class="num">2</div><h3>Kom met het team</h3><p>Eén ochtend per seizoen op locatie met de vogelwacht. Scan wat jullie zien met de Greide-scan.</p></div>
            <div class="step"><div class="num">3</div><h3>Wij scannen &amp; verifiëren</h3><p>AI herkent de soorten, onze experts annoteren en valideren de waarnemingen.</p></div>
            <div class="step"><div class="num">4</div><h3>Ontvang uw bewijs</h3><p>Een biodiversiteits-rapport en bewijs-kaart, klaar voor uw CSRD/MVO-verhaal en kanalen.</p></div>
        </div>
    </div>
</section>

<section id="momenten" class="moments-section">
    <div class="wrap">
        <div class="section-head">
            <span class="eyebrow">Gepubliceerd bewijs</span>
            <h2>Laatste momenten</h2>
            <p>Geverifieerde greidemomenten van het Friese weideland — zo ziet uw biodiversiteits-bewijs eruit na expertannotatie.</p>
        </div>

        @if($moments->isEmpty())
            <p class="res-empty">Nog geen momenten gepubliceerd. <a href="#scan">Wees de eerste</a> die een Greide-scan inzendt.</p>
        @else
            <div class="moments-grid">
                @foreach($moments as $moment)
                    @if($moment->slug)
                        <a href="{{ route('moments.show', $moment) }}" class="moment-card">
                            @include('components.moment-photo', ['moment' => $moment])
                            <div class="moment-card-body">
                                <h3>{{ $moment->annotation?->story_line }}</h3>
                                <p>{{ $moment->annotation?->species }} · {{ $moment->project?->name ?? 'Ljippelân' }}</p>
                            </div>
                        </a>
                    @endif
                @endforeach
            </div>
            <p style="margin-top:1.5rem"><a href="{{ route('moments.index') }}">Alle momenten →</a></p>
        @endif
    </div>
</section>

<section id="pakketten" class="pkgs">
    <div class="wrap">
        <div class="section-head center">
            <span class="eyebrow">Partnerpakketten</span>
            <h2>Adopteer als verdienmodel voor uw verhaal</h2>
            <p>Jaarlijkse partnerschappen, gekoppeld aan een bestaande parel. Bedragen indicatief.</p>
        </div>
        <div class="pkg-grid">
            <div class="pkg">
                <h3>Spotter</h3><div class="m2">500 m² · 1 scan-rapport/jaar</div>
                <div class="price">€1.000<small> / jaar</small></div>
                <ul>
                    <li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4 10-11" stroke="#6f8f4e" stroke-width="3" fill="none" stroke-linecap="round"/></svg>Naamsvermelding + digitaal certificaat</li>
                    <li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4 10-11" stroke="#6f8f4e" stroke-width="3" fill="none" stroke-linecap="round"/></svg>1 biodiversiteits-bewijs per jaar</li>
                    <li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4 10-11" stroke="#6f8f4e" stroke-width="3" fill="none" stroke-linecap="round"/></svg>Toegang tot de Greide-scan</li>
                </ul>
                <a class="btn btn--green" href="#contact">Aanvragen</a>
            </div>
            <div class="pkg feat">
                <span class="flag">Meest gekozen</span>
                <h3>Wachter</h3><div class="m2">2.000 m² · kwartaalrapport</div>
                <div class="price">€3.500<small> / jaar</small></div>
                <ul>
                    <li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4 10-11" stroke="#6f8f4e" stroke-width="3" fill="none" stroke-linecap="round"/></svg>Logo op het gebiedsbord</li>
                    <li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4 10-11" stroke="#6f8f4e" stroke-width="3" fill="none" stroke-linecap="round"/></svg>Teamdag met de vogelwacht (1×)</li>
                    <li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4 10-11" stroke="#6f8f4e" stroke-width="3" fill="none" stroke-linecap="round"/></svg>Geverifieerd kwartaal-bewijs (CSRD-bruikbaar)</li>
                    <li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4 10-11" stroke="#6f8f4e" stroke-width="3" fill="none" stroke-linecap="round"/></svg>Beeld &amp; tekst voor uw kanalen</li>
                </ul>
                <a class="btn" href="#contact">Aanvragen</a>
            </div>
            <div class="pkg">
                <h3>Beschermheer</h3><div class="m2">5.000 m² · naamgeving</div>
                <div class="price">€8.000<small> / jaar</small></div>
                <ul>
                    <li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4 10-11" stroke="#6f8f4e" stroke-width="3" fill="none" stroke-linecap="round"/></svg>Alles uit Wachter, plus:</li>
                    <li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4 10-11" stroke="#6f8f4e" stroke-width="3" fill="none" stroke-linecap="round"/></svg>Uw naam aan een deelgebied</li>
                    <li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4 10-11" stroke="#6f8f4e" stroke-width="3" fill="none" stroke-linecap="round"/></svg>Soortencompetitie tussen partners</li>
                    <li><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 13l4 4 10-11" stroke="#6f8f4e" stroke-width="3" fill="none" stroke-linecap="round"/></svg>Jaarlijks persmoment + maatwerkdashboard</li>
                </ul>
                <a class="btn btn--green" href="#contact">Aanvragen</a>
            </div>
        </div>
    </div>
</section>

<section id="contact" style="background:linear-gradient(160deg,#2f6b2e,#173d16);color:#fff">
    <div class="wrap center" style="max-width:620px">
        <h2 style="color:#fff;font-size:clamp(1.6rem,3.6vw,2.3rem)">Klaar om uw gebied te adopteren?</h2>
        <p style="color:#e6efe0;margin:.7rem 0 1.4rem">Laat uw gegevens achter — ons team belt u terug voor een voorstel op maat, inclusief een eerste Greide-scan van het gebied.</p>
        <div style="display:flex;gap:8px;max-width:460px;margin:0 auto;flex-wrap:wrap;justify-content:center">
            <input id="leadCo" placeholder="Bedrijfsnaam" style="flex:1;min-width:180px;border:none;border-radius:9px;padding:.8em 1em;font-family:inherit">
            <input id="leadMail" type="email" placeholder="E-mail" style="flex:1;min-width:180px;border:none;border-radius:9px;padding:.8em 1em;font-family:inherit">
            <button type="button" class="btn" id="partnerLeadBtn">Word partner</button>
        </div>
    </div>
</section>

</div>

<footer class="partner-footer">
    <div class="wrap">
        <div class="foot">
            <div style="font-weight:700;color:#fff">Greidefugels · Agrarisch Natuurfonds Fryslân</div>
            <div style="display:flex;gap:1.2rem;font-size:.9rem">
                <a href="#scan">Greide-scan</a>
                <a href="{{ route('moments.index') }}">Momenten</a>
                <a href="{{ route('donate') }}">Steun ANF</a>
            </div>
        </div>
        <div class="foot-bottom">
            <span>© {{ date('Y') }} · greidefugels.nl</span>
            <span>De Greide-scan is de manier om greidemomenten te delen en te verifiëren</span>
        </div>
    </div>
</footer>
@endsection

@push('scripts')
<script src="{{ asset('js/greide-scan.js') }}" defer></script>
@endpush
