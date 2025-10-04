<x-filament-panels::page>
    <div class="space-y-4">
        <div id="gantt-container" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4"></div>
    </div>

    <x-filament-actions::modals/>

    @push('scripts')
    <script>
        window.addEventListener('DOMContentLoaded', function() {
            if (typeof Gantt === 'undefined') {
                console.error('Frappe Gantt library not loaded');
                return;
            }

            const tasks = [
                {
                    id: 'Task 1',
                    name: 'Redesign website',
                    start: '2024-01-01',
                    end: '2024-01-07',
                    progress: 20,
                    dependencies: ''
                },
                {
                    id: 'Task 2',
                    name: 'Write new content',
                    start: '2024-01-05',
                    end: '2024-01-15',
                    progress: 50,
                    dependencies: 'Task 1'
                },
                {
                    id: 'Task 3',
                    name: 'Apply new styles',
                    start: '2024-01-10',
                    end: '2024-01-20',
                    progress: 10,
                    dependencies: 'Task 2'
                }
            ];

            const gantt = new Gantt("#gantt-container", tasks);
        });
    </script>
    @endpush
</x-filament-panels::page>
