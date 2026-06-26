@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i> Lịch Sử Phiếu Nhập Kho</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted uppercase font-weight-bold">
                        <tr>
                            <th class="ps-4">Thời gian</th>
                            <th>Mã Phiếu Nhập</th>
                            <th>Nhà Cung Cấp</th>
                            <th>Tổng Số Lượng</th>
                            <th>Tổng Giá Trị</th>
                            <th class="text-center pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $index => $batch)
                        <tr>
                            <td class="ps-4 text-secondary">{{ date('d/m/Y H:i', strtotime($batch->created_at)) }}</td>
                            <td><span class="badge bg-secondary font-monospace">{{ $batch->batch_code }}</span></td>
                            <td class="fw-bold text-dark">{{ $batch->supplier_name }}</td>
                            <td>{{ number_format($batch->total_quantity) }} sản phẩm</td>
                            <td class="text-success fw-bold">{{ number_format($batch->total_amount) }} đ</td>
                            <td class="text-center pe-4">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modal-{{ $index }}">
                                    <i class="fas fa-eye me-1"></i> Xem chi tiết
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Chưa có lịch sử nhập kho nào được ghi nhận.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@foreach($batches as $index => $batch)
<div class="modal fade" id="modal-{{ $index }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="fas fa-info-circle me-2 text-primary"></i> Chi Tiết Phiếu: {{ $batch->batch_code }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="p-3 bg-white border-bottom">
                    <div class="row">
                        <div class="col-6"><strong>Nhà cung cấp:</strong> {{ $batch->supplier_name }}</div>
                        <div class="col-6 text-end"><strong>Thời gian nhập:</strong> {{ date('d/m/Y H:i', strtotime($batch->created_at)) }}</div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" style="font-size: 0.9rem;">
                        <thead class="table-light fw-bold text-secondary">
                            <tr>
                                <th>Tên Sản Phẩm</th>
                                <th class="text-center">Số Lượng</th>
                                <th class="text-end">Giá Nhập</th>
                                <th class="text-center">Ngày SX</th>
                                <th class="text-center">Hạn SD</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($batch->details as $item)
                            <tr>
                                <td class="fw-bold text-dark">{{ $item->product_name }}</td>
                                <td class="text-center">{{ number_format($item->original_quantity) }}</td>
                                <td class="text-end text-success fw-bold">{{ number_format($item->purchase_price) }} đ</td>
                                <td class="text-center">{{ $item->manufacture_date ? date('d/m/Y', strtotime($item->manufacture_date)) : '---' }}</td>
                                <td class="text-center">
                                    @if($item->expiry_date)
                                        <span class="text-danger fw-bold">{{ date('d/m/Y', strtotime($item->expiry_date)) }}</span>
                                    @else
                                        <span class="text-muted">---</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection