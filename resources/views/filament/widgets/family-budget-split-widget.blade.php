<div>
    @if($this->shouldShow())
        @php
            $categories = $this->getCategories();
            $split = $this->getMemberSplit();
        @endphp

        <div
            class="fi-wi-stats-overview-stat relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="flex items-center justify-between mb-4">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    Gastos por Membro
                </span>
            </div>

            @if(count($categories) > 0)
                <div class="mb-4">
                    <select wire:model.live="selectedCategoryId"
                        class="w-full text-sm rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">
                        @foreach($categories as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                @if(!empty($split) && !empty($split['members']))
                    <div class="space-y-3">
                        @foreach($split['members'] as $member)
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-200">
                                        {{ $member['name'] }}
                                    </span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        R$ {{ number_format($member['spent'], 0, ',', '.') }}
                                        ({{ number_format($member['percentage'], 0) }}%)
                                    </span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700 overflow-hidden">
                                    <div class="h-2 rounded-full transition-all duration-500"
                                        style="width: {{ $member['percentage'] }}%; background-color: {{ $member['color'] }}"></div>
                                </div>
                            </div>
                        @endforeach

                        <div class="pt-3 mt-3 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between text-sm">
                                <span class="font-semibold text-gray-900 dark:text-gray-100">Total</span>
                                <span
                                    class="font-semibold {{ $split['total_spent'] > $split['budget_amount'] ? 'text-red-600' : 'text-gray-900 dark:text-gray-100' }}">
                                    R$ {{ number_format($split['total_spent'], 0, ',', '.') }} / R$
                                    {{ number_format($split['budget_amount'], 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                        Nenhum gasto nesta categoria
                    </p>
                @endif
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">
                    Nenhum orçamento definido
                </p>
            @endif
        </div>
    @endif
</div>