<div wire:init="onModalLoad">
    <x-filament::modal
        id="edit-entity-modal"
        sticky-header="true"
        sticky-footer="true"
        :slide-over="true"
        width="4xl"
    >
        <x-slot name="heading">
            @if($entity)
                Entitet
            @else
                Novi entitet
            @endif
        </x-slot>

        <div class="w-full justify-start">
            <x-filament::tabs>
                <x-filament::tabs.item
                    :icon="\Filament\Support\Icons\Heroicon::OutlinedHome"
                    :active="$activeTab === 'updates'"
                    wire:click="$set('activeTab', 'updates')"
                >
                    Ažuriranja
                </x-filament::tabs.item>

                <x-filament::tabs.item
                    :active="$activeTab === 'entityEdit'"
                    :icon="\Filament\Support\Icons\Heroicon::OutlinedPencil"
                    wire:click="$set('activeTab', 'entityEdit')"
                >
                    Uredi
                </x-filament::tabs.item>

            </x-filament::tabs>

            <div class="mt-4">
                @if($activeTab === 'updates')
                    @if($entity)
                        <livewire:filament-employee-management::entity-activity
                            :entity="$entity"
                            :entityType="$entityType"
                            :key="'entity-activity-'.$entity->id"
                        />
                    @endif
                @endif

                @if($activeTab === 'entityEdit')
                    <div wire:key="entity-form-{{ $entity?->id ?? 'new' }}">
                        {{$this->entityForm}}
                    </div>
                @endif

            </div>
        </div>

        <x-slot name="footer">
            {{$this->saveAction}}
            {{$this->cancelAction}}
        </x-slot>

    </x-filament::modal>

    <x-filament-actions::modals/>
</div>
