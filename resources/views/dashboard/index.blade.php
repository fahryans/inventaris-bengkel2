@extends('layouts.admin')

@section('content')
<div class="text-center">
    <h4>Anda akan dialihkan ke dashboard yang sesuai...</h4>
    <script>window.location.href = "{{ route('dashboard') }}";</script>
</div>
@endsection