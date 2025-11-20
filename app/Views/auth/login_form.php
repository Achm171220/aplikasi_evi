<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? 'Login' ?> | EVI</title>
    <link rel="icon" href="<?= base_url('images/logo.png'); ?>" type="image/x-icon">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(-45deg, #f8f9fb, #dbeafe, #f0fdf4, #fff1f2);
            background-size: 400% 400%;
            animation: gradientShift 15s ease infinite;
        }

        @keyframes gradientShift {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .login-wrapper {
            max-width: 420px;
            margin: auto;
            width: 100%;
        }

        .login-card {
            border: none;
            border-radius: 1rem;
            background: #fff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            position: relative;

            /* Animasi fade-in */
            opacity: 0;
            transform: translateY(20px) scale(0.97);
            animation: fadeInCard 0.8s ease forwards;
        }

        @keyframes fadeInCard {
            0% {
                opacity: 0;
                transform: translateY(20px) scale(0.97);
            }

            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .logo-container img {
            max-width: 90px;
            height: auto;
        }

        .login-title {
            font-weight: 700;
            font-size: 1.5rem;
            margin-top: 1rem;
        }

        .login-subtitle {
            font-size: 0.95rem;
            color: #6c757d;
        }

        .form-floating input {
            border-radius: 0.75rem;
        }

        .password-wrapper {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            top: 50%;
            right: 1rem;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }

        .btn-login {
            border-radius: 0.75rem;
            padding: 0.75rem;
            font-weight: 600;
            font-size: 1rem;
        }

        .footer-text {
            font-size: 0.85rem;
            margin-top: 1rem;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <div class="login-wrapper">
        <div class="card login-card">
            <div class="text-center mb-4 logo-container">
                <img src="<?= site_url('images/logo.png'); ?>" alt="Logo BPKP">
                <h3 class="login-title">Evaluasi Internal Kearsipan</h3>
                <p class="login-subtitle">Silakan masuk untuk melanjutkan</p>
            </div>

            <form action="<?= site_url('auth/login') ?>" method="post">
                <?= csrf_field() ?>
                <div class="form-floating mb-3">
                    <!-- Ganti type="username" menjadi type="text" -->
                    <input type="text"
                        class="form-control <?= $validation->hasError('username') ? 'is-invalid' : '' ?>"
                        id="username"
                        name="username"
                        placeholder="NIP atau Nama Lengkap"
                        value="<?= old('username') ?>"
                        required>
                    <!-- Label sudah benar sebagai 'Username' -->
                    <label for="username"><i class="bi bi-person me-2"></i> Username / NIP</label>
                    <div class="invalid-feedback"><?= $validation->getError('username') ?></div>
                </div>

                <div class="form-floating mb-3 password-wrapper">
                    <input type="password"
                        class="form-control <?= $validation->hasError('password') ? 'is-invalid' : '' ?>"
                        id="password"
                        name="password"
                        placeholder="Password"
                        required>
                    <label for="password"><i class="bi bi-lock me-2"></i> Password</label>
                    <i class="bi bi-eye toggle-password" id="togglePassword"></i>
                    <div class="invalid-feedback"><?= $validation->getError('password') ?></div>
                </div>

                <div class="d-grid">
                    <button class="btn btn-primary btn-login" type="submit">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Login
                    </button>
                </div>
            </form>

            <p class="text-center footer-text mt-3">© <?= date('Y') ?> EVI - BPKP</p>
        </div>
    </div>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Notifikasi & Toggle Password -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // toggle password visibility
            const togglePassword = document.querySelector("#togglePassword");
            const passwordInput = document.querySelector("#password");

            togglePassword.addEventListener("click", function() {
                const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
                passwordInput.setAttribute("type", type);
                this.classList.toggle("bi-eye");
                this.classList.toggle("bi-eye-slash");
            });

            // notifikasi sweetalert
            <?php if (session()->getFlashdata('error')): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Login Gagal!',
                    text: '<?= addslashes(session()->getFlashdata('error')) ?>',
                    timer: 3000,
                    showConfirmButton: false
                });
            <?php endif; ?>

            <?php if (session()->getFlashdata('success')): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '<?= addslashes(session()->getFlashdata('success')) ?>',
                    timer: 2000,
                    showConfirmButton: false
                });
            <?php endif; ?>

            if (sessionStorage.getItem('autoLogoutTriggered') === 'true') {
                sessionStorage.removeItem('autoLogoutTriggered');
                Swal.fire({
                    icon: 'info',
                    title: 'Logout Otomatis',
                    text: 'Anda telah logout otomatis karena tidak ada aktivitas.',
                    showConfirmButton: false,
                    timer: 3000
                });
            }
        });
    </script>
</body>

</html>