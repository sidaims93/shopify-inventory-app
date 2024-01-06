<!-- Recent Sales -->
<div class="col-12">
    <div class="card recent-sales overflow-auto">

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
        <h5 class="card-title">Recent Sales <span>| {{$data['dateRangeFormatted']}}</span></h5>

        <table class="table table-borderless datatable">
            <thead>
            <tr>
                @foreach($data['table']['headers'] as $header)
                <th scope="col">{{$header}}</th>
                @endforeach
            </tr>
            </thead>
            <tbody>
                @foreach($data['table']['rows'] as $row)
                <tr>
                    <th scope="row"><a href="{{$row['orderLink']}}">{{$row['#']}}</a></th>
                    <td>{{$row['customer']}}</td>
                    <td><a href="{{$row['productLink']}}" class="text-primary">{{$row['product']}}</a></td>
                    <td>{{$row['price']['prefix'].' '.$row['price']['value']}}</td>
                    <td><span class="badge" style="background-color: {{$row['status']['bg-color']}};">{{$row['status']['value']}}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        </div>

    </div>
</div><!-- End Recent Sales -->