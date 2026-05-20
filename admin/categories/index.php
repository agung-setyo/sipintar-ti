<?php

include_once __DIR__ . '/../../config/database.php';

$result = mysqli_query(
    $conn,
    "SELECT * FROM categories"
);
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navbar.php'; ?>

<div class="d-flex">

<?php include '../../includes/sidebar.php'; ?>

<div class="container p-4">

<h3>Categories</h3>

<a href="create.php"
class="btn btn-primary mb-3">

Tambah Category

</a>

<table class="table table-bordered">

<tr>

<th>No</th>
<th>Name</th>
<th>Description</th>
<th>Action</th>

</tr>

<?php
$no = 1;

while($row = mysqli_fetch_assoc($result)) :
?>

<tr>

<td><?= $no++; ?></td>

<td><?= $row['name']; ?></td>

<td><?= $row['description']; ?></td>

<td>

<a href="edit.php?id=<?= $row['id']; ?>"
class="btn btn-warning btn-sm">

Edit

</a>

<a href="delete.php?id=<?= $row['id']; ?>"
class="btn btn-danger btn-sm">

Delete

</a>

</td>

</tr>

<?php endwhile; ?>

</table>

</div>

</div>

<?php include '../../includes/footer.php'; ?>