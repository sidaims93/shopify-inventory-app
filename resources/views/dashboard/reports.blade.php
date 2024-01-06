<!-- Reports -->
<div class="col-12">
    <div class="card">

        <div class="filter">
        <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
            <li class="dropdown-header text-start">
            <h6>Filter</h6>
            </li>

            <li><a class="dropdown-item" href="#">Today</a></li>
            <li><a class="dropdown-item" href="#">This Month</a></li>
            <li><a class="dropdown-item" href="#">This Year</a></li>
        </ul>
        </div>

        <div class="card-body">
        <h5 class="card-title">Reports <span>/{{$data['dateRangeFormatted']}}</span></h5>

        <!-- Line Chart -->
        <div id="reportsChart"></div>

        <script>
        document.addEventListener("DOMContentLoaded", () => {

            new ApexCharts(document.querySelector("#reportsChart"), {
                series: [{
                    name: 'Sales',
                    data: [{{implode(', ', $data['salesCurve']['values'])}}],
                }, {
                    name: 'Revenue',
                    data: [{{implode(', ', $data['revenueCurve']['values'])}}]
                }, {
                    name: 'Customers',
                    data: [{{implode(', ', $data['customersCurve']['values'])}}]
                }],
                chart: {
                    height: {{$data['chartSettings']['height']}}, //350
                    type: '{{$data['chartSettings']['type']}}', //area
                    toolbar: {
                        show: {{$data['chartSettings']['toolbarShow']}} //false
                    },
                },
                markers: {
                    size: {{$data['markerSize']}}
                },
                @php 
                    $colors = implode("', '", $data['colors']);
                    $colors = "'".$colors."'";
                @endphp
                colors: [{!!$colors!!}],
                fill: {
                    type: "gradient",
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.3,
                        opacityTo: 0.4,
                        stops: [0, 90, 100]
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    curve: 'smooth',
                    width: 2
                },
                xaxis: {
                    type: 'datetime',
                    @php 
                        $categories = implode("', '", $data['categories']);
                        $categories = "'".$categories."'";
                    @endphp
                    categories: [{!!$categories!!}]
                },
                tooltip: {
                    x: {
                        format: '{{$data['tooltipDateTimeFormat']}}'
                    },
                }
            }).render();
        });
        </script>
        <!-- End Line Chart -->
        </div>
    </div>
</div>
<!-- End Reports -->