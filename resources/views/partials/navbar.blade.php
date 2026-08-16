<nav class="main-header navbar navbar-expand navbar-white navbar-light">

<ul class="navbar-nav">

<li class="nav-item">

<a class="nav-link" href="#" onclick="toggleSidebar(); return false;">

<i class="fas fa-bars"></i>

</a>

</li>

</ul>

<ul class="navbar-nav ms-auto">

<li class="nav-item dropdown">

<a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false" aria-haspopup="true">

{{ Auth::user()->nama }}

</a>

<div class="dropdown-menu dropdown-menu-end">

<a href="{{ route('profile.edit') }}" class="dropdown-item">

<i class="fas fa-user"></i> Profil

</a>

<form method="POST" action="{{ route('logout') }}">

@csrf

<button class="dropdown-item" type="submit">

<i class="fas fa-sign-out-alt"></i> Logout

</button>

</form>

</div>

</li>

</ul>

</nav>
