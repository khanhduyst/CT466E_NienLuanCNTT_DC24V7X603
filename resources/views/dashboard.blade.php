@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<div class="container-fluid py-4 bg-light" style="min-height: 90vh;">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1"><i class="fa-solid fa-gauge-high text-primary me-2"></i>Bảng Tổng Quan (Dashboard)</h4>
            <span class="text-muted small">Thời gian: {{ $start->format('d/m/Y') }} @if($start->format('Y-m-d') != $end->format('Y-m-d')) - {{ $end->format('d/m/Y') }} @endif</span>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            <form action="{{ route('admin.dashboard') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2">
                <select name="filter_type" id="filter_type" class="form-select form-select-sm fw-semibold style-pointer shadow-sm" style="width: 150px;" onchange="toggleCustomDates()">
                    <option value="today" {{ $filterType == 'today' ? 'selected' : '' }}>Hôm nay</option>
                    <option value="yesterday" {{ $filterType == 'yesterday' ? 'selected' : '' }}>Hôm qua</option>
                    <option value="last_7_days" {{ $filterType == 'last_7_days' ? 'selected' : '' }}>7 ngày qua</option>
                    <option value="this_month" {{ $filterType == 'this_month' ? 'selected' : '' }}>Tháng này</option>
                    <option value="custom" {{ $filterType == 'custom' ? 'selected' : '' }}>Tùy chọn ngày</option>
                </select>

                <div id="custom-date-box" class="d-none d-flex gap-2 align-items-center">
                    <input type="date" name="start_date" class="form-control form-control-sm shadow-sm" value="{{ $startDate }}">
                    <span class="text-muted">-</span>
                    <input type="date" name="end_date" class="form-control form-control-sm shadow-sm" value="{{ $endDate }}">
                </div>

                <button type="submit" class="btn btn-sm btn-primary fw-bold px-3 shadow-sm"><i class="fa-solid fa-filter me-1"></i> Lọc dữ liệu</button>
            </form>

            <a href="/admin/pos/ban-hang" class="btn btn-sm btn-success fw-bold px-3 shadow-sm"><i class="fa-solid fa-cart-plus me-1"></i> Bán hàng ngay</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small fw-bold text-uppercase">TỔNG DOANH THU</div>
                        <h4 class="fw-bold mb-0 mt-1">{{ number_format($totalRevenue) }} đ</h4>
                    </div>
                    <div class="bg-white bg-opacity-25 p-3 rounded-circle" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-dollar-sign fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-success text-white p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small fw-bold text-uppercase">LỢI NHUẬN GỘP</div>
                        <h4 class="fw-bold mb-0 mt-1">{{ number_format($grossProfit) }} đ</h4>
                    </div>
                    <div class="bg-white bg-opacity-25 p-3 rounded-circle" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-piggy-bank fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-info text-white p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small fw-bold text-uppercase">TỔNG ĐƠN HÀNG</div>
                        <h4 class="fw-bold mb-0 mt-1">{{ number_format($totalOrders) }} đơn</h4>
                    </div>
                    <div class="bg-white bg-opacity-25 p-3 rounded-circle" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-receipt fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 bg-danger text-white p-3 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="text-white-50 small fw-bold text-uppercase">PHÁT SINH NỢ MỚI</div>
                        <h4 class="fw-bold mb-0 mt-1">{{ number_format($totalDebtGenerated) }} đ</h4>
                    </div>
                    <div class="bg-white bg-opacity-25 p-3 rounded-circle" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-file-invoice-dollar fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-chart-line text-primary me-2"></i>Biểu đồ Doanh thu</h6>
                <div style="height: 320px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white">
                <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-wallet text-primary me-2"></i>Cơ cấu Thu tiền</h6>

                <div class="p-2 bg-light rounded-3 mb-2 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark small"><i class="fa-solid fa-money-bill-wave text-success me-2 fs-6"></i>Tiền mặt:</span>
                    <span class="fw-bold text-success">{{ number_format($cashRevenue) }} đ</span>
                </div>

                <div class="p-2 bg-light rounded-3 mb-3 d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark small"><i class="fa-solid fa-qrcode text-primary me-2 fs-6"></i>Chuyển khoản:</span>
                    <span class="fw-bold text-primary">{{ number_format($transferRevenue) }} đ</span>
                </div>

                <hr class="my-2">

                <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-trophy text-warning me-2"></i>Top 5 Bán chạy</h6>
                <ul class="list-group list-group-flush small mb-3">
                    @forelse($topProducts as $tp)
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center border-0 py-1">
                        <span class="fw-semibold text-truncate me-2" style="max-width: 210px;" title="{{ $tp->name }}">{{ $tp->name }}</span>
                        <span class="badge bg-primary-subtle text-primary rounded-pill fw-bold">{{ $tp->total_qty }} món</span>
                    </li>
                    @empty
                    <li class="list-group-item px-0 text-muted border-0">Chưa có dữ liệu.</li>
                    @endforelse
                </ul>

                <hr class="my-2">

                <h6 class="fw-bold text-danger mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i>Cảnh báo Tồn kho ít (&le; 10)</h6>
                <ul class="list-group list-group-flush small">
                    @forelse($lowStockProducts as $lp)
                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center border-0 py-1">
                        <span class="fw-semibold text-truncate text-secondary me-2" style="max-width: 210px;" title="{{ $lp->name }} ({{ $lp->unit_name }})">{{ $lp->name }} ({{ $lp->unit_name }})</span>
                        <span class="badge bg-danger-subtle text-danger rounded-pill fw-bold">Còn {{ $lp->stock_quantity }}</span>
                    </li>
                    @empty
                    <li class="list-group-item px-0 text-muted border-0">Tất cả sản phẩm đều đủ hàng.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 bg-white">
        <div class="card-header bg-white py-3 border-bottom-0 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>Danh sách Đơn hàng Gần đây</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th class="ps-3">Mã hóa đơn</th>
                        <th>Thời gian</th>
                        <th>Khách hàng</th>
                        <th>Hình thức</th>
                        <th class="text-end">Tổng thanh toán</th>
                        <th class="text-center pe-3">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="small">
                    @forelse($recentOrders as $ro)
                    <tr>
                        <td class="ps-3 fw-bold text-primary">#{{ $ro->invoice_number }}</td>
                        <td class="text-muted">{{ date('d/m/Y H:i', strtotime($ro->created_at)) }}</td>
                        <td class="fw-semibold">{{ $ro->customer_name ?? 'Khách lẻ' }}</td>
                        <td>
                            @if($ro->payment_method == 'cash')
                            <span class="badge bg-success-subtle text-success"><i class="fa-solid fa-money-bill me-1"></i>Tiền mặt</span>
                            @else
                            <span class="badge bg-primary-subtle text-primary"><i class="fa-solid fa-qrcode me-1"></i>Chuyển khoản</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold text-danger">{{ number_format($ro->final_amount) }} đ</td>
                        <td class="text-center pe-3">
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-circle" onclick="viewOrderDetail('{{ $ro->id }}')" title="Xem chi tiết đơn hàng">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Không có đơn hàng nào trong thời gian này.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Xem Chi Tiết Đơn Hàng -->
<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h6 class="modal-title fw-bold text-primary" id="modalInvoiceNumber"><i class="fa-solid fa-file-invoice me-2"></i>Chi tiết đơn hàng</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3" id="modalOrderContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="small text-muted mt-2">Đang tải dữ liệu...</div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm rounded-3 fw-bold" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function toggleCustomDates() {
        let filter = document.getElementById('filter_type').value;
        let customBox = document.getElementById('custom-date-box');
        if (filter === 'custom') {
            customBox.classList.remove('d-none');
        } else {
            customBox.classList.add('d-none');
        }
    }
    toggleCustomDates();

    const ctx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: JSON.parse('{!! json_encode($chartLabels) !!}'),
            datasets: [{
                label: 'Doanh thu (VNĐ)',
                data: JSON.parse('{!! json_encode($chartValues) !!}'),
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                fill: true,
                tension: 0.3,
                borderWidth: 2,
                pointRadius: 4,
                pointBackgroundColor: '#0d6efd'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return value.toLocaleString() + 'đ';
                        }
                    }
                }
            }
        }
    });

    function viewOrderDetail(orderId) {
        let myModal = new bootstrap.Modal(document.getElementById('orderDetailModal'));
        myModal.show();

        fetch(`/admin/orders/${orderId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    let o = data.order;
                    document.getElementById('modalInvoiceNumber').innerText = `Chi tiết hóa đơn #${o.invoice_number}`;

                    let itemsHtml = '';
                    data.items.forEach(item => {
                        itemsHtml += `
                    <tr>
                        <td>${item.product_name} (${item.unit_name ?? 'Đơn vị'})</td>
                        <td class="text-center">${item.quantity}</td>
                        <td class="text-end">${Number(item.sale_price).toLocaleString()} đ</td>
                        <td class="text-end fw-bold">${Number(item.subtotal).toLocaleString()} đ</td>
                    </tr>
                `;
                    });

                    let html = `
                <div class="row g-2 mb-3 bg-light p-3 rounded-3 small">
                    <div class="col-6"><strong>Khách hàng:</strong> ${o.customer_name ?? 'Khách lẻ'}</div>
                    <div class="col-6 text-end"><strong>Thời gian:</strong> ${o.created_at}</div>
                    <div class="col-6"><strong>Hình thức:</strong> ${o.payment_method == 'cash' ? 'Tiền mặt' : 'Chuyển khoản'}</div>
                    <div class="col-6 text-end"><strong>Thu ngân:</strong> ${o.user_name ?? 'N/A'}</div>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-2">
                        <thead class="table-light small">
                            <tr>
                                <th>Sản phẩm</th>
                                <th class="text-center">SL</th>
                                <th class="text-end">Đơn giá</th>
                                <th class="text-end">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            ${itemsHtml}
                        </tbody>
                    </table>
                </div>
                <div class="text-end small mt-2">
                    <div>Tổng tiền hàng: <strong>${Number(o.total_amount).toLocaleString()} đ</strong></div>
                    <div>Giảm giá: <strong class="text-danger">-${Number(o.discount_amount).toLocaleString()} đ</strong></div>
                    <div class="fs-6 fw-bold text-primary mt-1">Thực thu: ${Number(o.final_amount).toLocaleString()} đ</div>
                </div>
            `;
                    document.getElementById('modalOrderContent').innerHTML = html;
                }
            })
            .catch(err => {
                document.getElementById('modalOrderContent').innerHTML = '<div class="alert alert-danger mb-0">Không thể tải thông tin đơn hàng.</div>';
            });
    }
</script>
@endsection