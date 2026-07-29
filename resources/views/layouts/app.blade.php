<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartGrocer POS - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background-color: #f4f7f6;
            color: #334155;
        }

        .brand-logo {
            background: linear-gradient(135deg, #0ea5e9, #059669);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* SIDEBAR TRÊN DESKTOP */
        .sidebar {
            width: 280px;
            background-color: #ffffff;
            min-height: 100vh;
            border-right: 1px solid #e2e8f0;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 1030;
        }

        .sidebar .nav-link {
            color: #64748b;
            padding: 0.85rem 1.2rem;
            font-weight: 500;
            border-radius: 12px;
            margin-bottom: 0.3rem;
            transition: all 0.2s ease;
        }

        .sidebar .nav-link:hover {
            color: #059669;
            background-color: #f0fdf4;
        }

        .sidebar .nav-link.active {
            color: #059669;
            background-color: #dcfce7;
            font-weight: 600;
        }

        /* PHẦN NỘI DUNG BÊN PHẢI */
        .right-section {
            flex-grow: 1;
            margin-left: 280px;
            /* Chừa khoảng trống cho sidebar trên desktop */
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .top-header {
            height: 70px;
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1020;
        }

        .main-content {
            padding: 1.5rem;
            flex-grow: 1;
        }

        .content-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.02), 0 2px 4px -2px rgb(0 0 0 / 0.02);
            padding: 1.5rem;
            min-height: calc(100vh - 120px);
        }

        .btn-logout {
            border: 1px solid #fee2e2;
            background-color: #fff5f5;
            color: #ef4444;
            border-radius: 12px;
            padding: 0.6rem 1rem;
            font-weight: 600;
        }

        .btn-logout:hover {
            background-color: #ef4444;
            color: #ffffff;
        }

        .nav-link {
            transition: all 0.2s ease-in-out;
        }

        .nav-link:hover {
            background-color: #f8f9fa;
            color: #0d6efd !important;
            opacity: 1 !important;
        }

        /* 📱 ĐỊNH NGHĨA RESPONSIVE TRÊN MOBILE (MÀN HÌNH NHỎ HƠN 992PM) */
        @media (max-width: 991.98px) {
            .sidebar {
                display: none !important;
                /* Ẩn hẳn sidebar tĩnh */
            }

            .right-section {
                margin-left: 0 !important;
                /* Tràn hết màn hình */
            }

            .top-header {
                padding: 0 1rem;
            }

            .main-content {
                padding: 1rem;
            }

            .content-card {
                padding: 1.25rem;
            }
        }
    </style>
</head>

<body>

    <div class="d-flex">

        <div class="sidebar d-none d-lg-flex flex-column p-4 justify-content-between">
            <div>
                <div class="text-center mb-5 py-2">
                    <h3 class="fw-bold brand-logo mb-1" style="letter-spacing: -0.5px;">SMART GROCER</h3>
                    <span class="text-muted small fw-medium">Hệ thống POS thông minh</span>
                </div>

                @php
                $role = Auth::user()->role->name ?? '';
                $isSuperAdmin = ($role === 'super_admin');
                $isAdminOrManager = in_array($role, ['super_admin', 'manager']);
                @endphp

                <ul class="nav nav-pills flex-column">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ Request::is('dashboard') || Request::is('admin') || Request::is('admin/dashboard') ? 'active' : '' }}">
                            <i class="bi bi-grid-1x2-fill me-3"></i>Trang chủ chung
                        </a>
                    </li>

                    @if($isAdminOrManager)
                    <li class="nav-item mb-2">
                        <a class="nav-link d-flex align-items-center justify-content-between {{ Request::is('admin/danh-muc') || Request::is('admin/san-pham*') ? 'active' : '' }}"
                            data-bs-toggle="collapse"
                            href="#menuHangHoa"
                            role="button"
                            aria-expanded="{{ Request::is('admin/danh-muc') || Request::is('admin/san-pham*') ? 'true' : 'false' }}">
                            <div>
                                <i class="bi bi-box-seam me-3"></i>Quản lý hàng hóa
                            </div>
                            <i class="bi bi-chevron-down small chevron-icon"></i>
                        </a>

                        <div class="collapse {{ Request::is('admin/danh-muc') || Request::is('admin/san-pham*') ? 'show' : '' }} ms-3 mt-1" id="menuHangHoa">
                            <ul class="nav flex-column border-start text-start ps-2" style="border-color: #dee2e6 !important;">
                                <li class="nav-item">
                                    <a href="/admin/danh-muc" class="nav-link py-2 px-3 rounded-2 small d-flex align-items-center transition-all {{ Request::is('admin/danh-muc') ? 'bg-light text-primary fw-semibold' : 'text-secondary opacity-75' }}">
                                        <i class="bi bi-tags fs-6 text-center me-2" style="width: 20px;"></i>
                                        <span>Danh mục sản phẩm</span>
                                    </a>
                                </li>
                                <li class="nav-item mt-1">
                                    <a href="/admin/san-pham" class="nav-link py-2 px-3 rounded-2 small d-flex align-items-center transition-all {{ Request::is('admin/san-pham*') ? 'bg-light text-primary fw-semibold' : 'text-secondary opacity-75' }}">
                                        <i class="bi bi-box-seam fs-6 text-center me-2" style="width: 20px;"></i>
                                        <span>Danh sách sản phẩm</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <li class="nav-item mb-2">
                        <a class="nav-link d-flex align-items-center justify-content-between {{ Request::is('admin/nhap-kho') || Request::is('admin/lich-su-nhap-kho') || Request::is('admin/nha-cung-cap') ? 'active' : '' }}"
                            data-bs-toggle="collapse"
                            href="#menuKho"
                            role="button"
                            aria-expanded="{{ Request::is('admin/nhap-kho') || Request::is('admin/lich-su-nhap-kho') || Request::is('admin/nha-cung-cap') ? 'true' : 'false' }}">
                            <div>
                                <i class="bi bi-boxes me-3"></i>Quản lý Kho
                            </div>
                            <i class="bi bi-chevron-down small chevron-icon"></i>
                        </a>

                        <div class="collapse {{ Request::is('admin/nhap-kho') || Request::is('admin/lich-su-nhap-kho') || Request::is('admin/nha-cung-cap') ? 'show' : '' }} ms-3 mt-1" id="menuKho">
                            <ul class="nav flex-column border-start text-start ps-2" style="border-color: #dee2e6 !important;">
                                <li class="nav-item mt-1">
                                    <a href="/admin/nhap-kho" class="nav-link py-2 px-3 rounded-2 small d-flex align-items-center transition-all {{ Request::is('admin/nhap-kho') ? 'bg-light text-primary fw-semibold' : 'text-secondary opacity-75' }}">
                                        <i class="bi bi-file-earmark-arrow-down fs-6 text-center me-2" style="width: 20px;"></i>
                                        <span>Phiếu nhập kho</span>
                                    </a>
                                </li>
                                <li class="nav-item mt-1">
                                    <a href="{{ route('admin.purchase.history') }}" class="nav-link py-2 px-3 rounded-2 small d-flex align-items-center transition-all {{ Request::is('admin/lich-su-nhap-kho') ? 'bg-light text-primary fw-semibold' : 'text-secondary opacity-75' }}">
                                        <i class="bi bi-clock-history fs-6 text-center me-2" style="width: 20px;"></i>
                                        <span>Lịch sử nhập kho</span>
                                    </a>
                                </li>
                                <li class="nav-item mt-1">
                                    <a href="/admin/nha-cung-cap" class="nav-link py-2 px-3 rounded-2 small d-flex align-items-center transition-all {{ Request::is('admin/nha-cung-cap') ? 'bg-light text-primary fw-semibold' : 'text-secondary opacity-75' }}">
                                        <i class="bi bi-truck fs-6 text-center me-2" style="width: 20px;"></i>
                                        <span>Nhà cung cấp</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    <li class="nav-item">
                        <a href="/admin/pos/ban-hang" class="nav-link {{ Request::is('admin/pos*') || Request::is('pos/*') ? 'active' : '' }}">
                            <i class="bi bi-qr-code-scan me-3"></i>Màn hình bán hàng POS
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/admin/orders" class="nav-link {{ Request::is('admin/orders*') ? 'active' : '' }}">
                            <i class="bi bi-receipt me-3"></i>Quản lý đơn hàng
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/admin/cong-no" class="nav-link {{ Request::is('admin/cong-no*') ? 'active' : '' }}">
                            <i class="bi bi-wallet2 me-3"></i>Quản lý công nợ
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="/admin/khach-hang" class="nav-link {{ Request::is('admin/khach-hang*') ? 'active' : '' }}">
                            <i class="bi bi-person-lines-fill me-3"></i>Quản lý khách hàng
                        </a>
                    </li>

                    @if($isSuperAdmin)
                    <li class="nav-item">
                        <a href="/admin/nhan-vien" class="nav-link {{ Request::is('admin/nhan-vien*') ? 'active' : '' }}">
                            <i class="bi bi-people me-3"></i>Quản lý nhân viên
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('admin.settings.index') }}" class="nav-link {{ Request::is('admin/settings*') ? 'active' : '' }}">
                            <i class="bi bi-gear-wide-connected me-3"></i>Cấu hình hệ thống
                        </a>
                    </li>
                    @endif
                </ul>
            </div>

            <div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-logout w-100 btn-sm d-flex align-items-center justify-content-center">
                        <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất hệ thống
                    </button>
                </form>
            </div>
        </div>

        <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="mobileSidebar" style="width: 280px;">
            <div class="offcanvas-header border-bottom">
                <div>
                    <h4 class="fw-bold brand-logo mb-0">SMART GROCER</h4>
                    <small class="text-muted">Mobile Menu</small>
                </div>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column justify-content-between p-4">
                <ul class="nav nav-pills flex-column w-100">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}">
                            <i class="bi bi-grid-1x2-fill me-3"></i>Trang chủ chung
                        </a>
                    </li>

                    @if(in_array(Auth::user()->role->name, ['super_admin', 'manager']))
                    <li class="nav-item mb-2">
                        <a class="nav-link d-flex align-items-center justify-content-between {{ Request::is('admin/danh-muc') || Request::is('admin/san-pham*') ? 'active' : '' }}"
                            data-bs-toggle="collapse"
                            href="#menuHangHoa"
                            role="button"
                            aria-expanded="{{ Request::is('admin/danh-muc') || Request::is('admin/san-pham*') ? 'true' : 'false' }}">
                            <div>
                                <i class="bi bi-box-seam me-3"></i>Quản lý hàng hóa
                            </div>
                            <i class="bi bi-chevron-down small chevron-icon"></i>
                        </a>

                        <div class="collapse {{ Request::is('admin/danh-muc') || Request::is('admin/san-pham*') ? 'show' : '' }} ms-4 ps-2 mt-1" id="menuHangHoa">
                            <ul class="nav flex-column border-start text-start">
                                <li class="nav-item">
                                    <a href="/admin/danh-muc" class="nav-link py-1 small {{ Request::is('admin/danh-muc') ? 'fw-bold text-primary' : 'text-muted' }}">
                                        <i class="bi bi-tags me-2"></i>Danh mục sản phẩm
                                    </a>
                                </li>
                                <li class="nav-item mt-1">
                                    <a href="/admin/san-pham" class="nav-link py-1 small {{ Request::is('admin/san-pham*') ? 'fw-bold text-primary' : 'text-muted' }}">
                                        <i class="bi bi-cart-plus me-2"></i>Danh sách sản phẩm
                                    </a>
                                </li>
                                <li class="nav-item mt-1">
                                    <a href="/admin/nhap-kho" class="nav-link py-1 small {{ Request::is('admin/nhap-kho') ? 'fw-bold text-primary' : 'text-muted' }}">
                                        <i class="bi bi-cart-plus me-2"></i>Phiếu nhập kho
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if(in_array(Auth::user()->role->name, ['super_admin', 'manager']))
                    <li>
                        <a href="/kho/kiem-hang" class="nav-link {{ Request::is('kho/*') ? 'active' : '' }}">
                            <i class="bi bi-boxes me-3"></i>Quản lý kho hàng
                        </a>
                    </li>
                    @endif
                    @if(in_array(Auth::user()->role->name, ['super_admin', 'manager', 'staff']))
                    <li>
                        <a href="/pos/ban-hang" class="nav-link {{ Request::is('pos/*') ? 'active' : '' }}">
                            <i class="bi bi-qr-code-scan me-3"></i>Màn hình bán hàng POS
                        </a>
                    </li>
                    @endif

                    @if(in_array(Auth::user()->role->name, ['super_admin', 'manager', 'staff']))
                    <li>
                        <a href="/admin/orders" class="nav-link {{ Request::is('admin/orders*') ? 'active' : '' }}">
                            <i class="bi bi-receipt me-3"></i>Quản lý đơn hàng
                        </a>
                    </li>
                    @endif

                </ul>

                <form action="{{ route('logout') }}" method="POST" class="mt-auto w-100">
                    @csrf
                    <button type="submit" class="btn btn-logout w-100 d-flex align-items-center justify-content-center">
                        <i class="bi bi-box-arrow-right me-2"></i> Đăng xuất hệ thống
                    </button>
                </form>
            </div>
        </div>

        <div class="right-section d-flex flex-column">

            <div class="top-header">
                <div class="d-flex align-items-center">
                    <button class="btn btn-light d-lg-none me-2 border" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                        <i class="bi bi-list fs-4"></i>
                    </button>

                    <h6 class="mb-0 fw-semibold text-secondary d-none d-sm-block">
                        <i class="bi bi-clock-history me-2 text-primary"></i>
                        <span id="current-time" class="text-dark fw-bold"></span>
                    </h6>
                </div>

                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle p-1 rounded-3" data-bs-toggle="dropdown" aria-expanded="false">
                            <div class="me-2 text-end d-none d-sm-block">
                                <div class="fw-bold text-dark" style="font-size: 13px; max-width: 120px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ Auth::user()->name }}
                                </div>
                                <div class="text-muted fw-semibold" style="font-size: 11px;">
                                    @if(Auth::user()->role->name == 'super_admin') CHỦ CỬA HÀNG
                                    @elseif(Auth::user()->role->name == 'manager') QUẢN LÝ
                                    @else THU NGÂN
                                    @endif
                                </div>
                            </div>
                            <div class="bg-light rounded-circle d-flex align-items-center justify-content-center border" style="width: 38px; height: 38px;">
                                <i class="bi bi-person text-secondary fs-5"></i>
                            </div>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3 mt-2 p-2">
                            <li>
                                <a class="dropdown-item rounded-2 py-2 small fw-medium" href="{{ route('profile.show') }}">
                                    <i class="bi bi-person-gear me-2 text-primary"></i>Thông tin cá nhân
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item rounded-2 py-2 small fw-medium" href="{{ route('profile.password') }}">
                                    <i class="bi bi-shield-lock me-2 text-warning"></i>Đổi mật khẩu
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="main-content">
                <div class="content-card">
                    @yield('content')
                </div>
            </div>

        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function updateVietnamTime() {
            const options = {
                timeZone: 'Asia/Ho_Chi_Minh',
                weekday: 'short',
                year: 'numeric',
                month: 'numeric',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            const formatter = new Intl.DateTimeFormat('vi-VN', options);
            document.getElementById('current-time').innerText = formatter.format(new Date());
        }
        setInterval(updateVietnamTime, 1000);
        updateVietnamTime();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>