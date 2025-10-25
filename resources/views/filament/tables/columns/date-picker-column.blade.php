@php
    $state = $getState();
    $formattedDate = $state ? \Carbon\Carbon::parse($state)->format('d.m.Y') : null;
    $dateInputValue = $state ? \Carbon\Carbon::parse($state)->format('Y-m-d') : null;

    // Calculate due date status
    $dueStatus = null;
    $daysRemaining = 0;
    if ($state) {
        $dueDate = \Carbon\Carbon::parse($state);
        $daysRemaining = $dueDate->diffInDays(now(), false);

        if ($dueDate->isPast()) {
            $dueStatus = 'overdue';
        } elseif ($dueDate->isToday()) {
            $dueStatus = 'today';
        } elseif ($daysRemaining <= 3) {
            $dueStatus = 'soon';
        } else {
            $dueStatus = 'normal';
        }
    }
@endphp

<div
    x-data="{
        state: @js($state),
        selectedDate: @js($formattedDate),
        inputValue: @js($dateInputValue),
        dueStatus: @js($dueStatus),

        updateDate(newDate) {
            this.inputValue = newDate;
            this.state = newDate;
            this.selectedDate = newDate ? new Date(newDate).toLocaleDateString('hr-HR') : null;

            // Calculate new status
            if (newDate) {
                const dueDate = new Date(newDate);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                dueDate.setHours(0, 0, 0, 0);

                const diffTime = dueDate - today;
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                if (diffDays < 0) {
                    this.dueStatus = 'overdue';
                } else if (diffDays === 0) {
                    this.dueStatus = 'today';
                } else if (diffDays <= 3) {
                    this.dueStatus = 'soon';
                } else {
                    this.dueStatus = 'normal';
                }
            } else {
                this.dueStatus = null;
            }

            $wire.updateTableColumnState(@js($getName()), @js($getRecordKey()), newDate);
        }
    }"
    class="relative z-10"
    @click.stop
>
    <!-- Direct Date Input -->
    <div class="date-picker-wrapper" :class="{ 'empty-state': !inputValue }"
         @click="$event.stopPropagation(); $refs.dateInput.showPicker()">
        <div class="date-content">
            <!-- Status indicators based on due date -->
            <template x-if="inputValue">
                <div class="status-indicator">
                    <!-- Overdue: Red X -->
                    <x-filament::icon
                        :icon="\Filament\Support\Icons\Heroicon::OutlinedXCircle"
                        class="status-icon overdue"
                        x-show="dueStatus === 'overdue'"
                    />

                    <!-- Today: Orange circle -->
                    <div
                        class="status-circle today"
                        x-show="dueStatus === 'today'"
                    ></div>

                    <!-- Soon (within 3 days): Yellow circle -->
                    <div
                        class="status-circle soon"
                        x-show="dueStatus === 'soon'"
                    ></div>

                    <!-- Normal (more than 3 days): Green circle -->
                    <div
                        class="status-circle normal"
                        x-show="dueStatus === 'normal'"
                    ></div>
                </div>
            </template>

            <!-- Show calendar icon for empty state -->
            <x-filament::icon
                :icon="\Filament\Support\Icons\Heroicon::OutlinedCalendarDays"
                class="calendar-icon"
                x-show="!inputValue"
            />

            <!-- Show date text when has value -->
            <span x-show="inputValue" class="date-text" x-text="selectedDate"></span>

            <!-- Show placeholder when empty -->
            <span x-show="!inputValue" class="placeholder-text flex">
                <x-filament::icon :icon="\Filament\Support\Icons\Heroicon::OutlinedPlusCircle" class="pt-[2px]"/>
            </span>

            <!-- Hidden date input that covers the entire area -->
            <input
                type="date"
                :value="inputValue"
                x-on:change="updateDate($event.target.value)"
                x-ref="dateInput"
                class="date-input-hidden"
                style="position: absolute; opacity: 0; pointer-events: none; z-index: -1;"
            />
        </div>
    </div>
</div>
