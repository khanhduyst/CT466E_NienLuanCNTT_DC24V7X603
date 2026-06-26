@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<div class="container-fluid px-4 py-4">
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-truck me-2"></i> Danh Sách Nhà Cung Cấp</h5>
            <button type="button" class="btn btn-light btn-sm fw-bold text-primary" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                <i class="fas fa-plus me-1"></i> Thêm Nhà Cung Cấp
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted uppercase font-weight-bold">
                        <tr>
                            <th class="ps-4 text-center" style="width: 70px;">STT</th>
                            <th>Tên Nhà Cung Cấp / Mã</th>
                            <th>Số Điện Thoại</th>
                            <th>Email</th>
                            <th>Địa Chỉ</th>
                            <th>Công Nợ (đ)</th>
                            <th class="text-end pe-4">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($suppliers as $index => $ncc)
                        <tr>
                            <td class="ps-4 text-center text-secondary fw-bold">{{ $index + 1 }}</td>

                            <td>
                                <div class="fw-bold text-dark">{{ $ncc->name }}</div>
                                <small class="text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                    <i class="fas fa-id-card me-1"></i>{{ $ncc->barcode }}
                                </small>
                            </td>

                            <td>{{ $ncc->phone_number ?? '---' }}</td>
                            <td>{{ $ncc->email ?? '---' }}</td>
                            <td><small class="text-muted">{{ Str::limit($ncc->address, 40) }}</small></td>
                            <td><span class="text-danger fw-bold">{{ number_format($ncc->total_debt) }}</span></td>

                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-warning me-1 btn-edit"
                                    data-id="{{ $ncc->id }}"
                                    data-name="{{ $ncc->name }}"
                                    data-phone="{{ $ncc->phone_number }}"
                                    data-email="{{ $ncc->email }}"
                                    data-address="{{ $ncc->address }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger btn-delete" data-id="{{ $ncc->id }}" data-name="{{ $ncc->name }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Chưa có nhà cung cấp nào trong hệ thống.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus-circle me-2"></i>Thêm Nhà Cung Cấp Mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.supplier.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên nhà cung cấp</label>
                        <input type="text" name="name" class="form-control" required placeholder="Ví dụ: Công ty Unilever">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Số điện thoại</label>
                        <input type="text" name="phone" class="form-control" placeholder="Ví dụ: 02838236651">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="Ví dụ: ncc@gmail.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Địa chỉ</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="Địa chỉ trụ sở..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary">Lưu lại</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editSupplierModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i>Chỉnh Sửa Nhà Cung Cấp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditSupplier" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tên nhà cung cấp</label>
                        <input type="text" name="name" id="edit_supplier_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Số điện thoại</label>
                        <input type="text" name="phone_number" id="edit_supplier_phone" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" id="edit_supplier_email" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Địa chỉ</label>
                        <textarea name="address" id="edit_supplier_address" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-warning fw-bold">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>

<form id="globalDeleteForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // 1. Xử lý nút sửa để tự động điền dữ liệu vào Modal (Giữ nguyên)
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                document.getElementById('edit_supplier_name').value = this.dataset.name;
                document.getElementById('edit_supplier_phone').value = this.dataset.phone !== 'null' ? this.dataset.phone : '';
                document.getElementById('edit_supplier_email').value = this.dataset.email !== 'null' ? this.dataset.email : '';
                document.getElementById('edit_supplier_address').value = this.dataset.address !== 'null' ? this.dataset.address : '';

                document.getElementById('formEditSupplier').action = `/admin/nha-cung-cap/sua/${id}`;
                new bootstrap.Modal(document.getElementById('editSupplierModal')).show();
            });
        });

        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const name = this.dataset.name;

                Swal.fire({
                    title: 'Bạnh có chắc chắn muốn xóa?',
                    text: `Nhà cung cấp "${name}" sẽ bị xóa khỏi hệ thống vĩnh viễn!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Tiếp tục xóa!',
                    cancelButtonText: 'Hủy bỏ'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('globalDeleteForm');
                        form.action = `/admin/nha-cung-cap/xoa/${id}`;
                        form.submit();
                    }
                });
            });
        });
    });
</script>
@endsection