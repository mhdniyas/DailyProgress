<x-filament-widgets::widget>
    <x-filament::card>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            {{-- Bar Chart --}}
            <div>
                <h3 class="text-lg font-medium mb-4">Stock Comparison by Item</h3>
                <div id="stock-bar-chart"></div>
            </div>

            {{-- Donut Chart --}}
            <div>
                <h3 class="text-lg font-medium mb-4">Total Stock Distribution</h3>
                <div id="stock-donut-chart"></div>
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
