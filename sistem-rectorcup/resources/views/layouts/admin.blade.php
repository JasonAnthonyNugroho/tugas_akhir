@extends('layouts.app')
@section('title', 'Admin - Kelola Jadwal')
@section('content')

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white font-weight-bold">Tambah Jadwal Baru</div>
        <div class="card-body">
            <form action="{{ route('pertandingan.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-3">
                        <label>Tim A</label>
                        <select name="team_a" class="form-control" required>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Tim B</label>
                        <select name="team_b" class="form-control" required>
                            @foreach($teams as $team)
                                <option value="{{ $team->id }}">{{ $team->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Waktu</label>
                        <input type="datetime-local" name="waktu" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label>Lokasi</label>
                        <input type="text" name="lokasi" class="form-control" placeholder="Contoh: GOR UKDW" required>
                    </div>
                </div>
                <button class="btn btn-success mt-3 shadow-sm"><i class="bi bi-plus-circle"></i> Tambahkan Jadwal</button>
            </form>
        </div>
    </div>
@endsection