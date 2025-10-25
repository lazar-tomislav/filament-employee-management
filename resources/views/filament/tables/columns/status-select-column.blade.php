@php
    $currentState = $getState();
    $currentEnum = $currentState;

    // Get all enum options
    $enumOptions = collect($enumClass::cases())->map(function($case) {
        return [
            'value' => $case->value,
            'label' => $case->getLabel(),
            'color' => $case->getColor(),
        ];
    })->toArray();
@endphp

<div
    x-data="{
        isOpen: false,
        currentState: @js($currentEnum?->value),
        enumOptions: @js($enumOptions),

        selectStatus(value) {
            this.currentState = value;
            $wire.updateTableColumnState(@js($getName()), @js($getRecordKey()), value);
            this.isOpen = false;
        },

        getCurrentOption() {
            return this.enumOptions.find(option => option.value === this.currentState) || null;
        },

        getStatusColor(colorName) {
            const colorMap = {
                'gray': '#C4C4C4',
                'purple': '#a855f7',
                'orange': '#fb923c',
                'lime': '#a3e635',
                'green': '#22c55e',
                'warning': '#FDAB3D',
                'success': '#00C875',
                'info': '#579BFC',
                'danger': '#DF2F4A'
            };
            return colorMap[colorName] || '#C4C4C4';
        }
    }"
    class="relative w-full h-full"
    @click.stop
    {{ $getExtraAttributeBag() }}
>
    <!-- Status Cell (full width/height like Monday.com) -->
    <div
        x-ref="trigger"
        x-on:click="$event.stopPropagation(); isOpen = !isOpen"
        class="status-cell-component w-full h-full flex items-center justify-center cursor-pointer relative overflow-hidden"
        :style="getCurrentOption() ? `background-color: ${getStatusColor(getCurrentOption().color)}` : 'background-color: #C4C4C4'"
    >
        <div class="text-white font-bold text-sm text-center px-2 uppercase">
            <span x-text="getCurrentOption() ? getCurrentOption().label : 'Nije odabrano'"></span>
        </div>
    </div>

    <!-- Dropdown Tooltip -->
    <template x-teleport="body">
        <div
            x-show="isOpen"
            x-on:click.away="isOpen = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="fixed z-[9999] bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
            style="width: 200px; max-width: 200px;"
            x-anchor.bottom-start="$refs.trigger"
        >
            <!-- Status Options List -->
            <div class="status-picker-content">
                <ul role="listbox" class="py-2">
                    <template x-for="option in enumOptions" :key="option.value">
                        <li
                            role="option"
                            x-on:click="selectStatus(option.value)"
                            class="status-option cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150"
                            :class="{ 'bg-blue-50 dark:bg-blue-900/20': currentState === option.value }"
                        >
                            <div
                                class="status-color-background w-full px-3 py-2 flex items-center justify-center min-h-[36px]"
                                :style="`background-color: ${getStatusColor(option.color)}`"
                            >
                                <div class="text-white font-bold uppercase text-sm text-center">
                                    <span x-text="option.label" ></span>
                                </div>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>
        </div>
    </template>
</div>
