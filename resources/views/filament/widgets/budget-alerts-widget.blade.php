<div>
    <div class="relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        @if($this->hasAlerts())
            @php
                $alerts = $this->getBudgetAlerts();
            @endphp

            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div style="width: 20px; height: 20px; min-width: 20px;">
                        <svg style="width: 100%; height: 100%; color: #f59e0b;" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">
                        Alertas de Orçamento
                    </span>
                    <span
                        class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900 dark:text-amber-300 font-medium">
                        {{ count($alerts) }}
                    </span>
                </div>
            </div>

            <div class="flex flex-wrap gap-2">
                @foreach($alerts as $alert)
                    <div
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm {{ $alert['status'] === 'danger' ? 'bg-red-50 text-red-700 dark:bg-red-950 dark:text-red-300 ring-1 ring-red-600/10' : 'bg-amber-50 text-amber-700 dark:bg-amber-950 dark:text-amber-300 ring-1 ring-amber-600/10' }}">
                        @if($alert['status'] === 'danger')
                            <div style="width: 16px; height: 16px; min-width: 16px;">
                                <svg style="width: 100%; height: 100%;" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                            </div>
                        @else
                            <div style="width: 16px; height: 16px; min-width: 16px;">
                                <svg style="width: 100%; height: 100%;" xmlns="http://www.w3.org/2000/svg" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                                </svg>
                            </div>
                        @endif
                        <span class="font-medium">{{ $alert['category'] }}</span>
                        <span class="opacity-75 text-xs">{{ number_format($alert['percentage'], 0) }}%</span>
                    </div>
                @endforeach
            </div>
        @else
            <div
                class="relative rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 hidden">
                <!-- Hidden when no alerts -->
            </div>
        @endif
    </div>
</div>