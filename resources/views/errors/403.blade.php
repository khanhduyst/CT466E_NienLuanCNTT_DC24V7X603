<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Không có quyền truy cập</title>
    <!-- Link Bootstrap có sẵn từ dự án của bạnh -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
        }
        .error-card {
            max-width: 500px;
            border: none;
            border-radius: 16px;
        }
        .icon-box {
            font-size: 5rem;
            color: #dc3545;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center">

    <div class="card error-card shadow-sm p-5 text-center bg-white">
        <div class="icon-box mb-4">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        <h1 class="display-4 fw-bold text-dark mb-2">403</h1>
        <h3 class="h5 text-secondary mb-4">Truy cập bị từ chối!</h3>
        <p class="text-muted mb-4 px-3">
            Tài khoản của bạn không có quyền hạn để xem khu vực này. Vui lòng liên hệ Admin tối cao hoặc đăng nhập bằng tài khoản được cấp quyền.
        </p>
        <div class="d-flex justify-content-center gap-2">
            <a href="/admin/san-pham" class="btn btn-primary px-4 py-2 rounded-2 d-flex align-items-center">
                <i class="bi bi-arrow-left me-2"></i> Trở về danh sách
            </a>
            <a href="#" onclick="location.reload();" class="btn btn-outline-secondary px-4 py-2 rounded-2">
                <i class="bi bi-arrow-clockwise"></i> Thử lại
            </a>
        </div>
    </div>

</body>
</html>