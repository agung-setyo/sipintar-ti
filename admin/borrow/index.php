<?php

include_once __DIR__ . '/../../config/database.php';

$query = mysqli_query(

    $conn,

    "SELECT borrow_requests.*,
    users.name

    FROM borrow_requests

    JOIN users

    ON borrow_requests.user_id =
    users.id

    ORDER BY borrow_requests.id DESC"
);
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navbar.php'; ?>

<div class="d-flex">

<?php include '../../includes/sidebar.php'; ?>

<div class="container p-4">

<h3>Borrow Requests</h3>

<table class="table table-bordered">

<tr>

<th>No</th>
<th>User</th>
<th>Code</th>
<th>Status</th>
<th>Action</th>

</tr>

<?php
$no = 1;

while($row = mysqli_fetch_assoc($query)) :
?>

<tr>

<td><?= $no++; ?></td>

<td><?= $row['name']; ?></td>

<td><?= $row['request_code']; ?></td>

<td><?= $row['status']; ?></td>

<td>

<a href="approve.php?id=<?= $row['id']; ?>"
class="btn btn-success btn-sm">

Approve

</a>

<a href="reject.php?id=<?= $row['id']; ?>"
class="btn btn-danger btn-sm">

Reject

</a>

<a href="return.php?id=<?= $row['id']; ?>"
class="btn btn-primary btn-sm">

Returned

</a>

</td>

</tr>

<?php endwhile; ?>

</table>

</div>

</div>

<?php include '../../includes/footer.php'; ?>