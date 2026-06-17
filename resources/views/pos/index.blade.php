@extends('layouts.app')

@section('content')
    <h3 class="fw-bold text-dark mb-3">Màn hình bán hàng POS tại quầy</h3>
    <p class="text-muted">Hệ thống quét mã vạch sản phẩm, tự động tính toán tiền theo đơn vị quy đổi và kết xuất hóa đơn.</p>
    
    <div class="alert alert-primary border-0 shadow-sm d-inline-block p-3" style="border-radius: 12px;">
        <i class="bi bi-qr-code-scan me-2"></i> Đang chờ quét mã vạch sản phẩm...
    </div>
@endsection