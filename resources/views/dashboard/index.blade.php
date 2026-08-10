@extends('layouts.admin')

@section('title','Dashboard')

@section('content')

<div class="row">

<div class="col-lg-3">

<div class="small-box bg-primary">

<div class="inner">

<h3>0</h3>

<p>Total Alat</p>

</div>

<div class="icon">

<i class="fas fa-tools"></i>

</div>

</div>

</div>

<div class="col-lg-3">

<div class="small-box bg-success">

<div class="inner">

<h3>0</h3>

<p>Total Bahan</p>

</div>

<div class="icon">

<i class="fas fa-box"></i>

</div>

</div>

</div>

<div class="col-lg-3">

<div class="small-box bg-warning">

<div class="inner">

<h3>0</h3>

<p>Peminjaman</p>

</div>

<div class="icon">

<i class="fas fa-handshake"></i>

</div>

</div>

</div>

<div class="col-lg-3">

<div class="small-box bg-danger">

<div class="inner">

<h3>0</h3>

<p>User</p>

</div>

<div class="icon">

<i class="fas fa-users"></i>

</div>

</div>

</div>

</div>

@endsection