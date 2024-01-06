@extends('layouts.app')
@section('css')
<link href="https://cdn.datatables.net/v/dt/jq-3.7.0/dt-1.13.8/datatables.min.css" rel="stylesheet">
@endsection
@section('content')

  <div class="pagetitle">
    <div class="row">
      <div class="col-8">
        <h1>Product Collections</h1>
        <nav>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
            <li class="breadcrumb-item">Product Collections</li>
          </ol>
        </nav>
      </div>
      <div class="col-4" style="display: none;">
        {{-- @can('write-orders')
          <a href="{{route('orders.sync')}}" style="float: right" class="btn btn-primary">Sync Product Collections</a>
        @endcan --}}
      </div>
    </div>
  </div><!-- End Page Title -->

  <section class="section">
    <div class="row">
      <div class="col-lg-12">

        <div class="card">
          <div class="card-body">
            <!-- <h5 class="card-title">Product Collections</h5> -->
            <table class="table" id="productsTable">
                <thead>
                  <tr>
                    <th scope="col"></th>
                    <th scope="col">ID</th>
                    <th scope="col">Title</th>
                    <th scope="col">Collection Type</th>
                    <th scope="col">Handle</th>
                    <!-- <th scope="col">Created Date</th> -->
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
      $('#productsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{route('shopify.product.collections')}}",
        columns: [
          {data: 'image', name: 'image'},
          {data: '#', name: '#'},
          {data: 'title', name: 'title'},
          {data: 'collection_type', name: 'collection_type'},
          {data: 'handle', name: 'handle'},
          //{data: 'created_at', name: 'created_at'}
        ]
      })
    });
  </script>
@endsection