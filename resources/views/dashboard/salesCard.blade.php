<!-- Sales Card -->
<div class="col-xxl-4 col-md-6">
    <div class="card info-card sales-card">

        <div class="filter">
        <a class="icon" href="#" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></a>
        <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
            <li class="dropdown-header text-start"><h6>Filter</h6></li>
            <li><a class="dropdown-item salesCardFilter" data-range="today" href="#">Today</a></li>
            <li><a class="dropdown-item salesCardFilter" data-range="month" href="#">This Month</a></li>
            <li><a class="dropdown-item salesCardFilter" data-range="year" href="#">This Year</a></li>
        </ul>
        </div>

        <div class="card-body">
            <h5 class="card-title">Sales <span>| Today</span></h5>

            <div class="d-flex align-items-center">
                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                    <i class="{{$data['class']}}"></i>
                </div>
                <div class="ps-3">
                    <h6 class="displayVal">{{$data['displayValformatted']}}</h6>
                    <span class="small pt-1 fw-bold percentage" style="color:{{$data['trend']['color']}}">{{$data['trend']['label']}}</span> 
                    <span class="text-muted small pt-2 ps-1 text">{{$data['trend']['text']}}</span>
                </div>
            </div>
        </div>

    </div>
</div><!-- End Sales Card -->