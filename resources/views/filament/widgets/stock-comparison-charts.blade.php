<x-filament-widgets::widget>
    <x-filament::card class="bg-white p-6 rounded-lg shadow-md">
        <div class="mb-6">
            <h2 class="text-2xl font-semibold text-gray-800">Stock Comparison Dashboard</h2>
            <p class="text-sm text-gray-600">Latest stock levels across system and shop inventories</p>
            <p class="text-xs text-gray-500 mt-1">Last updated: {{ now()->format('F j, Y, g:i a') }}</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Bar Chart -->
            <div class="bg-gray-50 p-4 rounded">
                <h3 class="text-lg font-medium text-gray-700 mb-3">Stock by Item</h3>
                <div id="stock-bar-chart" class="w-full"></div>
            </div>

            <!-- Donut Chart -->
            <div class="bg-gray-50 p-4 rounded">
                <h3 class="text-lg font-medium text-gray-700 mb-3">Total Stock Distribution</h3>
                <div id="stock-donut-chart" class="w-full"></div>
            </div>
        </div>
    </x-filament::card>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Bar Chart
            var barOptions = @json($this->getOptions());
            var barChart = new ApexCharts(document.querySelector("#stock-bar-chart"), barOptions);
            barChart.render();

            // Donut Chart
            var donutOptions = @json($this->getDonutOptions());
            var donutChart = new ApexCharts(document.querySelector("#stock-donut-chart"), donutOptions);
            donutChart.render();
        });
    </script>
</x-filament-widgets::widget>
