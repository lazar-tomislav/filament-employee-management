@php
    use App\Enums\StatusProjekta;

    $state = $getState() ?? [];
    $phases = [
        StatusProjekta::Priprema,
        StatusProjekta::Skladiste,
        StatusProjekta::Provedba,
        StatusProjekta::Finalizacija,
        StatusProjekta::Arhiviran,
    ];
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div class="flex items-center gap-3" {{ $getExtraAttributeBag() }}>
        @foreach($phases as $phase)
            @php
                $isSelected = in_array($phase->value, $state);
                $colors = $phase->getTailwindClasses();
            @endphp

            <button
                type="button"
                wire:click="toggleProjectPhase('{{ $phase->value }}')"
                @class([
                    'inline-flex items-center gap-2 px-4 py-2 rounded-lg font-medium text-sm transition-all duration-200 cursor-pointer border-2',
                    $colors['bg'] . ' text-white border-transparent shadow-sm hover:shadow-md' => $isSelected,
                    'bg-white dark:bg-gray-900 ' . $colors['text'] . ' ' . $colors['border'] . ' ' . $colors['hover'] => !$isSelected,
                ])
            >
                @if($isSelected)
                    <x-filament::icon
                        icon="heroicon-m-check-circle"
                        class="w-5 h-5"
                    />
                @else
                    <x-filament::icon
                        icon="heroicon-o-check-circle"
                        class="w-5 h-5"
                    />
                @endif

                <span>{{ $phase->getLabel() }}</span>
            </button>
        @endforeach
    </div>
</x-dynamic-component>
