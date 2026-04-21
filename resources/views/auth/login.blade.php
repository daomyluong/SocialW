<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập | W-Social</title>
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
                radial-gradient(circle at top left, rgba(29, 78, 216, 0.28), transparent 32%),
                radial-gradient(circle at bottom right, rgba(22, 163, 74, 0.22), transparent 28%),
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
            max-width: 440px;
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
                <h1 class="h3 fw-bold mb-2">Đăng nhập</h1>
                <p class="text-secondary mb-0">Tiếp tục vào W-Social bằng tài khoản của bạn.</p>
            </div>

            <div class="auth-body">
                @if (session('status'))
                    <div class="alert alert-success border-0 mb-3">
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                        @error('email')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-semibold mb-0">Mật khẩu</label>
                            @if (Route::has('password.request'))
                                <a class="muted-link small" href="{{ route('password.request') }}">Quên mật khẩu?</a>
                            @endif
                        </div>
                        <input type="password" name="password" class="form-control" required autocomplete="current-password">
                        @error('password')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input type="checkbox" name="remember" class="form-check-input" id="remember_me">
                            <label class="form-check-label small" for="remember_me">Ghi nhớ đăng nhập</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-auth w-100">Đăng nhập</button>

                    <div class="text-center mt-3">
                        <small>Chưa có tài khoản? <a href="{{ route('register') }}" class="muted-link fw-semibold">Đăng ký ngay</a></small>
                    </div>
                </form>
            </div>
        </section>
    </main>
</body>
</html>