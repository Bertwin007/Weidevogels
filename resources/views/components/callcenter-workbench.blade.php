@php $labels = ['hot' => 'Belreden', 'warm' => 'Opvolgen', 'new' => 'Nieuw']; @endphp

<div class="callcenter-workbench">
    <div class="kpis">
        <div class="kpi"><b>{{ $kpis['partners'] }}</b><span>actieve bedrijfspartners</span></div>
        <div class="kpi"><b>{{ $kpis['open_triggers'] }}</b><span>open belredenen vandaag</span></div>
        <div class="kpi"><b>{{ $kpis['scans_24h'] }}</b><span>scans laatste 24u</span></div>
        <div class="kpi"><b>{{ $kpis['areas_kuikens'] }}</b><span>gebieden met kuikens 🐣</span></div>
    </div>

    @if(empty($queue))
        <div class="callcenter-empty">Geen open belredenen — alle Greide-scans zijn verwerkt of er zijn nog geen inzendingen.</div>
    @else
        <div class="queue">
            <div class="qrow head">
                <div>Bedrijf / gebied</div>
                <div>Belreden (uit de data)</div>
                <div>Status</div>
                <div></div>
            </div>
            @foreach($queue as $row)
                <div class="qrow">
                    <div class="co">{{ $row['co'] }}<span>{{ $row['geb'] }}</span></div>
                    <div class="trigger">📸 <b>{{ $row['trig'] }}</b></div>
                    <div><span class="pill {{ $row['st'] }}">{{ $labels[$row['st']] ?? 'Nieuw' }}</span></div>
                    <div>
                        @if(! empty($row['observation_id']))
                            <a class="btn btn-secondary" style="font-size:.82rem;padding:.5em 1em" href="{{ route('admin.submissions.edit', $row['observation_id']) }}">Inzending openen</a>
                        @else
                            <span class="meta">{{ $row['act'] }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="scriptbox">
        <b>Belscript-suggestie:</b> "Goedemiddag, u spreekt met <i>[agent]</i> van Greidefugels. Ik bel omdat onze laatste Greide-scan op <b>úw</b> gebied <i>[gebied]</i> <b>[belreden]</b> liet zien — een mooi moment om met uw team langs te komen en het vast te leggen voor uw biodiversiteits-bewijs. Zal ik een datum in het broedseizoen voor u reserveren?"
    </div>
</div>
