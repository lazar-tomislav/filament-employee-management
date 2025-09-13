<x-filament-panels::page>
    <div class="max-w-4xl mx-auto">
        <div class="bg-white dark:bg-gray-900 shadow rounded-lg p-6">
            <div class="mb-6">
                <div class="flex justify-start">
                    <x-filament-panels::header.simple/>
                </div>
                <div class="space-y-2">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                        Postavi Telegram obavijesti
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Za primanje obavijesti putem Telegrama, molimo postavite svoj Telegram Chat ID u dva jednostavna koraka.
                    </p>
                </div>
            </div>

            <div>

                <div class="text-gray-700 dark:text-gray-200 space-y-3">
                    <p>
                        Da biste omogućili primanje obavijesti putem Telegrama, morate se povezati s našim botom:
                    </p>
                    <ol class="list-decimal list-inside space-y-2 ml-4">
                        <li>Kliknite na sljedeći link ili ga otvorite u Telegram aplikaciji:</li>
                        <div class="bg-gray-50 dark:bg-gray-800 p-3 rounded-lg border border-gray-200 dark:border-gray-700 ml-4">
                            <a href="https://t.me/net_eko_bot?text=/start"
                               target="_blank"
                               class="text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 hover:underline font-mono text-sm transition-colors">
                                https://t.me/net_eko_bot?text=/start
                            </a>
                        </div>
                        <li>Pošaljite bilo koju poruku botu (npr. "Pozdrav" ili "/start")</li>
                        <li>Otvorite NetEko aplikaciju</li>
                        <li>Pritisnite: Poslao sam poruku</li>
                    </ol>
                    <div class="mt-4 p-3 bg-warning-50 dark:bg-warning-900/20 border border-warning-200 dark:border-warning-800 rounded-lg">
                        <p class="text-warning-800 dark:text-warning-200 text-sm">
                            <strong>Važno:</strong> Chat ID obično počinje s brojem (npr. 123456789) ili znakom minus za grupne chatove (npr. -123456789).
                        </p>
                    </div>
                </div>
            </div>
            <div class="space-y-6 mt-4">
                    {{$this->getChatId()}}
                {{ $this->form }}
            </div>
        </div>
    </div>
</x-filament-panels::page>
