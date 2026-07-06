@extends('layouts.app')
@section('title', 'Riwayat Audit')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <form class="row g-3 mb-4" method="GET" action="{{ route('activity-logs.index') }}">
            <div class="col-md-3">
                <label class="form-label">Pengguna</label>
                <select name="user_id" class="form-select">
                    <option value="">Semua</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tindakan</label>
                <select name="action" class="form-select">
                    <option value="">Semua</option>
                    @foreach(['created', 'updated', 'deleted'] as $action)
                        <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ ucfirst($action) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Sumber Data</label>
                <select name="subject_type" class="form-select">
                    <option value="">Semua</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject }}" {{ request('subject_type') === $subject ? 'selected' : '' }}>{{ class_basename($subject) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Pencarian</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Aksi, URL, subjek...">
            </div>
            <div class="col-md-12 text-end">
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="{{ route('activity-logs.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>User</th>
                        <th>Aksi</th>
                        <th>Subjek</th>
                        <th>URL (Tindakan)</th>
                        <th>Perubahan</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activities as $activity)
                        <tr>
                            <td>{{ $activity->created_at->format('Y-m-d H:i:s') }}</td>
                            <td>{{ $activity->user?->name ?? 'Sistem' }}</td>
                            <td class="text-capitalize">{{ $activity->action }}</td>
                            <td>{{ $activity->subject_name ?? class_basename($activity->subject_type) }} @if($activity->subject_id) #{{ $activity->subject_id }}@endif</td>
                            <td class="text-truncate" style="max-width: 220px;">{{ $activity->url }}</td>
                            <td style="max-width:320px;">
                                @if($activity->changes)
                                    <pre class="mb-0 small">{{ json_encode($activity->changes, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $activity->description }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">Tidak ada riwayat audit.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $activities->links() }}
        </div>
    </div>
</div>
@endsection
