@extends('layouts.app')
@section('title', 'Riwayat Audit')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="mb-0">Riwayat Audit</h5>
                <p class="text-muted mb-0">Log lengkap semua perubahan data dalam sistem.</p>
            </div>
        </div>

        <!-- FILTER SECTION -->
        <div class="card mb-4 bg-light border-0">
            <div class="card-body">
                <form class="row g-3" method="GET" action="{{ route('activity-logs.index') }}">
                    <div class="col-md-2">
                        <label class="form-label small">Pengguna</label>
                        <select name="user_id" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Tindakan</label>
                        <select name="action" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach(['created', 'updated', 'deleted'] as $action)
                                <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ ucfirst($action) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Tipe Data</label>
                        <select name="subject_type" class="form-select form-select-sm">
                            <option value="">Semua</option>
                            @foreach($subjects as $subject)
                                <option value="{{ $subject }}" {{ request('subject_type') === $subject ? 'selected' : '' }}>{{ class_basename($subject) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Pencarian</label>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Cari user, subjek, deskripsi...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel"></i> Filter</button>
                        <a href="{{ route('activity-logs.index') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-clockwise"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- ACTIVITY TIMELINE -->
        @forelse($activities as $activity)
            <div class="activity-card mb-3 border rounded-3 position-relative">
                <!-- Timeline dot -->
                <div class="position-absolute start-0 top-50 translate-middle-y" style="left: -12px; width: 24px; height: 24px;">
                    <div class="rounded-circle bg-white border-2 border-primary d-flex align-items-center justify-content-center h-100 w-100">
                        @if($activity->action === 'created')
                            <i class="bi bi-plus text-success" style="font-size: 12px;"></i>
                        @elseif($activity->action === 'updated')
                            <i class="bi bi-pencil text-info" style="font-size: 12px;"></i>
                        @elseif($activity->action === 'deleted')
                            <i class="bi bi-trash text-danger" style="font-size: 12px;"></i>
                        @endif
                    </div>
                </div>

                <div class="card-body py-3 ps-4">
                    <div class="row align-items-start">
                        <!-- Left: Core Info -->
                        <div class="col-md-8">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-{{ $activity->action === 'created' ? 'success' : ($activity->action === 'updated' ? 'info' : 'danger') }}">
                                    {{ ucfirst($activity->action) }}
                                </span>
                                <span class="text-muted small">{{ $activity->created_at->format('d M Y H:i:s') }}</span>
                            </div>

                            <div class="mb-2">
                                <strong>{{ $activity->user?->name ?? 'Sistem' }}</strong>
                                <span class="text-muted ms-2 small">
                                    {{ $activity->user?->email ?? '—' }}
                                </span>
                            </div>

                            <div class="mb-2">
                                <p class="mb-0">
                                    <strong>{{ $activity->subject_name ?? class_basename($activity->subject_type) }}</strong>
                                    @if($activity->subject_id)
                                        <span class="text-muted small">#{{ $activity->subject_id }}</span>
                                    @endif
                                </p>
                                <small class="text-muted">{{ $activity->description }}</small>
                            </div>
                        </div>

                        <!-- Right: Actions & Details -->
                        <div class="col-md-4 text-md-end">
                            @if($activity->changes)
                                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#changes-{{ $activity->id }}" aria-expanded="false">
                                    <i class="bi bi-eye"></i> Lihat Perubahan
                                </button>
                            @else
                                <span class="badge bg-secondary">No Changes</span>
                            @endif
                        </div>
                    </div>

                    <!-- Collapsible Changes Section -->
                    @if($activity->changes)
                        <div class="collapse mt-3" id="changes-{{ $activity->id }}">
                            <div class="bg-light border rounded p-3">
                                <div class="row">
                                    @if($activity->old_values)
                                        <div class="col-md-6">
                                            <h6 class="text-danger small mb-2"><i class="bi bi-dash-circle"></i> Sebelumnya</h6>
                                            <div class="bg-white border border-danger border-opacity-25 rounded p-2">
                                                @foreach($activity->old_values as $key => $value)
                                                    <div class="mb-2">
                                                        <small class="text-muted">{{ $key }}:</small>
                                                        <div class="small font-monospace text-truncate" title="{{ json_encode($value) }}">
                                                            {{ is_array($value) || is_object($value) ? json_encode($value) : $value }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if($activity->new_values)
                                        <div class="col-md-6">
                                            <h6 class="text-success small mb-2"><i class="bi bi-plus-circle"></i> Sesudahnya</h6>
                                            <div class="bg-white border border-success border-opacity-25 rounded p-2">
                                                @foreach($activity->new_values as $key => $value)
                                                    <div class="mb-2">
                                                        <small class="text-muted">{{ $key }}:</small>
                                                        <div class="small font-monospace text-truncate" title="{{ json_encode($value) }}">
                                                            {{ is_array($value) || is_object($value) ? json_encode($value) : $value }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @else
                                        <div class="col-12">
                                            <h6 class="text-success small mb-2"><i class="bi bi-plus-circle"></i> Perubahan</h6>
                                            <div class="bg-white border border-success border-opacity-25 rounded p-2">
                                                @foreach($activity->changes as $key => $value)
                                                    <div class="mb-2">
                                                        <small class="text-muted">{{ $key }}:</small>
                                                        <div class="small font-monospace text-truncate" title="{{ json_encode($value) }}">
                                                            {{ is_array($value) || is_object($value) ? json_encode($value) : $value }}
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="alert alert-info text-center py-4">
                <i class="bi bi-info-circle me-2"></i> Tidak ada riwayat audit yang sesuai dengan filter.
            </div>
        @endforelse

        <!-- PAGINATION -->
        @if($activities->hasPages())
            <nav class="mt-4" aria-label="Page navigation">
                {{ $activities->links() }}
            </nav>
        @endif
    </div>
</div>

<style>
    .activity-card {
        border-left: 3px solid #0d6efd !important;
        transition: all 0.3s ease;
    }

    .activity-card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        background-color: rgba(13, 110, 253, 0.02);
    }

    .font-monospace {
        font-family: 'Courier New', monospace;
        font-size: 0.85rem;
    }

    .collapse-transition {
        transition: max-height 0.3s ease;
    }
</style>
@endsection
