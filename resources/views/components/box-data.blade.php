<div class="col-sm-6 col-lg-3">
    <div class="card text-white bg-flat-color-1">
        <div class="card-body pb-0">
            <div class="float-right">
                <i class="fa fa-book"></i>
            </div>
            <h4 class="mb-0">
                <span class="count">{{ $totalStuff }}</span>
            </h4>
            <p class="text-light">Total Obat</p>

        </div>

    </div>
</div>

<div class="col-sm-6 col-lg-3">
    <div class="card text-white bg-flat-color-5">
        <div class="card-body pb-0">
            <div class="float-right">
                <i class="fa fa-truck"></i>
            </div>
            <h4 class="mb-0">
                <span class="count">{{ $totalStock }}</span>
            </h4>
            <p class="text-light">Total Transaksi Pembelian</p>

        </div>

    </div>
</div>

<div class="col-sm-6 col-lg-3">
    <div class="card text-white bg-flat-color-4">
        <div class="card-body pb-0">
            <div class="float-right">
                <i class="fa fa-archive"></i>
            </div>
            <h4 class="mb-0">
                <span class="count">{{ $selledBook }}</span>
            </h4>
            <p class="text-light">Obat Terjual</p>

        </div>

    </div>
</div>

<div class="col-sm-6 col-lg-3">
    <div class="card text-white bg-flat-color-3">
        <div class="card-body pb-0">
            <div class="float-right">
                <i class="fa fa-archive"></i>
            </div>
            <h4 class="mb-0">
                <span class="count">{{ $buyedBook }}</span>
            </h4>
            <p class="text-light">Obat Bertambah</p>

        </div>

    </div>
</div>

<div class="col-sm-6 col-lg-3">
    <div class="card text-white bg-flat-color-1">
        <div class="card-body pb-0">
            <div class="float-right">
                <i class="fa fa-shopping-basket"></i>
            </div>
            <h4 class="mb-0">
                <span class="count">{{ $totalTransaction }}</span>
            </h4>
            <p class="text-light">Total Transaksi Penjualan</p>

        </div>

    </div>
</div>

<div class="col-sm-6 col-lg-3">
    <div class="card text-white bg-flat-color-5">
        <div class="card-body pb-0">
            <div class="float-right">
                <i class="fa fa-times"></i>
            </div>
            <h4 class="mb-0">
                <span class="count">{{ $totalCancel }}</span>
            </h4>
            <p class="text-light">Total Batal Transaksi Penjualan </p>

        </div>

    </div>
</div>

<div class="col-sm-6 col-lg-3">
    <div class="card text-white bg-flat-color-4">
        <div class="card-body pb-0">
            <div class="float-right">
                <i class="fa fa-money"></i>
            </div>
            <h4 class="mb-0">
                <span class="count">{{ $totalIncome }}</span>
            </h4>
            <p class="text-light">Total Pendapatan</p>

        </div>

    </div>
</div>

<div class="col-sm-6 col-lg-3">
    <div class="card text-white bg-flat-color-3">
        <div class="card-body pb-0">
            <div class="float-right">
                <i class="fa fa-money"></i>
            </div>
            <h4 class="mb-0">
                <span class="count">{{ $totalBuy }}</span>
            </h4>
            <p class="text-light">Total Pembelian Obat</p>

        </div>

    </div>
</div>

<div class="col-sm-6 col-lg-3">
    <div class="card text-white bg-flat-color-1">
        <div class="card-body pb-0">
            <div class="float-right">
                <i class="fa fa-money"></i>
            </div>
            <h4 class="mb-0">
                <span class="count">{{ $totalExpend }}</span>
            </h4>
            <p class="text-light">Total Pengeluaran</p>

        </div>

    </div>
</div>

<div class="col-sm-6 col-lg-3">
    <div class="card text-white bg-flat-color-5">
        <div class="card-body pb-0">
            <div class="float-right">
                <i class="fa fa-money"></i>
            </div>
            <h4 class="mb-0">
                <span class="count">{{ $profitOne }}</span>
            </h4>
            <p class="text-light">Laba / Rugi (Kotor)</p>

        </div>

    </div>
</div>

<div class="col-sm-6 col-lg-3">
    <div class="card text-white bg-flat-color-4">
        <div class="card-body pb-0">
            <div class="float-right">
                <i class="fa fa-money"></i>
            </div>
            <h4 class="mb-0">
                <span class="count">{{ $profit }}</span>
            </h4>
            <p class="text-light">Laba / Rugi (Bersih)</p>

        </div>

    </div>
</div>

<div class="col-sm-6 col-lg-3">
    <div class="card text-white bg-flat-color-3">
        <div class="card-body pb-0">
            <div class="float-right">
                <i class="fa fa-money"></i>
            </div>
            <h4 class="mb-0">
                <span class="count">{{ $totalOpname }}</span>
            </h4>
            <p class="text-light">Total Stok Opname</p>

        </div>

    </div>
</div>

<div class="col-lg-6">
   <div class="card">
       <div class="card-body">
           @php
               $name = [
                   'today' => 'Hari ini',
                   'week' => '7 Hari Terakhir',
                   'month' => 'Bulan ini',
                   'year' => 'Tahun ini'
               ];
           @endphp
           <h4 class="mb-3">Grafik Pengeluaran {{ $name[$type] }}</h4>
           <canvas id="barChartPengeluaran-{{ $type }}"></canvas>
       </div>
   </div>
</div>

<div class="col-lg-6">
   <div class="card">
       <div class="card-body">
           <h4 class="mb-3">Grafik Pendapatan {{ $name[$type] }}</h4>
           <canvas id="barChartPenjualan-{{ $type }}"></canvas>
       </div>
   </div>
</div>

@push('js')
    <script src="{{ asset('sufee-admin/vendors/chart.js/dist/Chart.min.js') }}"></script>
    <script>
        const transactions{{ $type }} = JSON.parse('@json($incomeGraphic)')
        const stocks{{ $type }} = JSON.parse('@json($stockGraphic)')
        const type{{ $type }} = '{{ request()->type }}'

        var ctx{{ $type }} = document.getElementById("barChartPenjualan-{{ $type }}");
        var myChart = new Chart(ctx{{ $type }}, {
            type: 'line',
            data: {
                labels: transactions{{ $type }}.date,
                datasets: [{
                    label: "Pendapatan",
                    data: transactions{{ $type }}.data,
                    borderColor: "rgba(0, 123, 255, 0.9)",
                    borderWidth: "2.5",
                    backgroundColor: "transparent"
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }],
                    xAxes: [
                        {
                            scaleLabel: {
                                display: true,
                                labelString: 'Grafik Pendapatan {{ $name[$type] }}',
                                borderColor: "rgba(0, 123, 255, 0.9)",
                                borderWidth: "2.5",
                                backgroundColor: "transparent"
                            }
                        }
                    ]
                }
            }
        });

        var ctx2{{ $type }} = document.getElementById("barChartPengeluaran-{{ $type }}");
        var myChart2 = new Chart(ctx2{{ $type }}, {
            type: 'line',
            data: {
                labels: stocks{{ $type }}.date,
                datasets: [{
                    label: "Pengeluaran",
                    data: stocks{{ $type }}.data,
                    borderColor: "rgba(255,193,7,0.9)",
                    borderWidth: "2.5",
                    backgroundColor: "transparent"
                }]
            },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }],
                    xAxes: [
                        {
                            scaleLabel: {
                                display: true,
                                labelString: 'Grafik Pendapatan {{ $name[$type] }}',
                                borderColor: "rgba(255,193,7,0.9)",
                                borderWidth: "2.5",
                                backgroundColor: "transparent"
                            }
                        }
                    ]
                }
            }
        });
    </script>
@endpush