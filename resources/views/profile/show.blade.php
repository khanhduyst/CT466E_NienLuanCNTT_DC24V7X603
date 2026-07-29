@extends('layouts.app')

@section('content')
<div class="container-fluid py-2">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-person-circle text-primary me-2"></i>Thông tin cá nhân
                </h5>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-3" role="alert">
                    <ul class="mb-0 ps-3 small">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-secondary">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control fw-semibold" value="{{ old('name', $user->name) }}" required placeholder="Nhập họ và tên...">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Địa chỉ Email</label>
                                <input type="email" class="form-control bg-light fw-medium text-muted" value="{{ $user->email }}" disabled readonly>
                                <div class="form-text small">Email dùng để đăng nhập và không thể thay đổi.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Chức vụ / Vai trò</label>
                                <input type="text" class="form-control bg-light fw-bold text-primary" value="@if(($user->role->name ?? '') == 'super_admin' || $user->role_id == 1) CHỦ CỬA HÀNG @elseif(($user->role->name ?? '') == 'manager' || $user->role_id == 2) QUẢN LÝ @else THU NGÂN @endif" disabled readonly>
                            </div>
                        </div>

                        <hr class="my-4 text-muted opacity-25">

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('profile.password') }}" class="btn btn-outline-warning btn-sm rounded-3 fw-semibold">
                                <i class="bi bi-shield-lock me-1"></i> Đổi mật khẩu
                            </a>

                            <button type="submit" class="btn btn-primary px-4 fw-bold rounded-3 shadow-sm">
                                <i class="bi bi-save me-1"></i> Lưu thay đổi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection