@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-primary text-white">
                <div class="card-body d-flex align-items-center justify-content-between py-3">
                    <div>
                        <small class="text-white-50 d-block text-uppercase fw-semibold" style="font-size: 11px;">Tổng doanh thu thực tế</small>
                        <h4 class="mb-0 fw-bold mt-1">{{ number_format($stats->total_revenue ?? 0, 0, ',', '.') }}đ</h4>
                    </div>
                    <div class="fs-1 text-white-50"><i class="bi bi-cash-coin"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-danger text-white">
                <div class="card-body d-flex align-items-center justify-content-between py-3">
                    <div>
                        <small class="text-white-50 d-block text-uppercase fw-semibold" style="font-size: 11px;">Tổng tiền ghi nợ mới</small>
                        <h4 class="mb-0 fw-bold mt-1">{{ number_format($stats->total_debt ?? 0, 0, ',', '.') }}đ</h4>
                    </div>
                    <div class="fs-1 text-white-50"><i class="bi bi-credit-card-2-front"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 bg-warning text-white">
                <div class="card-body d-flex align-items-center justify-content-between py-3">
                    <div>
                        <small class="text-white-50 d-block text-uppercase fw-semibold" style="font-size: 11px;">Tổng giảm giá chiết khấu</small>
                        <h4 class="mb-0 fw-bold mt-1">{{ number_format($stats->total_discount ?? 0, 0, ',', '.') }}đ</h4>
                    </div>
                    <div class="fs-1 text-white-50"><i class="bi bi-tags"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body bg-light rounded-top">
            <form action="{{ route('admin.orders.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-muted mb-1">Tìm kiếm thông tin</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Mã hóa đơn, tên, số điện thoại..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Từ ngày</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Đến ngày</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-muted mb-1">Hình thức</label>
                    <select name="method" class="form-select form-select-sm">
                        <option value="">-- Tất cả --</option>
                        <option value="cash" {{ request('method') === 'cash' ? 'selected' : '' }}>Tiền mặt</option>
                        <option value="qr_code" {{ request('method') === 'qr_code' ? 'selected' : '' }}>Chuyển khoản</option>
                        <option value="debt" {{ request('method') === 'debt' ? 'selected' : '' }}>Ghi nợ</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold"><i class="bi bi-filter"></i> Lọc</button>
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline-secondary w-100 fw-semibold">Xóa</a>
                </div>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Mã hóa đơn</th>
                            <th>Thời gian</th>
                            <th>Khách hàng</th>
                            <th>Thu ngân</th>
                            <th class="text-end">Tổng tiền hàng</th>
                            <th class="text-end">Chiết khấu</th>
                            <th class="text-end">Khách cần trả</th>
                            <th class="text-center">Hình thức</th>
                            <th class="text-end pe-3">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td class="ps-3 fw-bold text-primary">{{ $order->invoice_number }}</td>
                            <td>{{ date('d/m/Y H:i', strtotime($order->created_at)) }}</td>
                            <td><span class="fw-semibold text-dark">{{ $order->customer_name ?? 'Khách vãng lai' }}</span></td>
                            <td><small class="text-muted">{{ $order->user_name }}</small></td>
                            <td class="text-end">{{ number_format($order->total_amount, 0, ',', '.') }}đ</td>
                            <td class="text-end text-danger">{{ number_format($order->discount_amount, 0, ',', '.') }}đ</td>
                            <td class="text-end fw-bold text-dark">{{ number_format($order->final_amount, 0, ',', '.') }}đ</td>
                            <td class="text-center">
                                @if($order->payment_method === 'cash')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Tiền mặt</span>
                                @elseif($order->payment_method === 'qr_code')
                                    <span class="badge bg-info-subtle text-info border border-info-subtle">Chuyển khoản</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Ghi nợ</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-sm btn-outline-primary btn-view-detail" data-id="{{ $order->id }}"><i class="bi bi-eye"></i> Chi tiết</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary btn-print-again" data-id="{{ $order->id }}"><i class="bi bi-printer"></i> In lại</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i> Không tìm thấy hóa đơn nào khớp với bộ lọc
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($orders->hasPages())
        <div class="card-footer bg-white pt-3">
            {{ $orders->links() }}
        </div>
        @endif
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

<div id="print-section-again" style="display: none;">
    <div style="width: 80mm; font-family: 'Courier New', Courier, monospace; font-size: 12px; color: #000; padding: 5mm; line-height: 1.4;">
        <div style="text-align: center; margin-bottom: 10px;">
            <h2 style="margin: 0; font-size: 16px; font-weight: bold;">SMART GROCER</h2>
            <p style="margin: 3px 0; font-size: 11px;">Hệ thống POS thông minh (IN LẠI)</p>
            <p style="margin: 3px 0; font-size: 10px;">ĐC: Cần Thơ, Việt Nam</p>
            <div style="border-top: 1px dashed #000; margin-top: 10px;"></div>
        </div>
        <div style="margin-bottom: 10px; font-size: 11px;">
            <table style="width: 100%;">
                <tr><td>Mã HĐ:</td><td style="text-align: right; font-weight: bold;" id="pa-invoice-number">---------</td></tr>
                <tr><td>Ngày:</td><td style="text-align: right;" id="pa-date">--/--/---- --:--</td></tr>
                <tr><td>Khách hàng:</td><td style="text-align: right;" id="pa-customer-name">-</td></tr>
                <tr><td>Thu ngân:</td><td style="text-align: right;" id="pa-user-name">-</td></tr>
            </table>
            <div style="border-top: 1px dashed #000; margin-top: 10px;"></div>
        </div>
        <table style="width: 100%; font-size: 11px; border-collapse: collapse; margin-bottom: 10px;">
            <thead>
                <tr style="border-bottom: 1px dashed #000;">
                    <th style="text-align: left; padding-bottom: 5px;">Tên món</th>
                    <th style="text-align: center; padding-bottom: 5px;">SL</th>
                    <th style="text-align: right; padding-bottom: 5px;">T.Tiền</th>
                </tr>
            </thead>
            <tbody id="pa-items-body"></tbody>
        </table>
        <div style="border-top: 1px dashed #000; margin-top: 10px; padding-top: 5px; font-size: 11px;">
            <table style="width: 100%;">
                <tr><td>Tổng tiền hàng:</td><td style="text-align: right;" id="pa-total-amount">0đ</td></tr>
                <tr><td>Chiết khấu:</td><td style="text-align: right;" id="pa-discount">0đ</td></tr>
                <tr style="font-weight: bold; font-size: 13px;"><td>Khách cần trả:</td><td style="text-align: right;" id="pa-final-amount">0đ</td></tr>
                <tr><td>Khách đưa:</td><td style="text-align: right;" id="pa-paid">0đ</td></tr>
                <tr><td>Tiền thừa trả:</td><td style="text-align: right;" id="pa-change">0đ</td></tr>
                <tr><td>Hình thức:</td><td style="text-align: right; font-weight: bold;" id="pa-method">-</td></tr>
            </table>
            <div style="border-top: 1px dashed #000; margin-top: 10px;"></div>
        </div>
        <div style="text-align: center; margin-top: 15px; font-size: 11px;">
            <p style="margin: 0; font-weight: bold;">CẢM ƠN QUÝ KHÁCH QUAY LẠI!</p>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).on('click', '.btn-view-detail', function() {
    let id = $(this).data('id');
    $.get('/admin/orders/' + id, function(res) {
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
            $('#orderDetailModal').modal('show');
        }
    });
});

$(document).on('click', '.btn-print-again', function() {
    let id = $(this).data('id');
    $.get('/admin/orders/' + id, function(res) {
        if (res.success) {
            $('#pa-invoice-number').text(res.order.invoice_number);
            let dateObj = new Date(res.order.created_at);
            $('#pa-date').text(dateObj.toLocaleString('vi-VN'));
            $('#pa-customer-name').text(res.order.customer_name ? res.order.customer_name : 'Khách vãng lai');
            $('#pa-user-name').text(res.order.user_name);
            $('#pa-total-amount').text(parseFloat(res.order.total_amount).toLocaleString('vi-VN') + 'đ');
            $('#pa-discount').text(parseFloat(res.order.discount_amount).toLocaleString('vi-VN') + 'đ');
            $('#pa-final-amount').text(parseFloat(res.order.final_amount).toLocaleString('vi-VN') + 'đ');
            $('#pa-paid').text(parseFloat(res.order.paid_amount).toLocaleString('vi-VN') + 'đ');
            $('#pa-change').text(parseFloat(res.order.change_amount).toLocaleString('vi-VN') + 'đ');
            let methodText = 'Tiền mặt';
            if(res.order.payment_method === 'qr_code') methodText = 'Chuyển khoản';
            if(res.order.payment_method === 'debt') methodText = 'Ghi nợ';
            $('#pa-method').text(methodText);
            let html = '';
            res.items.forEach(item => {
                html += `<tr style="border-bottom: 1px dotted #eee;"><td style="padding: 5px 0;">${item.product_name} (${item.unit_name})</td><td style="text-align: center; padding: 5px 0;">${item.quantity}</td><td style="text-align: right; padding: 5px 0;">${parseFloat(item.subtotal).toLocaleString('vi-VN')}đ</td></tr>`;
            });
            $('#pa-items-body').html(html);
            let iframe = document.getElementById('print-iframe-again');
            if (!iframe) {
                iframe = document.createElement('iframe');
                iframe.id = 'print-iframe-again';
                iframe.style.position = 'absolute';
                iframe.style.top = '-9999px';
                iframe.style.left = '-9999px';
                document.body.appendChild(iframe);
            }
            let doc = iframe.contentDocument || iframe.contentWindow.document;
            doc.open();
            doc.write('<html><head><title>Print Invoice Again</title></head><body style="margin:0;">' + $('#print-section-again').html() + '</body></html>');
            doc.close();
            setTimeout(function() {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            }, 500);
        }
    });
});
</script>
@endsection