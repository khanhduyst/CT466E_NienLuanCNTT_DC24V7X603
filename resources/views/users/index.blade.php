@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-people me-2"></i>Quản lý nhân viên</h4>
        <button class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#userModal" onclick="resetForm()">
            <i class="bi bi-plus-lg me-1"></i> Thêm nhân viên
        </button>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body bg-light rounded-top">
            <form action="{{ route('admin.users.index') }}" method="GET" class="row g-2">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Tìm theo tên, email nhân viên..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold"><i class="bi bi-search"></i> Tìm kiếm</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary w-100 fw-semibold">Xóa lọc</a>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">STT</th>
                            <th>Tên nhân viên</th>
                            <th>Email (Tài khoản)</th>
                            <th>Vai trò</th>
                            <th>Trạng thái</th>
                            <th class="text-end pe-3">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td class="ps-3 fw-semibold text-secondary">{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                            <td class="fw-bold text-dark">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->role_name == 'super_admin')
                                    <span class="badge bg-danger">Quản trị viên</span>
                                @elseif($user->role_name == 'manager')
                                    <span class="badge bg-warning text-dark">Quản lý</span>
                                @elseif($user->role_name == 'staff')
                                    <span class="badge bg-info text-dark">Nhân viên</span>
                                @else
                                    <span class="badge bg-secondary">{{ $user->role_name }}</span>
                                @endif
                            </td>
                            <td>
                                @if($user->is_active == 1)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Hoạt động</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Đã khóa</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-outline-primary me-1 btn-edit-user" 
                                        data-id="{{ $user->id }}"
                                        data-name="{{ $user->name }}"
                                        data-email="{{ $user->email }}"
                                        data-role="{{ $user->role_id }}"
                                        data-active="{{ $user->is_active }}">
                                    <i class="bi bi-pencil"></i> Sửa
                                </button>
                                <button class="btn btn-sm btn-outline-info me-1 btn-reset-pass" data-id="{{ $user->id }}" data-email="{{ $user->email }}">
                                    <i class="bi bi-key"></i> Cấp lại MK
                                </button>
                                <button class="btn btn-sm btn-outline-warning btn-toggle-status" data-id="{{ $user->id }}">
                                    <i class="bi bi-power"></i> Khóa/Mở
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Không tìm thấy nhân viên nào</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($users->hasPages())
        <div class="card-footer bg-white py-3 d-flex justify-content-center">
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    @if ($users->onFirstPage())
                        <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $users->previousPageUrl() }}" rel="prev">&laquo;</a></li>
                    @endif

                    @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                        @if ($page == $users->currentPage())
                            <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach

                    @if ($users->hasMorePages())
                        <li class="page-item"><a class="page-link" href="{{ $users->nextPageUrl() }}" rel="next">&raquo;</a></li>
                    @else
                        <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                    @endif
                </ul>
            </nav>
        </div>
        @endif
    </div>
</div>

<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-semibold" id="modalTitle">Thêm nhân viên mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                <form id="userForm">
                    <input type="hidden" id="userId">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" id="userName" class="form-control" required placeholder="Nhập tên nhân viên...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email (Tên đăng nhập) <span class="text-danger">*</span></label>
                        <input type="email" id="userEmail" class="form-control" required placeholder="example@domain.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Vai trò <span class="text-danger">*</span></label>
                        <select id="userRoleId" class="form-select" required>
                            <option value="">-- Chọn vai trò --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">
                                    @if($role->name == 'super_admin')
                                        Quản trị viên
                                    @elseif($role->name == 'manager')
                                        Quản lý
                                    @elseif($role->name == 'staff')
                                        Nhân viên (Thu ngân)
                                    @else
                                        {{ $role->name }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="userIsActive" checked>
                        <label class="form-check-label small fw-semibold" for="userIsActive">Cho phép hoạt động</label>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-white">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="saveUser()">Lưu thông tin</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function resetForm() {
    $('#userId').val('');
    $('#userName').val('');
    $('#userEmail').val('');
    $('#userRoleId').val('');
    $('#userIsActive').prop('checked', true);
    $('#modalTitle').text('Thêm nhân viên mới');
}

$(document).on('click', '.btn-edit-user', function() {
    let id = $(this).data('id');
    let name = $(this).data('name');
    let email = $(this).data('email');
    let role = $(this).data('role');
    let active = $(this).data('active');

    $('#userId').val(id);
    $('#userName').val(name);
    $('#userEmail').val(email);
    $('#userRoleId').val(role);
    $('#userIsActive').prop('checked', active == 1);
    
    $('#modalTitle').text('Sửa thông tin nhân viên');
    $('#userModal').modal('show');
});

function saveUser() {
    let id = $('#userId').val();
    let url = id ? `/admin/nhan-vien/${id}/update` : "{{ route('admin.users.store') }}";
    
    let data = {
        _token: "{{ csrf_token() }}",
        name: $('#userName').val(),
        email: $('#userEmail').val(),
        role_id: $('#userRoleId').val(),
    };
    if($('#userIsActive').is(':checked')) data.is_active = 1;

    // Loading notification
    Swal.fire({
        title: 'Đang xử lý...',
        text: id ? 'Đang cập nhật thông tin...' : 'Đang tạo tài khoản và gửi email mật khẩu...',
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    $.ajax({
        url: url,
        type: "POST",
        data: data,
        success: function(res) {
            if(res.success) {
                $('#userModal').modal('hide');
                Swal.fire('Thành công', res.message, 'success').then(() => location.reload());
            }
        },
        error: function(xhr) {
            let res = xhr.responseJSON;
            Swal.fire('Lỗi', res && res.message ? res.message : 'Kiểm tra lại dữ liệu nhập!', 'error');
        }
    });
}

// Xử lý nút "Cấp lại MK"
$(document).on('click', '.btn-reset-pass', function() {
    let id = $(this).data('id');
    let email = $(this).data('email');

    Swal.fire({
        title: 'Xác nhận cấp lại mật khẩu?',
        text: `Hệ thống sẽ tạo mật khẩu ngẫu nhiên mới và gửi về email: ${email}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Đồng ý cấp',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Đang xử lý...',
                text: 'Đang cấp mật khẩu mới và gửi email...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.post(`/admin/nhan-vien/${id}/reset-password`, { _token: "{{ csrf_token() }}" }, function(res) {
                if(res.success) {
                    Swal.fire('Thành công', res.message, 'success');
                }
            }).fail(function() {
                Swal.fire('Lỗi', 'Không thể cấp lại mật khẩu vào lúc này!', 'error');
            });
        }
    });
});

$(document).on('click', '.btn-toggle-status', function() {
    let id = $(this).data('id');
    
    Swal.fire({
        title: 'Xác nhận đổi trạng thái?',
        text: "Tài khoản bị khóa sẽ không thể đăng nhập vào hệ thống!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Đồng ý',
        cancelButtonText: 'Hủy'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post(`/admin/nhan-vien/${id}/toggle`, { _token: "{{ csrf_token() }}" }, function(res) {
                if(res.success) {
                    Swal.fire('Thành công', res.message, 'success').then(() => location.reload());
                }
            });
        }
    });
});
</script>
@endsection