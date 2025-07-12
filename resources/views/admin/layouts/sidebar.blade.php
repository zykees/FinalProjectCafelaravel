
<div class="sidebar bg-dark text-white" style="min-height: 100vh; width: 250px;">
    <div class="sidebar-header p-3">
        <h3 class="text-center">Admin Panel</h3>
    </div>
    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}" class="nav-link text-white {{ request()->routeIs('admin.dashboard') ? 'active bg-primary' : '' }}">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('admin.users.index') }}" class="nav-link text-white {{ request()->routeIs('admin.users.*') ? 'active bg-primary' : '' }}">
                    <i class="fas fa-users me-2"></i> Users Management
                </a>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('admin.products.index') }}" class="nav-link text-white {{ request()->routeIs('admin.products.*') ? 'active bg-primary' : '' }}">
                    <i class="fas fa-coffee me-2"></i> Products
                </a>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('admin.orders.index') }}" class="nav-link text-white {{ request()->routeIs('admin.orders.*') ? 'active bg-primary' : '' }}">
                    <i class="fas fa-shopping-cart me-2"></i> Orders
                </a>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('admin.promotion-bookings.index') }}" class="nav-link text-white {{ request()->routeIs('admin.promotion-bookings.*') ? 'active bg-primary' : '' }}">
                    <i class="fas fa-calendar-alt me-2"></i> Bookings
                </a>
            </li>
            
            <li class="nav-item">
                <a href="{{ route('admin.promotions.index') }}" class="nav-link text-white {{ request()->routeIs('admin.promotions.*') ? 'active bg-primary' : '' }}">
                    <i class="fas fa-tag me-2"></i> Promotions
                </a>
            </li>
                        <li class="nav-item">
                <a href="#" class="nav-link text-white" data-bs-toggle="collapse" data-bs-target="#reportsCollapse" 
                   aria-expanded="{{ request()->routeIs('admin.reports.*') ? 'true' : 'false' }}">
                    <i class="fas fa-chart-bar me-2"></i> Reports
                    <i class="fas fa-angle-down float-end mt-1"></i>
                </a>
                <div id="reportsCollapse" class="collapse {{ request()->routeIs('admin.reports.*') ? 'show' : '' }}">
                    <ul class="nav flex-column ms-3">
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.index') }}" 
                               class="nav-link text-white {{ request()->routeIs('admin.reports.index') ? 'active bg-primary' : '' }}">
                                <i class="fas fa-chart-line me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.sales') }}" 
                               class="nav-link text-white {{ request()->routeIs('admin.reports.sales') ? 'active bg-primary' : '' }}">
                                <i class="fas fa-dollar-sign me-2"></i> Sales
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.bookings') }}" 
                               class="nav-link text-white {{ request()->routeIs('admin.reports.bookings') ? 'active bg-primary' : '' }}">
                                <i class="fas fa-calendar-check me-2"></i> Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('admin.reports.promotions') }}" 
                               class="nav-link text-white {{ request()->routeIs('admin.reports.promotions') ? 'active bg-primary' : '' }}">
                                <i class="fas fa-percentage me-2"></i> Promotions
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item ">
                <a class="nav-link" href="{{ route('admin.news.index') }}">
                    <i class="fas fa-newspaper"></i> News Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.gallery.index') }}">
                    <i class="fas fa-images"></i>
                    <span>Gallery Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('admin.line-richmenu.index') }}" class="nav-link text-white {{ request()->routeIs('admin.line-richmenu.*') ? 'active bg-primary' : '' }}">
                    <i class="fab fa-line me-2"></i> LINE Rich Menu
                </a>
            </li>
            <li class="nav-item mt-4">
                <a href="{{ route('admin.settings') }}" class="nav-link text-white {{ request()->routeIs('admin.settings') ? 'active bg-primary' : '' }}">
                    <i class="fas fa-cog me-2"></i> Settings
                </a>
            </li>

        </ul>
    </nav>
</div>