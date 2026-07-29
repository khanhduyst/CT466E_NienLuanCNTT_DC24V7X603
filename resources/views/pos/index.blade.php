@extends('layouts.app')

@section('content')
<style>
    .pos-card-product {
        transition: all 0.2s ease;
        cursor: pointer;
        border: 1px solid #e9ecef;
    }
    .pos-card-product:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.08) !important;
        border-color: #0d6efd;
    }
    .cart-table-wrapper {
        max-height: calc(100vh - 420px);
        min-height: 220px;
        overflow-y: auto;
    }
    .category-badge-btn {
        cursor: pointer;
        white-space: nowrap;
        transition: all 0.2s;
        font-size: 12px;
    }
    .customer-suggestion-item:hover {
        background-color: #e9ecef;
        cursor: pointer;
    }
    ::-webkit-scrollbar {
        width: 5px;
        height: 5px;
    }
    ::-webkit-scrollbar-thumb {
        background: #ccc;
        border-radius: 4px;
    }
</style>

<div class="container-fluid py-2 bg-light overflow-hidden" style="min-height: 90vh;">
    <div class="row g-2">
        <div class="col-lg-7 col-xl-8 d-flex flex-column">
            <div class="card border-0 shadow-sm mb-2 rounded-3">
                <div class="card-body p-2">
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white border-end-0 text-primary"><i class="bi bi-search"></i></span>
                                <input type="text" id="search-product" class="form-control border-start-0 ps-0 fw-semibold" placeholder="Tìm tên sản phẩm hoặc Quét mã vạch (Barcode)..." autofocus>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex gap-1 overflow-x-auto pb-1" id="category-filter-list" style="white-space: nowrap;">
                        <button class="btn btn-xs btn-primary category-badge-btn active fw-bold px-2 py-1" data-category-id="all">Tất cả</button>
                        @foreach($categories as $cate)
                        <button class="btn btn-xs btn-outline-secondary category-badge-btn fw-semibold px-2 py-1" data-category-id="{{ $cate->id }}">{{ $cate->name }}</button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="row g-2 overflow-y-auto align-content-start flex-grow-1" id="pos-product-grid" style="max-height: calc(100vh - 160px);">
                @foreach($products as $p)
                    @php
                        $firstVariant = $p->variants->first();
                        $baseUnit = $firstVariant ? $firstVariant->units->where('is_base', 1)->first() : null;
                        $allBarcodes = $p->variants->pluck('barcode')->filter()->implode(' ');
                        $allVariantNames = $p->variants->pluck('variant_name')->implode(' ');
                    @endphp
                    <div class="col-6 col-sm-4 col-md-3 col-xl-2-4 pos-product-item" 
                         style="flex: 0 0 auto; width: 20%;"
                         data-name="{{ strtolower($p->name . ' ' . $allVariantNames . ' ' . $allBarcodes) }}"
                         data-category="{{ $p->category_id }}"
                         data-parent-category="{{ $p->category->parent_id ?? $p->category_id }}">
                        <div class="card h-100 pos-card-product rounded-3 bg-white p-2 btn-select-product" 
                             data-product-id="{{ $p->id }}"
                             data-product-name="{{ $p->name }}"
                             data-variants='@json($p->variants->load("units"))'>
                            <div class="position-relative text-center mb-1">
                                @if($p->image && file_exists(public_path('uploads/products/' . $p->image)))
                                    <img src="{{ asset('uploads/products/' . $p->image) }}" class="rounded-2 object-fit-cover w-100" style="height: 75px;">
                                @else
                                    <div class="bg-light rounded-2 d-flex align-items-center justify-content-center text-muted" style="height: 75px;">
                                        <i class="bi bi-box-seam fs-3"></i>
                                    </div>
                                @endif
                                <span class="position-absolute top-0 end-0 badge bg-primary opacity-90 m-1 rounded-pill" style="font-size: 9px;">
                                    {{ $p->variants->count() }} BT
                                </span>
                            </div>
                            <div class="fw-bold text-dark text-truncate small mb-0" title="{{ $p->name }}" style="font-size: 12px;">{{ $p->name }}</div>
                            <div class="text-secondary text-truncate mb-1" style="font-size: 10px;">
                                {{ $firstVariant->variant_name ?? 'Mặc định' }}
                            </div>
                            <div class="fw-bold text-danger" style="font-size: 13px;">
                                {{ number_format($baseUnit->sale_price ?? 0) }}đ
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="col-lg-5 col-xl-4">
            <div class="card border-0 shadow-sm rounded-3 h-100 d-flex flex-column">
                <div class="card-header bg-white border-bottom-0 pt-2 px-2 pb-0">
                    <div class="position-relative mb-2">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white text-secondary border-end-0"><i class="bi bi-person-search"></i></span>
                            <input type="text" id="input-search-customer" class="form-control border-start-0 ps-0 fw-semibold" placeholder="Gõ tên hoặc SĐT..." autocomplete="off">
                            <button class="btn btn-outline-danger btn-sm d-none" type="button" id="btn-clear-customer" title="Bỏ chọn"><i class="bi bi-x-lg"></i></button>
                            <button class="btn btn-primary btn-sm fw-bold px-2" type="button" data-bs-toggle="modal" data-bs-target="#quickCustomerModal" title="Thêm khách hàng">
                                <i class="bi bi-person-plus-fill"></i> Thêm
                            </button>
                        </div>
                        <input type="hidden" id="selected-customer-id" value="">
                        
                        <div class="dropdown-menu w-100 shadow border-0 p-0 mt-1" id="customer-suggestions-box" style="display: none; max-height: 200px; overflow-y: auto; z-index: 1050;"></div>
                    </div>

                    <div id="customer-info-box" class="d-none bg-primary-subtle p-2 rounded-2 text-primary d-flex justify-content-between small fw-bold mb-2" style="font-size: 11px;">
                        <span><i class="bi bi-star-fill text-warning me-1"></i>Điểm: <span id="cust-points">0</span> pt</span>
                        <span><i class="bi bi-wallet2 text-danger me-1"></i>Nợ: <span id="cust-debt">0</span> đ</span>
                    </div>
                </div>

                <div class="card-body p-0 cart-table-wrapper">
                    <table class="table table-hover align-middle mb-0" id="cart-table">
                        <thead class="table-light small">
                            <tr>
                                <th class="ps-2 py-1" style="font-size: 11px;">Sản phẩm</th>
                                <th style="width: 55px; font-size: 11px;">ĐVT</th>
                                <th style="width: 75px; font-size: 11px;">SL</th>
                                <th class="text-end pe-2" style="font-size: 11px;">Thành tiền</th>
                            </tr>
                        </thead>
                        <tbody id="cart-body">
                            <tr id="empty-cart-msg">
                                <td colspan="4" class="text-center py-4 text-muted small">
                                    <i class="bi bi-cart-x fs-2 d-block mb-1 text-secondary"></i>Chưa có sản phẩm nào
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white border-top p-2 mt-auto">
                    <div class="d-flex justify-content-between mb-1 small fw-semibold">
                        <span class="text-muted">Tổng tiền hàng:</span>
                        <span class="fw-bold text-dark" id="txt-subtotal">0 đ</span>
                    </div>

                    <div class="row g-1 mb-2">
                        <div class="col-12">
                            <input type="number" id="input-discount" class="form-control form-control-sm" placeholder="Giảm giá (đ)" min="0">
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch bg-light p-1 rounded-2 border ps-4 style-pointer" style="font-size: 11px;">
                                <input class="form-check-input ms-n3" type="checkbox" id="chk-use-points" disabled>
                                <label class="form-check-input-label fw-bold text-dark d-flex justify-content-between w-100 pe-1" for="chk-use-points">
                                    <span><i class="bi bi-stars text-warning me-1"></i>Đổi điểm</span>
                                    <span class="text-danger fw-bold" id="lbl-point-discount-val">-0 đ</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mb-2 align-items-center">
                        <span class="fw-bold text-dark small">KHÁCH PHẢI TRẢ:</span>
                        <span class="fw-bold text-danger fs-5" id="txt-final-total">0 đ</span>
                    </div>

                    <div class="mb-2">
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="payment_method" id="pay-cash" value="cash" checked>
                            <label class="btn btn-outline-primary btn-sm fw-bold py-1" for="pay-cash" style="font-size: 12px;"><i class="bi bi-cash-stack me-1"></i>Tiền mặt</label>

                            <input type="radio" class="btn-check" name="payment_method" id="pay-transfer" value="transfer">
                            <label class="btn btn-outline-primary btn-sm fw-bold py-1" for="pay-transfer" style="font-size: 12px;"><i class="bi bi-qr-code-scan me-1"></i>Chuyển khoản</label>
                        </div>
                    </div>

                    <div class="mb-2">
                        <input type="number" id="input-paid-amount" class="form-control form-control-sm fw-bold text-success fs-6" placeholder="Tiền khách đưa (đ)">
                        <div class="d-flex justify-content-between mt-1 small fw-bold" id="box-change-debt" style="font-size: 11px;">
                            <span class="text-muted" id="label-change-debt">Tiền thừa trả khách:</span>
                            <span class="text-primary fs-6" id="txt-change-amount">0 đ</span>
                        </div>
                    </div>

                    <button class="btn btn-success btn-md w-100 fw-bold py-2 rounded-3 shadow-sm" id="btn-submit-order" onclick="submitOrder()">
                        <i class="bi bi-printer-fill me-1"></i>THANH TOÁN & IN HÓA ĐƠN
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="productOptionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0">
            <div class="modal-header bg-dark text-white py-2 px-3">
                <h6 class="modal-title fw-bold" id="productModalTitle">Tên sản phẩm</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <label class="form-label small fw-bold text-muted mb-2"><i class="bi bi-layers me-1"></i>1. Chọn biến thể / Thuộc tính:</label>
                <div class="d-flex flex-wrap gap-2 mb-3" id="variant-btn-group"></div>

                <hr class="my-2">

                <label class="form-label small fw-bold text-muted mb-2"><i class="bi bi-box-seam me-1"></i>2. Chọn đơn vị tính:</label>
                <div class="d-flex flex-column gap-2" id="unit-btn-group"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="quickCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-2 px-3">
                <h6 class="modal-title fw-bold mb-0"><i class="bi bi-person-plus-fill me-1"></i>Thêm khách hàng nhanh</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3 bg-light">
                <div class="mb-2">
                    <label class="form-label small fw-bold text-secondary mb-1">Họ và tên <span class="text-danger">*</span></label>
                    <input type="text" id="quick-cust-name" class="form-control form-control-sm" placeholder="Nhập tên khách hàng...">
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-bold text-secondary mb-1">Số điện thoại <span class="text-danger">*</span></label>
                    <input type="text" id="quick-cust-phone" class="form-control form-control-sm" placeholder="Nhập số điện thoại...">
                </div>
                <div class="mb-2">
                    <label class="form-label small fw-bold text-secondary mb-1">Barcode (Tùy chọn)</label>
                    <input type="text" id="quick-cust-barcode" class="form-control form-control-sm" placeholder="Để trống tự động tạo mã...">
                </div>
            </div>
            <div class="modal-footer bg-white border-top-0 py-2 px-3">
                <button type="button" class="btn btn-sm btn-secondary fw-semibold" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-sm btn-primary fw-bold" onclick="saveQuickCustomer()">Lưu & Chọn ngay</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let cart = [];
let currentProduct = null;
let currentSelectedVariant = null;
let currentCustomerPoints = 0;
let minOrderAmountForRedeem = Number("{{ $minOrderAmountForRedeem }}") || 30000;
let maxPointDiscountPercent = Number("{{ $maxPointDiscountPercent }}") || 50;
let allCustomersList = JSON.parse('{!! json_encode($customers) !!}');

$(document).on('click', '.btn-select-product', function() {
    let productId = $(this).data('product-id');
    let productName = $(this).data('product-name');
    let variants = $(this).data('variants');

    if(!variants || variants.length === 0) return;

    currentProduct = { id: productId, name: productName, variants: variants };
    $('#productModalTitle').text(productName);

    let variantHtml = '';
    variants.forEach((v, idx) => {
        let activeClass = idx === 0 ? 'btn-primary active' : 'btn-outline-primary';
        variantHtml += `
            <button class="btn btn-sm ${activeClass} fw-bold btn-variant-choice" data-variant-idx="${idx}">
                ${v.variant_name}
            </button>
        `;
    });
    $('#variant-btn-group').html(variantHtml);

    selectVariant(0);
    $('#productOptionsModal').modal('show');
});

$(document).on('click', '.btn-variant-choice', function() {
    $('.btn-variant-choice').removeClass('btn-primary active').addClass('btn-outline-primary');
    $(this).removeClass('btn-outline-primary').addClass('btn-primary active');

    let idx = $(this).data('variant-idx');
    selectVariant(idx);
});

function selectVariant(variantIndex) {
    currentSelectedVariant = currentProduct.variants[variantIndex];
    let units = currentSelectedVariant.units;

    let unitHtml = '';
    units.forEach((u) => {
        unitHtml += `
            <button class="btn btn-outline-success text-start d-flex justify-content-between align-items-center py-2 px-3 rounded-3 btn-unit-choice" 
                    data-unit-id="${u.id}"
                    data-unit-name="${u.unit_name}"
                    data-sale-price="${u.sale_price}">
                <span class="fw-bold fs-6">${u.unit_name}</span>
                <span class="fw-bold text-danger fs-6">${Number(u.sale_price).toLocaleString()} đ</span>
            </button>
        `;
    });
    $('#unit-btn-group').html(unitHtml);
}

$(document).on('click', '.btn-unit-choice', function() {
    let unitId = $(this).data('unit-id');
    let unitName = $(this).data('unit-name');
    let salePrice = $(this).data('sale-price');

    addToCart(
        currentProduct.id, 
        currentProduct.name, 
        currentSelectedVariant.variant_name, 
        currentSelectedVariant.id, 
        unitId, 
        unitName, 
        salePrice
    );

    $('#productOptionsModal').modal('hide');
});

function addToCart(productId, productName, variantName, variantId, unitId, unitName, salePrice) {
    let existIndex = cart.findIndex(item => item.unit_id === unitId);
    if(existIndex > -1) {
        cart[existIndex].quantity += 1;
    } else {
        cart.push({
            product_id: productId,
            variant_id: variantId,
            product_name: productName,
            variant_name: variantName,
            unit_id: unitId,
            unit_name: unitName,
            sale_price: Number(salePrice),
            quantity: 1
        });
    }
    renderCart();
}

function renderCart() {
    if(cart.length === 0) {
        $('#cart-body').html(`
            <tr id="empty-cart-msg">
                <td colspan="4" class="text-center py-4 text-muted small">
                    <i class="bi bi-cart-x fs-2 d-block mb-1 text-secondary"></i>Chưa có sản phẩm nào
                </td>
            </tr>
        `);
        updateCalculations();
        return;
    }

    let html = '';
    cart.forEach((item, index) => {
        let itemTotal = item.sale_price * item.quantity;
        html += `
            <tr>
                <td class="ps-2 py-1">
                    <div class="fw-bold text-dark small text-truncate" style="max-width: 110px; font-size:11px;">${item.product_name}</div>
                    <span class="text-muted" style="font-size:10px;">${item.variant_name}</span>
                </td>
                <td class="py-1"><span class="badge bg-light text-dark border px-1" style="font-size:10px;">${item.unit_name}</span></td>
                <td class="py-1">
                    <div class="input-group input-group-sm" style="width: 70px;">
                        <button class="btn btn-outline-secondary btn-xs px-1" onclick="updateQty(${index}, -1)">-</button>
                        <input type="text" class="form-control text-center px-0 fw-bold" style="font-size:11px;" value="${item.quantity}" readonly>
                        <button class="btn btn-outline-secondary btn-xs px-1" onclick="updateQty(${index}, 1)">+</button>
                    </div>
                </td>
                <td class="text-end pe-2 py-1 fw-bold text-danger small" style="font-size:11px;">
                    ${itemTotal.toLocaleString()}đ
                    <i class="bi bi-x-circle text-muted ms-1 style-pointer" onclick="removeFromCart(${index})" title="Xóa món"></i>
                </td>
            </tr>
        `;
    });
    $('#cart-body').html(html);
    updateCalculations();
}

function updateQty(index, change) {
    cart[index].quantity += change;
    if(cart[index].quantity <= 0) {
        cart.splice(index, 1);
    }
    renderCart();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    renderCart();
}

function updateCalculations() {
    let subtotal = cart.reduce((sum, item) => sum + (item.sale_price * item.quantity), 0);
    let discount = Number($('#input-discount').val()) || 0;
    let customerId = $('#selected-customer-id').val();

    let maxByPercent = Math.floor(subtotal * (maxPointDiscountPercent / 100));
    let maxAllowedPoints = Math.min(currentCustomerPoints, maxByPercent);

    let chkPoints = $('#chk-use-points');
    
    if (!customerId || subtotal < minOrderAmountForRedeem || currentCustomerPoints <= 0) {
        chkPoints.prop('disabled', true).prop('checked', false);
        $('#lbl-point-discount-val').text('-0 đ');
    } else {
        chkPoints.prop('disabled', false);
    }

    let pointDiscountMoney = 0;
    if (chkPoints.is(':checked')) {
        pointDiscountMoney = maxAllowedPoints;
        $('#lbl-point-discount-val').text(`-${pointDiscountMoney.toLocaleString()} đ (${maxAllowedPoints}pt)`);
    } else {
        $('#lbl-point-discount-val').text(`0 đ (Dùng ${maxAllowedPoints.toLocaleString()}pt)`);
    }

    let finalTotal = Math.max(0, subtotal - discount - pointDiscountMoney);
    let paidAmount = Number($('#input-paid-amount').val()) || 0;

    $('#txt-subtotal').text(subtotal.toLocaleString() + ' đ');
    $('#txt-final-total').text(finalTotal.toLocaleString() + ' đ');

    if (paidAmount >= finalTotal) {
        let change = paidAmount - finalTotal;
        $('#label-change-debt').text('Tiền thừa trả khách:').removeClass('text-danger').addClass('text-muted');
        $('#txt-change-amount').text(change.toLocaleString() + ' đ').removeClass('text-danger').addClass('text-primary');
    } else {
        let debt = finalTotal - paidAmount;
        $('#label-change-debt').text('Khách còn thiếu (Nợ):').removeClass('text-muted').addClass('text-danger');
        $('#txt-change-amount').text(debt.toLocaleString() + ' đ').removeClass('text-primary').addClass('text-danger');
    }
}

$('#chk-use-points').on('change', updateCalculations);
$('#input-discount, #input-paid-amount').on('input', updateCalculations);

$('#input-search-customer').on('input', function() {
    let kw = $(this).val().toLowerCase().trim();
    let suggestionsBox = $('#customer-suggestions-box');

    if (kw === '') {
        suggestionsBox.hide().empty();
        return;
    }

    suggestionsBox.empty();

    let matched = allCustomersList.filter(c => 
        (c.name && c.name.toLowerCase().includes(kw)) || 
        (c.phone_number && c.phone_number.includes(kw))
    );

    if (matched.length > 0) {
        matched.forEach(c => {
            let pts = Number(c.current_points || 0);
            let itemHtml = `
                <div class="p-2 border-bottom customer-suggestion-item" data-id="${c.id}">
                    <div class="fw-bold text-dark small">👤 ${c.name}</div>
                    <div class="text-muted d-flex justify-content-between" style="font-size: 11px;">
                        <span>📞 ${c.phone_number}</span>
                        <span>⭐ ${pts.toLocaleString()} pt | Nợ: ${Number(c.total_debt || 0).toLocaleString()}đ</span>
                    </div>
                </div>
            `;
            suggestionsBox.append(itemHtml);
        });
        suggestionsBox.show();
    } else {
        suggestionsBox.html('<div class="p-2 text-muted small text-center">Không tìm thấy khách hàng nào</div>').show();
    }
});

$(document).on('click', '.customer-suggestion-item', function() {
    let id = $(this).data('id');
    let customer = allCustomersList.find(c => c.id == id);

    if(customer) {
        selectCustomerObj(customer);
    }
    $('#customer-suggestions-box').hide();
});

$(document).on('click', function(e) {
    if(!$(e.target).closest('#input-search-customer, #customer-suggestions-box').length) {
        $('#customer-suggestions-box').hide();
    }
});

function selectCustomerObj(customer) {
    $('#selected-customer-id').val(customer.id);
    $('#input-search-customer').val(`👤 ${customer.name} - ${customer.phone_number}`).prop('readonly', true);
    $('#btn-clear-customer').removeClass('d-none');

    currentCustomerPoints = Number(customer.current_points || 0);

    $('#cust-points').text(currentCustomerPoints.toLocaleString());
    $('#cust-debt').text(Number(customer.total_debt || 0).toLocaleString());
    $('#customer-info-box').removeClass('d-none');

    updateCalculations();
}

$('#btn-clear-customer').on('click', function() {
    $('#selected-customer-id').val('');
    $('#input-search-customer').val('').prop('readonly', false);
    $(this).addClass('d-none');
    $('#customer-info-box').addClass('d-none');
    currentCustomerPoints = 0;
    $('#chk-use-points').prop('checked', false).prop('disabled', true);
    updateCalculations();
});

function saveQuickCustomer() {
    let name = $('#quick-cust-name').val().trim();
    let phone = $('#quick-cust-phone').val().trim();
    let barcode = $('#quick-cust-barcode').val().trim();

    if (!name || !phone) {
        Swal.fire('Chú ý', 'Vui lòng điền tên và số điện thoại!', 'warning');
        return;
    }

    $.ajax({
        url: "{{ route('admin.pos.quick_customer') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            name: name,
            phone_number: phone,
            barcode: barcode
        },
        success: function(res) {
            if (res.success) {
                let c = res.customer;
                allCustomersList.push(c);
                selectCustomerObj(c);

                $('#quickCustomerModal').modal('hide');
                $('#quick-cust-name, #quick-cust-phone, #quick-cust-barcode').val('');
                Swal.fire('Thành công', 'Đã thêm và chọn khách hàng!', 'success');
            }
        },
        error: function(xhr) {
            Swal.fire('Lỗi', 'Không thể lưu khách hàng!', 'error');
        }
    });
}

$('#search-product').on('input', function() {
    let kw = $(this).val().toLowerCase().trim();
    $('.pos-product-item').each(function() {
        let name = $(this).data('name');
        if(name.includes(kw)) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
});

$('.category-badge-btn').on('click', function() {
    $('.category-badge-btn').removeClass('btn-primary active').addClass('btn-outline-secondary');
    $(this).removeClass('btn-outline-secondary').addClass('btn-primary active');

    let cateId = $(this).data('category-id');
    $('.pos-product-item').each(function() {
        let prodCategory = $(this).data('category');
        let prodParentCategory = $(this).data('parent-category');

        if(cateId === 'all' || prodCategory == cateId || prodParentCategory == cateId) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
});

function submitOrder() {
    if(cart.length === 0) {
        Swal.fire('Chú ý', 'Vui lòng chọn ít nhất 1 sản phẩm!', 'warning');
        return;
    }

    let customerId = $('#selected-customer-id').val();
    let paymentMethod = $('input[name="payment_method"]:checked').val();
    let paidAmount = Number($('#input-paid-amount').val()) || 0;

    let subtotal = cart.reduce((sum, item) => sum + (item.sale_price * item.quantity), 0);
    let discount = Number($('#input-discount').val()) || 0;
    let applyPoints = $('#chk-use-points').is(':checked');

    let maxByPercent = Math.floor(subtotal * (maxPointDiscountPercent / 100));
    let pointDiscountMoney = applyPoints ? Math.min(currentCustomerPoints, maxByPercent) : 0;
    let finalTotal = Math.max(0, subtotal - discount - pointDiscountMoney);

    if (paidAmount < finalTotal && !customerId) {
        Swal.fire('Yêu cầu Khách hàng', 'Khách hàng thanh toán chưa đủ. Bắt buộc chọn Khách hàng để ghi nợ!', 'warning');
        return;
    }

    let data = {
        _token: "{{ csrf_token() }}",
        customer_id: customerId,
        payment_method: paymentMethod,
        discount_amount: discount,
        apply_points: applyPoints,
        paid_amount: paidAmount,
        items: cart
    };

    $('#btn-submit-order').prop('disabled', true).html('Đang xử lý...');

    $.ajax({
        url: "{{ route('admin.pos.checkout') }}",
        type: "POST",
        data: data,
        success: function(res) {
            if(res.success) {
                Swal.fire('Thành công!', `Đã thanh toán hóa đơn #${res.invoice_number}`, 'success').then(() => {
                    cart = [];
                    renderCart();
                    location.reload();
                });
            }
        },
        error: function(xhr) {
            let res = xhr.responseJSON;
            Swal.fire('Lỗi thanh toán', res && res.message ? res.message : 'Kiểm tra lại dữ liệu!', 'error');
        },
        complete: function() {
            $('#btn-submit-order').prop('disabled', false).html('<i class="bi bi-printer-fill me-1"></i>THANH TOÁN & IN HÓA ĐƠN');
        }
    });
}
</script>
@endsection