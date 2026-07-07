@extends('layouts.app')

@section('content')
<div class="container-fluid py-3">
    <div class="row">
        <div class="col-md-7">
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-body">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" id="search-input" class="form-control border-start-0 ps-0" placeholder="Nhập tên sản phẩm hoặc mã vạch cần tìm...">
                    </div>
                    <div id="search-results" class="list-group mt-2 position-absolute z-3 shadow" style="display:none; max-height:300px; overflow-y:auto; right: 0; left: 12px; width: calc(100% - 24px) !important;"></div>
                </div>
            </div>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-secondary text-white fw-semibold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-list-stars me-2"></i> Danh sách sản phẩm đã chọn</span>
                    <span class="badge bg-light text-secondary" id="cart-count">0 món</span>
                </div>
                <div class="card-body p-0">
                    <div style="min-height: 350px; max-height: 500px; overflow-y: auto;">
                        <table class="table table-align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Sản phẩm</th>
                                    <th>ĐVT</th>
                                    <th style="width: 120px;" class="text-center">Số lượng</th>
                                    <th>Giá bán</th>
                                    <th>Thành tiền</th>
                                    <th class="text-end pe-3">Xóa</th>
                                </tr>
                            </thead>
                            <tbody id="cart-table-body">
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-cart-x fs-2 d-block mb-2"></i> Chưa có sản phẩm nào trong giỏ
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-primary text-white fw-semibold">
                    <i class="bi bi-person-lines-fill me-2"></i> Thông tin thanh toán
                </div>
                <div class="card-body bg-light d-flex flex-column justify-content-between">
                    <div>
                        <div class="card border-0 bg-white p-3 mb-3 shadow-sm">
                            <div class="mb-2">
                                <label class="form-label small fw-semibold text-primary">Khách hàng:</label>
                                <div class="input-group">
                                    <input type="text" id="customer-search" class="form-control form-control-sm" placeholder="Tìm tên, SĐT, mã khách hàng..." value="Khách vãng lai" autocomplete="off">
                                    <input type="hidden" id="customer_id" value="">
                                    <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="modal" data-bs-target="#addCustomerModal"><i class="bi bi-plus-lg"></i></button>
                                </div>
                                <div id="customer-search-results" class="list-group position-absolute shadow z-3" style="display:none; max-height:200px; overflow-y:auto; width: calc(100% - 48px);"></div>
                            </div>
                            <div class="row g-2 small border-top pt-2 mt-1">
                                <div class="col-6 text-muted">Số điện thoại:</div>
                                <div class="col-6 fw-semibold text-dark text-end" id="lbl-cust-phone">Không có</div>
                                <div class="col-6 text-muted">Mã khách hàng:</div>
                                <div class="col-6 fw-semibold text-dark text-end" id="lbl-cust-barcode">Không có</div>
                                <div class="col-6 text-muted">Nợ cũ hiện tại:</div>
                                <div class="col-6 fw-bold text-danger text-end" id="lbl-cust-debt">0đ</div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between mb-3 fs-5">
                            <span>Tổng tiền hàng:</span>
                            <span id="txt-total-amount" class="fw-bold text-dark">0đ</span>
                        </div>

                        <div class="mb-3 row align-items-center">
                            <label class="col-sm-5 col-form-label">Chiết khấu (đ):</label>
                            <div class="col-sm-7">
                                <input type="number" id="input-discount" class="form-control text-end fw-semibold fs-5 text-danger" value="0" min="0">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mb-3 fs-4 border-top pt-2">
                            <span class="fw-bold text-danger">Khách cần trả:</span>
                            <span id="txt-final-amount" class="fw-bold text-danger">0đ</span>
                        </div>

                        <div class="mb-3 row align-items-center">
                            <label class="col-sm-5 col-form-label">Khách đưa (đ):</label>
                            <div class="col-sm-7">
                                <input type="number" id="input-paid" class="form-control text-end fw-semibold fs-5 text-success" value="0" min="0">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mb-4 fs-5">
                            <span>Tiền thừa trả khách:</span>
                            <span id="txt-change-amount" class="text-success fw-bold">0đ</span>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-semibold">Hình thức thanh toán</label>
                            <select id="payment_method" class="form-select">
                                <option value="cash">💵 Tiền mặt (Cash)</option>
                                <option value="qr_code">📲 Chuyển khoản / QR Code</option>
                                <option value="debt">📝 Ghi nợ (Debt)</option>
                            </select>
                        </div>
                    </div>

                    <button type="button" id="btn-checkout" class="btn btn-primary w-100 py-3 fw-bold fs-5 shadow-sm mt-3">
                        <i class="bi bi-check2-circle me-2"></i> HOÀN THÀNH THANH TOÁN
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-semibold"><i class="bi bi-person-plus me-2"></i> Thêm khách hàng mới</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body bg-light">
                <form id="form-add-customer">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Tên khách hàng <span class="text-danger">*</span></label>
                        <input type="text" id="cust-new-name" class="form-control" required placeholder="Nhập tên khách hàng...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Số điện thoại <span class="text-danger">*</span></label>
                        <input type="text" id="cust-new-phone" class="form-control" required placeholder="Nhập số điện thoại...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Mã khách hàng / Barcode (Tùy chọn)</label>
                        <input type="text" id="cust-new-barcode" class="form-control" placeholder="Để trống hệ thống tự sinh mã...">
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-white">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy bỏ</button>
                <button type="button" id="btn-save-customer" class="btn btn-sm btn-primary">Lưu khách hàng</button>
            </div>
        </div>
    </div>
</div>

<div id="print-section" style="display: none;">
    <div style="width: 80mm; font-family: 'Courier New', Courier, monospace; font-size: 12px; color: #000; padding: 5mm; line-height: 1.4;">
        <div style="text-align: center; margin-bottom: 10px;">
            <h2 style="margin: 0; font-size: 16px; font-weight: bold;">SMART GROCER</h2>
            <p style="margin: 3px 0; font-size: 11px;">Hệ thống POS thông minh</p>
            <p style="margin: 3px 0; font-size: 10px;">ĐC: Cần Thơ, Việt Nam</p>
            <p style="margin: 3px 0; font-size: 10px;">SĐT: 0123.456.789</p>
            <div style="border-top: 1px dashed #000; margin-top: 10px;"></div>
        </div>

        <div style="margin-bottom: 10px; font-size: 11px;">
            <table style="width: 100%;">
                <tr>
                    <td>Mã HĐ:</td>
                    <td style="text-align: right; font-weight: bold;" id="p-invoice-number">---------</td>
                </tr>
                <tr>
                    <td>Ngày:</td>
                    <td style="text-align: right;" id="p-date">--/--/---- --:--</td>
                </tr>
                <tr>
                    <td>Khách hàng:</td>
                    <td style="text-align: right;" id="p-customer-name">Khách vãng lai</td>
                </tr>
                <tr>
                    <td>Thu ngân:</td>
                    <td style="text-align: right;">{{ Auth::user()->name }}</td>
                </tr>
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
            <tbody id="p-items-body"></tbody>
        </table>

        <div style="border-top: 1px dashed #000; margin-top: 10px; padding-top: 5px; font-size: 11px;">
            <table style="width: 100%;">
                <tr>
                    <td>Tổng tiền hàng:</td>
                    <td style="text-align: right;" id="p-total-amount">0đ</td>
                </tr>
                <tr>
                    <td>Chiết khấu:</td>
                    <td style="text-align: right;" id="p-discount">0đ</td>
                </tr>
                <tr style="font-weight: bold; font-size: 13px;">
                    <td>Khách cần trả:</td>
                    <td style="text-align: right;" id="p-final-amount">0đ</td>
                </tr>
                <tr>
                    <td>Khách đưa:</td>
                    <td style="text-align: right;" id="p-paid">0đ</td>
                </tr>
                <tr>
                    <td>Tiền thừa trả:</td>
                    <td style="text-align: right;" id="p-change">0đ</td>
                </tr>
                <tr>
                    <td>Hình thức:</td>
                    <td style="text-align: right; font-weight: bold;" id="p-method">Tiền mặt</td>
                </tr>
            </table>
            <div style="border-top: 1px dashed #000; margin-top: 10px;"></div>
        </div>

        <div style="text-align: center; margin-top: 15px; font-size: 11px;">
            <p style="margin: 0; font-weight: bold;">CẢM ƠN QUÝ KHÁCH QUAY LẠI!</p>
            <p style="margin: 5px 0 0 0; font-size: 9px;">Powered by Smart Grocer 2026</p>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let cart = [];
    let allCustomers = @json($customers);

    $('#customer-search').on('keyup', function() {
        let keyword = $(this).val().toLowerCase().trim();
        if (keyword === 'khách vãng lai' || keyword.length < 1) {
            $('#customer-search-results').hide();
            return;
        }
        let filtered = allCustomers.filter(c =>
            c.name.toLowerCase().includes(keyword) ||
            (c.phone_number && c.phone_number.includes(keyword)) ||
            (c.barcode && c.barcode.toLowerCase().includes(keyword))
        );
        let html = '<button type="button" class="list-group-item list-group-item-action btn-select-customer" data-id="" data-name="Khách vãng lai" data-phone="Không có" data-barcode="Không có" data-debt="0">Khách vãng lai</button>';
        if (filtered.length > 0) {
            filtered.forEach(c => {
                html += `
                <button type="button" class="list-group-item list-group-item-action btn-select-customer"
                    data-id="${c.id}"
                    data-name="${c.name}"
                    data-phone="${c.phone_number ? c.phone_number : 'Không có'}"
                    data-barcode="${c.barcode ? c.barcode : 'Không có'}"
                    data-debt="${c.total_debt ? c.total_debt : 0}">
                    <div class="fw-semibold text-dark">${c.name}</div>
                    <small class="text-muted">${c.phone_number ? c.phone_number : ''} ${c.barcode ? ' | Mã: ' + c.barcode : ''}</small>
                </button>`;
            });
        } else {
            html += '<div class="list-group-item text-muted small">Không tìm thấy khách hàng này</div>';
        }
        $('#customer-search-results').html(html).show();
    });

    $(document).on('click', '.btn-select-customer', function() {
        let id = $(this).data('id');
        let name = $(this).data('name');
        let phone = $(this).data('phone');
        let barcode = $(this).data('barcode');
        let debt = parseFloat($(this).data('debt')) || 0;
        $('#customer_id').val(id);
        $('#customer-search').val(name);
        $('#lbl-cust-phone').text(phone);
        $('#lbl-cust-barcode').text(barcode);
        $('#lbl-cust-debt').text(debt.toLocaleString('vi-VN') + 'đ');
        $('#customer-search-results').hide();
        calculateMoney();
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('#customer-search, #customer-search-results').length) {
            $('#customer-search-results').hide();
        }
    });

    $('#btn-save-customer').on('click', function() {
        let name = $('#cust-new-name').val().trim();
        let phone = $('#cust-new-phone').val().trim();
        let barcode = $('#cust-new-barcode').val().trim();
        if (!name || !phone) {
            Swal.fire({
                icon: 'warning',
                title: 'Chú ý',
                text: 'Vui lòng nhập đầy đủ các trường bắt buộc!',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        $.ajax({
            url: "{{ route('admin.pos.add_customer') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                name: name,
                phone_number: phone,
                barcode: barcode
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                    allCustomers.push(response.customer);
                    $('#customer_id').val(response.customer.id);
                    $('#customer-search').val(response.customer.name);
                    $('#lbl-cust-phone').text(response.customer.phone_number);
                    $('#lbl-cust-barcode').text(response.customer.barcode);
                    $('#lbl-cust-debt').text('0đ');
                    $('#form-add-customer')[0].reset();
                    $('#addCustomerModal').modal('hide');
                    calculateMoney();
                }
            },
            error: function(xhr) {
                let res = xhr.responseJSON;
                Swal.fire({
                    icon: 'error',
                    title: 'Thất bại',
                    text: res && res.message ? res.message : 'Lỗi không thể lưu khách hàng.'
                });
            }
        });
    });

    $('#search-input').on('keyup', function() {
        let keyword = $(this).val();
        if (keyword.length < 1) {
            $('#search-results').hide();
            return;
        }
        $.ajax({
            url: "{{ route('admin.pos.search') }}",
            type: "GET",
            data: {
                keyword: keyword
            },
            success: function(data) {
                let html = '';
                if (data.length > 0) {
                    data.forEach(item => {
                        html += `
                        <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center btn-select-product"
                            data-id="${item.product_id}"
                            data-unit-id="${item.product_unit_id}"
                            data-name="${item.product_display_name}"
                            data-unit="${item.unit_name}"
                            data-price="${item.sale_price}">
                            <div>
                                <span class="fw-semibold text-dark">${item.product_display_name}</span> 
                                <small class="text-muted d-block">Tồn kho hiện tại: ${item.current_stock}</small>
                            </div>
                        </button>`;
                    });
                    $('#search-results').html(html).show();
                } else {
                    $('#search-results').html('<div class="list-group-item text-muted">Không tìm thấy sản phẩm</div>').show();
                }
            }
        });
    });

    $(document).on('click', '.btn-select-product', function() {
        let product = {
            product_id: $(this).data('id'),
            product_unit_id: $(this).data('unit-id'),
            name: $(this).data('name'),
            unit_name: $(this).data('unit'),
            sale_price: parseFloat($(this).data('price')),
            quantity: 1
        };
        let existingIndex = cart.findIndex(item => item.product_unit_id === product.product_unit_id);
        if (existingIndex > -1) {
            cart[existingIndex].quantity += 1;
        } else {
            cart.push(product);
        }
        $('#search-input').val('');
        $('#search-results').hide();
        renderCart();
    });

    function renderCart() {
        let html = '';
        let totalAmount = 0;
        if (cart.length === 0) {
            html = `<tr><td colspan="6" class="text-center py-5 text-muted"><i class="bi bi-cart-x fs-2 d-block mb-2"></i> Chưa có sản phẩm nào trong giỏ</td></tr>`;
            $('#cart-count').text('0 món');
        } else {
            cart.forEach((item, index) => {
                let subtotal = item.sale_price * item.quantity;
                totalAmount += subtotal;
                html += `
                <tr>
                    <td class="ps-3 fw-semibold text-dark">${item.name}</td>
                    <td><span class="badge bg-secondary">${item.unit_name}</span></td>
                    <td><div class="input-group input-group-sm m-auto" style="width: 100px;"><input type="number" class="form-control text-center change-qty" data-index="${index}" value="${item.quantity}" min="1"></div></td>
                    <td class="fw-medium">${item.sale_price.toLocaleString('vi-VN')}đ</td>
                    <td class="fw-bold text-primary">${subtotal.toLocaleString('vi-VN')}đ</td>
                    <td class="text-end pe-3"><button type="button" class="btn btn-sm btn-outline-danger btn-remove" data-index="${index}"><i class="bi bi-trash"></i></button></td>
                </tr>`;
            });
            $('#cart-count').text(cart.length + ' món');
        }
        $('#cart-table-body').html(html);
        calculateMoney(totalAmount);
    }

    $(document).on('change', '.change-qty', function() {
        let index = $(this).data('index');
        let newQty = parseInt($(this).val());
        if (newQty > 0) {
            cart[index].quantity = newQty;
            renderCart();
        }
    });

    $(document).on('click', '.btn-remove', function() {
        let index = $(this).data('index');
        cart.splice(index, 1);
        renderCart();
    });

    function calculateMoney(totalAmount = null) {
        if (totalAmount === null) {
            totalAmount = cart.reduce((sum, item) => sum + (item.sale_price * item.quantity), 0);
        }
        let discount = parseFloat($('#input-discount').val()) || 0;
        let finalAmount = totalAmount - discount;
        if (finalAmount < 0) finalAmount = 0;
        let method = $('#payment_method').val();
        if (method === 'debt') {
            $('#input-paid').val(0).prop('disabled', true);
        } else {
            $('#input-paid').prop('disabled', false);
        }
        let paidAmount = parseFloat($('#input-paid').val()) || 0;
        let changeAmount = paidAmount - finalAmount;
        if (changeAmount < 0) changeAmount = 0;
        $('#txt-total-amount').text(totalAmount.toLocaleString('vi-VN') + 'đ');
        $('#txt-final-amount').text(finalAmount.toLocaleString('vi-VN') + 'đ');
        $('#txt-change-amount').text(changeAmount.toLocaleString('vi-VN') + 'đ');
    }

    $('#input-discount, #input-paid, #payment_method').on('input change', function() {
        calculateMoney();
    });

    function executePrintReceipt(orderData, invoiceNumber) {
        $('#p-invoice-number').text(invoiceNumber);
        let now = new Date();
        $('#p-date').text(now.toLocaleDateString('vi-VN') + ' ' + now.toLocaleTimeString('vi-VN', {
            hour: '2-digit',
            minute: '2-digit'
        }));
        $('#p-customer-name').text($('#customer-search').val());
        $('#p-total-amount').text(orderData.total_amount.toLocaleString('vi-VN') + 'đ');
        $('#p-discount').text(orderData.discount_amount.toLocaleString('vi-VN') + 'đ');
        $('#p-final-amount').text(orderData.final_amount.toLocaleString('vi-VN') + 'đ');
        $('#p-paid').text(orderData.paid_amount.toLocaleString('vi-VN') + 'đ');
        $('#p-change').text(orderData.change_amount.toLocaleString('vi-VN') + 'đ');

        let methodText = 'Tiền mặt';
        if (orderData.payment_method === 'qr_code') methodText = 'Chuyển khoản';
        if (orderData.payment_method === 'debt') methodText = 'Ghi nợ';
        $('#p-method').text(methodText);

        let itemsHtml = '';
        cart.forEach(item => {
            let total = item.sale_price * item.quantity;
            itemsHtml += `
            <tr style="border-bottom: 1px dotted #eee;">
                <td style="padding: 5px 0;">${item.name}</td>
                <td style="text-align: center; padding: 5px 0;">${item.quantity}</td>
                <td style="text-align: right; padding: 5px 0;">${total.toLocaleString('vi-VN')}đ</td>
            </tr>`;
        });
        $('#p-items-body').html(itemsHtml);

        let iframe = document.getElementById('print-iframe');
        if (!iframe) {
            iframe = document.createElement('iframe');
            iframe.id = 'print-iframe';
            iframe.style.position = 'absolute';
            iframe.style.top = '-9999px';
            iframe.style.left = '-9999px';
            document.body.appendChild(iframe);
        }

        let doc = iframe.contentDocument || iframe.contentWindow.document;
        doc.open();
        doc.write('<html><head><title>Print Invoice</title></head><body style="margin:0;">' + $('#print-section').html() + '</body></html>');
        doc.close();

        setTimeout(function() {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        }, 500);
    }

    $('#btn-checkout').on('click', function() {
        if (cart.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Giỏ hàng trống',
                text: 'Vui lòng chọn sản phẩm trước khi thanh toán!',
                confirmButtonColor: '#3085d6'
            });
            return;
        }
        let method = $('#payment_method').val();
        let customerId = $('#customer_id').val();
        if (method === 'debt' && !customerId) {
            Swal.fire({
                icon: 'error',
                title: 'Chặn ghi nợ',
                text: 'Không thể ghi nợ cho Khách vãng lai! Vui lòng chọn một khách hàng cụ thể.',
                confirmButtonColor: '#d33'
            });
            return;
        }
        let totalAmount = cart.reduce((sum, item) => sum + (item.sale_price * item.quantity), 0);
        let discount = parseFloat($('#input-discount').val()) || 0;
        let finalAmount = totalAmount - discount;
        let paidAmount = parseFloat($('#input-paid').val()) || 0;
        let changeAmount = paidAmount - finalAmount;

        let orderData = {
            _token: "{{ csrf_token() }}",
            cart: cart,
            customer_id: customerId,
            total_amount: totalAmount,
            discount_amount: discount,
            final_amount: finalAmount > 0 ? finalAmount : 0,
            paid_amount: paidAmount,
            change_amount: changeAmount > 0 ? changeAmount : 0,
            payment_method: method
        };

        $.ajax({
            url: "{{ route('admin.pos.checkout') }}",
            type: "POST",
            data: orderData,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công',
                        text: response.message,
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: '<i class="bi bi-printer me-1"></i> In hóa đơn',
                        cancelButtonText: 'Đóng lại'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            executePrintReceipt(orderData, response.invoice_number);
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
                    title: 'Thanh toán thất bại',
                    text: res && res.message ? res.message : 'Lỗi hệ thống không xác định.'
                });
            }
        });
    });
</script>
@endsection