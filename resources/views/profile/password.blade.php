@extends('layouts.app')

@section('content')
<div class="container-fluid py-2">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-shield-lock-fill text-warning me-2"></i>Đổi mật khẩu tài khoản
                </h5>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form action="{{ route('profile.password.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                            <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required placeholder="Nhập mật khẩu hiện tại...">
                            @error('current_password')
                                <div class="invalid-feedback fw-medium">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Mật khẩu mới <span class="text-danger">*</span></label>
                            <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" required placeholder="Tối thiểu 6 ký tự...">
                            @error('new_password')
                                <div class="invalid-feedback fw-medium">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-bold text-secondary">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                            <input type="password" name="new_password_confirmation" class="form-control" required placeholder="Nhập lại mật khẩu mới...">
                        </div>

                        <hr class="my-3 text-muted opacity-25">

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('profile.show') }}" class="btn btn-light btn-sm text-secondary rounded-3 fw-semibold border">
                                <i class="bi bi-arrow-left me-1"></i> Quay lại
                            </a>

                            <button type="submit" class="btn btn-warning text-dark px-4 fw-bold rounded-3 shadow-sm">
                                <i class="bi bi-key-fill me-1"></i> Cập nhật mật khẩu
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection