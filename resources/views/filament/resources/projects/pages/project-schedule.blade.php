<x-filament-panels::page>
    <div class="space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Plan Gradilišta</h2>
                <div class="flex gap-2">
                    {{-- Promjena: Uklonjeni gumbi za Tjedan i Mjesec --}}
                    <button id="btn-day"
                            class="px-3 py-1 text-sm bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded">
                        Dan
                    </button>
                </div>
            </div>
            <div id="gantt-container" class="p-4"></div>
        </div>
    </div>

    <x-filament-actions::modals/>

    @push('scripts')
        <script>
            window.addEventListener('DOMContentLoaded', function () {
                if (typeof Gantt === 'undefined') {
                    console.error('Frappe Gantt library not loaded');
                    return;
                }

                // Hardcoded projekti - kasnije iz baze
                const today = new Date();
                const tasks = [
                    {
                        id: 'projekt-1',
                        name: 'Solarni Projekt - Kuća Novak',
                        start: formatDate(addDays(today, -3)),
                        end: formatDate(addDays(today, 5)),
                        custom_class: 'project-active'
                    },
                    {
                        id: 'projekt-2',
                        name: 'Tvrtka XYZ - Montaža panela',
                        start: formatDate(addDays(today, 2)),
                        end: formatDate(addDays(today, 12)),
                        dependencies: '',
                        custom_class: 'project-pending'
                    },
                    {
                        id: 'projekt-3',
                        name: 'Škola - Elektroinstalacije',
                        start: formatDate(addDays(today, -7)),
                        end: formatDate(addDays(today, -2)),
                        custom_class: 'project-completed'
                    },
                    {
                        id: 'projekt-4',
                        name: 'Javni Objekt - Sunčana Energija',
                        start: formatDate(addDays(today, 6)),
                        end: formatDate(addDays(today, 20)),
                        dependencies: 'projekt-2',
                        custom_class: 'project-future'
                    },
                    {
                        id: 'projekt-5',
                        name: 'Servis - Održavanje Invertora',
                        start: formatDate(today),
                        end: formatDate(addDays(today, 2)),
                        custom_class: 'project-active'
                    }
                ];

                const gantt = new Gantt("#gantt-container", tasks, {
                    // Promjena: Zadani prikaz je 'Day'
                    view_mode: 'Day',
                    bar_height: 40,
                    // Promjena: Povećana širina stupca radi bolje čitljivosti u dnevnom prikazu
                    column_width: 50,
                    padding: 18,
                    language: 'hr',
                    date_format: 'DD.MM.YYYY',
                    popup_on: "click",
                    popup: function (task) {
                        console.log("task;", task.task);
                        console.log("task;", task.task.name);
                        return `
                        <div class="gantt-popup">
                            <h3>${task.task.name}</h3>
                        </div>
                    `;
                    },
                    custom_popup_html: null,
                    on_date_change: function (task, start, end) {
                        console.log('Promjena datuma:', task.name, start, end);
                        //Promjena datuma: Solarni Projekt - Kuća Novak Tue Sep 30 2025 00:00:00 GMT+0200 (Central European Summer Time) Thu Oct 09 2025 23:59:59 GMT+0200 (Central European Summer Time)
                    },
                });

                // View mode button
                document.getElementById('btn-day').addEventListener('click', function () {
                    gantt.change_view_mode('Day');
                });

                // Helper functions
                function addDays(date, days) {
                    const result = new Date(date);
                    result.setDate(result.getDate() + days);
                    return result;
                }

                function formatDate(date) {
                    const year = date.getFullYear();
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const day = String(date.getDate()).padStart(2, '0');
                    return `${year}-${month}-${day}`;
                }

                function formatDisplayDate(date) {
                    const day = String(date.getDate()).padStart(2, '0');
                    const month = String(date.getMonth() + 1).padStart(2, '0');
                    const year = date.getFullYear();
                    return `${day}.${month}.${year}`;
                }
            });
        </script>

        <style>
            .gantt-popup {
                padding: 8px;
            }

            .gantt-popup h3 {
                margin: 0 0 8px 0;
                font-size: 14px;
                font-weight: 600;
            }

            .gantt-popup p {
                margin: 4px 0;
                font-size: 12px;
            }
        </style>
    @endpush
</x-filament-panels::page>
