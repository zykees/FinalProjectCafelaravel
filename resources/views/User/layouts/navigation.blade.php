<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain" 
                aria-controls="navbarMain" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link {{ Request::routeIs('user.home') ? 'active' : '' }}" 
                       href="{{ route('user.home') }}">หน้าแรก</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::routeIs('user.about') ? 'active' : '' }}" 
                       href="{{ route('user.about') }}">เกี่ยวกับเรา</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::routeIs('user.promotions.*') ? 'active' : '' }}" 
                       href="{{ route('user.promotions.index') }}">โปรโมชั่น</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::routeIs('user.booking.*') ? 'active' : '' }}" 
                       href="{{ route('user.booking.create') }}">จองคิว</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::routeIs('user.shop.*') ? 'active' : '' }}" 
                       href="{{ route('user.shop.index') }}">ร้านค้า</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::routeIs('user.contact') ? 'active' : '' }}" 
                       href="{{ route('user.contact') }}">ติดต่อเรา</a>
                </li>
            </ul>
            
            <div class="d-flex align-items-center">
                <a href="{{ route('user.shop.cart') }}" class="btn btn-outline-dark position-relative me-3">
                    <i class="fas fa-shopping-cart"></i>
                    @if(Cart::count() > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            {{ Cart::count() }}
                        </span>
                    @endif
                </a>
            </div>
        </div>
    </div>
</nav>