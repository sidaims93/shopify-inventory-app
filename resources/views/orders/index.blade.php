@extends('layouts.app')
@section('css')
<link href="https://cdn.datatables.net/v/dt/jq-3.7.0/dt-1.13.8/datatables.min.css" rel="stylesheet">
@endsection
@section('content')

  <div class="pagetitle">
    <div class="row">
      <div class="col-8">
        <h1>Orders</h1>
        <nav>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
            <li class="breadcrumb-item">Orders</li>
          </ol>
        </nav>
      </div>
      <div class="col-4">
        <a href="{{route('shopify.orders.sync')}}" style="float: right" class="btn btn-primary">Sync Orders</a>
      </div>
    </div>
  </div><!-- End Page Title -->

  <section class="section">
    <div class="row">
      <div class="col-lg-12">

        <div class="card">
          <div class="card-body">
            <!-- <h5 class="card-title">Orders</h5> -->
            <table class="table" id="ordersTable">
              <thead>
                <tr>
                  <th scope="col">#</th>
                  <th scope="col">Name</th>
                  <th scope="col">Customer Email</th>
                  <th scope="col" class="text-center">Payment Status</th>
                  <th scope="col">Customer Phone</th>
                  <th scope="col">Created Date</th>
                </tr>
              </thead>
              <tbody>
                
              </tbody>
            </table>
            <!-- End Table with stripped rows -->
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection

@section('scripts')
  <script src="https://cdn.datatables.net/v/dt/jq-3.7.0/dt-1.13.8/datatables.min.js"></script>
  <script>
    $(document).ready(function () {
      $('#ordersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{route('shopify.orders')}}",
        columns: [
          {data: '#', name: '#'},
          {data: 'name', name: 'name'},
          {data: 'email', name: 'email'},
          {data: 'payment_status', name: 'payment_status'},
          {data: 'phone', name: 'phone'},
          {data: 'created_at', name: 'created_at'}
        ]
      })
    });
  </script>
@endsection