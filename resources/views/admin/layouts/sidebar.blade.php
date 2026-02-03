
<aside id="left-panel" class="left-panel">
        <nav class="navbar navbar-expand-sm navbar-default">

            <div class="navbar-header">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#main-menu" aria-controls="main-menu" aria-expanded="false" aria-label="Toggle navigation">
                    <i class="fa fa-bars"></i>
                </button>
               <H2 class="text-white p-3">Custom POS</H2>
            </div>

            <div id="main-menu" class="main-menu collapse navbar-collapse">
                <ul class="nav navbar-nav">
                    <li class="active">
                        <a href=""> <i class="menu-icon fa fa-dashboard"></i>Dashboard </a>
                    </li>
                    <h3 class="menu-title">UI elements</h3>
                    <li class="menu-item-has-children">
                       
                            <a href="{{ route('customers.index') }}">
                                <i class="fa fa-puzzle-piece"></i> Customer
                            </a>
                         
                           
                    </li>
                    <!-- In your navigation -->
<li class="nav-item">
    <a class="nav-link" href="{{ route('invoices.pos') }}">
        <i class="fa fa-cash-register"></i> POS
    </a>
</li>
<li class="nav-item">
    <a class="nav-link" href="{{ route('invoices.index') }}">
        <i class="fa fa-file-invoice"></i> Invoices
    </a>
</li>

                   
                  
                </ul>
            </div>
        </nav>
    </aside>