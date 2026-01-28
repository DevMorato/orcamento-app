<div
    class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="flex items-center justify-between mb-4">
        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
            Orçamento do Mês
        </span>
        <span
            class="text-xs px-2 py-1 rounded-full {{ $this->getOverallPercentage() >= 100 ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' : ($this->getOverallPercentage() >= 80 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300' : 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-300') }}">
            {{ number_format($this->getOverallPercentage(), 0) }}% usado
        </span>
    </div>

    @php
        $budgets = $this->getBudgets();
    @endphp

    @if(count($budgets) > 0)
        <div class="space-y-4">
            @foreach($budgets as $budget)
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <div class="flex items-center gap-2">
                            <span class="text-sm">{{ $budget['icon'] }}</span>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $budget['category'] }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                R$ {{ number_format($budget['spent'], 0, ',', '.') }} / R$
                                {{ number_format($budget['budget'], 0, ',', '.') }}
                            </span>
                            @if($budget['percentage_real'] >= 100)
                                <svg class="w-4 h-4 text-red-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                            @endif
                        </div>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 overflow-hidden">
                        <div class="h-2.5 rounded-full transition-all duration-500"
                            style="width: {{ min($budget['percentage'], 100) }}%; background-color: {{ $budget['color'] }}">
                        </div>
                    </div>
                    @if($budget['percentage_real'] > 100)
                        <div class="text-xs text-red-500 mt-1">
                            Excedeu R$ {{ number_format($budget['spent'] - $budget['budget'], 2, ',', '.') }}
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-6">
            <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum orçamento definido</p>
            <a href="{{ \App\Filament\Resources\Budgets\BudgetResource::getUrl('create') }}"
                class="mt-2 inline-flex items-center gap-1 text-sm text-primary-600 hover:text-primary-700">
                + Definir orçamento
            </a>
        </div>
    @endif
</div>