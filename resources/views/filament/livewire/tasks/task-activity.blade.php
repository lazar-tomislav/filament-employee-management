<div class="space-y-6" x-data="{ scrollToTop() { this.$el.scrollIntoView({ behavior: 'smooth' }); } }" @scroll-to-top.window="scrollToTop()">
    @if($task)
        {{-- Rich Editor Form --}}
        <form wire:submit="addCommentAction">
            {{ $this->commentForm }}

            <div class="mt-4 flex justify-end">
                {{ $this->addCommentAction }}
            </div>
        </form>

        {{-- Task Updates List --}}
        <div class="space-y-4" wire:poll.10000ms>
            @forelse($updates as $update)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    {{-- Header s autorom i vremenom --}}
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-3">
                            {{-- Avatar --}}
                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                {{ substr($update->author->first_name, 0, 1) }}{{ substr($update->author?->last_name, 0, 1) }}
                            </div>

                            {{-- Ime i vrijeme --}}
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $update->author->full_name}}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $update->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>

                        {{-- Action Group --}}
                        @if(auth()->user()->employee->id === $update->author->id)
                            <x-filament-actions::group :actions="[
                                $this->editAction->arguments(['updateId' => $update->id]),
                                $this->deleteAction->arguments(['updateId' => $update->id]),
                            ]"

                           dropdown-placement="left"
                            />
                        @endif
                    </div>

                    {{-- Sadržaj komentara --}}
                    <div class="prose prose-sm max-w-none dark:prose-invert">
                        {!! $update->body !!}
                    </div>
                </div>
            @empty
                <div class="text-center py-12">
                    <div class="text-gray-400 dark:text-gray-600">
                        <svg class="mx-auto h-12 w-12 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Nema komentara. Budite prvi koji će dodati komentar!
                        </p>
                    </div>
                </div>
            @endforelse
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-500 dark:text-gray-400">Odaberite zadatak da biste vidjeli aktivnost.</p>
        </div>
    @endif
        <x-filament-actions::modals />

</div>
