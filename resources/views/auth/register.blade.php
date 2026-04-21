<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký | W-Social</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --brand-blue: #1d4ed8;
            --brand-green: #16a34a;
            --surface: rgba(255, 255, 255, 0.92);
        }

        body {
            min-height: 100vh;
            margin: 0;
            font-family: Inter, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top right, rgba(22, 163, 74, 0.28), transparent 30%),
                radial-gradient(circle at bottom left, rgba(29, 78, 216, 0.26), transparent 28%),
                linear-gradient(135deg, #0f172a 0%, #111827 45%, #0b1220 100%);
            color: #e5e7eb;
        }

        .auth-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .auth-card {
            width: 100%;
            max-width: 520px;
            background: var(--surface);
            color: #111827;
            border: 1px solid rgba(255, 255, 255, 0.28);
            border-radius: 28px;
            box-shadow: 0 28px 80px rgba(15, 23, 42, 0.35);
            backdrop-filter: blur(18px);
            overflow: hidden;
        }

        .auth-hero {
            padding: 28px 28px 0;
        }

        .brand-mark {
            width: 54px;
            height: 54px;
            border-radius: 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            background: linear-gradient(135deg, var(--brand-blue), var(--brand-green));
            box-shadow: 0 12px 28px rgba(29, 78, 216, 0.35);
        }

        .auth-body {
            padding: 24px 28px 30px;
        }

        .form-control {
            border-radius: 14px;
            min-height: 48px;
        }

        .form-control:focus {
            border-color: var(--brand-blue);
            box-shadow: 0 0 0 0.2rem rgba(29, 78, 216, 0.15);
        }

        .btn-auth {
            min-height: 48px;
            border: none;
            border-radius: 14px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--brand-blue), var(--brand-green));
            box-shadow: 0 14px 30px rgba(29, 78, 216, 0.22);
        }

        .btn-auth:hover {
            opacity: 0.96;
        }

        .muted-link {
            color: #4b5563;
            text-decoration: none;
        }

        .muted-link:hover {
            color: var(--brand-blue);
        }
    </style>
</head>
<body>
    <main class="auth-shell">
        <section class="auth-card">
            <div class="auth-hero text-center">
                <div class="brand-mark mb-3">
                    <i class="fa-solid fa-link"></i>
                </div>
                <h1 class="h3 fw-bold mb-2">Tạo tài khoản mới</h1>
                <p class="text-secondary mb-0">Tham gia W-Social để kết nối và chia sẻ nội dung.</p>
            </div>

            <div class="auth-body">
                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên hiển thị</label>
                        <input type="text" name="display_name" value="{{ old('display_name') }}" class="form-control" placeholder="Ví dụ: Nguyễn Văn A" required autofocus>
                        @error('display_name')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tên đăng nhập (Username)</label>
                        <input type="text" name="username" value="{{ old('username') }}" class="form-control" placeholder="nguyenvana" required>
                        @error('username')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Địa chỉ Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="name@example.com" required>
                        @error('email')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Mật khẩu</label>
                        <input type="password" name="password" class="form-control" required autocomplete="new-password">
                        @error('password')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Xác nhận mật khẩu</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary btn-auth w-100">Đăng ký</button>

                    <div class="text-center mt-3">
                        <small>Đã có tài khoản? <a href="{{ route('login') }}" class="muted-link fw-semibold">Đăng nhập ngay</a></small>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>
</html>