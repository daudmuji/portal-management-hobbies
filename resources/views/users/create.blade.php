@extends('layouts.app')
@section('title', 'Tambah User')

@php
    $hobbies = old('hobbies', []);
@endphp

@section('content')
    <div class="card shadow-sm">
        <div class="card-header"><h5 class="mb-0">Tambah User</h5></div>
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Nama</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="form-control @error('name') is-invalid @enderror" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="form-control @error('email') is-invalid @enderror" autocomplete="off" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password"
                           class="form-control @error('password') is-invalid @enderror" required autocomplete="new-password">
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Hobi</label>

                    <div id="hobby-list">
                        @foreach ($hobbies as $i => $hobby)
                            <div class="input-group mb-2 hobby-row">
                                <input type="text" name="hobbies[{{ $i }}][name]" value="{{ $hobby['name'] ?? '' }}"
                                       class="form-control" placeholder="Nama hobi">
                                <button type="button" class="btn btn-outline-danger btn-remove-hobby">Hapus</button>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" id="btn-add-hobby" class="btn btn-outline-primary btn-sm">+ Tambah Hobi</button>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const hobbyList = document.getElementById('hobby-list');
        let hobbyIndex = {{ count($hobbies) }};

        document.getElementById('btn-add-hobby').addEventListener('click', () => {
            const row = document.createElement('div');
            row.className = 'input-group mb-2 hobby-row';
            row.innerHTML = `
                <input type="text" name="hobbies[${hobbyIndex}][name]" class="form-control" placeholder="Nama hobi">
                <button type="button" class="btn btn-outline-danger btn-remove-hobby">Hapus</button>`;
            hobbyList.appendChild(row);
            hobbyIndex++;
        });

        hobbyList.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-remove-hobby')) {
                e.target.closest('.hobby-row').remove();
            }
        });
    </script>
@endpush
