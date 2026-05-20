<?php

include_once __DIR__ . '/../config/session.php';
include_once __DIR__ . '/../config/database.php';

include_once __DIR__ . '/../middleware/auth.php';
include_once __DIR__ . '/../middleware/admin.php';

$total_users = mysqli_num_rows(
    mysqli_query($conn, "SELECT * FROM users")
);

$total_items = mysqli_num_rows(
    mysqli_query($conn, "SELECT * FROM items")
);

$total_borrow = mysqli_num_rows(
    mysqli_query($conn, "SELECT * FROM borrow_requests")
);

$total_pending = mysqli_num_rows(
    mysqli_query(
        $conn,
        "SELECT * FROM borrow_requests
        WHERE status='pending'"
    )
);
?>

<?php include '../includes/header.php'; ?>
<?php include '../includes/navbar.php'; ?>

<div class="d-flex">

<?php include '../includes/sidebar.php'; ?>

<div class="container p-4">

<h3>Dashboard Admin</h3>

<div class="row mt-4">

<div class="col-md-3">

<div class="card shadow">

<div class="card-body">

<h5>Total Users</h5>

<h2><?= $total_users; ?></h2>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card shadow">

<div class="card-body">

<h5>Total Items</h5>

<h2><?= $total_items; ?></h2>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card shadow">

<div class="card-body">

<h5>Total Borrow</h5>

<h2><?= $total_borrow; ?></h2>

</div>

</div>

</div>

<div class="col-md-3">

<div class="card shadow">

<div class="card-body">

<h5>Pending</h5>

<h2><?= $total_pending; ?></h2>

</div>

</div>

</div>

</div>

</div>

</div>

<?php include '../includes/footer.php'; ?>