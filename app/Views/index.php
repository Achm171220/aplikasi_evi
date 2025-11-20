<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Clean Dashboard</title>

    <!-- Google Fonts: Ubuntu -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.7/css/dataTables.bootstrap5.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Select2 Bootstrap 5 Theme -->
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= base_url(); ?>css/style2.css">
</head>

<body>

    <div class="d-flex" id="wrapper">

        <!-- Sidebar -->
        <div class="sidebar-wrapper" id="sidebar-wrapper">
            <div class="sidebar-heading text-center py-4 primary-bg text-white">
                <i class="bi bi-box-seam-fill me-2"></i> My Dashboard
            </div>
            <div class="list-group list-group-flush my-3">
                <a href="#" class="list-group-item list-group-item-action bg-transparent custom-text-secondary active">
                    <i class="bi bi-grid-1x2-fill me-2"></i> Dashboard
                </a>
                <a href="#" class="list-group-item list-group-item-action bg-transparent custom-text-secondary">
                    <i class="bi bi-bar-chart-fill me-2"></i> Analytics
                </a>

                <!-- Sub Menu Example: Users -->
                <a href="#submenu-users" class="list-group-item list-group-item-action bg-transparent custom-text-secondary d-flex justify-content-between align-items-center" data-bs-toggle="collapse" aria-expanded="false">
                    <div><i class="bi bi-people-fill me-2"></i> Users</div>
                    <i class="bi bi-chevron-down toggle-icon"></i>
                </a>
                <div class="collapse list-group-submenu" id="submenu-users">
                    <a href="#" class="list-group-item list-group-item-action bg-transparent custom-text-secondary ps-5">
                        <i class="bi bi-person-fill me-2"></i> All Users
                    </a>
                    <a href="#" class="list-group-item list-group-item-action bg-transparent custom-text-secondary ps-5">
                        <i class="bi bi-person-add me-2"></i> Add New
                    </a>
                    <a href="#" class="list-group-item list-group-item-action bg-transparent custom-text-secondary ps-5">
                        <i class="bi bi-person-lock me-2"></i> Roles & Permissions
                    </a>
                </div>

                <a href="#" class="list-group-item list-group-item-action bg-transparent custom-text-secondary">
                    <i class="bi bi-box-seam-fill me-2"></i> Products
                </a>
                <a href="#" class="list-group-item list-group-item-action bg-transparent custom-text-secondary">
                    <i class="bi bi-cart-fill me-2"></i> Orders
                </a>
                <a href="#" class="list-group-item list-group-item-action bg-transparent custom-text-secondary">
                    <i class="bi bi-chat-dots-fill me-2"></i> Messages
                </a>
                <a href="#" class="list-group-item list-group-item-action bg-transparent custom-text-secondary">
                    <i class="bi bi-gear-fill me-2"></i> Settings
                </a>
                <a href="#" class="list-group-item list-group-item-action bg-transparent text-custom-danger" id="logout-button">
                    <i class="bi bi-box-arrow-right me-2"></i> Logout
                </a>
            </div>
        </div>
        <!-- /Sidebar -->

        <!-- Page Content -->
        <div id="page-content-wrapper" class="p-3 p-md-4">
            <nav class="navbar navbar-expand-lg navbar-light bg-light py-3 px-4 shadow-sm rounded-pill mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-list fs-4 me-3 toggle-btn" id="menu-toggle"></i>
                    <h2 class="fs-2 m-0 text-muted">Dashboard Overview</h2>
                </div>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle custom-text-secondary fw-bold" href="#" id="navbarDropdown"
                                role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle me-2"></i> John Doe
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i> Profile</a></li>
                                <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i> Settings</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-custom-danger" href="#" id="logout-button-dropdown"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="container-fluid px-0">
                <div class="row g-4 my-3">
                    <div class="col-md-3">
                        <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded-3 border">
                            <div>
                                <h3 class="fs-2">720</h3>
                                <p class="fs-5 text-muted">Products</p>
                            </div>
                            <i class="bi bi-gift-fill fs-1 primary-text"></i>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded-3 border">
                            <div>
                                <h3 class="fs-2">4920</h3>
                                <p class="fs-5 text-muted">Sales</p>
                            </div>
                            <i class="bi bi-cart-fill fs-1 primary-text"></i>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded-3 border">
                            <div>
                                <h3 class="fs-2">340</h3>
                                <p class="fs-5 text-muted">Delivery</p>
                            </div>
                            <i class="bi bi-truck fs-1 primary-text"></i>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="p-3 bg-white shadow-sm d-flex justify-content-around align-items-center rounded-3 border">
                            <div>
                                <h3 class="fs-2">%25</h3>
                                <p class="fs-5 text-muted">Increase</p>
                            </div>
                            <i class="bi bi-graph-up-arrow fs-1 primary-text"></i>
                        </div>
                    </div>
                </div>

                <div class="row my-5">
                    <h3 class="fs-4 mb-3 text-muted">Recent Orders</h3>
                    <div class="col">
                        <div class="bg-white rounded-3 shadow-sm border p-3">
                            <div class="mb-3">
                                <label for="exampleSelect2" class="form-label">Example Select2</label>
                                <select class="form-select select2-basic" id="exampleSelect2" data-placeholder="Choose anything" multiple>
                                    <option></option> <!-- Kosongkan untuk placeholder -->
                                    <option value="AL">Alabama</option>
                                    <option value="WY">Wyoming</option>
                                    <option value="NY">New York</option>
                                    <option value="TX">Texas</option>
                                    <option value="CA">California</option>
                                    <option value="FL">Florida</option>
                                </select>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover" id="recentOrdersTable">
                                    <thead>
                                        <tr>
                                            <th scope="col" width="50">#</th>
                                            <th scope="col">Product</th>
                                            <th scope="col">Customer</th>
                                            <th scope="col">Price</th>
                                            <th scope="col">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <th scope="row">1</th>
                                            <td>Television</td>
                                            <td>Jonny</td>
                                            <td>$1200</td>
                                            <td><span class="badge bg-custom-warning text-dark">Pending</span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row">2</th>
                                            <td>Laptop</td>
                                            <td>Kenny</td>
                                            <td>$750</td>
                                            <td><span class="badge bg-custom-success">Delivered</span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row">3</th>
                                            <td>Cell Phone</td>
                                            <td>Jenny</td>
                                            <td>$600</td>
                                            <td><span class="badge bg-custom-warning text-dark">Pending</span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row">4</th>
                                            <td>Fridge</td>
                                            <td>Killy</td>
                                            <td>$300</td>
                                            <td><span class="badge bg-custom-success">Delivered</span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row">5</th>
                                            <td>Books</td>
                                            <td>Filly</td>
                                            <td>$120</td>
                                            <td><span class="badge bg-custom-success">Delivered</span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row">6</th>
                                            <td>Gold</td>
                                            <td>Bumbo</td>
                                            <td>$1800</td>
                                            <td><span class="badge bg-custom-warning text-dark">Pending</span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row">7</th>
                                            <td>Pen</td>
                                            <td>Bilbo</td>
                                            <td>$75</td>
                                            <td><span class="badge bg-custom-success">Delivered</span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row">8</th>
                                            <td>Notebook</td>
                                            <td>Frodo</td>
                                            <td>$36</td>
                                            <td><span class="badge bg-custom-warning text-dark">Pending</span></td>
                                        </tr>
                                        <tr>
                                            <th scope="row">9</th>
                                            <td>Phone Holder</td>
                                            <td>Shire</td>
                                            <td>$12</td>
                                            <td><span class="badge bg-custom-success">Delivered</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <!-- /Page Content -->

    </div>

    <!-- JQuery (Required for DataTables and Select2) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/2.0.7/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.7/js/dataTables.bootstrap5.min.js"></script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Custom JS -->
    <script src="<?= base_url(); ?>js/script2.js"></script>
</body>

</html>