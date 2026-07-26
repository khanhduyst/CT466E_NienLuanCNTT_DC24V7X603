@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold text-dark mb-0"><i class="bi bi-person-lines-fill me-2"></i>Quản lý khách hàng</h4>
        <button class="btn btn-primary btn-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#customerModal" onclick="resetForm()">
            <i class="bi bi-plus-lg me-1"></i> Thêm khách hàng
        </button>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body bg-light rounded-top">
            <form action="{{ route('admin.customers.index') }}" method="GET" class="row g-2">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Tìm theo tên, số điện thoại, mã vạch..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold"><i class="bi bi-search"></i> Tìm kiếm</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-sm btn-outline-secondary w-100 fw-semibold">Xóa lọc</a>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">STT</th>
                            <th>Mã vạch / Barcode</th>
                            <th>Tên khách hàng</th>
                            <th>Số điện thoại</th>
                            <th>Điểm tích lũy</th>
                            <th>Nợ hiện tại</th>
                            <th class="text-end pe-3">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                        <tr>
                            <td class="ps-3 fw-semibold text-secondary">{{ $loop->iteration + ($customers->currentPage() - 1) * $customers->perPage() }}</td>
                            <td><span class="badge bg-light text-dark border"><i class="bi bi-barcode me-1"></i>{{ $customer->barcode ?? 'Chưa có' }}</span></td>
                            <td class="fw-bold text-dark">{{ $customer->name }}</td>
                            <td><span class="badge bg-info-subtle text-info border border-info-subtle">{{ $customer->phone_number }}</span></td>
                            <td>
                                <span class="badge bg-warning text-dark border btn-view-points"
                                    data-id="{{ $customer->id }}"
                                    data-name="{{ $customer->name }}"
                                    style="cursor: pointer;"
                                    title="Xem lịch sử điểm">
                                    <i class="bi bi-star-fill me-1"></i>{{ number_format($customer->current_points ?? 0) }} điểm
                                </span>
                            </td>
                            <td>
                                @if(($customer->total_debt ?? 0) > 0)
                                <span class="fw-bold text-danger">{{ number_format($customer->total_debt) }} đ</span>
                                @else
                                <span class="text-muted">0 đ</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <button class="btn btn-sm btn-outline-warning me-1 btn-view-points" data-id="{{ $customer->id }}" data-name="{{ $customer->name }}">
                                    <i class="bi bi-star"></i> Điểm
                                </button>
                                <button class="btn btn-sm btn-outline-info me-1 btn-view-orders" data-id="{{ $customer->id }}" data-name="{{ $customer->name }}">
                                    <i class="bi bi-receipt"></i> Đơn mua
                                </button>
                                <button class="btn btn-sm btn-outline-secondary me-1 btn-view-debts" data-id="{{ $customer->id }}" data-name="{{ $customer->name }}">
                                    <i class="bi bi-wallet2"></i> Công nợ
                                </button>
                                <button class="btn btn-sm btn-outline-primary me-1 btn-edit-customer"
                                    data-id="{{ $customer->id }}"
                                    data-name="{{ $customer->name }}"
                                    data-phone="{{ $customer->phone_number }}"
                                    data-barcode="{{ $customer->barcode }}">
                                    <i class="bi bi-pencil"></i> Sửa
                                </button>
                                <button class="btn btn-sm btn-outline-danger btn-delete-customer" data-id="{{ $customer->id }}">
                                    <i class="bi bi-trash"></i> Xóa
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Không tìm thấy khách hàng nào</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($customers->hasPages())
        <div class="card-footer bg-white py-3 d-flex justify-content-center">
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    @if ($customers->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                    @else
                    <li class="page-item"><a class="page-link" href="{{ $customers->previousPageUrl() }}" rel="prev">&laquo;</a></li>
                    @endif

                    @foreach ($customers->getUrlRange(1, $customers->lastPage()) as $page => $url)
                    @if ($page == $customers->currentPage())
                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                    @else
                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                    @endforeach

                    @if ($customers->hasMorePages())
                    <li class="page-item"><a class="page-link" href="{{ $customers->nextPageUrl() }}" rel="next">&raquo;</a></li>
                    @else
                    <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                    @endif
                </ul>
            </nav>
        </div>
        @endif
    </div>
</div>

<div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-semibold" id="modalTitle">Thêm khách hàng mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body bg-light">
                <form id="customerForm">
                    <input type="hidden" id="customerId">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Họ và tên khách hàng <span class="text-danger">*</span></label>
                        <input type="text" id="customerName" class="form-control" required placeholder="Nhập tên khách hàng...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="text" id="customerPhone" class="form-control" required placeholder="Nhập số điện thoại...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Mã vạch / Barcode (Tùy chọn)</label>
                        <input type="text" id="customerBarcode" class="form-control" placeholder="Để trống hệ thống sẽ tự sinh...">
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-white">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="saveCustomer()">Lưu thông tin</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="detailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-semibold" id="detailModalTitle">Lịch sử</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="detailTable">
                        <thead class="table-light">
                            <tr id="detailTableHeader">
                            </tr>
                        </thead>
                        <tbody id="detailTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between align-items-center">
                <div id="modalPagination" class="mb-0"></div>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    let currentCustomerId = null;

    function resetForm() {
        $('#customerId').val('');
        $('#customerName').val('');
        $('#customerPhone').val('');
        $('#customerBarcode').val('');
        $('#modalTitle').text('Thêm khách hàng mới');
    }

    $(document).on('click', '.btn-edit-customer', function() {
        let id = $(this).data('id');
        let name = $(this).data('name');
        let phone = $(this).data('phone');
        let barcode = $(this).data('barcode');

        $('#customerId').val(id);
        $('#customerName').val(name);
        $('#customerPhone').val(phone);
        $('#customerBarcode').val(barcode);

        $('#modalTitle').text('Sửa thông tin khách hàng');
        $('#customerModal').modal('show');
    });

    function saveCustomer() {
        let id = $('#customerId').val();
        let url = id ? `/admin/khach-hang/${id}/update` : "{{ route('admin.customers.store') }}";

        let data = {
            _token: "{{ csrf_token() }}",
            name: $('#customerName').val(),
            phone_number: $('#customerPhone').val(),
            barcode: $('#customerBarcode').val(),
        };

        $.ajax({
            url: url,
            type: "POST",
            data: data,
            success: function(res) {
                if (res.success) {
                    $('#customerModal').modal('hide');
                    Swal.fire('Thành công', res.message, 'success').then(() => location.reload());
                }
            },
            error: function(xhr) {
                let res = xhr.responseJSON;
                Swal.fire('Lỗi', res && res.message ? res.message : 'Kiểm tra lại dữ liệu nhập!', 'error');
            }
        });
    }

    $(document).on('click', '.btn-view-points', function() {
        currentCustomerId = $(this).data('id');
        let name = $(this).data('name');

        $('#detailModalTitle').text(`Lịch sử tích & đổi điểm - ${name}`);
        $('#detailTableHeader').html(`
        <th class="ps-3">Thời gian</th>
        <th>Loại biến động</th>
        <th>Mã đơn hàng</th>
        <th>Số điểm</th>
    `);

        $('#detailModal').modal('show');
        loadPointsPage(1);
    });

    function loadPointsPage(page) {
        $('#detailTableBody').html('<tr><td colspan="4" class="text-center py-3">Đang tải dữ liệu...</td></tr>');

        $.get(`/admin/khach-hang/${currentCustomerId}/points?page=${page}`, function(res) {
            let html = '';
            if (res.data && res.data.length > 0) {
                res.data.forEach(item => {
                    let isEarn = item.change_type === 'earn';
                    let typeBadge = isEarn ?
                        '<span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-plus-circle me-1"></i>Tích điểm</span>' :
                        '<span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-dash-circle me-1"></i>Đổi quà / Trừ điểm</span>';
                    let pointsClass = isEarn ? 'text-success fw-bold' : 'text-danger fw-bold';
                    let pointsPrefix = isEarn ? '+' : '-';

                    html += `
                    <tr>
                        <td class="ps-3">${item.created_at || '---'}</td>
                        <td>${typeBadge}</td>
                        <td class="fw-bold text-primary">${item.order_id ? '#' + item.order_id : '---'}</td>
                        <td class="${pointsClass}">${pointsPrefix}${Number(item.points).toLocaleString()} pt</td>
                    </tr>
                `;
                });
                renderModalPagination(res, 'loadPointsPage');
            } else {
                html = '<tr><td colspan="4" class="text-center py-4 text-muted">Khách hàng chưa có lịch sử tích/đổi điểm nào</td></tr>';
                $('#modalPagination').html('');
            }
            $('#detailTableBody').html(html);
        });
    }

    $(document).on('click', '.btn-view-orders', function() {
        currentCustomerId = $(this).data('id');
        let name = $(this).data('name');

        $('#detailModalTitle').text(`Lịch sử đơn mua - ${name}`);
        $('#detailTableHeader').html(`
        <th class="ps-3">Mã đơn</th>
        <th>Thời gian</th>
        <th>Tổng tiền</th>
        <th>Thanh toán</th>
        <th>Trạng thái</th>
    `);

        $('#detailModal').modal('show');
        loadOrdersPage(1);
    });

    function loadOrdersPage(page) {
        $('#detailTableBody').html('<tr><td colspan="5" class="text-center py-3">Đang tải dữ liệu...</td></tr>');

        $.get(`/admin/khach-hang/${currentCustomerId}/orders?page=${page}`, function(res) {
            let html = '';
            if (res.data && res.data.length > 0) {
                res.data.forEach(item => {
                    html += `
                    <tr>
                        <td class="ps-3 fw-bold text-primary">#${item.code || item.id}</td>
                        <td>${item.created_at || '---'}</td>
                        <td class="fw-bold">${Number(item.total_amount || 0).toLocaleString()} đ</td>
                        <td>${item.payment_method || 'Tiền mặt'}</td>
                        <td><span class="badge bg-success">Hoàn thành</span></td>
                    </tr>
                `;
                });
                renderModalPagination(res, 'loadOrdersPage');
            } else {
                html = '<tr><td colspan="5" class="text-center py-4 text-muted">Khách hàng chưa có đơn mua nào</td></tr>';
                $('#modalPagination').html('');
            }
            $('#detailTableBody').html(html);
        });
    }

    $(document).on('click', '.btn-view-debts', function() {
        currentCustomerId = $(this).data('id');
        let name = $(this).data('name');

        $('#detailModalTitle').text(`Lịch sử công nợ - ${name}`);
        $('#detailTableHeader').html(`
        <th class="ps-3">Thời gian</th>
        <th>Loại giao dịch</th>
        <th>Mã đơn</th>
        <th>Số tiền</th>
        <th>Ghi chú</th>
    `);

        $('#detailModal').modal('show');
        loadDebtsPage(1);
    });

    function loadDebtsPage(page) {
        $('#detailTableBody').html('<tr><td colspan="5" class="text-center py-3">Đang tải dữ liệu...</td></tr>');

        $.get(`/admin/khach-hang/${currentCustomerId}/debts?page=${page}`, function(res) {
            let html = '';
            if (res.data && res.data.length > 0) {
                res.data.forEach(item => {
                    let isBorrow = item.transaction_type === 'borrow';
                    let typeBadge = isBorrow ?
                        '<span class="badge bg-danger-subtle text-danger border border-danger-subtle">Ghi nợ</span>' :
                        '<span class="badge bg-success-subtle text-success border border-success-subtle">Trả nợ</span>';
                    let amountClass = isBorrow ? 'text-danger fw-bold' : 'text-success fw-bold';
                    let amountPrefix = isBorrow ? '+' : '-';

                    html += `
                    <tr>
                        <td class="ps-3">${item.created_at || '---'}</td>
                        <td>${typeBadge}</td>
                        <td class="fw-bold text-primary">${item.order_id ? '#' + item.order_id : '---'}</td>
                        <td class="${amountClass}">${amountPrefix}${Number(item.amount).toLocaleString()} đ</td>
                        <td>${item.note || '---'}</td>
                    </tr>
                `;
                });
                renderModalPagination(res, 'loadDebtsPage');
            } else {
                html = '<tr><td colspan="5" class="text-center py-4 text-muted">Khách hàng chưa có lịch sử công nợ</td></tr>';
                $('#modalPagination').html('');
            }
            $('#detailTableBody').html(html);
        });
    }

    function renderModalPagination(res, callbackFuncName) {
        if (res.last_page <= 1) {
            $('#modalPagination').html('');
            return;
        }

        let prevBtn = res.prev_page_url ?
            `<button class="btn btn-sm btn-outline-primary me-1" onclick="${callbackFuncName}(${res.current_page - 1})">&laquo; Trước</button>` :
            `<button class="btn btn-sm btn-outline-secondary me-1" disabled>&laquo; Trước</button>`;

        let nextBtn = res.next_page_url ?
            `<button class="btn btn-sm btn-outline-primary" onclick="${callbackFuncName}(${res.current_page + 1})">Sau &raquo;</button>` :
            `<button class="btn btn-sm btn-outline-secondary" disabled>Sau &raquo;</button>`;

        let info = `<span class="small text-muted me-2">Trang ${res.current_page} / ${res.last_page}</span>`;

        $('#modalPagination').html(`<div class="d-flex align-items-center">${info} ${prevBtn} ${nextBtn}</div>`);
    }

    $(document).on('click', '.btn-delete-customer', function() {
        let id = $(this).data('id');

        Swal.fire({
            title: 'Xác nhận xóa?',
            text: "Dữ liệu khách hàng sẽ bị xóa khỏi hệ thống!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(`/admin/khach-hang/${id}/delete`, {
                    _token: "{{ csrf_token() }}"
                }, function(res) {
                    if (res.success) {
                        Swal.fire('Thành công', res.message, 'success').then(() => location.reload());
                    }
                });
            }
        });
    });
</script>
@endsection