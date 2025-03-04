<x-filament::page>
    <div>
        {{-- Date Range Selector --}}
        <div class="mb-4">
            <x-filament::card>
                <div class="flex space-x-4">
                    <x-filament::input type="date" wire:model="startDate" label="Start Date" />
                    <x-filament::input type="date" wire:model="endDate" label="End Date" />
                </div>
            </x-filament::card>
        </div>

        <x-filament::card>
            <div class="space-y-4">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Financial Report</h2>

                {{-- This is the 3D view container that will be used for visualization --}}
                <div id="financial-3d-view" class="w-full rounded-lg bg-gray-50 dark:bg-gray-800">
                    {{-- Container for all financial data that should be hidden when 3D view is active --}}
                    <div id="financial-data-container">
                        {{-- Summary Stats --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 p-3">
                            <x-filament::card>
                                <div class="text-center">
                                    <h3 class="text-lg font-medium">Total Income</h3>
                                    <p class="text-2xl font-bold text-emerald-600">
                                        ₹{{ number_format($this->getTotalIncome(), 2) }}
                                    </p>
                                </div>
                            </x-filament::card>

                            <x-filament::card>
                                <div class="text-center">
                                    <h3 class="text-lg font-medium">Total Expenses</h3>
                                    <p class="text-2xl font-bold text-rose-600">
                                        ₹{{ number_format($this->getTotalExpenses(), 2) }}
                                    </p>
                                </div>
                            </x-filament::card>

                            <x-filament::card>
                                <div class="text-center">
                                    <h3 class="text-lg font-medium">Net Balance</h3>
                                    <p
                                        class="text-2xl font-bold {{ $this->getNetCashFlow() >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        ₹{{ number_format($this->getNetCashFlow(), 2) }}
                                    </p>
                                </div>
                            </x-filament::card>
                        </div>

                        {{-- Today's Transactions Summary --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 p-3">
                            @foreach($todayTransactions as $transaction)
                            <x-filament::card>
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-lg font-semibold">{{ $transaction['category'] }}</h3>
                                        <p class="text-sm text-gray-500">{{ $transaction['description'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-lg font-bold text-{{ $transaction['type'] === 'Credit' ? 'emerald' : 'rose' }}-600">
                                            ₹{{ number_format($transaction['amount'], 2) }}
                                        </span>
                                    </div>
                                </div>
                            </x-filament::card>
                            @endforeach
                        </div>

                        {{-- Last 3 Expenses --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 p-3">
                            @foreach($this->getLastThreeExpenses() as $expense)
                            <x-filament::card>
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h3 class="text-lg font-semibold">{{ $expense['category'] }}</h3>
                                        <p class="text-sm text-gray-500">{{ $expense['date'] }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-lg font-bold text-rose-600">
                                            ₹{{ number_format($expense['amount'], 2) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-600">
                                        {{ Str::limit($expense['description'], 50) }}
                                    </p>
                                </div>
                            </x-filament::card>
                            @endforeach
                        </div>

                        {{-- Transaction Summaries Table --}}
                        <div class="mb-6 p-3">
                            <x-filament::card>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Transaction Summaries</h3>
                                {{ $this->table }}
                            </x-filament::card>
                        </div>

                        {{-- Charts Section --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-3">
                            {{-- Expenses by Category Chart --}}
                            <div class="relative">
                                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Expenses by Category</h3>
                                    <div class="h-80" wire:ignore>
                                        <div x-data="{
                                            chart: null,
                                            init() {
                                                const expenseData = {{ Js::from($this->getExpenseByCategory()) }};
                                                const categories = Object.keys(expenseData);
                                                const values = Object.values(expenseData);

                                                this.chart = new ApexCharts(this.$refs.expensesChart, {
                                                    chart: {
                                                        type: 'donut',
                                                        height: 300,
                                                        animations: {
                                                            enabled: true,
                                                            speed: 500,
                                                            animateGradually: { enabled: true, delay: 150 },
                                                            dynamicAnimation: { enabled: true, speed: 350 }
                                                        },
                                                        fontFamily: 'Inter var'
                                                    },
                                                    series: values,
                                                    labels: categories,
                                                    colors: ['#0F766E', '#0369A1', '#7E22CE', '#BE185D', '#B45309', '#1E40AF'],
                                                    plotOptions: {
                                                        pie: {
                                                            donut: {
                                                                size: '70%',
                                                                labels: {
                                                                    show: true,
                                                                    total: {
                                                                        show: true,
                                                                        label: 'Total Expenses',
                                                                        formatter: function(w) {
                                                                            return '₹' + w.globals.series.reduce((a, b) => a + b, 0).toLocaleString();
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    },
                                                    dataLabels: {
                                                        enabled: true,
                                                        formatter: function(val, opts) {
                                                            return opts.w.config.labels[opts.seriesIndex] + ': ' + val.toFixed(1) + '%';
                                                        }
                                                    },
                                                    legend: {
                                                        position: 'bottom',
                                                        horizontalAlign: 'center',
                                                        labels: { colors: '#6B7280' }
                                                    },
                                                    tooltip: {
                                                        y: {
                                                            formatter: function(value) {
                                                                return '₹' + value.toLocaleString();
                                                            }
                                                        }
                                                    },
                                                    stroke: { width: 2 }
                                                });
                                                this.chart.render();
                                            }
                                        }" wire:key="expenses-chart">
                                            <div x-ref="expensesChart"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Cash Flow Trend Chart --}}
                            <div class="relative">
                                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Cash Flow Trend</h3>
                                    <div class="h-80" wire:ignore>
                                        <div x-data="{
                                            chart: null,
                                            init() {
                                                const data = {{ Js::from($this->getCashFlowData()) }};

                                                this.chart = new ApexCharts(this.$refs.cashFlowChart, {
                                                    chart: {
                                                        type: 'area',
                                                        height: 300,
                                                        toolbar: { show: false },
                                                        animations: {
                                                            enabled: true,
                                                            easing: 'easeinout',
                                                            speed: 800,
                                                            animateGradually: { enabled: true, delay: 150 }
                                                        },
                                                        fontFamily: 'Inter var'
                                                    },
                                                    series: [{
                                                        name: 'Income',
                                                        data: data.map(item => item.credits)
                                                    }, {
                                                        name: 'Expenses',
                                                        data: data.map(item => item.debits)
                                                    }],
                                                    colors: ['#047857', '#BE123C'],
                                                    fill: {
                                                        type: 'gradient',
                                                        gradient: {
                                                            shadeIntensity: 1,
                                                            opacityFrom: 0.7,
                                                            opacityTo: 0.3,
                                                            stops: [0, 90, 100]
                                                        }
                                                    },
                                                    stroke: { curve: 'smooth', width: 2 },
                                                    xaxis: {
                                                        categories: data.map(item => item.week),
                                                        labels: { style: { colors: '#6B7280' } }
                                                    },
                                                    yaxis: {
                                                        labels: {
                                                            formatter: function(value) {
                                                                return '₹' + value.toLocaleString();
                                                            },
                                                            style: { colors: '#6B7280' }
                                                        }
                                                    },
                                                    grid: {
                                                        borderColor: '#E5E7EB',
                                                        strokeDashArray: 4,
                                                        xaxis: { lines: { show: true } }
                                                    },
                                                    legend: {
                                                        position: 'top',
                                                        horizontalAlign: 'right',
                                                        labels: { colors: '#6B7280' }
                                                    },
                                                    tooltip: {
                                                        y: {
                                                            formatter: function(value) {
                                                                return '₹' + value.toLocaleString();
                                                            }
                                                        }
                                                    }
                                                });
                                                this.chart.render();
                                            }
                                        }" wire:key="cashflow-chart">
                                            <div x-ref="cashFlowChart"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
    `               <div class="mb-6 p-3">
                        <x-filament::card>
                        <h2 class="text-lg font-semibold mb-4">Daily Transactions Calendar</h2>
                        <div id="financial-calendar"
                            data-transactions="{{ json_encode($calendarData) }}"
                            class="w-full"></div>
                        </x-filament::card>
                    </div>
                </div>
            </div>
        </x-filament::card>


        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script type="module">
            import { FinancialVisualizer } from '../../js/financial-3d.js';

            document.addEventListener('DOMContentLoaded', () => {
                const container = document.getElementById('financial-3d-view');
                const dataContainer = document.getElementById('financial-data-container');

                const toggleButton = document.createElement('button');
                toggleButton.textContent = 'Switch to 3D View';
                toggleButton.className = 'px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 rounded-md text-gray-900 dark:text-gray-100 mb-4';
                container.parentNode.insertBefore(toggleButton, container);

                let is3DActive = false;
                const visualizer = new FinancialVisualizer('financial-3d-view');
                const expenseData = @json($expenseByCategory);

                toggleButton.addEventListener('click', () => {
                    is3DActive = !is3DActive;

                    if (is3DActive) {
                        dataContainer.style.display = 'none';
                        container.style.height = '600px';
                        toggleButton.textContent = 'Switch to Standard View';
                        visualizer.visualizeExpenses(expenseData);
                        visualizer.animate();
                    } else {
                        dataContainer.style.display = 'block';
                        container.style.height = 'auto';
                        toggleButton.textContent = 'Switch to 3D View';
                        visualizer.stop();
                    }
                });
            });
        </script>
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var calendarEl = document.getElementById('financial-calendar');
                var transactions = JSON.parse(calendarEl.dataset.transactions);

                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: ''
                    },
                    events: Object.entries(transactions).map(([date, data]) => ({
                        title: `Credit: ₹${data.credit.toLocaleString('en-IN')}\nDebit: ₹${data.debit.toLocaleString('en-IN')}`,
                        start: date,
                        backgroundColor: data.credit > data.debit ? '#10B981' : '#EF4444',
                        borderColor: data.credit > data.debit ? '#059669' : '#DC2626',
                        textColor: '#ffffff',
                        extendedProps: {
                            credit: data.credit,
                            debit: data.debit
                        }
                    })),
                    eventContent: function(arg) {
                        return {
                            html: `
                                <div class="fc-event-main p-1">
                                    <div class="text-xs font-medium">Credit: ₹${arg.event.extendedProps.credit.toLocaleString('en-IN')}</div>
                                    <div class="text-xs font-medium">Debit: ₹${arg.event.extendedProps.debit.toLocaleString('en-IN')}</div>
                                </div>
                            `
                        }
                    },
                    eventMouseEnter: function(info) {
                        tippy(info.el, {
                            content: `
                                <div class="bg-gray-800 text-white p-2 rounded shadow-lg">
                                    <p>Date: ${info.event.start.toLocaleDateString()}</p>
                                    <p>Credit: ₹${info.event.extendedProps.credit.toLocaleString('en-IN')}</p>
                                    <p>Debit: ₹${info.event.extendedProps.debit.toLocaleString('en-IN')}</p>
                                    <p>Net: ₹${(info.event.extendedProps.credit - info.event.extendedProps.debit).toLocaleString('en-IN')}</p>
                                </div>
                            `,
                            allowHTML: true,
                            theme: 'dark'
                        });
                    },
                    height: 'auto',
                    dayMaxEvents: true,
                    eventDisplay: 'block',
                });

                calendar.render();
            });
        </script>
        @endpush
        @push('styles')
<link href="https://cdn.jsdelivr.net/npm/tippy.js@6.3.7/dist/tippy.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tippy.js@6.3.7/dist/tippy-bundle.umd.min.js"></script>
<style>
    #financial-calendar {
        max-width: 100%;
    }

    .fc {
        --fc-small-font-size: 0.85em;
        --fc-bg-event-opacity: 0.9;
    }

    .fc-daygrid-day-number {
        color: #374151;
        font-weight: 500;
    }

    .fc-daygrid-event {
        margin: 2px;
        padding: 2px;
        border-radius: 4px;
        font-size: 0.75rem;
        line-height: 1.2;
    }

    .fc .fc-daygrid-day-top {
        display: flex;
        justify-content: center;
    }

    .fc .fc-daygrid-day.fc-day-today {
        background-color: #F3F4F6;
    }
</style>
@endpush
    </div>
</x-filament::page>
