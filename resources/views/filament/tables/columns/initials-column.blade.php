@php

    $state = $getState();
    $employee = $state ? \Amicus\FilamentEmployeeManagement\Models\Employee::find($state) : null;
    $employees = collect(\Amicus\FilamentEmployeeManagement\Models\Employee::options())->map(function($name, $id) {
        $emp = \Amicus\FilamentEmployeeManagement\Models\Employee::find($id);
        return [
            'id' => (int) $id,  // Ensure it's integer
            'name' => $name,
            'initials' => $emp ? $emp->initials : '',
        ];
    })->values()->toArray();
@endphp

<div
        x-data="{
        state: @js($state),
        isOpen: false,
        selectedEmployee: @js($employee ? ['id' => $employee->id, 'name' => $employee->full_name, 'initials' => $employee->initials] : null),
        employees: @js($employees),

        init() {
            this.loadSelectedEmployee();
        },

        loadSelectedEmployee() {
            if (this.state) {
                const employee = this.employees.find(emp => {
                    // Check if state is initials or ID
                    return emp.id == this.state || emp.initials === this.state;
                });
                this.selectedEmployee = employee || null;
            }
        },

        selectEmployee(employee) {
            this.selectedEmployee = employee;
            this.state = employee.id;
            $wire.updateTableColumnState(@js($getName()), @js($getRecordKey()), employee.id);
            this.isOpen = false;
        },

        clearSelection() {
            this.selectedEmployee = null;
            this.state = null;
            $wire.updateTableColumnState(@js($getName()), @js($getRecordKey()), null);
            this.isOpen = false;
        }
    }"
        class="relative"
        @click.stop
>
    <!-- Initials Circle Display -->
    <div
            x-ref="trigger"
            x-on:click="$event.stopPropagation(); isOpen = !isOpen"
            class="initials-circle-wrapper cursor-pointer "
            :class="{ 'ring-2 ring-blue-500': isOpen }"
    >
        <div class="initials-circle w-full">
            <div class="initials-text">
                <span x-text="selectedEmployee ? selectedEmployee.initials : '?'"></span>
            </div>
        </div>
    </div>

    <!-- Dropdown Popup -->
    <template x-teleport="body">
        <div
                x-show="isOpen"
                x-on:click.away="isOpen = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95 transform translate-y-2"
                x-transition:enter-end="opacity-100 scale-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 scale-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 transform translate-y-2"
                class="fixed z-[9999] w-72 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 max-h-80 overflow-y-auto"
                x-anchor.bottom-center="$refs.trigger"
        >
            <!-- Header -->
            <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Zadužena osoba</h3>
            </div>

            <!-- Employee List -->
            <ul class="py-2">
                <!-- Clear Selection Option -->
                <li>
                    <button
                            type="button"
                            x-on:click="clearSelection()"
                            class="w-full px-4 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-3 text-gray-500 dark:text-gray-400"
                    >
                        <div class="initials-circle-small empty">
                            <span class="text-xs">?</span>
                        </div>
                        <span class="text-sm">Nedodjeljeno</span>
                    </button>
                </li>

                <!-- Separator -->
                <li class="border-t border-gray-200 dark:border-gray-700 my-1"></li>

                <!-- Employee Options -->
                <template x-for="employee in employees" :key="employee.id">
                    <li>
                        <button
                                type="button"
                                x-on:click="selectEmployee(employee)"
                                class="w-full px-4 py-2 text-left hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center gap-3"
                                :class="{ 'bg-blue-50 dark:bg-blue-900/20': selectedEmployee && selectedEmployee.id === employee.id }"
                        >
                            <div class="initials-circle-small">
                                <span class="text-xs font-semibold text-gray-700 dark:text-gray-200"
                                      x-text="employee.initials"></span>
                            </div>
                            <span class="text-sm text-gray-900 dark:text-white" x-text="employee.name"></span>
                        </button>
                    </li>
                </template>
            </ul>
        </div>
    </template>
</div>

<style>

</style>
