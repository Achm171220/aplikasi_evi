<nav class="navbar navbar-expand-lg navbar-light bg-light main-navbar">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= base_url('/pemindahan') ?>">
            <i class="fas fa-cubes me-2"></i> Pemindahan Arsip
        </a>

        <?php if (session()->get('isLoggedIn')): ?>
            <?php $currentPath = service('request')->getUri()->getPath(); ?>
            <a class="nav-link dashboard-nav-link <?= (rtrim($currentPath, '/') === '') ? 'active' : '' ?>" href="<?= base_url('pemindahan/') ?>">
                <i class="fas fa-tv me-2"></i> Dashboard
            </a>
        <?php endif; ?>

        <!-- Navbar Toggler for user dropdown on small screens -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#userNavbarCollapse" aria-controls="userNavbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- User Dropdown (moved into its own collapse div for responsive behavior) -->
        <div class="collapse navbar-collapse justify-content-end" id="userNavbarCollapse">
            <ul class="navbar-nav mb-2 mb-lg-0">
                <?php if (session()->get('isLoggedIn')): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUserStatus" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-1"></i> <?= esc(session()->get('name')) ?> | <?= esc(session()->get('role_jabatan')) ?>
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-primary" href="<?= base_url('login') ?>"><i class="fas fa-sign-in-alt me-1"></i> Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>