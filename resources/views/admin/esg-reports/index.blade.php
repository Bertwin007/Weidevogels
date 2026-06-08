@extends('layouts.app')

@section('title', 'ESG-rapporten')

@section('content')
<div class="container">
    <p class="meta"><a href="{{ route('admin.dashboard') }}">← Beheer</a></p>
    <h1>ESG biodiversiteits-bewijs</h1>
    <p class="lead">Genereer per bedrijfspartner een geverifieerd biodiversiteits-rapport (HTML + PDF) op basis van gepubliceerde Greide-scans en expertannotatie.</p>

    @if($errors->any())
        <div class="alert alert-error">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="get" class="card card-body" style="margin-bottom:1.25rem">
        <label for="season">Seizoen (jaar)</label>
        <div style="display:flex;flex-wrap:wrap;gap:0.75rem;align-items:end">
            <select name="season" id="season" style="margin:0;min-width:8rem">
                @foreach($seasonOptions as $year)
                    <option value="{{ $year }}" @selected($season === $year)>{{ $year }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn">Toon partners</button>
        </div>
    </form>

    @if($partners === [])
        <div class="card card-body">
            <p>Geen gepubliceerde partnermomenten gevonden voor seizoen <strong>{{ $season }}</strong>.</p>
            <p class="meta" style="margin-top:0.75rem">Publiceer eerst inzendingen met bedrijfsnaam (<code>guest_name</code>) via <a href="{{ route('admin.submissions.index') }}">Inzendingen</a>.</p>
        </div>
    @else
        <div class="card">
            <table>
                <thead>
                    <tr>
                        <th>Bedrijf</th>
                        <th>E-mail</th>
                        <th>Gebied</th>
                        <th>Gepubliceerd</th>
                        <th>Laatste moment</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($partners as $partner)
                        <tr>
                            <td><strong>{{ $partner['company'] }}</strong></td>
                            <td>{{ $partner['email'] ?? '—' }}</td>
                            <td>{{ $partner['project'] ?? '—' }}</td>
                            <td>{{ $partner['published'] }}</td>
                            <td>{{ $partner['latest'] ?? '—' }}</td>
                            <td style="white-space:nowrap">
                                <a href="{{ route('admin.esg-reports.show', ['partnerSlug' => $partner['slug'], 'season' => $season]) }}" target="_blank" rel="noopener">Preview</a>
                                ·
                                <a href="{{ route('admin.esg-reports.pdf', ['partnerSlug' => $partner['slug'], 'season' => $season]) }}">PDF</a>
                                ·
                                @include('components.esg-report-send-form', [
                                    'partnerSlug' => $partner['slug'],
                                    'season' => $season,
                                    'email' => $partner['email'],
                                    'compact' => true,
                                ])
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <p class="meta" style="margin-top:1.25rem">
        Partnerpakket en leefgebied-cijfers zijn instelbaar via <code>config/esg.php</code> (per bedrijfsslug).
        Habitat-velden zonder data tonen een streepje in het rapport.
    </p>
</div>
@endsection
