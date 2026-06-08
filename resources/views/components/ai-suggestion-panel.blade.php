@if($observation->hasAiSuggestion())
    <div class="alert alert-info" id="ai-suggestion-panel">
        @if($observation->isHeuristicSuggestion())
            <div class="alert alert-error" style="margin-bottom:0.75rem">
                <strong>Let op:</strong> dit is een basisvoorstel (25% zekerheid) zonder echte Google/OpenAI-analyse.
                Voeg <code>GOOGLE_AI_API_KEY</code> toe op de server en klik <em>Opnieuw analyseren</em>.
            </div>
        @endif
        <strong>AI-voorstel</strong>
        @if($observation->aiProviderLabel())
            <span class="meta">via {{ $observation->aiProviderLabel() }}</span>
        @endif
        @if($observation->aiConfidence())
            <span class="meta">· zekerheid {{ $observation->aiConfidence() }}%</span>
        @endif
        <ul style="margin:0.75rem 0 0;padding-left:1.2rem">
            @if($observation->suggestedField('species'))
                <li><strong>Soort:</strong> {{ $observation->suggestedField('species') }}</li>
            @endif
            @if($observation->suggestedField('count_label'))
                <li><strong>Aantal:</strong> {{ $observation->suggestedField('count_label') }}</li>
            @endif
            @if($observation->suggestedField('behavior'))
                <li><strong>Gedrag:</strong> {{ $observation->suggestedField('behavior') }}</li>
            @endif
            @if($observation->suggestedField('season'))
                <li><strong>Seizoen:</strong> {{ $observation->suggestedField('season') }}</li>
            @endif
            @if($observation->suggestedField('story_line'))
                <li><strong>Verhaalregel:</strong> {{ $observation->suggestedField('story_line') }}</li>
            @endif
        </ul>
        <p style="margin:0.75rem 0 0">
            <button type="button" class="btn btn-secondary" id="apply-ai-suggestion">Velden invullen met AI-voorstel</button>
            @if($aiEnabled ?? false)
                <form action="{{ route('annotate.ai', $observation) }}" method="post" style="display:inline;margin-left:0.5rem">
                    @csrf
                    <button type="submit" class="btn btn-secondary">Opnieuw analyseren</button>
                </form>
            @endif
        </p>
        <p class="meta" style="margin-top:0.5rem">Controleer altijd het voorstel — jij beslist wat er live gaat. Na <em>Opnieuw analyseren</em> even de pagina verversen.</p>
    </div>

    @push('head')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const button = document.getElementById('apply-ai-suggestion');
            if (!button) return;

            const values = {
                species: @json($observation->suggestedField('species')),
                count_label: @json($observation->suggestedField('count_label')),
                behavior: @json($observation->suggestedField('behavior')),
                season: @json($observation->suggestedField('season')),
                story_line: @json($observation->suggestedField('story_line')),
                caption: @json($observation->suggestedField('caption')),
            };

            const limits = { story_line: 200, behavior: 160 };

            function clip(text, max) {
                if (!text || text.length <= max) return text;
                let cut = text.slice(0, max);
                const lastSpace = cut.lastIndexOf(' ');
                if (lastSpace > max * 0.6) cut = cut.slice(0, lastSpace);
                return cut.replace(/[.,;:!?…]+$/, '') + '…';
            }

            button.addEventListener('click', function () {
                Object.entries(values).forEach(([id, value]) => {
                    if (!value) return;
                    const field = document.getElementById(id);
                    if (!field || field.value) return;
                    field.value = limits[id] ? clip(value, limits[id]) : value;
                });
            });
        });
    </script>
    @endpush
@elseif($aiEnabled ?? false)
    <div class="alert alert-info">
        @if($aiConfigured ?? false)
            AI-analyse wordt uitgevoerd of is nog niet klaar.
            <form action="{{ route('annotate.ai', $observation) }}" method="post" style="display:inline;margin-left:0.5rem">
                @csrf
                <button type="submit" class="btn btn-secondary">Nu analyseren</button>
            </form>
        @else
            Geen AI-sleutel geconfigureerd — vul de velden handmatig in of voeg <code>GOOGLE_AI_API_KEY</code> toe.
        @endif
    </div>
@endif
