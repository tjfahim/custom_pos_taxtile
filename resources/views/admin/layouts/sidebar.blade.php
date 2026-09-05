<aside id="left-panel" class="left-panel">
    <nav class="navbar navbar-expand-sm navbar-default">
        <div class="navbar-header">
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main-menu" 
                    aria-controls="main-menu" aria-expanded="false" aria-label="Toggle navigation">
                <i class="fa fa-bars"></i>
            </button>
            <div class="p-2">
                <h4 class="text-white mb-0">Faisal Textile</h4>
                <small class="text-white-50">POS</small>
            </div>
        </div>

        <div id="main-menu" class="main-menu collapse navbar-collapse">
            <ul class="nav navbar-nav">
                <!-- Dashboard -->
                <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}">
                        <i class="menu-icon fa fa-building"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                
                <!-- Customer -->
                <li class="{{ request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.customers.index') }}">
                        <i class="menu-icon fa fa-users"></i>
                        <span>Customers</span>
                    </a>
                </li>
                @can('create invoices')
                <!-- POS -->
                <li class="{{ request()->routeIs('admin.invoices.pos') ? 'active' : '' }}">
                    <a href="{{ route('admin.invoices.pos') }}">
                        <i class="menu-icon fa fa-calculator"></i>
                        <span>POS</span>
                    </a>
                </li>
                @endcan
                
                <!-- Invoices -->
                <li class="{{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.invoices.index') }}">
                        <i class="menu-icon fa fa-calendar-o"></i>
                        <span>Invoices</span>
                    </a>
                </li>
                @can('attendance')
                <li class="{{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.attendance.index') }}">
                        <i class="menu-icon fa fa-map-marker-alt"></i>
                        <span>Attendance</span>
                    </a>
                </li>
                @endcan
                
                <!-- Admin Only Section -->
                @hasrole('admin')
                    <li class="menu-title">
                        <span>Admin</span>
                    </li>
                    
                    <!-- User Management -->
                    <li class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.users.index') }}">
                            <i class="menu-icon fa fa-user-plus"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    
                    <!-- Role Management -->
                    <li class="{{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.roles.index') }}">
                            <i class="menu-icon	fa fa-vcard-o"></i>
                            <span>Roles</span>
                        </a>
                    </li>

                    <li class="{{ request()->routeIs('admin.pathao.*') ? 'active' : '' }}">
                        <a href="{{ route('admin.pathao.manage') }}">
                            <i class="menu-icon fa fa-map-marker"></i>
                            <span>Pathao Locations</span>
                        </a>
                    </li>
               <li class="{{ request()->routeIs('admin.delivery-charges.*') ? 'active' : '' }}">
    <a href="{{ route('admin.delivery-charges.index') }}">
        <i class="menu-icon fa fa-truck"></i>
        <span>Delivery Charges</span>
    </a>
</li>
<li class="{{ request()->routeIs('admin.inside-dhaka.*') ? 'active' : '' }}">
    <a href="{{ route('admin.inside-dhaka.index') }}">
        <i class="menu-icon fa fa-map-marker-alt"></i>
        <span>Inside Dhaka</span>
    </a>
</li>
<li class="{{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}">
    <a href="{{ route('admin.attendance.index') }}">
        <i class="menu-icon fa fa-map-marker-alt"></i>
        <span>Attendance</span>
    </a>
</li>
<li class="{{ request()->routeIs('admin.invoices.history.*') ? 'active' : '' }}">
    <a href="{{ route('admin.invoices.history.list') }}">
        <i class="menu-icon fa fa-map-marker-alt"></i>
        <span>admin.invoices.history</span>
    </a>
</li>
    

                @endhasrole
                
                <!-- Logout -->
                <li class="menu-title">
                    <span>Account</span>
                </li>
                
                <li>
                    <a href="{{ route('logout') }}"
                       >
                        <i class="menu-icon 	fa fa-power-off"></i>
                        <span>Logout</span>
                    </a>
                  
                </li>
            </ul>
        </div>
    </nav>
</aside>

<style>
.left-panel {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.navbar-header {
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.main-menu {
    padding-top: 5px;
}

.navbar-nav {
    width: 100%;
}

.navbar-nav li {
    position: relative;
}

.navbar-nav li a {
    color: rgba(255,255,255,0.8);
    padding: 6px 12px;
    display: flex;
    align-items: center;
    transition: all 0.15s ease;
    text-decoration: none;
    font-size: 13px;
    line-height: 1.2;
}

.navbar-nav li a:hover {
    color: #fff;
    background: rgba(255,255,255,0.1);
    padding-left: 15px;
}


.navbar-nav li a i {
    width: 18px;
    font-size: 13px;
    margin-right: 8px;
    text-align: center;
}

.navbar-nav li a span {
    flex: 1;
}

.menu-title {
    color: rgba(255,255,255,0.5);
    font-size: 9px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 8px 12px 2px;
    margin-top: 3px;
    border-top: 1px solid rgba(255,255,255,0.1);
}

.menu-title:first-child {
    border-top: none;
    margin-top: 0;
    padding-top: 6px;
}
.navbar .navbar-nav li > a {
    background: none !important;
    color: #c8c9ce !important;
    display: inline-block;
    font-family: 'Open Sans';
    font-size: 14px;
    line-height: 30px;
    padding: 7px 0 !important;
    }
    .navbar-nav li a i {
 
    margin-right: 0px !important;
    }
</style>