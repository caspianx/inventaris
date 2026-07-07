@extends('layouts.app')
@section('title', 'Konfirmasi Import')

@section('content')
<div class="card">
    <div class="card-header bg-white">
        <h5 class="mb-0">Konfirmasi Import: {{ ucfirst($type) }}</h5>
        <p class="small text-muted mb-0">Beberapa baris di file Anda memiliki data yang sudah ada di sistem. Pilih apakah ingin mengganti data yang ada atau batalkan import.</p>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('reports.import', $type) }}">
            @csrf
            <input type="hidden" name="path" value="{{ $path }}">
            <div class="mb-3">
                <h6>Baris yang konflik</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Baris</th>
                                <th>SKU</th>
                                <th>Nama (file)</th>
                                <th>Existing ID</th>
                                <th>Existing Nama</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($conflicts as $c)
                                <tr>
                                    <td>{{ $c['row'] }}</td>
                                    <td>{{ $c['sku'] }}</td>
                                    <td>{{ $c['name'] }}</td>
                                    <td>{{ $c['existing_id'] }}</td>
                                    <td>{{ $c['existing_name'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" name="confirm" value="1" class="btn btn-success">Ganti data yang ada dan Lanjutkan</button>
            </div>
        </form>

        <form method="POST" action="{{ route('reports.import', $type) }}" class="mt-2">
            @csrf
            <input type="hidden" name="path" value="{{ $path }}">
            <button type="submit" name="cancel" value="1" class="btn btn-outline-secondary">Batalkan dan Hapus File</button>
        </form>

        <hr>
        <h6>Contoh beberapa baris awal</h6>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        @if(!empty($sampleRows[0]))
                            @foreach($sampleRows[0] as $h)
                                <th>{{ $h }}</th>
                            @endforeach
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach(array_slice($sampleRows, 1, 20) as $r)
                        <tr>
                            @foreach($r as $c)
                                <td>{{ $c }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
