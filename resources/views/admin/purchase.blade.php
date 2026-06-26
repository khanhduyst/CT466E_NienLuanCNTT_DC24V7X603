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
        <div class="card-header bg-primary text-white py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-file-invoice me-2"></i> Tạo Phiếu Nhập Kho</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.purchase.store') }}" method="POST" id="purchaseForm">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold mb-0">Nhà Cung Cấp</label>
                            <button type="button" class="btn btn-sm btn-outline-primary py-0" data-bs-toggle="modal" data-bs-target="#addSupplierModal">
                                + Thêm mới NCC
                            </button>
                        </div>
                        <select name="supplier_id" class="form-select" required>
                            <option value="">-- Chọn nhà cung cấp --</option>
                            @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Mã Lô Hàng (Batch Code)</label>
                        <input type="text" name="batch_code" id="batch_code" class="form-control" placeholder="Để trống để tự động sinh mã...">
                    </div>
                </div>

                <hr>

                <div class="mb-4 position-relative">
                    <label class="form-label fw-bold text-primary"><i class="fas fa-search me-1"></i> Tìm kiếm sản phẩm muốn nhập kho</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-barcode"></i></span>
                        <input type="text" id="searchProductInput" class="form-control" placeholder="Gõ tên sản phẩm hoặc quét mã vạch để thêm vào danh sách...">
                    </div>
                    <ul id="searchResultList" class="list-group position-absolute w-100 mt-1 shadow-sm d-none" style="z-index: 1050; max-height: 200px; overflow-y: auto;">
                    </ul>
                </div>

                <h6 class="fw-bold mb-3 text-secondary"><i class="fas fa-list me-1"></i> Chi Tiết Sản Phẩm Nhập</h6>
                <div class="table-responsive mb-4">
                    <table class="table table-bordered align-middle" id="purchaseTable">
                        <thead class="table-light fw-bold text-secondary">
                            <tr>
                                <th style="width: 30%;">Sản Phẩm</th>
                                <th style="width: 12%;">Số Lượng Nhập</th>
                                <th style="width: 15%;">Giá Nhập (đ)</th>
                                <th style="width: 18%;">Ngày Sản Xuất (NSX)</th>
                                <th style="width: 18%;">Hạn Sử Dụng (HSD)</th>
                                <th class="text-center" style="width: 7%;">Xóa</th>
                            </tr>
                        </thead>
                        <tbody id="purchaseTableBody">
                            <tr id="emptyRow">
                                <td colspan="6" class="text-center py-4 text-muted">Chưa có sản phẩm nào được chọn. Vui lòng tìm kiếm phía trên!</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success fw-bold px-4 py-2"><i class="fas fa-save me-2"></i> Lưu Phiếu Nhập</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const products = JSON.parse('{!! json_encode($products) !!}');
        const searchInput = document.getElementById('searchProductInput');
        const resultList = document.getElementById('searchResultList');
        const tableBody = document.getElementById('purchaseTableBody');
        const emptyRow = document.getElementById('emptyRow');
        const purchaseForm = document.getElementById('purchaseForm');
        const batchCodeInput = document.getElementById('batch_code');

        let selectedProductIds = [];

        purchaseForm.addEventListener('submit', function(e) {
            if (selectedProductIds.length === 0) {
                e.preventDefault();
                Swal.fire({
                    title: 'Thông báo',
                    text: 'Vui lòng chọn ít nhất một sản phẩm trước khi thực hiện lưu phiếu nhập kho!',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            if (batchCodeInput.value.trim() === '') {
                const dateStr = new Date().toISOString().slice(0, 10).replace(/-/g, "");
                const randomStr = Math.random().toString(36).substring(2, 6).toUpperCase();
                batchCodeInput.value = `LH-${dateStr}-${randomStr}`;
            }
        });

        searchInput.addEventListener('input', function() {
            const keyword = this.value.trim().toLowerCase();
            resultList.innerHTML = '';

            if (keyword === '') {
                resultList.classList.add('d-none');
                return;
            }

            const filtered = products.filter(p =>
                p.name.toLowerCase().includes(keyword) ||
                (p.barcode && p.barcode.includes(keyword))
            );

            if (filtered.length > 0) {
                filtered.forEach(p => {
                    const li = document.createElement('li');
                    li.className = 'list-group-item list-group-item-action cursor-pointer d-flex justify-content-between align-items-center py-2';
                    li.style.cursor = 'pointer';
                    li.innerHTML = `<div><strong>${p.name}</strong></div> <small class="text-muted">${p.barcode ?? ''}</small>`;

                    li.addEventListener('click', function() {
                        addProductRow(p);
                        searchInput.value = '';
                        resultList.classList.add('d-none');
                        searchInput.focus();
                    });
                    resultList.appendChild(li);
                });
                resultList.classList.remove('d-none');
            } else {
                resultList.innerHTML = '<li class="list-group-item text-muted small py-2">Không tìm thấy sản phẩm hợp lệ...</li>';
                resultList.classList.remove('d-none');
            }
        });

        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !resultList.contains(e.target)) {
                resultList.classList.add('d-none');
            }
        });

        function addProductRow(product) {
            if (selectedProductIds.includes(product.id)) {
                Swal.fire({
                    title: 'Hệ thống cảnh báo',
                    text: `Sản phẩm "${product.name}" đã tồn tại trong danh sách phiếu nhập kho.`,
                    icon: 'error',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            if (emptyRow) emptyRow.style.display = 'none';

            selectedProductIds.push(product.id);

            const tr = document.createElement('tr');
            tr.id = `row-${product.id}`;
            tr.innerHTML = `
            <td>
                <span class="fw-bold text-dark">${product.name}</span>
                <input type="hidden" name="items[${product.id}][product_id]" value="${product.id}">
            </td>
            <td>
                <input type="number" name="items[${product.id}][quantity]" class="form-control" min="1" value="1" required>
            </td>
            <td>
                <input type="number" name="items[${product.id}][purchase_price]" class="form-control" min="0" value="0" required>
            </td>
            <td>
                <input type="date" name="items[${product.id}][manufacture_date]" class="form-control">
            </td>
            <td>
                <input type="date" name="items[${product.id}][expiry_date]" class="form-control">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" data-id="${product.id}">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </td>
        `;

            tr.querySelector('.btn-remove-row').addEventListener('click', function() {
                const idToRemove = parseInt(this.dataset.id);
                document.getElementById(`row-${idToRemove}`).remove();
                selectedProductIds = selectedProductIds.filter(id => id !== idToRemove);

                if (selectedProductIds.length === 0) {
                    emptyRow.style.display = '';
                }
            });

            tableBody.appendChild(tr);
        }
    });
</script>

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
@endsection