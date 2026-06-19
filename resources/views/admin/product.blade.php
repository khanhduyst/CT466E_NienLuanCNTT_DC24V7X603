@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex align-items-center justify-content-between">
            <div>
                <h4 class="fw-bold text-dark mb-1">Quản lý sản phẩm & Hàng hóa</h4>
                <p class="text-muted small mb-0">Thiết lập danh sách sản phẩm và các đơn vị tính quy đổi</p>
            </div>
            <button type="button" class="btn btn-primary fw-bold px-3 py-2" data-bs-toggle="modal" data-bs-target="#addProductModal" style="border-radius: 10px;">
                <i class="bi bi-plus-circle-fill me-2"></i>Thêm sản phẩm mới
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
        <div class="card-body p-3">
            <form action="/admin/san-pham" method="GET" class="row g-2">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" name="search" value="{{ request('search') }}" placeholder="Tìm tên sản phẩm, mã vạch...">
                    </div>
                </div>

                <div class="col-md-3">
                    <select class="form-select" id="filter-parent-category" name="parent_category_id">
                        <option value="">-- Tất cả danh mục cha ({{ $categories->where('parent_id', null)->count() }}) --</option>
                        @foreach($categories->where('parent_id', null) as $parent)
                        <option value="{{ $parent->id }}" {{ request('parent_category_id') == $parent->id ? 'selected' : '' }}>
                            📂 {{ $parent->name }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 position-relative">
                    <input type="text" class="form-control" id="search-child-input" placeholder="Gõ tìm mục con trong 1000 mục..." autocomplete="off" {{ request('parent_category_id') ? '' : 'disabled' }}>
                    <input type="hidden" name="category_id" id="filter-child-id" value="{{ request('category_id') }}">
                    <div class="dropdown-menu w-100 shadow-sm border-0 mt-1 p-0" id="child-suggestions-box" style="max-height: 250px; overflow-y: auto; display: none; z-index: 1050; border-radius: 8px;"></div>
                </div>

                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary fw-bold w-100" style="border-radius: 8px;">
                        <i class="bi bi-filter"></i> Lọc
                    </button>
                    @if(request()->has('search') || request()->has('category_id') || request()->has('parent_category_id'))
                    <a href="/admin/san-pham" class="btn btn-light fw-bold text-secondary border" style="border-radius: 8px;">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 16px;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-3" style="width: 60px;">STT</th>
                            <th style="width: 80px;">Ảnh</th>
                            <th>Tên sản phẩm</th>
                            <th>Danh mục</th>
                            <th>Đơn vị & Giá bán</th>
                            <th class="text-end pe-3" style="width: 100px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td class="fw-bold text-secondary ps-3">{{ $loop->iteration }}</td>
                            <td>
                                @if($product->image)
                                <img src="{{ asset('uploads/products/' . $product->image) }}" class="object-fit-cover border shadow-sm" style="width: 45px; height: 45px; border-radius: 8px;">
                                @else
                                <div class="bg-light border text-muted d-flex align-items-center justify-content-center shadow-sm" style="width: 45px; height: 45px; border-radius: 8px; font-size: 18px;">
                                    <i class="bi bi-box"></i>
                                </div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $product->name }}</div>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border px-2 py-1">{{ $product->category->name ?? 'Chưa phân loại' }}</span>
                            </td>
                            <td>
                                <button type="button"
                                    class="btn btn-sm btn-light border fw-bold text-secondary text-start d-flex align-items-center justify-content-between p-2 popover-variant-trigger"
                                    data-bs-container="body"
                                    data-bs-toggle="popover"
                                    data-bs-placement="left"
                                    data-bs-html="true"
                                    title="<i class='bi bi-layers-half text-primary me-1'></i> Chi tiết biến thể"
                                    data-bs-content="
                                            <div class='d-flex flex-column gap-2' style='min-width: 240px; max-height: 280px; overflow-y: auto;'>
                                                @foreach($product->variants as $variant)
                                                    <div class='p-2 bg-light rounded border-start border-3 border-success mb-1' style='font-size: 12px;'>
                                                        <div class='fw-bold text-dark mb-1'>
                                                            <i class='bi bi-tag-fill text-warning me-1'></i>{{ $variant->variant_name }}
                                                            @if($variant->barcode)<span class='text-muted fw-normal' style='font-size: 10px;'> ({{ $variant->barcode }})</span>@endif
                                                        </div>
                                                        <div class='d-flex flex-column gap-1 ps-2 border-start' style='font-size: 11px;'>
                                                            @foreach($variant->units as $unit)
                                                                <div>
                                                                    <span class='text-secondary'>{{ $unit->unit_name }}:</span>
                                                                    <span class='text-danger fw-bold'>{{ number_format($unit->sale_price, 0, ',', '.') }}đ</span>
                                                                    @if(!$unit->is_base)<small class='text-muted'>(x{{ $unit->conversion_rate }})</small>
                                                                    @else<span class='text-info fw-bold' style='font-size: 10px;'> [Gốc-Kho: {{ $unit->stock_quantity }}]</span>@endif
                                                                </div>
                                                               @endforeach
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        "
                                    style="border-radius: 8px; min-width: 150px; font-size: 13px;">
                                    <span><i class="bi bi-box-seam text-primary me-2"></i>{{ $product->variants->count() }} biến thể</span>
                                    <i class="bi bi-chat-square-text text-muted ms-2 small"></i>
                                </button>
                            </td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-sm btn-outline-primary border-0 btn-edit-product"
                                    data-id="{{ $product->id }}"
                                    data-name="{{ $product->name }}"
                                    data-parent-id="{{ $product->category->parent_id ?? $product->category_id }}"
                                    data-child-id="{{ $product->category->parent_id ? $product->category_id : '' }}"
                                    data-image="{{ $product->image ? asset('uploads/products/' . $product->image) : '' }}"
                                    data-variants='@json($product->variants->load("units"))'>
                                    <i class="bi bi-pencil-square"></i>
                                </button>

                                <button type="button" class="btn btn-sm btn-outline-danger border-0 btn-delete-product" data-id="{{ $product->id }}">
                                    <i class="bi bi-trash3"></i>
                                </button>
                                <form id="delete-form-{{ $product->id }}" action="/admin/san-pham/{{ $product->id }}" method="POST" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Chưa có sản phẩm nào trong hệ thống.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($products->hasPages())
            <div class="card-footer bg-white border-top-0 d-flex align-items-center justify-content-between p-3" style="border-bottom-left-radius: 16px; border-bottom-right-radius: 16px;">
                <div class="text-muted small">
                    Hiển thị từ {{ $products->firstItem() }} đến {{ $products->lastItem() }} trong tổng số {{ $products->total() }} sản phẩm
                </div>
                <div class="laravel-pagination-wrapper">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark" id="addProductModalLabel">
                    <i class="bi bi-box-seam-fill text-primary me-2"></i>Thêm sản phẩm mới vào hệ thống
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="/admin/san-pham" method="POST" enctype="multipart/form-data" id="form-add-product" data-errors='@json($errors->toArray())' data-old='@json(old("variants"))' data-old-parent="{{ old('parent_category_id') }}" data-old-child="{{ old('category_id') }}" novalidate>
                @csrf
                <div class="modal-body px-4 pb-4 pt-2">
                    <div class="row g-4">
                        <div class="col-md-4 border-end pe-md-4">
                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle-fill me-2"></i>Thông tin chung</h6>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Tên sản phẩm chính</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="Ví dụ: Nước mắm Knorr 15 độ đạm" required>
                                @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Danh mục chính</label>
                                    <select class="form-select" id="parent-category-select" name="parent_category_id">
                                        <option value="">-- Chọn danh mục cha --</option>
                                        @foreach($categories->where('parent_id', null) as $parent)
                                        <option value="{{ $parent->id }}" {{ old('parent_category_id') == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Danh mục chi tiết</label>
                                    <select class="form-select @error('category_id') is-invalid @enderror" name="category_id" id="child-category-select" required disabled>
                                        <option value="">-- Chọn danh mục con --</option>
                                    </select>
                                    @error('category_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Hình ảnh đại diện</label>
                                <input type="file" class="form-control" name="image" id="product-image-input" accept="image/*">
                                <div class="mt-2 text-center position-relative d-none" id="image-preview-container">
                                    <img id="image-preview" src="#" alt="Xem trước ảnh" class="img-thumbnail shadow-sm" style="max-height: 120px; border-radius: 8px;">
                                    <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 translate-middle badge rounded-circle" id="btn-remove-preview" style="padding: 4px 6px; font-size: 10px;">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8 ps-md-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="fw-bold text-success mb-0"><i class="bi bi-layers-half me-2"></i>Danh sách biến thể & Đơn vị tính</h6>
                                <button type="button" class="btn btn-sm btn-outline-success fw-bold" id="btn-add-variant">
                                    <i class="bi bi-plus-lg me-1"></i>Thêm biến thể thuộc tính
                                </button>
                            </div>
                            <div id="variants-container" style="max-height: 450px; overflow-y: auto; padding-right: 5px;"></div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top-0 px-4 pb-4">
                    <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal" style="border-radius: 10px;">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary fw-bold" style="border-radius: 10px;">
                        <i class="bi bi-cloud-arrow-up-fill me-2"></i>Lưu vào hệ thống
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editProductModal" tabindex="-1" aria-labelledby="editProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark" id="editProductModalLabel">
                    <i class="bi bi-pencil-square text-warning me-2"></i>Cập nhật thông tin sản phẩm đa biến thể
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="form-edit-product" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')
                <div class="modal-body px-4 pb-4 pt-2">
                    <div class="row g-4">
                        <div class="col-md-4 border-end pe-md-4">
                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-info-circle-fill me-2"></i>Thông tin sản phẩm chính</h6>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Tên sản phẩm chính</label>
                                <input type="text" class="form-control" name="name" id="edit-name" required>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Danh mục chính (Cha)</label>
                                    <select class="form-select" id="edit-parent-category-select">
                                        <option value="">-- Chọn danh mục cha --</option>
                                        @foreach($categories->where('parent_id', null) as $parent)
                                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Danh mục chi tiết (Con)</label>
                                    <select class="form-select" name="category_id" id="edit-child-category-select" required>
                                        <option value="">-- Chọn danh mục con --</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Hình ảnh sản phẩm</label>
                                <input type="file" class="form-control" name="image" id="edit-product-image-input" accept="image/*">
                                <div class="mt-2 text-center position-relative w-100" id="edit-image-preview-container">
                                    <div class="position-relative d-inline-block">
                                        <img id="edit-image-preview" src="#" alt="Xem trước ảnh" class="img-thumbnail shadow-sm" style="max-height: 120px; border-radius: 8px;">
                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 translate-middle badge rounded-circle" id="btn-edit-remove-preview" style="padding: 4px 6px; font-size: 10px; z-index: 10;">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" name="remove_current_image" id="remove-current-image" value="0">
                            </div>
                        </div>

                        <div class="col-md-8 ps-md-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="fw-bold text-success mb-0"><i class="bi bi-layers-half me-2"></i>Danh sách biến thể đang có</h6>
                                <button type="button" class="btn btn-sm btn-outline-success fw-bold px-2 py-1" id="btn-add-variant-edit" style="font-size: 12px; border-radius: 8px;">
                                    <i class="bi bi-plus-lg me-1"></i>Thêm biến thể mới
                                </button>
                            </div>

                            <div style="max-height: 450px; overflow-y: auto; padding-right: 5px;">
                                <div id="edit-variants-container"></div>
                                <div id="edit-new-variants-container" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top-0 px-4 pb-4">
                    <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal" style="border-radius: 10px;">Hủy bỏ</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold" style="border-radius: 10px;">
                        <i class="bi bi-check-all me-2"></i>Cập nhật ngay
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const allChildCategories = JSON.parse('{!! json_encode($categories->where("parent_id", "!=", null)->values()) !!}');
    let variantIndex = 0;
    let editNewVariantIndex = 0;

    // ==========================================
    // Hàm sinh biến thể động cho MODAL THÊM MỚI
    // ==========================================
    function createVariantRow(oldData = null) {
        const variantsContainer = document.getElementById('variants-container');
        if (!variantsContainer) return;

        const vIndex = variantIndex;
        let unitIndex = 0;

        const formFormEl = document.getElementById('form-add-product');
        const allErrors = formFormEl && formFormEl.getAttribute('data-errors') ?
            JSON.parse(formFormEl.getAttribute('data-errors')) : {};

        const getErrorMsg = (fieldPath) => {
            return allErrors[fieldPath] ? allErrors[fieldPath][0] : null;
        };

        const errVariantName = getErrorMsg(`variants.${vIndex}.variant_name`);
        const errBaseUnit = getErrorMsg(`variants.${vIndex}.base_unit`);
        const errImportPrice = getErrorMsg(`variants.${vIndex}.import_price`);
        const errSalePrice = getErrorMsg(`variants.${vIndex}.sale_price`);

        const valVariantName = oldData ? (oldData.variant_name || '') : '';
        const valBarcode = oldData ? (oldData.barcode || '') : '';
        const valStock = oldData ? (oldData.stock_quantity || '0') : '0';
        const valBaseUnit = oldData ? (oldData.base_unit || '') : '';
        const valImportPrice = oldData ? (oldData.import_price || '') : '';
        const valSalePrice = oldData ? (oldData.sale_price || '') : '';

        const variantHtml = `
            <div class="card card-body border border-light shadow-sm mb-4 variant-block-item" data-index="${vIndex}" style="border-radius: 12px; background-color: #f8f9fa;">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                    <span class="fw-bold text-secondary" style="font-size: 14px;">
                        <i class="bi bi-tag-fill me-1 text-warning"></i>Biến thể #${vIndex + 1}
                    </span>
                    ${vIndex > 0 ? `<button type="button" class="btn btn-sm text-danger p-0 btn-remove-variant"><i class="bi bi-trash3-fill"></i> Xóa biến thể</button>` : ''}
                </div>
                
                <div class="row g-2 mb-3">
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold">Tên thuộc tính / Dung tích</label>
                        <input type="text" class="form-control form-control-sm ${errVariantName ? 'is-invalid' : ''}" name="variants[${vIndex}][variant_name]" value="${valVariantName}" placeholder="Ví dụ: Chai 242ml, Chai 750ml..." required>
                        ${errVariantName ? `<div class="invalid-feedback d-block">${errVariantName}</div>` : ''}
                    </div>
                    <div class="col-4">
                        <label class="form-label small fw-semibold">Mã vạch (Barcode)</label>
                        <input type="text" class="form-control form-control-sm" name="variants[${vIndex}][barcode]" value="${valBarcode}" placeholder="Quét mã vạch...">
                    </div>
                    <div class="col-3">
                        <label class="form-label small fw-semibold">Tồn kho ban đầu</label>
                        <input type="number" class="form-control form-control-sm" name="variants[${vIndex}][stock_quantity]" value="${valStock}" placeholder="Số lượng" min="0" required>
                    </div>
                </div>

                <div class="row g-2 mb-3 bg-white p-2 border-start border-primary border-3" style="border-radius: 6px;">
                    <div class="col-4">
                        <label class="form-label small fw-semibold text-primary">Đơn vị gốc</label>
                        <input type="text" class="form-control form-control-sm ${errBaseUnit ? 'is-invalid' : ''}" name="variants[${vIndex}][base_unit]" value="${valBaseUnit}" placeholder="Chai, gói..." required>
                        ${errBaseUnit ? `<div class="invalid-feedback d-block">${errBaseUnit}</div>` : ''}
                    </div>
                    <div class="col-4">
                        <label class="form-label small fw-semibold text-primary">Giá nhập gốc (đ)</label>
                        <input type="number" class="form-control form-control-sm ${errImportPrice ? 'is-invalid' : ''}" name="variants[${vIndex}][import_price]" value="${valImportPrice}" placeholder="Giá vốn" min="0" required>
                        ${errImportPrice ? `<div class="invalid-feedback d-block">${errImportPrice}</div>` : ''}
                    </div>
                    <div class="col-4">
                        <label class="form-label small fw-semibold text-primary">Giá bán lẻ gốc (đ)</label>
                        <input type="number" class="form-control form-control-sm ${errSalePrice ? 'is-invalid' : ''}" name="variants[${vIndex}][sale_price]" value="${valSalePrice}" placeholder="Giá bán lẻ" min="0" required>
                        ${errSalePrice ? `<div class="invalid-feedback d-block">${errSalePrice}</div>` : ''}
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-bold text-muted"><i class="bi bi-arrow-left-right me-1"></i>Đơn vị quy đổi phụ (nếu có)</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary border-0 px-2 py-0 btn-add-conversion" style="font-size: 12px;">
                        <i class="bi bi-plus-lg"></i> Thêm quy đổi
                    </button>
                </div>

                <div class="conversion-units-list"></div>
            </div>
        `;

        variantsContainer.insertAdjacentHTML('beforeend', variantHtml);

        const currentBlock = variantsContainer.querySelector(`.variant-block-item[data-index="${vIndex}"]`);
        const conversionList = currentBlock.querySelector('.conversion-units-list');
        const btnAddConversion = currentBlock.querySelector('.btn-add-conversion');

        function addConversionRow(oldConv = null) {
            const cIndex = unitIndex;

            const errConvName = getErrorMsg(`variants.${vIndex}.conversions.${cIndex}.unit_name`);
            const errConvRate = getErrorMsg(`variants.${vIndex}.conversions.${cIndex}.conversion_rate`);
            const errConvPrice = getErrorMsg(`variants.${vIndex}.conversions.${cIndex}.sale_price`);

            const valConvName = oldConv ? (oldConv.unit_name || '') : '';
            const valConvRate = oldConv ? (oldConv.conversion_rate || '') : '';
            const valConvPrice = oldConv ? (oldConv.sale_price || '') : '';

            const cHtml = `
                <div class="row g-2 mb-2 alignment-unit-row">
                    <div class="col-4">
                        <input type="text" class="form-control form-control-sm ${errConvName ? 'is-invalid' : ''}" name="variants[${vIndex}][conversions][${cIndex}][unit_name]" value="${valConvName}" placeholder="Thùng, lốc..." required>
                        ${errConvName ? `<div class="invalid-feedback d-block" style="font-size:10px;">${errConvName}</div>` : ''}
                    </div>
                    <div class="col-3">
                        <input type="number" class="form-control form-control-sm ${errConvRate ? 'is-invalid' : ''}" name="variants[${vIndex}][conversions][${cIndex}][conversion_rate]" value="${valConvRate}" placeholder="Tỷ lệ" min="2" required>
                        ${errConvRate ? `<div class="invalid-feedback d-block" style="font-size:10px;">${errConvRate}</div>` : ''}
                    </div>
                    <div class="col-4">
                        <input type="number" class="form-control form-control-sm ${errConvPrice ? 'is-invalid' : ''}" name="variants[${vIndex}][conversions][${cIndex}][sale_price]" value="${valConvPrice}" placeholder="Giá bán" min="0" required>
                        ${errConvPrice ? `<div class="invalid-feedback d-block" style="font-size:10px;">${errConvPrice}</div>` : ''}
                    </div>
                    <div class="col-1 d-flex align-items-center justify-content-center">
                        <button type="button" class="btn btn-sm text-danger p-0 btn-remove-unit-row"><i class="bi bi-x-circle-fill"></i></button>
                    </div>
                </div>
            `;
            conversionList.insertAdjacentHTML('beforeend', cHtml);
            unitIndex++;
        }

        if (oldData && oldData.conversions) {
            Object.values(oldData.conversions).forEach(conv => {
                addConversionRow(conv);
            });
        }

        btnAddConversion.addEventListener('click', function() {
            addConversionRow();
        });

        variantIndex++;
    }

    // ==========================================
    // Hàm sinh biến thể MỚI BỔ SUNG cho MODAL SỬA
    // ==========================================
    function createNewVariantRowForEdit() {
        const container = document.getElementById('edit-new-variants-container');
        if (!container) return;

        const curIndex = editNewVariantIndex;
        let unitIndex = 0;

        const html = `
            <div class="card card-body border border-success-subtle shadow-sm mb-4 edit-new-variant-block" data-index="${curIndex}" style="border-radius: 12px; background-color: #f4faf6;">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2 border-success-subtle">
                    <span class="fw-bold text-success" style="font-size: 14px;">
                        <i class="bi bi-plus-circle-fill me-1"></i>Biến thể bổ sung mới #${curIndex + 1}
                    </span>
                    <button type="button" class="btn btn-sm text-danger p-0 btn-remove-new-edit-variant"><i class="bi bi-trash3-fill"></i> Hủy hàng</button>
                </div>
                
                <div class="row g-2 mb-3">
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold text-success">Tên thuộc tính / Dung tích mới</label>
                        <input type="text" class="form-control form-control-sm" name="new_variants[${curIndex}][variant_name]" placeholder="Ví dụ: Chai 1 lít..." required>
                    </div>
                    <div class="col-4">
                        <label class="form-label small fw-semibold text-success">Mã vạch (Barcode)</label>
                        <input type="text" class="form-control form-control-sm" name="new_variants[${curIndex}][barcode]" placeholder="Quét mã vạch...">
                    </div>
                    <div class="col-3">
                        <label class="form-label small fw-semibold text-success">Tồn kho ban đầu</label>
                        <input type="number" class="form-control form-control-sm" name="new_variants[${curIndex}][stock_quantity]" value="0" min="0" required>
                    </div>
                </div>

                <div class="row g-2 mb-3 bg-white p-2 border-start border-success border-3" style="border-radius: 6px;">
                    <div class="col-4">
                        <label class="form-label small fw-semibold text-success">Đơn vị gốc</label>
                        <input type="text" class="form-control form-control-sm" name="new_variants[${curIndex}][base_unit]" placeholder="Chai..." required>
                    </div>
                    <div class="col-4">
                        <label class="form-label small fw-semibold text-success">Giá nhập gốc (đ)</label>
                        <input type="number" class="form-control form-control-sm" name="new_variants[${curIndex}][import_price]" placeholder="Giá vốn" min="0" required>
                    </div>
                    <div class="col-4">
                        <label class="form-label small fw-semibold text-success">Giá bán lẻ gốc (đ)</label>
                        <input type="number" class="form-control form-control-sm" name="new_variants[${curIndex}][sale_price]" placeholder="Giá bán lẻ" min="0" required>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-bold text-muted"><i class="bi bi-arrow-left-right me-1"></i>Đơn vị quy đổi phụ (nếu có)</span>
                    <button type="button" class="btn btn-sm btn-outline-success border-0 px-2 py-0 btn-add-conversion-edit-new" style="font-size: 12px;">
                        <i class="bi bi-plus-lg"></i> Thêm quy đổi
                    </button>
                </div>

                <div class="edit-new-conversion-list"></div>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', html);

        const currentBlock = container.querySelector(`.edit-new-variant-block[data-index="${curIndex}"]`);
        const conversionList = currentBlock.querySelector('.edit-new-conversion-list');
        const btnAddConversion = currentBlock.querySelector('.btn-add-conversion-edit-new');

        btnAddConversion.addEventListener('click', function() {
            const cIndex = unitIndex;
            const cHtml = `
                <div class="row g-2 mb-2 alignment-unit-row">
                    <div class="col-4">
                        <input type="text" class="form-control form-control-sm" name="new_variants[${curIndex}][conversions][${cIndex}][unit_name]" placeholder="Thùng, lốc..." required>
                    </div>
                    <div class="col-3">
                        <input type="number" class="form-control form-control-sm" name="new_variants[${curIndex}][conversions][${cIndex}][conversion_rate]" placeholder="Tỷ lệ" min="2" required>
                    </div>
                    <div class="col-4">
                        <input type="number" class="form-control form-control-sm" name="new_variants[${curIndex}][conversions][${cIndex}][sale_price]" placeholder="Giá bán" min="0" required>
                    </div>
                    <div class="col-1 d-flex align-items-center justify-content-center">
                        <button type="button" class="btn btn-sm text-danger p-0 btn-remove-unit-row"><i class="bi bi-x-circle-fill"></i></button>
                    </div>
                </div>
            `;
            conversionList.insertAdjacentHTML('beforeend', cHtml);
            unitIndex++;
        });

        editNewVariantIndex++;
    }

    // ==========================================
    // Hàm bổ trợ: Lọc danh mục con cho MODAL SỬA
    // ==========================================
    function filterEditChildCategories(parentId, selectedChildId = '') {
        const editChildSelect = document.getElementById('edit-child-category-select');
        if (!editChildSelect) return;

        editChildSelect.innerHTML = '<option value="">-- Chọn danh mục con --</option>';
        if (parentId) {
            const filtered = allChildCategories.filter(cate => cate.parent_id == parentId);
            if (filtered.length > 0) {
                filtered.forEach(child => {
                    const selected = child.id == selectedChildId ? 'selected' : '';
                    editChildSelect.innerHTML += `<option value="${child.id}" ${selected}>${child.name}</option>`;
                });
                editChildSelect.disabled = false;
            } else {
                editChildSelect.innerHTML = '<option value="">(Không có danh mục con)</option>';
                editChildSelect.disabled = true;
            }
        } else {
            editChildSelect.disabled = true;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const parentSelect = document.getElementById('parent-category-select');
        const childSelect = document.getElementById('child-category-select');
        const btnAddVariant = document.getElementById('btn-add-variant');
        const variantsContainer = document.getElementById('variants-container');

        const btnAddVariantEdit = document.getElementById('btn-add-variant-edit');
        const editNewVariantsContainer = document.getElementById('edit-new-variants-container');
        const editVariantsContainer = document.getElementById('edit-variants-container');

        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
        var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl)
        });

        const formFormEl = document.getElementById('form-add-product');
        const allErrors = formFormEl && formFormEl.getAttribute('data-errors') ? JSON.parse(formFormEl.getAttribute('data-errors')) : {};
        const oldVariants = formFormEl && formFormEl.getAttribute('data-old') ? JSON.parse(formFormEl.getAttribute('data-old')) : null;

        if (variantsContainer) {
            variantsContainer.innerHTML = '';
            variantIndex = 0;

            if (oldVariants && Object.keys(oldVariants).length > 0) {
                Object.values(oldVariants).forEach(variant => {
                    createVariantRow(variant);
                });
            } else {
                createVariantRow();
            }
        }

        if (btnAddVariant) {
            btnAddVariant.addEventListener('click', function() {
                createVariantRow();
            });
        }

        if (variantsContainer) {
            variantsContainer.addEventListener('click', function(e) {
                if (e.target.closest('.btn-remove-variant')) {
                    e.target.closest('.variant-block-item').remove();
                }
                if (e.target.closest('.btn-remove-unit-row')) {
                    e.target.closest('.alignment-unit-row').remove();
                }
            });
        }

        // ==========================================
        // SỰ KIỆN LIÊN QUAN ĐẾN MODAL SỬA (EDIT)
        // ==========================================
        if (btnAddVariantEdit) {
            btnAddVariantEdit.addEventListener('click', function() {
                createNewVariantRowForEdit();
            });
        }

        if (editNewVariantsContainer) {
            editNewVariantsContainer.addEventListener('click', function(e) {
                if (e.target.closest('.btn-remove-new-edit-variant')) {
                    e.target.closest('.edit-new-variant-block').remove();
                }
                if (e.target.closest('.btn-remove-unit-row')) {
                    e.target.closest('.alignment-unit-row').remove();
                }
            });
        }

        // Xử lý bấm thêm dòng quy đổi phụ trực tiếp cho BIẾN THỂ CŨ
        if (editVariantsContainer) {
            editVariantsContainer.addEventListener('click', function(e) {
                if (e.target.closest('.btn-remove-unit-row')) {
                    e.target.closest('.alignment-unit-row').remove();
                    return;
                }

                const btnAddConvOld = e.target.closest('.btn-add-conversion-old');
                if (btnAddConvOld) {
                    const vIndex = btnAddConvOld.getAttribute('data-variant-index');
                    const conversionList = editVariantsContainer.querySelector(`.edit-conversion-units-list[data-variant-index="${vIndex}"]`);

                    const uIndex = conversionList.querySelectorAll('.alignment-unit-row').length;

                    const cHtml = `
                        <div class="row g-2 mb-2 alignment-unit-row">
                            <div class="col-4">
                                <input type="text" class="form-control form-control-sm" name="variants[${vIndex}][conversions][${uIndex}][unit_name]" placeholder="Thùng, lốc..." required>
                            </div>
                            <div class="col-3">
                                <input type="number" class="form-control form-control-sm" name="variants[${vIndex}][conversions][${uIndex}][conversion_rate]" placeholder="Tỷ lệ" min="2" required>
                            </div>
                            <div class="col-4">
                                <input type="number" class="form-control form-control-sm" name="variants[${vIndex}][conversions][${uIndex}][sale_price]" placeholder="Giá bán" min="0" required>
                            </div>
                            <div class="col-1 d-flex align-items-center justify-content-center">
                                <button type="button" class="btn btn-sm text-danger p-0 btn-remove-unit-row"><i class="bi bi-x-circle-fill"></i></button>
                            </div>
                        </div>
                    `;
                    conversionList.insertAdjacentHTML('beforeend', cHtml);
                }
            });
        }

        // XỬ LÝ ĐỔ DỮ LIỆU ĐA BIẾN THỂ VÀO MODAL SỬA KHI CLICK LỚP CŨ
        document.querySelectorAll('.btn-edit-product').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const parentId = this.getAttribute('data-parent-id');
                const childId = this.getAttribute('data-child-id');
                const imageSrc = this.getAttribute('data-image');
                const productVariants = JSON.parse(this.getAttribute('data-variants') || '[]');

                document.getElementById('form-edit-product').setAttribute('action', '/admin/san-pham/' + id);
                document.getElementById('edit-name').value = name;
                document.getElementById('remove-current-image').value = "0";

                const editParentSelect = document.getElementById('edit-parent-category-select');
                if (editParentSelect) {
                    editParentSelect.value = parentId;
                }
                filterEditChildCategories(parentId, childId);

                if (editNewVariantsContainer) {
                    editNewVariantsContainer.innerHTML = '';
                    editNewVariantIndex = 0;
                }

                const editPreviewContainer = document.getElementById('edit-image-preview-container');
                const editPreviewImg = document.getElementById('edit-image-preview');
                if (imageSrc) {
                    editPreviewImg.setAttribute('src', imageSrc);
                    editPreviewContainer.classList.remove('d-none');
                } else {
                    editPreviewImg.setAttribute('src', '#');
                    editPreviewContainer.classList.add('d-none');
                }

                if (!editVariantsContainer) return;
                editVariantsContainer.innerHTML = '';
                let editVIndex = 0;

                productVariants.forEach(variant => {
                    const baseUnitObj = variant.units.find(u => u.is_base == 1) || {};
                    const extraUnits = variant.units.filter(u => u.is_base == 0) || [];
                    let editUIndex = 0;

                    let variantHtml = `
                    <div class="card card-body border border-light shadow-sm mb-4 edit-variant-block-item" style="border-radius: 12px; background-color: #f8f9fa;">
                        <div class="fw-bold text-secondary mb-3 border-bottom pb-2" style="font-size: 14px;">
                            <i class="bi bi-tag-fill me-1 text-warning"></i>Biến thể chỉnh sửa #${editVIndex + 1}
                        </div>
                        <input type="hidden" name="variants[${editVIndex}][id]" value="${variant.id}">
                        
                        <div class="row g-2 mb-3">
                            <div class="col-md-5">
                                <label class="form-label small fw-semibold">Tên thuộc tính / Dung tích</label>
                                <input type="text" class="form-control form-control-sm" name="variants[${editVIndex}][variant_name]" value="${variant.variant_name}" required>
                            </div>
                            <div class="col-4">
                                <label class="form-label small fw-semibold">Mã vạch (Barcode)</label>
                                <input type="text" class="form-control form-control-sm" name="variants[${editVIndex}][barcode]" value="${variant.barcode || ''}">
                            </div>
                            <div class="col-3">
                                <label class="form-label small fw-semibold">Tồn kho hiện tại</label>
                                <input type="number" class="form-control form-control-sm" name="variants[${editVIndex}][stock_quantity]" value="${baseUnitObj.stock_quantity || 0}" min="0" required>
                            </div>
                        </div>

                        <div class="row g-2 mb-3 bg-white p-2 border-start border-primary border-3" style="border-radius: 6px;">
                            <div class="col-4">
                                <label class="form-label small fw-semibold text-primary">Đơn vị gốc</label>
                                <input type="text" class="form-control form-control-sm" name="variants[${editVIndex}][base_unit]" value="${baseUnitObj.unit_name || ''}" required>
                            </div>
                            <div class="col-4">
                                <label class="form-label small fw-semibold text-primary">Giá nhập gốc (đ)</label>
                                <input type="number" class="form-control form-control-sm" name="variants[${editVIndex}][import_price]" value="${parseInt(baseUnitObj.import_price) || 0}" min="0" required>
                            </div>
                            <div class="col-4">
                                <label class="form-label small fw-semibold text-primary">Giá bán lẻ gốc (đ)</label>
                                <input type="number" class="form-control form-control-sm" name="variants[${editVIndex}][sale_price]" value="${parseInt(baseUnitObj.sale_price) || 0}" min="0" required>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small fw-bold text-muted"><i class="bi bi-arrow-left-right me-1"></i>Đơn vị quy đổi phụ (nếu có)</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary border-0 px-2 py-0 btn-add-conversion-old" data-variant-index="${editVIndex}" style="font-size: 12px;">
                                <i class="bi bi-plus-lg"></i> Thêm quy đổi
                            </button>
                        </div>

                        <div class="edit-conversion-units-list" data-variant-index="${editVIndex}"></div>
                    </div>
                    `;

                    editVariantsContainer.insertAdjacentHTML('beforeend', variantHtml);
                    const currentBlock = editVariantsContainer.querySelectorAll('.edit-variant-block-item')[editVIndex];
                    const conversionList = currentBlock.querySelector('.edit-conversion-units-list');

                    extraUnits.forEach(unit => {
                        const cHtml = `
                        <div class="row g-2 mb-2 alignment-unit-row">
                            <div class="col-4">
                                <input type="text" class="form-control form-control-sm" name="variants[${editVIndex}][conversions][${editUIndex}][unit_name]" value="${unit.unit_name}" required>
                            </div>
                            <div class="col-3">
                                <input type="number" class="form-control form-control-sm" name="variants[${editVIndex}][conversions][${editUIndex}][conversion_rate]" value="${unit.conversion_rate}" min="2" required>
                            </div>
                            <div class="col-4">
                                <input type="number" class="form-control form-control-sm" name="variants[${editVIndex}][conversions][${editUIndex}][sale_price]" value="${parseInt(unit.sale_price)}" min="0" required>
                            </div>
                            <div class="col-1 d-flex align-items-center justify-content-center">
                                <button type="button" class="btn btn-sm text-danger p-0 btn-remove-unit-row"><i class="bi bi-x-circle-fill"></i></button>
                            </div>
                        </div>
                        `;
                        conversionList.insertAdjacentHTML('beforeend', cHtml);
                        editUIndex++;
                    });

                    editVIndex++;
                });

                const editModal = new bootstrap.Modal(document.getElementById('editProductModal'));
                editModal.show();
            });
        });

        // GIỮ NGUYÊN CÁC LOGIC PHỤ TRỢ KHÁC
        const oldParentId = formFormEl && formFormEl.getAttribute('data-old-parent');
        const oldChildId = formFormEl && formFormEl.getAttribute('data-old-child');
        if (oldParentId) {
            parentSelect.value = oldParentId;
            childSelect.innerHTML = '<option value="">-- Chọn danh mục con --</option>';
            const filtered = allChildCategories.filter(cate => cate.parent_id == oldParentId);
            if (filtered.length > 0) {
                filtered.forEach(child => {
                    const isSelected = child.id == oldChildId ? 'selected' : '';
                    childSelect.innerHTML += `<option value="${child.id}" ${isSelected}>${child.name}</option>`;
                });
                childSelect.disabled = false;
                childSelect.required = true;
            }
        }

        if (Object.keys(allErrors).length > 0) {
            const addModalEl = document.getElementById('addProductModal');
            if (addModalEl) {
                const addModal = new bootstrap.Modal(addModalEl);
                addModal.show();
            }
        }

        const filterParent = document.getElementById('filter-parent-category');
        const searchChildInput = document.getElementById('search-child-input');
        const filterChildId = document.getElementById('filter-child-id');
        const suggestionsBox = document.getElementById('child-suggestions-box');
        let currentParentId = filterParent.value;
        let selectedChildId = filterChildId.value;

        if (selectedChildId && currentParentId) {
            const found = allChildCategories.find(c => c.id == selectedChildId);
            if (found) searchChildInput.value = found.name;
        }

        filterParent.addEventListener('change', function() {
            currentParentId = this.value;
            searchChildInput.value = '';
            filterChildId.value = '';
            suggestionsBox.style.display = 'none';
            if (currentParentId === '') {
                searchChildInput.disabled = true;
                searchChildInput.placeholder = "Chọn cha trước...";
            } else {
                searchChildInput.disabled = false;
                searchChildInput.placeholder = "Gõ từ khóa để tìm mục con...";
                searchChildInput.focus();
            }
        });

        searchChildInput.addEventListener('input', function() {
            const keyword = this.value.toLowerCase().trim();
            suggestionsBox.innerHTML = '';
            const matchedChildren = allChildCategories.filter(cate =>
                cate.parent_id == currentParentId && cate.name.toLowerCase().includes(keyword)
            );
            if (matchedChildren.length > 0) {
                matchedChildren.forEach(child => {
                    const itemHtml = `<button type="button" class="dropdown-item py-2 text-start small-suggestion-item" data-id="${child.id}" data-name="${child.name}" style="font-size: 13px;"><i class="bi bi-file-earmark-text text-muted me-2"></i>${child.name}</button>`;
                    suggestionsBox.insertAdjacentHTML('beforeend', itemHtml);
                });
                suggestionsBox.style.display = 'block';
            } else {
                suggestionsBox.innerHTML = '<div class="text-muted p-2 small text-center">❌ Không thấy mục con</div>';
                suggestionsBox.style.display = 'block';
            }
        });

        suggestionsBox.addEventListener('click', function(e) {
            const clickedBtn = e.target.closest('.small-suggestion-item');
            if (clickedBtn) {
                searchChildInput.value = clickedBtn.getAttribute('data-name');
                filterChildId.value = clickedBtn.getAttribute('data-id');
                suggestionsBox.style.display = 'none';
            }
        });

        document.addEventListener('click', function(e) {
            if (!searchChildInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                suggestionsBox.style.display = 'none';
            }
        });

        if (parentSelect && childSelect) {
            parentSelect.addEventListener('change', function() {
                const parentId = this.value;
                childSelect.innerHTML = '<option value="">-- Chọn danh mục con --</option>';
                if (parentId === "") {
                    childSelect.disabled = true;
                    childSelect.required = false;
                } else {
                    const filtered = allChildCategories.filter(cate => cate.parent_id == parentId);
                    if (filtered.length > 0) {
                        filtered.forEach(child => {
                            childSelect.innerHTML += `<option value="${child.id}">${child.name}</option>`;
                        });
                        childSelect.disabled = false;
                        childSelect.required = true;
                    } else {
                        childSelect.innerHTML = '<option value="">(Không có danh mục con)</option>';
                        childSelect.disabled = true;
                        childSelect.required = false;
                    }
                }
            });
        }

        const editParentSelect = document.getElementById('edit-parent-category-select');
        if (editParentSelect) {
            editParentSelect.addEventListener('change', function() {
                filterEditChildCategories(this.value);
            });
        }

        const editImageInput = document.getElementById('edit-product-image-input');
        if (editImageInput) {
            editImageInput.addEventListener('change', function() {
                const file = this.files[0];
                const editPreviewContainer = document.getElementById('edit-image-preview-container');
                const editPreviewImg = document.getElementById('edit-image-preview');
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        editPreviewImg.setAttribute('src', e.target.result);
                        editPreviewContainer.classList.remove('d-none');
                        document.getElementById('remove-current-image').value = "0";
                    }
                    reader.readAsDataURL(file);
                }
            });
        }

        const btnEditRemovePreview = document.getElementById('btn-edit-remove-preview');
        if (btnEditRemovePreview) {
            btnEditRemovePreview.addEventListener('click', function(e) {
                e.preventDefault();
                editImageInput.value = "";
                document.getElementById('edit-image-preview').setAttribute('src', '#');
                document.getElementById('edit-image-preview-container').classList.add('d-none');
                document.getElementById('remove-current-image').value = "1";
            });
        }

        document.querySelectorAll('.btn-delete-product').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                var id = this.getAttribute('data-id');
                Swal.fire({
                    title: 'Xác nhận xóa?',
                    text: "Sản phẩm này sẽ bị ẩn đi khỏi hệ thống bán hàng!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Đồng ý xóa!',
                    cancelButtonText: 'Hủy'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('delete-form-' + id).submit();
                    }
                });
            });
        });
    });
</script>
@endsection