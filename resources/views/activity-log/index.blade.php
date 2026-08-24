@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        <i class="fas fa-history me-2"></i>Aktivitas Sistem
    </h1>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>User</th>
                        <th>Aksi</th>
                        <th>Model</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                    <tr>
                        <td>{{ $activity->created_at->format('d/m/Y H:i:s') }}</td>
                        <td>{{ $activity->causer->nama ?? 'System' }}</td>
                        <td>
                            @if($activity->event === 'created')
                                <span class="badge bg-success">Create</span>
                            @elseif($activity->event === 'updated')
                                <span class="badge bg-warning">Update</span>
                            @elseif($activity->event === 'deleted')
                                <span class="badge bg-danger">Delete</span>
                            @else
                                <span class="badge bg-info">{{ $activity->event }}</span>
                            @endif
                        </td>
                        <td>{{ class_basename($activity->subject_type) }} #{{ $activity->subject_id }}</td>
                        <td>
                            @if($activity->event === 'updated' && isset($activity->properties['old']))
                                <small class="text-muted">
                                    @foreach($activity->properties['attributes'] as $key => $value)
                                        @if(isset($activity->properties['old'][$key]) && $activity->properties['old'][$key] != $value)
                                            <strong>{{ $key }}:</strong> {{ $activity->properties['old'][$key] }} → {{ $value }}<br>
                                        @endif
                                    @endforeach
                                </small>
                            @else
                                <small class="text-muted">{{ json_encode($activity->properties['attributes'] ?? []) }}</small>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center">Belum ada aktivitas</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $activities->links() }}
    </div>
</div>
@endsection
