<x-filament::page>
    <div>
        {{-- Date Range Selector --}}
        <div class="mb-4">
            <x-filament::card>
                <div class="flex space-x-4">
                    <x-filament::input
                        type="date"
                        wire:model="startDate"
                        label="Start Date"
                    />

                    <x-filament::input
                        type="date"
                        wire:model="endDate"
                        label="End Date"
                    />
                </div>
            </x-filament::card>
        </div>
{{-- Summary Stats --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 p-3">
    <x-filament::card>
        <div class="text-center">
            <h3 class="text-lg font-medium">Total Income</h3>
            <p class="text-2xl font-bold text-success-500">
                ₹{{ number_format($this->getTotalIncome(), 2) }}
            </p>
        </div>
    </x-filament::card>

    <x-filament::card>
        <div class="text-center">
            <h3 class="text-lg font-medium">Total Expenses</h3>
            <p class="text-2xl font-bold text-danger-500">
                ₹{{ number_format($this->getTotalExpenses(), 2) }}
            </p>
        </div>
    </x-filament::card>

    <x-filament::card>
        <div class="text-center">
            <h3 class="text-lg font-medium">Net Balance</h3>
            <p class="text-2xl font-bold {{ $this->getNetCashFlow() >= 0 ? 'text-success-500' : 'text-danger-500' }}">
                ₹{{ number_format($this->getNetCashFlow(), 2) }}
            </p>
        </div>
    </x-filament::card>
</div>
        {{-- Last 3 Expenses --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 p-3">
            @foreach($this->getLastThreeExpenses() as $expense)
                <x-filament::card>
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-semibold">
                                {{ $expense['category'] }}
                            </h3>
                            <p class="text-sm text-gray-500">
                                {{ $expense['date'] }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="text-lg font-bold text-danger-500">
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

        {{-- Charts Section --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Expenses by Category Chart --}}
            <div class="relative group">
                <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-indigo-400/10 via-purple-100/20 to-pink-50/30 transition duration-300 group-hover:scale-105"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
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
                                            animateGradually: {
                                                enabled: true,
                                                delay: 150
                                            },
                                            dynamicAnimation: {
                                                enabled: true,
                                                speed: 350
                                            }
                                        },
                                        fontFamily: 'Inter var'
                                    },
                                    series: values,
                                    labels: categories,
                                    colors: ['#6366F1', '#8B5CF6', '#EC4899', '#14B8A6', '#F59E0B', '#06B6D4'],
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
                                                            return '₹' + w.globals.series.reduce((a, b) => a + b, 0).toLocaleString()
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    },
                                    dataLabels: {
                                        enabled: true,
                                        formatter: function(val, opts) {
                                            return opts.w.config.labels[opts.seriesIndex] + ': ' + val.toFixed(1) + '%'
                                        }
                                    },
                                    legend: {
                                        position: 'bottom',
                                        horizontalAlign: 'center',
                                        labels: {
                                            colors: '#6B7280'
                                        }
                                    },
                                    tooltip: {
                                        y: {
                                            formatter: function(value) {
                                                return '₹' + value.toLocaleString()
                                            }
                                        }
                                    },
                                    stroke: {
                                        width: 2
                                    }
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
            <div class="relative group">
                <div class="absolute inset-0 rounded-xl bg-gradient-to-br from-emerald-400/10 via-teal-100/20 to-cyan-50/30 transition duration-300 group-hover:scale-105"></div>
                <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
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
                                        toolbar: {
                                            show: false
                                        },
                                        animations: {
                                            enabled: true,
                                            easing: 'easeinout',
                                            speed: 800,
                                            animateGradually: {
                                                enabled: true,
                                                delay: 150
                                            }
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
                                    colors: ['#10B981', '#EF4444'],
                                    fill: {
                                        type: 'gradient',
                                        gradient: {
                                            shadeIntensity: 1,
                                            opacityFrom: 0.7,
                                            opacityTo: 0.3,
                                            stops: [0, 90, 100]
                                        }
                                    },
                                    stroke: {
                                        curve: 'smooth',
                                        width: 2
                                    },
                                    xaxis: {
                                        categories: data.map(item => item.week),
                                        labels: {
                                            style: {
                                                colors: '#6B7280'
                                            }
                                        }
                                    },
                                    yaxis: {
                                        labels: {
                                            formatter: function(value) {
                                                return '₹' + value.toLocaleString()
                                            },
                                            style: {
                                                colors: '#6B7280'
                                            }
                                        }
                                    },
                                    grid: {
                                        borderColor: '#E5E7EB',
                                        strokeDashArray: 4,
                                        xaxis: {
                                            lines: {
                                                show: true
                                            }
                                        }
                                    },
                                    legend: {
                                        position: 'top',
                                        horizontalAlign: 'right',
                                        labels: {
                                            colors: '#6B7280'
                                        }
                                    },
                                    tooltip: {
                                        y: {
                                            formatter: function(value) {
                                                return '₹' + value.toLocaleString()
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

        @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        @endpush


    </div>
</x-filament::page>
