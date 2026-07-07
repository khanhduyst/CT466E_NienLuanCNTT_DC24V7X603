@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-danger text-white">
                <div class="card-body d-flex align-items-center justify-content-between py-3">
                    <div>
                        <small class="text-white-50 d-block text-uppercase fw-semibold" style="font-size: 11px;">Tổng tiền nợ toàn hệ thống</small>
                        <h4 class="mb-0 fw-bold mt-1">{{ number_format($totalDebtSystem ?? 0, 0, ',', '.') }}đ</h4>
                    </div>
                    <div class="fs-1 text-white-50"><i class="bi bi-wallet2"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body bg-light rounded-top">
            <form action="{{ route('admin.debts.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-6">
                    <label class="form-label small fw-semibold text-muted mb-1">Tìm kiếm khách nợ</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Nhập tên, số điện thoại, mã khách hàng..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold"><i class="bi bi-filter"></i> Tìm kiếm</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('admin.debts.index') }}" class="btn btn-sm btn-outline-secondary w-100 fw-semibold">Xóa lọc</a>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Mã khách hàng</th>
                            <th>Tên khách hàng</th>
                            <th>Số điện thoại</th>
                            <th class="text-end">Số tiền đang nợ</th>
                            <th class="text-end pe-3">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($debtors as $debtor)
                        <tr>
                            <td class="ps-3 fw-semibold text-secondary">{{ $debtor->barcode ?? '---' }}</td>
                            <td class="fw-bold text-dark">{{ $debtor->name }}</td>
                            <td>{{ $debtor->phone_number ?? 'Không có' }}</td>
                            <td class="text-end fw-bold text-danger fs-6">{{ number_format($debtor->total_debt, 0, ',', '.') }}đ</td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-sm btn-outline-info btn-view-logs" data-id="{{ $debtor->id }}" data-name="{{ $debtor->name }}">
                                    <i class="bi bi-clock-history"></i> Lịch sử
                                </button>
                                <button type="button" class="btn btn-sm btn-success btn-pay-trigger" data-id="{{ $debtor->id }}" data-name="{{ $debtor->name }}" data-debt="{{ $debtor->total_debt }}">
                                    <i class="bi bi-currency-dollar"></i> Thu nợ
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-check-circle fs-2 text-success d-block mb-2"></i> Hiện tại không có khách hàng nào nợ tiền tiệm
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($debtors->hasPages())
        <div class="card-footer bg-white pt-3">
            {{ $debtors->links() }}
        </div>
        @endif
    </div>
</div>

<div class="modal fade" id="payDebtModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-semibold"><i class="bi bi-cash-stack me-2"></i> Phiếu thu tiền nợ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <form id="form-pay-debt">
                    <input type="hidden" id="pay-customer-id">
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Khách hàng:</label>
                        <div class="fw-bold text-dark fs-5" id="pay-customer-name">-</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small text-muted mb-1">Tổng nợ hiện tại:</label>
                        <div class="fw-bold text-danger fs-5" id="pay-customer-debt">0đ</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Số tiền khách trả (đ) <span class="text-danger">*</span></label>
                        <input type="number" id="pay-amount" class="form-control form-control-lg text-end fw-bold text-success" required min="1" placeholder="Nhập số tiền...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Ghi chú phiếu thu</label>
                        <input type="text" id="pay-note" class="form-control" placeholder="Ví dụ: Khách trả tiền mặt...">
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-white">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" id="btn-submit-pay" class="btn btn-sm btn-success">Xác nhận thu nợ</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="debtLogsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title fw-semibold"><i class="bi bi-journal-text me-2"></i> Nhật ký công nợ: <span id="log-customer-name"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 overflow-hidden">
                <div style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover table-align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Thời gian</th>
                                <th>Loại giao dịch</th>
                                <th class="text-end">Số tiền</th>
                                <th>Hóa đơn liên quan</th>
                                <th class="pe-3">Nội dung / Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody id="log-table-body"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-white">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-semibold"><i class="bi bi-info-circle me-2"></i> Chi tiết hóa đơn: <span id="md-invoice-num"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="card border-0 p-3 shadow-sm bg-white">
                            <small class="text-muted d-block mb-1">Thông tin khách hàng</small>
                            <span class="fw-bold text-dark fs-6" id="md-cust-name">-</span>
                            <small class="text-muted mt-2 d-block mb-1">Thu ngân thực hiện</small>
                            <span class="fw-semibold text-secondary" id="md-user-name">-</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 p-3 shadow-sm bg-white">
                            <small class="text-muted d-block mb-1">Thời gian tạo đơn</small>
                            <span class="fw-semibold text-dark" id="md-date">-</span>
                            <small class="text-muted mt-2 d-block mb-1">Hình thức thanh toán</small>
                            <span id="md-method">-</span>
                        </div>
                    </div>
                </div>
                <div class="card border-0 shadow-sm bg-white p-0 overflow-hidden">
                    <table class="table table-align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Sản phẩm mua</th>
                                <th class="text-center">ĐVT</th>
                                <th class="text-center">SL</th>
                                <th class="text-end">Đơn giá</th>
                                <th class="text-end pe-3">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody id="md-items-body"></tbody>
                    </table>
                </div>
                <div class="row justify-content-end mt-3">
                    <div class="col-md-5">
                        <div class="card border-0 p-3 shadow-sm bg-white small">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Tổng tiền hàng:</span>
                                <span class="fw-semibold text-dark" id="md-total-amount">0đ</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Chiết khấu giảm giá:</span>
                                <span class="fw-semibold text-danger" id="md-discount">0đ</span>
                            </div>
                            <hr class="my-2">
                            <div class="d-flex justify-content-between mb-2 fs-6">
                                <span class="fw-bold text-dark">Khách cần trả:</span>
                                <span class="fw-bold text-primary" id="md-final-amount">0đ</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Khách đưa:</span>
                                <span class="fw-semibold text-dark" id="md-paid">0đ</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Tiền thừa trả khách:</span>
                                <span class="fw-bold text-success" id="md-change">0đ</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-white">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Đóng lại</button>
            </div>
        </div>
    </div>
</div>

<div id="print-receipt-section" style="display: none;">
    <div style="width: 80mm; font-family: 'Courier New', Courier, monospace; font-size: 12px; color: #000; padding: 5mm; line-height: 1.4;">
        <div style="text-align: center; margin-bottom: 10px;">
            <h2 style="margin: 0; font-size: 15px; font-weight: bold;">SMART GROCER</h2>
            <p style="margin: 3px 0; font-size: 12px; font-weight: bold; letter-spacing: 1px;">PHIẾU THU TIỀN NỢ</p>
            <div style="border-top: 1px dashed #000; margin-top: 10px;"></div>
        </div>
        <div style="font-size: 11px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="height: 20px;">
                    <td>Số phiếu:</td>
                    <td style="text-align: right; font-weight: bold;" id="pr-number">---------</td>
                </tr>
                <tr style="height: 20px;">
                    <td>Thời gian:</td>
                    <td style="text-align: right;" id="pr-date">--/--/---- --:--</td>
                </tr>
                <tr style="height: 20px;">
                    <td>Khách hàng:</td>
                    <td style="text-align: right; font-weight: bold;" id="pr-customer-name">-</td>
                </tr>
                <tr style="height: 20px;">
                    <td>Thu ngân:</td>
                    <td style="text-align: right;" id="pr-cashier">-</td>
                </tr>
                <tr>
                    <td colspan="2" style="border-top: 1px dotted #000; padding-top: 5px; margin-top: 5px;"></td>
                </tr>
                <tr style="height: 22px;">
                    <td>Nợ cũ:</td>
                    <td style="text-align: right;" id="pr-old-debt">0đ</td>
                </tr>
                <tr style="height: 22px; font-weight: bold; font-size: 13px;">
                    <td>Số tiền trả:</td>
                    <td style="text-align: right; text-decoration: underline;" id="pr-amount-paid">0đ</td>
                </tr>
                <tr style="height: 22px; font-weight: bold;">
                    <td>Nợ còn lại:</td>
                    <td style="text-align: right; color:#d33;" id="pr-remain-debt">0đ</td>
                </tr>
            </table>
            <div style="border-top: 1px dashed #000; margin-top: 10px; padding-top: 5px;"></div>
            <p style="margin: 3px 0; font-style: italic;">Ghi chú: <span id="pr-note"></span></p>
        </div>
        <div style="text-align: center; margin-top: 15px; font-size: 11px;">
            <p style="margin: 0; font-weight: bold;">XIN CẢM ƠN QUÝ KHÁCH!</p>
            <p style="margin: 3px 0 0 0; font-size: 8px; color: #555;">Hệ thống Smart Grocer 2026</p>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).on('click', '.btn-pay-trigger', function() {
        let id = $(this).data('id');
        let name = $(this).data('name');
        let debt = parseFloat($(this).data('debt'));
        $('#pay-customer-id').val(id);
        $('#pay-customer-name').text(name);
        $('#pay-customer-debt').text(debt.toLocaleString('vi-VN') + 'đ');
        $('#pay-amount').val('').attr('max', debt);
        $('#pay-note').val('');
        $('#payDebtModal').modal('show');
    });

    function executePrintReceipt(data, noteText) {
        $('#pr-number').text(data.receipt_number);
        let now = new Date();
        $('#pr-date').text(now.toLocaleDateString('vi-VN') + ' ' + now.toLocaleTimeString('vi-VN', {
            hour: '2-digit',
            minute: '2-digit'
        }));
        $('#pr-customer-name').text(data.customer_name);
        $('#pr-cashier').text(data.cashier);
        $('#pr-old-debt').text(data.old_debt.toLocaleString('vi-VN') + 'đ');
        $('#pr-amount-paid').text(data.amount_paid.toLocaleString('vi-VN') + 'đ');
        $('#pr-remain-debt').text(data.remain_debt.toLocaleString('vi-VN') + 'đ');
        $('#pr-note').text(noteText ? noteText : 'Khách trả bớt nợ cũ');

        let iframe = document.getElementById('print-receipt-iframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'print-receipt-iframe';
            iframe.style.position = 'absolute';
            iframe.style.top = '-9999px';
            iframe.style.left = '-9999px';
            document.body.appendChild(iframe);
        }
        let doc = iframe.contentDocument || iframe.contentWindow.document;
        doc.open();
        doc.write('<html><head><title>Print Receipt</title></head><body style="margin:0;">' + $('#print-receipt-section').html() + '</body></html>');
        doc.close();

        setTimeout(function() {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        }, 500);
    }

    $('#btn-submit-pay').on('click', function() {
        let id = $('#pay-customer-id').val();
        let amount = parseFloat($('#pay-amount').val()) || 0;
        let note = $('#pay-note').val().trim();

        if (amount <= 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Chú ý',
                text: 'Vui lòng nhập số tiền hợp lệ!'
            });
            return;
        }

        $.ajax({
            url: "{{ route('admin.debts.pay') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                customer_id: id,
                amount: amount,
                note: note
            },
            success: function(response) {
                if (response.success) {
                    $('#payDebtModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Thu nợ thành công',
                        text: response.message,
                        showCancelButton: true,
                        confirmButtonColor: '#198754',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="bi bi-printer me-1"></i> In phiếu thu nợ',
                        cancelButtonText: 'Đóng lại'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            executePrintReceipt(response, note);
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            window.location.reload();
                        }
                    });
                }
            },
            error: function(xhr) {
                let res = xhr.responseJSON;
                Swal.fire({
                    icon: 'error',
                    title: 'Thất bại',
                    text: res && res.message ? res.message : 'Lỗi hệ thống.'
                });
            }
        });
    });

    $(document).on('click', '.btn-view-logs', function() {
        let id = $(this).data('id');
        let name = $(this).data('name');
        $('#log-customer-name').text(name);

        $.get('/admin/cong-no/' + id + '/lich-su', function(res) {
            if (res.success) {
                let html = '';
                if (res.logs.length === 0) {
                    html = '<tr><td colspan="5" class="text-center py-4 text-muted">Chưa ghi nhận lịch sử biến động nợ</td></tr>';
                } else {
                    res.logs.forEach(log => {
                        let date = new Date(log.created_at).toLocaleString('vi-VN');
                        let badge = '';

                        let isDebt = (log.transaction_type === 'borrow' || log.transaction_type === 'debt');

                        if (isDebt) {
                            badge = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle">Nợ mới (Mua hàng)</span>';
                        } else {
                            badge = '<span class="badge bg-success-subtle text-success border border-success-subtle">Trả nợ (Thu tiền)</span>';
                        }

                        let invoiceLink = '---';
                        if (log.invoice_number) {
                            invoiceLink = `<a href="javascript:void(0)" class="fw-bold text-primary view-invoice-from-debt" data-order-id="${log.order_id}">${log.invoice_number}</a>`;
                        }

                        html += `
        <tr>
            <td class="ps-3 small text-muted">${date}</td>
            <td>${badge}</td>
            <td class="text-end fw-bold ${isDebt ? 'text-danger' : 'text-success'}">${parseFloat(log.amount).toLocaleString('vi-VN')}đ</td>
            <td>${invoiceLink}</td>
            <td class="small text-secondary pe-3">${log.note ? log.note : ''}</td>
        </tr>`;
                    });
                }
                $('#log-table-body').html(html);
                $('#debtLogsModal').modal('show');
            }
        });
    });

    $(document).on('click', '.view-invoice-from-debt', function() {
        let orderId = $(this).data('order-id');
        $.get('/admin/orders/' + orderId, function(res) {
            if (res.success) {
                $('#md-invoice-num').text(res.order.invoice_number);
                $('#md-cust-name').text(res.order.customer_name ? res.order.customer_name : 'Khách vãng lai');
                $('#md-user-name').text(res.order.user_name);
                let dateObj = new Date(res.order.created_at);
                $('#md-date').text(dateObj.toLocaleString('vi-VN'));
                let methodText = '<span class="badge bg-success">Tiền mặt</span>';
                if (res.order.payment_method === 'qr_code') methodText = '<span class="badge bg-info">Chuyển khoản</span>';
                if (res.order.payment_method === 'debt') methodText = '<span class="badge bg-danger">Ghi nợ</span>';
                $('#md-method').html(methodText);
                $('#md-total-amount').text(parseFloat(res.order.total_amount).toLocaleString('vi-VN') + 'đ');
                $('#md-discount').text(parseFloat(res.order.discount_amount).toLocaleString('vi-VN') + 'đ');
                $('#md-final-amount').text(parseFloat(res.order.final_amount).toLocaleString('vi-VN') + 'đ');
                $('#md-paid').text(parseFloat(res.order.paid_amount).toLocaleString('vi-VN') + 'đ');
                $('#md-change').text(parseFloat(res.order.change_amount).toLocaleString('vi-VN') + 'đ');
                let html = '';
                res.items.forEach(item => {
                    html += `<tr><td class="ps-3 fw-semibold text-dark">${item.product_name}</td><td class="text-center"><span class="badge bg-secondary-subtle text-secondary border">${item.unit_name}</span></td><td class="text-center fw-bold">${item.quantity}</td><td class="text-end">${parseFloat(item.sale_price).toLocaleString('vi-VN')}đ</td><td class="text-end pe-3 fw-bold text-primary">${parseFloat(item.subtotal).toLocaleString('vi-VN')}đ</td></tr>`;
                });
                $('#md-items-body').html(html);
                $('#debtLogsModal').modal('hide');
                setTimeout(function() {
                    $('#orderDetailModal').modal('show');
                }, 400);
            }
        });
    });

    $('#orderDetailModal').on('hidden.bs.modal', function() {
        setTimeout(function() {
            $('#debtLogsModal').modal('show');
        }, 300);
    });
</script>
@endsection