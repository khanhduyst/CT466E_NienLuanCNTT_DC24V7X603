@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Quản lý danh mục hàng hóa</h3>
        <p class="text-muted small mb-0">Thiết lập nhóm sản phẩm đa cấp cho tiệm tạp hóa</p>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success border-0 shadow-sm mb-4 py-2 small" style="border-radius: 12px;">
    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="alert alert-danger border-0 shadow-sm mb-4 py-2 small" style="border-radius: 12px;">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="row g-4">
    <div class="col-12 col-md-4">
        <div class="card border p-3" style="border-radius: 12px; background-color: #f8fafc;">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-plus-circle-fill me-2 text-primary"></i>Thêm danh mục mới</h6>

            <form action="/admin/danh-muc" method="POST" novalidate>
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label small fw-semibold">Tên danh mục</label>
                    <input type="text" class="form-control form-control-sm" id="name" name="name" placeholder="Ví dụ: Nước ngọt, Bánh kẹo..." required>
                </div>

                <div class="mb-3">
                    <label for="parent_id" class="form-label small fw-semibold">Thuộc danh mục cha</label>
                    <select class="form-select form-select-sm" id="parent_id" name="parent_id">
                        <option value="">-- Là danh mục gốc --</option>
                        @foreach($parentCategories as $parent)
                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold py-2" style="border-radius: 8px;">
                    <i class="bi bi-download me-1"></i> Lưu danh mục vào DB
                </button>
            </form>
        </div>
    </div>

    <div class="col-12 col-md-8">
        <div class="table-responsive border rounded-3 bg-white">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                    <tr>
                        <th class="ps-3" style="width: 80px;">STT</th>
                        <th>Tên danh mục</th>
                        <th>Cấp bậc</th>
                        <th>Ngày tạo</th>
                        <th class="text-end pe-3" style="width: 150px;">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td class="fw-bold text-secondary ps-3">{{ $loop->iteration }}</td>
                        <td>
                            @if($category->parent_id)
                            <span class="text-muted me-1">—</span> <span class="fw-medium text-dark">{{ $category->name }}</span>
                            @else
                            <span class="fw-bold text-primary">{{ $category->name }}</span>
                            @endif
                        </td>
                        <td>
                            @if($category->parent)
                            <span class="badge bg-light text-secondary border px-2 py-1">{{ $category->parent->name }}</span>
                            @else
                            <span class="badge bg-success-subtle text-success px-2 py-1">Danh mục gốc</span>
                            @endif
                        </td>
                        <td class="text-muted">{{ $category->created_at->format('d/m/Y H:i') }}</td>
                        <td class="text-end pe-3">
                            <button type="button" class="btn btn-sm btn-outline-warning border-0"
                                style="border-radius: 8px; width: 32px; height: 32px;"
                                data-bs-toggle="modal" data-bs-target="#editModal{{ $category->id }}">
                                <i class="bi bi-pencil-square"></i>
                            </button>

                            <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-delete-category"
                                style="border-radius: 8px; width: 32px; height: 32px;"
                                data-id="{{ $category->id }}">
                                <i class="bi bi-trash3"></i>
                            </button>

                            <form id="delete-form-{{ $category->id }}" action="/admin/danh-muc/{{ $category->id }}" method="POST" class="d-none">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>

                    <div class="modal fade" id="editModal{{ $category->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-sm">
                            <div class="modal-content" style="border-radius: 16px;">
                                <div class="modal-header">
                                    <h6 class="modal-title fw-bold text-dark"><i class="bi bi-pencil-square me-2 text-warning"></i>Sửa danh mục</h6>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <form action="/admin/danh-muc/{{ $category->id }}" method="POST" novalidate>
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-body text-start">
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Tên danh mục</label>
                                            <input type="text" class="form-control form-control-sm" name="name" value="{{ $category->name }}" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">Thuộc danh mục cha</label>
                                            <select class="form-select form-select-sm" name="parent_id">
                                                <option value="">-- Là danh mục gốc --</option>
                                                @foreach($parentCategories as $parent)
                                                @if($parent->id != $category->id)
                                                <option value="{{ $parent->id }}" {{ $category->parent_id == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                                                @endif
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer p-2 border-top">
                                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Hủy</button>
                                        <button type="submit" class="btn btn-warning btn-sm text-dark fw-bold">Cập nhật</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">Chưa có danh mục nào.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.btn-delete-category').forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();

                var id = this.getAttribute('data-id');
                Swal.fire({
                    title: 'Xác nhận xóa?',
                    text: "Danh mục này sẽ bị ẩn đi và có thể khôi phục lại sau!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Đồng ý xóa!',
                    cancelButtonText: 'Hủy bỏ',
                    background: '#ffffff',
                    customClass: {
                        popup: 'rounded-4 shadow-lg border'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-form-' + id).submit();
                    }
                });
            });
        });
    });
</script>