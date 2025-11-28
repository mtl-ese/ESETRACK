<x-layout>
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">

    <x-success></x-success>
    <x-error></x-error>

    <div class="row">
        <!-- Total Purchase Requisitions Card -->
        <x-card style="background-color:rgb(114, 75, 117);">
            <x-card-body>
                <a href="{{ route('purchase.index') }}" class="text-decoration-none text-black">
                    <x-card-title>
                        <i class="fas fa-shopping-cart navbar navbar-brand rounded-5 mx-auto bg-transparent d-block"
                            style="max-height: 40px;"></i>
                        Total Purchase Requisitions
                    </x-card-title>
                    <x-card-text>{{ $purchase }}</x-card-text>
                    <p class="card-footer text-body-primary">
                        Last requisition:
                        @if ($lastPurchaseUpdated)
                            {{ $lastPurchaseUpdated->updated_at->diffForHumans() }}
                        @else
                            No records
                        @endif
                    </p>
                </a>
            </x-card-body>
        </x-card>

        <x-card class=" bg-success">
            <x-card-body>
                <a href="{{ route('store.index') }}" class="text-decoration-none text-black">
                    <x-card-title>
                        <i class="fas fa-warehouse navbar navbar-brand rounded-5 mx-auto bg-transparent d-block"
                            style="max-height: 40px;"></i>
                        Total Store Requisitions
                    </x-card-title>
                    <x-card-text>{{ $store }}</x-card-text>
                    <p class="card-footer text-body-primary">
                        Last requisition:
                        @if ($lastStoreUpdated)
                            {{ $lastStoreUpdated->updated_at->diffForHumans() }}
                        @else
                            No records
                        @endif
                    </p>
                </a>
            </x-card-body>
        </x-card>

        <x-card class="bg-warning" style="max-height: 230px;">
            <x-card-body>
                <a href="{{ route('recovery.index') }}" class="text-decoration-none text-black">
                    <x-card-title>
                        <i class="fas fa-tools navbar navbar-brand rounded-5 mx-auto bg-transparent d-block"
                            style="max-height: 40px;"></i>
                        Total Recovery Requisitions
                    </x-card-title>
                    <x-card-text>{{ $recovery }}</x-card-text>
                    <p class="card-footer text-body-primary">
                        Last requisition:
                        @if ($lastRecoveryUpdated)
                            {{ $lastRecoveryUpdated->updated_at->diffForHumans() }}
                        @else
                            No records
                        @endif
                    </p>
                </a>
            </x-card-body>
        </x-card>

        <x-card class=" bg-secondary bg-opacity">
            <x-card-body>
                <a href="{{ route('returns.index') }}" class="text-decoration-none text-black">
                    <x-card-title>
                        <i class="fas fa-undo-alt navbar navbar-brand rounded-5 mx-auto bg-transparent d-block"
                            style="max-height: 40px;"></i>
                        Total Returns
                    </x-card-title>
                    <x-card-text>{{ $return }}</x-card-text>
                    <p class="card-footer text-body-primary">
                        Last return:
                        @if ($lastReturnUpdated)
                            {{ $lastReturnUpdated->updated_at->diffForHumans() }}
                        @else
                            No records
                        @endif
                    </p>
                </a>
            </x-card-body>
        </x-card>

        <x-card class="bg-primary">
            <x-card-body>
                <a href="{{ route('emergencyIndex') }}" class="text-decoration-none text-black">
                    <x-card-title>
                        <i class="fas fa-exclamation-triangle navbar navbar-brand rounded-5 mx-auto bg-transparent d-block"
                            style="max-height: 40px;"></i>
                        Total Emergency Requisitions </x-card-title>
                    <x-card-text>{{ $emergency }}</x-card-text>
                    <p class="card-footer text-body-primary">
                        Last requisition:
                        @if ($lastEmergencyUpdated)
                            {{ $lastEmergencyUpdated->updated_at->diffForHumans() }}
                        @else
                            No records
                        @endif
                    </p>
                </a>
            </x-card-body>
        </x-card>

        <x-card style="background-color:rgb(201, 45, 71);">
            <x-card-body>
                <a href="{{ route('acquired.index') }}" class="text-decoration-none text-black">
                    <x-card-title>
                        <i class="fas fa-check-circle navbar navbar-brand rounded-5 mx-auto bg-transparent d-block"
                            style="max-height: 40px;"></i>
                        Total Acquired Requisitions
                    </x-card-title>
                    <x-card-text>{{ $acquired }}</x-card-text>
                    <p class="card-footer text-body-primary">
                        Last requisition:
                        @if ($lastAcquiredUpdated)
                            {{ $lastAcquiredUpdated->updated_at->diffForHumans() }}
                        @else
                            No records
                        @endif
                    </p>
                </a>
            </x-card-body>
        </x-card>
    </div>

    <div class="row mt-4"> <!-- Added margin-top for spacing -->
        <!-- <div class="col-7">
            
            <div class="card chart-clickable"
                style="border-top: 3px solid rgb(255, 174, 0); background: rgba(255, 255, 255, 0.7);">
                <div class="card-header" style="background-color: rgb(255, 174, 0); color: black; padding: 5px;">
                    <h4 class="card-title" style="font-size: 16px; display: inline;">
                        <i class="far fa-chart-bar"></i>
                        System Requisitions Overview
                    </h4>
                    <div class="card-tools" style="display: inline; float: right;">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse" title="Collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-tool" data-card-widget="remove" title="Remove">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div id="interactive" style="height: 300px;"></div>
                </div>
            </div>
        </div> -->

        <div class="col-12">
            <div class="card" style="background: rgba(255, 255, 255, 0.8);">
                <div class="card-header text-center" style="background-color: rgb(255, 174, 0); padding: 5px;">
                    <strong style="font-size: 16px;">Requisition Breakdown</strong>
                </div>
                <div class="card-body">
                    <canvas id="myPieChart" width="400" height="400"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Load jQuery first -->
    <!-- Load jQuery from CDN -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <!-- Load Flot from CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flot/0.8.3/jquery.flot.resize.min.js"></script>

    <!-- Load Chart.js from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(function () {
            // Ensure data is safely converted to numbers
            var purchase = parseInt('{{ $purchase }}', 10) || 0;
            var store = parseInt('{{ $store }}', 10) || 0;
            var recovery = parseInt('{{ $recovery }}', 10) || 0;
            var returnReq = parseInt('{{ $return }}', 10) || 0;
            var emergency = parseInt('{{ $emergency }}', 10) || 0;
            var acquired = parseInt('{{ $acquired }}', 10) || 0;

            var labels = [
                'Purchase Requisitions',
                'Store Requisitions',
                'Recovery Requisitions',
                'Returns',
                'Emergency Requisitions',
                'Acquired Requisitions'
            ];

            var dataValues = [purchase, store, recovery, returnReq, emergency, acquired];
            var backgroundColors = ['#724B75', '#198754', '#ffc107', '#6c757d', '#0d6efd', '#c92a47'];

            // Pie Chart
            var pieChartCanvas = $('#myPieChart').get(0).getContext('2d');

            var pieData = {
                labels: labels,
                datasets: [{
                    data: dataValues.slice(),
                    backgroundColor: backgroundColors
                }]
            };

            var originalData = [...dataValues];

            var pieOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                var value = context.raw || 0;
                                var total = context.chart.data.datasets[0].data.reduce((acc, val) => acc + val, 0);
                                var percentage = total ? ((value / total) * 100).toFixed(2) : 0;
                                return `${context.label}: ${percentage}%`;
                            }
                        }
                    },
                    legend: {
                        display: true,
                        position: 'top',
                        onClick: function (event, legendItem, legend) {
                            var index = legendItem.index;
                            var dataset = pieChart.data.datasets[0];

                            // Toggle data visibility
                            if (dataset.data[index] === 0) {
                                dataset.data[index] = originalData[index];
                            } else {
                                dataset.data[index] = 0;
                            }
                            pieChart.update();
                        },
                        labels: {
                            generateLabels: function (chart) {
                                var data = chart.data;
                                return data.labels.map((label, i) => {
                                    var dataset = data.datasets[0];
                                    var isHidden = dataset.data[i] === 0;
                                    return {
                                        text: isHidden ? `${label}` : label,
                                        fillStyle: dataset.backgroundColor[i],
                                        hidden: isHidden,
                                        index: i
                                    };
                                });
                            }
                        }
                    }
                }
            };

            var pieChart = new Chart(pieChartCanvas, {
                type: 'pie',
                data: pieData,
                options: pieOptions
            });

            // Interactive Chart
            var chartData = [
                [0, purchase],
                [1, store],
                [2, recovery],
                [3, returnReq],
                [4, emergency],
                [5, acquired]
            ];

            $.plot('#interactive', [{
                data: chartData,
                label: 'Total Requisitions',
                color: "#007bff"
            }], {
                grid: {
                    hoverable: true,
                    borderColor: "#ccc",
                    borderWidth: 1
                },
                yaxis: {
                    min: 0,
                    show: true,
                    axisLabel: 'Total Requisitions'
                },
                xaxis: {
                    ticks: chartData.map((d, i) => [i, labels[i]]),
                    axisLabel: 'Requisition Types'
                }
            });

            // Tooltip for Interactive Chart
            var tooltip = $('<div class="tooltip-inner"></div>').appendTo('body').hide();

            $('#interactive').bind('plothover', function (event, pos, item) {
                if (item) {
                    tooltip.html(`${labels[item.datapoint[0]]}: ${item.datapoint[1]}`)
                        .css({ top: item.pageY + 10, left: item.pageX + 10, position: 'absolute', background: '#000', color: '#fff', padding: '5px', borderRadius: '5px' })
                        .fadeIn(200);
                } else {
                    tooltip.fadeOut(200);
                }
            });
        });

    </script>

   
    <!-- <script>
        $(function () {
            // Days of the Week
            var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            var dataAdmin = [], dataHOD = [], dataESEManager = [], dataRequisitionOfficers = [];

            // Simulate time spent on ESETRACK (in hours) for each day
            for (var i = 0; i < days.length; i++) {
                dataAdmin.push([i, Math.floor(Math.random() * 10) + 1]); // Random data for Admins
                dataHOD.push([i, Math.floor(Math.random() * 10) + 1]); // Random data for HOD
                dataESEManager.push([i, Math.floor(Math.random() * 10) + 1]); // Random data for ESE Manager
                dataRequisitionOfficers.push([i, Math.floor(Math.random() * 10) + 1]); // Random data for Requisition Officers
            }

            // Plot the chart
            $.plot('#interactive', [
                { data: dataAdmin, label: 'Admins', color: '#3c8dbc' },
                { data: dataHOD, label: 'HOD', color: '#00c0ef' },
                { data: dataESEManager, label: 'ESE Manager', color: '#00a65a' },
                { data: dataRequisitionOfficers, label: 'Requisition Officers', color: '#f56954' }
            ], {
                grid: {
                    hoverable: true,
                    borderColor: '#f3f3f3',
                    borderWidth: 1,
                    tickColor: '#f3f3f3'
                },
                series: {
                    shadowSize: 0,
                    lines: {
                        show: true,
                        fill: true
                    }
                },
                yaxis: {
                    min: 0,
                    max: 18, // Adjusted max value to 18
                    tickInterval: 3, // Set interval for y-axis
                    show: true,
                    axisLabel: 'Time Spent On ESETRACK (Hours)'
                },
                xaxis: {
                    show: true,
                    ticks: days.map((day, index) => [index, day]), // Use day names for x-axis
                    axisLabel: 'Days of the Week'
                }
            });

            // Tooltip for System Usage Chart
            var $tooltip = $('<div class="tooltip-inner"></div>').appendTo('body');

            $('#interactive').bind('plothover', function (event, pos, item) {
                if (item) {
                    var x = item.datapoint[0],
                        y = item.datapoint[1];
                    var day = days[x]; // Get the day name
                    var roleLabel = item.series.label; // Get original label

                    $tooltip.html(roleLabel + " on " + day + ": " + y + " hours")
                        .css({ top: item.pageY + 5, left: item.pageX + 5 })
                        .fadeIn(200);
                } else {
                    $tooltip.fadeOut(200);
                }
            });
        });
    </script> -->
</x-layout>