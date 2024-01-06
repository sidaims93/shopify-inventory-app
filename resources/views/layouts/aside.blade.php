@php
$routeName = Route::currentRouteName();
@endphp
<!-- ======= Sidebar ======= -->
<aside id="sidebar" class="sidebar">
  <ul class="sidebar-nav" id="sidebar-nav">
    <li class="nav-item">
      <a class="nav-link @if($routeName == 'home') active @endif" @if($routeName == 'home') href="#" @else href="{{route('home')}}" @endif>
        <i class="bi bi-grid"></i>
        <span>Dashboard</span>
      </a>
    </li><!-- End Dashboard Nav -->
    <li class="nav-item">
      <a class="nav-link collapsed" data-bs-target="#components-nav" data-bs-toggle="collapse" href="#">
        <i class="bi bi-menu-button-wide"></i><span>Your Store</span><i class="bi bi-chevron-down ms-auto"></i>
      </a>
      <ul id="components-nav" class="nav-content collapse show" data-bs-parent="#sidebar-nav">
        <li>
          <a href="{{route('shopify.orders')}}">
            <i class="bi bi-circle"></i><span>Orders</span>
          </a>
        </li>
        <li>
          <a href="{{route('shopify.products')}}">
            <i class="bi bi-circle"></i><span>Products</span>
          </a>
        </li>
        <li>
          <a href="{{route('shopify.product.collections')}}">
            <i class="bi bi-circle"></i><span>Product Collections</span>
          </a>
        </li>
      </ul>
    </li><!-- End Components Nav --> 
    <li class="nav-item">
      <a class="nav-link collapsed" href="{{route('shopify.inventories')}}">
        <i class="bi bi-gear"></i>
        <span>Inventories</span>
      </a>
    </li><!-- End Blank Page Nav -->
  </ul>
</aside><!-- End Sidebar-->