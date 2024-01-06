@extends('layouts.app')
@section('css')
<link href="https://cdn.datatables.net/v/dt/jq-3.7.0/dt-1.13.8/datatables.min.css" rel="stylesheet">
@endsection
@section('content')

  <div class="pagetitle">
    <div class="row">
      <div class="col-8">
        <h1>Products</h1>
        <nav>
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{route('home')}}">Home</a></li>
            <li class="breadcrumb-item">Products</li>
          </ol>
        </nav>
      </div>
      <div class="col-4">  
          <a href="{{route('shopify.products.sync')}}" target="_blank" style="float: right" class="btn btn-primary">Sync Products</a>
      </div>
    </div>
  </div><!-- End Page Title -->

  <section class="section">
    <div class="row">
      <div class="col-lg-12">

        <div class="card">
          <div class="card-body">
            <input type="hidden" id="storeName" value="{{str_replace('.myshopify.com', '', $storeDetails['myshopify_domain'])}}">
            <!-- <h5 class="card-title">Products</h5> -->
            <table class="table" id="productsTable">
                <thead>
                  <tr>
                    <th scope="col"></th>
                    <th scope="col">ID</th>
                    <th scope="col">Title</th>
                    <th scope="col">Vendor</th>
                    <th scope="col">Status</th>
                    <th scope="col">Created Date</th>
                    <th scope="col">Action</th>
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
        ajax: "{{route('shopify.products')}}",
        columns: [
          {data: 'image', name: 'image'},
          {data: '#', name: '#'},
          {data: 'title', name: 'title'},
          {data: 'vendor', name: 'vendor'},
          {data: 'status', name: 'status'},
          {data: 'created_at', name: 'created_at'},
          {data: 'actions', name: 'actions'}
        ]
      })
    });

    $(document).on('click', '.showProduct', function (e) {
      e.preventDefault();
      var productId = $(this).data('product-id');
      var route = "{{route('shopify.product.show')}}";
      var win = window.open(route+'?product_id='+productId, '_blank');
      win.focus();
    });

    $(document).on('click', '.shopifyProduct', function (e)  {
      e.preventDefault();
      var storeName = $('#storeName').val();
      var productId = $(this).data('product-id');
      var url = 'https://admin.shopify.com/store/'+storeName+'/products/'+productId;
      var win = window.open(url, '_blank');
      win.focus();
    });
  </script>
@endsection