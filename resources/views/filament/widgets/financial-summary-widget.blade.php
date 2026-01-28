@php
    $summary = $this->getSummary();
@endphp

<div>
    <div class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <div class="flex items-center justify-between mb-4" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <div class="flex items-center gap-2" style="display: flex; align-items: center; gap: 0.5rem;">
                <div style="width: 24px; height: 24px; min-width: 24px;">
                     <svg style="width: 100%; height: 100%; color: #f59e0b;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                </div>
                <span class="text-lg font-semibold text-gray-950 dark:text-white">Resumo {{ $summary['month_name'] }}</span>
            </div>
            <span class="text-xs px-3 py-1 rounded-full bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 font-medium whitespace-nowrap">
                {{ $summary['scope'] === 'family' ? '👨‍👩‍👧‍👦 Família' : '👤 Pessoal' }}
            </span>
        </div>

        <!-- Usando Grid Inline para garantir funcionamento -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem;">
            @if($summary['has_budgets'])
                <div class="text-center p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 font-medium uppercase tracking-wider">Orçado</p>
                    <p class="text-lg font-bold text-gray-950 dark:text-white">R$ {{ number_format($summary['total_budgeted'], 0, ',', '.') }}</p>
                </div>
            @else
                <div class="text-center p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 font-medium uppercase tracking-wider">Receitas</p>
                    <p class="text-lg font-bold text-gray-950 dark:text-white">R$ {{ number_format($summary['total_income'], 0, ',', '.') }}</p>
                </div>
            @endif
            
            <div class="text-center p-3 rounded-lg bg-gray-50 dark:bg-gray-800">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 font-medium uppercase tracking-wider">Gasto</p>
                <p class="text-lg font-bold text-gray-950 dark:text-white">R$ {{ number_format($summary['total_spent'], 0, ',', '.') }}</p>
            </div>
            
            <div class="text-center p-3 rounded-lg {{ $summary['remaining'] >= 0 ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400' }}">
                <p class="text-xs opacity-80 mb-1 font-medium uppercase tracking-wider">{{ $summary['remaining'] >= 0 ? 'Disponível' : 'Excedido' }}</p>
                <p class="text-lg font-bold">R$ {{ number_format(abs($summary['remaining']), 0, ',', '.') }}</p>
            </div>
            
            <div class="text-center p-3 rounded-lg {{ $summary['savings'] >= 0 ? 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400' }}">
                <p class="text-xs opacity-80 mb-1 font-medium uppercase tracking-wider">Economia</p>
                <p class="text-lg font-bold">R$ {{ number_format($summary['savings'], 0, ',', '.') }}</p>
            </div>
        </div>

        @if($summary['alert_count'] > 0 || $summary['exceeded_count'] > 0)
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-white/5 flex flex-wrap items-center gap-4 text-sm font-medium">
                @if($summary['exceeded_count'] > 0)
                    <span class="flex items-center gap-1.5 text-red-700 bg-red-50 px-2 py-1 rounded dark:bg-red-900/20 dark:text-red-400">
                        <div style="width: 16px; height: 16px; min-width: 16px;">
                            <svg style="width: 100%; height: 100%;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                        </div>
                        {{ $summary['exceeded_count'] }} orç. excedido(s)
                    </span>
                @endif
                @if($summary['alert_count'] > 0)
                    <span class="flex items-center gap-1.5 text-amber-700 bg-amber-50 px-2 py-1 rounded dark:bg-amber-900/20 dark:text-amber-400">
                        <div style="width: 16px; height: 16px; min-width: 16px;">
                            <svg style="width: 100%; height: 100%;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                            </svg>
                        </div>
                        {{ $summary['alert_count'] }} em alerta
                    </span>
                @endif
            </div>
        @endif
    </div>
</div>