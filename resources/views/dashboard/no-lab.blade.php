@extends('layouts.admin')

@section('content')
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-flask text-muted fa-3x mb-3"></i>
        <h4>{{ $message ?? 'Belum ada informasi' }}</h4>
        <p class="text-muted">Silakan hubungi admin jurusan untuk ditugaskan sebagai kepala laboratorium.</p>
    </div>
</div>
@endsection