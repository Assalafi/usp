<header class="navbar pcoded-header navbar-expand-lg navbar-light headerpos-fixed noprint" style="background-color: #f4eae1;">
      <div class="m-header" style="background-color: #f4eae1;">
        <a class="mobile-menu" id="mobile-collapse" href="#!" style="background-color: #474311; height: 50%; margin-top: 18px;"><span></span></a>
        <a href="#!" class="b-brand">
          <!-- ========   change your logo hear   ============ -->
          <!-- ========   change your logo hear   ============ -->
          <img src="{{ url('assets/images/logo.png') }}" alt="" class="logo">
        </a>
        <a href="#!" class="mob-toggler" style="background-color: #474311;">
          <i class="feather icon-more-vertical"></i>
        </a>
      </div>
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav mr-auto">
          {{-- <li class="nav-item">
            <a href="#!" class="pop-search"><i class="feather icon-search"></i></a>
            <div class="search-bar">
              <input type="text" class="form-control border-0 shadow-none" placeholder="search here">
              <button type="button" class="close" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
          </li> --}}
        </ul>
        <ul class="navbar-nav ml-auto">
          <li>
            <div class="dropdown drp-user">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <i class="feather icon-user"></i>
              </a>
              <div class="dropdown-menu dropdown-menu-right profile-notification">
                <div class="pro-head">
                  <img src="{{ url('assets/images/user/avatar-1.jpg') }}" class="img-radius" alt="User-Profile-Image">
                  <span>{{ session('name') }}</span>
                  <a href="/logout" class="dud-logout" title="Logout">
                    <i class="feather icon-power"></i>
                  </a>
                </div>
              </div>
            </div>
          </li>
        </ul>
      </div>
</header>
