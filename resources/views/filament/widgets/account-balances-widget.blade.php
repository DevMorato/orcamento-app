<div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="flex items-center gap-x-2 mb-4">
        <span class="fi-wi-stats-overview-stat-label text-sm font-medium text-gray-500 dark:text-gray-400">
            Saldo das Contas
        </span>
    </div>

    @php
        $accounts = $this->getAccounts();
    @endphp

    @if(count($accounts) > 0)
        <div class="space-y-3">
            @foreach($accounts as $account)
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full shrink-0" style="background-color: {{ $account['color'] }}"></div>
                        <div>
                            <p class="font-medium text-sm text-gray-900 dark:text-gray-100">{{ $account['name'] }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $account['type'] }}</p>
                        </div>
                    </div>
                    <p class="font-semibold text-sm {{ $account['balance'] >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                        R$ {{ number_format($account['balance'], 2, ',', '.') }}
                    </p>
                </div>
            @endforeach

            <div class="flex items-center justify-between pt-3 mt-3 border-t border-gray-200 dark:border-gray-700">
                <p class="font-semibold text-gray-900 dark:text-gray-100">Total</p>
                <p class="font-bold text-lg {{ $this->getTotalBalance() >= 0 ? 'text-success-600' : 'text-danger-600' }}">
                    R$ {{ number_format($this->getTotalBalance(), 2, ',', '.') }}
                </p>
            </div>
        </div>
    @else
        <div class="text-center py-4">
            <p class="text-sm text-gray-500 dark:text-gray-400">Nenhuma conta cadastrada</p>
            <a href="{{ \App\Filament\Resources\Accounts\AccountResource::getUrl('create') }}"
                class="mt-2 inline-flex items-center gap-1 text-sm text-primary-600 hover:text-primary-700">
                + Adicionar conta
            </a>
        </div>
    @endif
</div>