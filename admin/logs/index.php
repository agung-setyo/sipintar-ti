<?php

include_once __DIR__ . '/../../config/database.php';

$query = mysqli_query(

    $conn,

    "SELECT audit_logs.*,
    users.name

    FROM audit_logs

    LEFT JOIN users

    ON audit_logs.user_id =
    users.id

    ORDER BY audit_logs.id DESC"
);
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navbar.php'; ?>

<div class="d-flex">

<?php include '../../includes/sidebar.php'; ?>

<div class="container p-4">

<h3>Audit Logs</h3>

<table class="table table-bordered">

<tr>

<th>No</th>
<th>User</th>
<th>Action</th>
<th>Description</th>
<th>Date</th>

</tr>

<?php
$no = 1;

while($row = mysqli_fetch_assoc($query)) :
?>

<tr>

<td><?= $no++; ?></td>

<td><?= $row['name']; ?></td>

<td><?= $row['action']; ?></td>

<td><?= $row['description']; ?></td>

<td><?= $row['created_at']; ?></td>

</tr>

<?php endwhile; ?>

</table>

</div>

</div>

<?php include '../../includes/footer.php'; ?>