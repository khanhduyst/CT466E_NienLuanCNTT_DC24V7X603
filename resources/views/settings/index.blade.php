@extends('layouts.app')

@section('content')
<div class="container-fluid py-4 bg-light" style="min-height: 90vh;">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-bold text-dark mb-0"><i class="bi bi-gear-wide-connected text-primary me-2"></i>Cấu hình Hệ thống</h4>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-3" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('admin.settings.update') }}" method="POST">
                @csrf
                
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h6 class="fw-bold text-primary mb-0"><i class="bi bi-shop me-2"></i>Thông tin Cửa hàng</h6>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label small fw-bold text-secondary">Tên cửa hàng</label>
                                <input type="text" name="store_name" class="form-control fw-semibold" value="{{ $settings['store_name'] ?? '' }}" placeholder="VD: Tiệm Tạp Hóa SmartGrocer">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h6 class="fw-bold text-primary mb-0"><i class="bi bi-qr-code-scan me-2"></i>Cấu hình Thanh toán VietQR</h6>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Tên chủ tài khoản</label>
                                <input type="text" name="bank_account_holder" class="form-control fw-semibold" value="{{ $settings['bank_account_holder'] ?? '' }}" placeholder="VD: LE KHANH DUY">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Số tài khoản ngân hàng</label>
                                <input type="text" name="vietqr_account_no" class="form-control fw-semibold" value="{{ $settings['vietqr_account_no'] ?? '' }}" placeholder="Nhập số tài khoản...">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom-0">
                        <h6 class="fw-bold text-primary mb-0"><i class="bi bi-stars me-2"></i>Cấu hình Tích điểm & Đổi điểm</h6>
                    </div>
                    <div class="card-body pt-0">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Mức mua tối thiểu để tích điểm (VNĐ)</label>
                                <div class="input-group">
                                    <input type="number" name="point_conversion_rate" class="form-control fw-semibold" value="{{ $settings['point_conversion_rate'] ?? 10000 }}" min="0">
                                    <span class="input-group-text bg-light fw-bold text-muted">VNĐ</span>
                                </div>
                                <div class="form-text small">Số tiền hóa đơn đạt mốc này sẽ bắt đầu được tích điểm.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Số điểm nhận được tương ứng (pt)</label>
                                <div class="input-group">
                                    <input type="number" name="point_redeem_value" class="form-control fw-semibold" value="{{ $settings['point_redeem_value'] ?? 100 }}" min="0">
                                    <span class="input-group-text bg-light fw-bold text-muted">pt</span>
                                </div>
                                <div class="form-text small">Cứ mỗi mốc tiền trên sẽ tích được số điểm tương ứng (1pt = 1đ giảm giá).</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Đơn hàng tối thiểu được đổi điểm (VNĐ)</label>
                                <div class="input-group">
                                    <input type="number" name="min_order_amount_for_redeem" class="form-control fw-semibold" value="{{ $settings['min_order_amount_for_redeem'] ?? 30000 }}" min="0">
                                    <span class="input-group-text bg-light fw-bold text-muted">VNĐ</span>
                                </div>
                                <div class="form-text small">Hóa đơn phải từ giá trị này trở lên mới được tích chọn dùng điểm.</div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-secondary">Tỷ lệ giảm giá tối đa bằng điểm (%)</label>
                                <div class="input-group">
                                    <input type="number" name="max_point_discount_percent" class="form-control fw-semibold" value="{{ $settings['max_point_discount_percent'] ?? 50 }}" min="1" max="100">
                                    <span class="input-group-text bg-light fw-bold text-muted">%</span>
                                </div>
                                <div class="form-text small">Điểm tích lũy chỉ được trừ tối đa theo % tổng tiền hóa đơn (tránh lỗ tiền mặt).</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mb-4">
                    <button type="submit" class="btn btn-primary btn-lg px-5 fw-bold rounded-3 shadow-sm">
                        <i class="bi bi-save2-fill me-2"></i>LƯU CẤU HÌNH
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection