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
    @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert" style="border-radius: 12px;">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

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
                                @if($product->barcode)
                                <small class="text-muted"><i class="bi bi-qr-code me-1"></i>{{ $product->barcode }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border px-2 py-1">{{ $product->category->name }}</span>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    @foreach($product->units as $unit)
                                    <span style="font-size: 13px;">
                                        <strong class="text-dark">{{ $unit->unit_name }}</strong>:
                                        <span class="text-danger fw-semibold">{{ number_format($unit->sale_price, 0, ',', '.') }}đ</span>
                                        @if(!$unit->is_base)
                                        <small class="text-muted">(X{{ $unit->conversion_rate }})</small>
                                        @endif
                                    </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-sm btn-outline-primary border-0 btn-edit-product"
                                    data-bs-toggle="modal" data-bs-target="#editProductModal"
                                    data-id="{{ $product->id }}"
                                    data-name="{{ $product->name }}"
                                    data-barcode="{{ $product->barcode }}"
                                    data-parent-id="{{ $product->category->parent_id ?? $product->category_id }}"
                                    data-child-id="{{ $product->category->parent_id ? $product->category_id : '' }}"
                                    data-image="{{ $product->image ? asset('uploads/products/' . $product->image) : '' }}"
                                    data-base-unit="{{ $product->units->where('is_base', true)->first()->unit_name ?? '' }}"
                                    data-base-price="{{ $product->units->where('is_base', true)->first()->sale_price ?? 0 }}"
                                    data-units='@json($product->units->where("is_base", false)->values())'>
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
        </div>
    </div>
</div>

<div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header border-bottom-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark" id="addProductModalLabel">
                    <i class="bi bi-plus-circle-fill text-primary me-2"></i>Thêm sản phẩm mới vào hệ thống
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="/admin/san-pham" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body px-4 pb-4 pt-2">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Tên sản phẩm</label>
                                <input type="text" class="form-control" name="name" placeholder="Ví dụ: Nước tương Nhất Ca Tam Thái Tử" required>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Danh mục chính (Cha)</label>
                                    <select class="form-select" id="parent-category-select">
                                        <option value="">-- Chọn danh mục cha --</option>
                                        @foreach($categories->where('parent_id', null) as $parent)
                                        <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Danh mục chi tiết (Con)</label>
                                    <select class="form-select" name="category_id" id="child-category-select" required disabled>
                                        <option value="">-- Chọn danh mục con --</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Mã vạch (Barcode)</label>
                                <input type="text" class="form-control" name="barcode" placeholder="Quét hoặc nhập mã vạch (nếu có)">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Hình ảnh sản phẩm</label>
                                <input type="file" class="form-control" name="image" id="product-image-input" accept="image/*">
                                <div class="mt-2 text-center d-none position-relative w-100" id="image-preview-container">
                                    <div class="position-relative d-inline-block">
                                        <img id="image-preview" src="#" alt="Xem trước ảnh" class="img-thumbnail shadow-sm" style="max-height: 120px; border-radius: 8px;">
                                        <button type="button" class="btn btn-sm btn-danger position-absolute top-0 end-0 translate-middle badge rounded-circle" id="btn-remove-preview" style="padding: 4px 6px; font-size: 10px; z-index: 10;">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 border-start ps-md-4">
                            <h6 class="fw-bold text-success mb-3"><i class="bi bi-calculator-fill me-2"></i>Đơn vị tính cơ bản</h6>
                            <div class="row g-2 mb-4">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Đơn vị gốc</label>
                                    <input type="text" class="form-control" name="base_unit" placeholder="Chai, Lon, Cái..." required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Giá bán lẻ (đ)</label>
                                    <input type="number" class="form-control" name="base_sale_price" placeholder="0" min="0" required>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="fw-bold text-secondary mb-0" style="font-size: 14px;">Đơn vị quy đổi (nếu có)</h6>
                                <button type="button" class="btn btn-sm btn-outline-success border-0 px-2 py-1" id="btn-add-unit">
                                    <i class="bi bi-plus-lg me-1"></i>Thêm quy đổi
                                </button>
                            </div>

                            <div id="conversion-units-container" style="max-height: 220px; overflow-y: auto; padding-right: 5px;"></div>
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
                    <i class="bi bi-pencil-square text-warning me-2"></i>Cập nhật thông tin sản phẩm
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="form-edit-product" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT') <div class="modal-body px-4 pb-4 pt-2">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Tên sản phẩm</label>
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
                                <label class="form-label small fw-semibold">Mã vạch (Barcode)</label>
                                <input type="text" class="form-control" name="barcode" id="edit-barcode">
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

                        <div class="col-md-6 border-start ps-md-4">
                            <h6 class="fw-bold text-success mb-3"><i class="bi bi-calculator-fill me-2"></i>Đơn vị tính cơ bản</h6>
                            <div class="row g-2 mb-4">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Đơn vị gốc</label>
                                    <input type="text" class="form-control" name="base_unit" id="edit-base-unit" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold">Giá bán lẻ (đ)</label>
                                    <input type="number" class="form-control" name="base_sale_price" id="edit-base-sale-price" min="0" required>
                                </div>
                            </div>

                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="fw-bold text-secondary mb-0" style="font-size: 14px;">Đơn vị quy đổi (nếu có)</h6>
                                <button type="button" class="btn btn-sm btn-outline-success border-0 px-2 py-1" id="btn-edit-add-unit">
                                    <i class="bi bi-plus-lg me-1"></i>Thêm quy đổi
                                </button>
                            </div>

                            <div id="edit-conversion-units-container" style="max-height: 220px; overflow-y: auto; padding-right: 5px;"></div>
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

    document.addEventListener('DOMContentLoaded', function() {

        const parentSelect = document.getElementById('parent-category-select');
        const childSelect = document.getElementById('child-category-select');

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


        const imageInput = document.getElementById('product-image-input');
        const previewContainer = document.getElementById('image-preview-container');
        const previewImg = document.getElementById('image-preview');
        const btnRemovePreview = document.getElementById('btn-remove-preview');

        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.setAttribute('src', e.target.result);
                    previewContainer.classList.remove('d-none');
                    previewContainer.classList.add('d-inline-block');
                }
                reader.readAsDataURL(file);
            } else {
                previewContainer.classList.add('d-none');
            }
        });

        btnRemovePreview.addEventListener('click', function(e) {
            e.preventDefault();
            imageInput.value = "";
            previewImg.setAttribute('src', '#');
            previewContainer.classList.add('d-none');
            previewContainer.classList.remove('d-inline-block');
        });


        let unitIndex = 0;
        const container = document.getElementById('conversion-units-container');
        const btnAddUnit = document.getElementById('btn-add-unit');

        btnAddUnit.addEventListener('click', function() {
            const unitHtml = `
                <div class="row g-2 mb-2 alignment-unit-row">
                    <div class="col-4">
                        <input type="text" class="form-control form-control-sm" name="units[${unitIndex}][unit_name]" placeholder="Thùng, lốc..." required>
                    </div>
                    <div class="col-3">
                        <input type="number" class="form-control form-control-sm" name="units[${unitIndex}][conversion_rate]" placeholder="Tỷ lệ" min="2" required>
                    </div>
                    <div class="col-4">
                        <input type="number" class="form-control form-control-sm" name="units[${unitIndex}][sale_price]" placeholder="Giá lẻ" min="0" required>
                    </div>
                    <div class="col-1 d-flex align-items-center justify-content-center">
                        <button type="button" class="btn btn-sm text-danger p-0 btn-remove-unit-row"><i class="bi bi-x-circle-fill"></i></button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', unitHtml);
            unitIndex++;
        });

        container.addEventListener('click', function(e) {
            if (e.target.closest('.btn-remove-unit-row')) {
                e.target.closest('.alignment-unit-row').remove();
            }
        });


        document.querySelectorAll('.btn-delete-product').forEach(function(button) {
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


    // Edit Modal

    const editParentSelect = document.getElementById('edit-parent-category-select');
    const editChildSelect = document.getElementById('edit-child-category-select');
    const editContainer = document.getElementById('edit-conversion-units-container');
    let editUnitIndex = 0;


    function filterEditChildCategories(parentId, selectedChildId = '') {
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

    editParentSelect.addEventListener('change', function() {
        filterEditChildCategories(this.value);
    });


    document.querySelectorAll('.btn-edit-product').forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const name = this.getAttribute('data-name');
            const barcode = this.getAttribute('data-barcode');
            const parentId = this.getAttribute('data-parent-id');
            const childId = this.getAttribute('data-child-id');
            const imageSrc = this.getAttribute('data-image');
            const baseUnit = this.getAttribute('data-base-unit');
            const basePrice = this.getAttribute('data-base-price');
            const extraUnits = JSON.parse(this.getAttribute('data-units') || '[]');


            document.getElementById('form-edit-product').setAttribute('action', '/admin/san-pham/' + id);


            document.getElementById('edit-name').value = name;
            document.getElementById('edit-barcode').value = barcode;
            document.getElementById('edit-base-unit').value = baseUnit;
            document.getElementById('edit-base-sale-price').value = basePrice;
            document.getElementById('remove-current-image').value = "0";


            editParentSelect.value = parentId;
            filterEditChildCategories(parentId, childId);


            const editPreviewContainer = document.getElementById('edit-image-preview-container');
            const editPreviewImg = document.getElementById('edit-image-preview');
            if (imageSrc) {
                editPreviewImg.setAttribute('src', imageSrc);
                editPreviewContainer.classList.remove('d-none');
            } else {
                editPreviewImg.setAttribute('src', '#');
                editPreviewContainer.classList.add('d-none');
            }


            editContainer.innerHTML = '';
            editUnitIndex = 0;


            extraUnits.forEach(unit => {
                const unitHtml = `
                        <div class="row g-2 mb-2 alignment-unit-row">
                            <div class="col-4">
                                <input type="text" class="form-control form-control-sm" name="units[${editUnitIndex}][unit_name]" value="${unit.unit_name}" required>
                            </div>
                            <div class="col-3">
                                <input type="number" class="form-control form-control-sm" name="units[${editUnitIndex}][conversion_rate]" value="${unit.conversion_rate}" min="2" required>
                            </div>
                            <div class="col-4">
                                <input type="number" class="form-control form-control-sm" name="units[${editUnitIndex}][sale_price]" value="${unit.sale_price}" min="0" required>
                            </div>
                            <div class="col-1 d-flex align-items-center justify-content-center">
                                <button type="button" class="btn btn-sm text-danger p-0 btn-remove-unit-row"><i class="bi bi-x-circle-fill"></i></button>
                            </div>
                        </div>
                    `;
                editContainer.insertAdjacentHTML('beforeend', unitHtml);
                editUnitIndex++;
            });
        });
    });


    document.getElementById('btn-edit-add-unit').addEventListener('click', function() {
        const unitHtml = `
                <div class="row g-2 mb-2 alignment-unit-row">
                    <div class="col-4">
                        <input type="text" class="form-control form-control-sm" name="units[${editUnitIndex}][unit_name]" placeholder="Thùng, lốc..." required>
                    </div>
                    <div class="col-3">
                        <input type="number" class="form-control form-control-sm" name="units[${editUnitIndex}][conversion_rate]" placeholder="Tỷ lệ" min="2" required>
                    </div>
                    <div class="col-4">
                        <input type="number" class="form-control form-control-sm" name="units[${editUnitIndex}][sale_price]" placeholder="Giá bán" min="0" required>
                    </div>
                    <div class="col-1 d-flex align-items-center justify-content-center">
                        <button type="button" class="btn btn-sm text-danger p-0 btn-remove-unit-row"><i class="bi bi-x-circle-fill"></i></button>
                    </div>
                </div>
            `;
        editContainer.insertAdjacentHTML('beforeend', unitHtml);
        editUnitIndex++;
    });


    const editImageInput = document.getElementById('edit-product-image-input');
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


    document.getElementById('btn-edit-remove-preview').addEventListener('click', function(e) {
        e.preventDefault();
        editImageInput.value = "";
        document.getElementById('edit-image-preview').setAttribute('src', '#');
        document.getElementById('edit-image-preview-container').add('d-none');
        document.getElementById('remove-current-image').value = "1";
    });
</script>
@endsection