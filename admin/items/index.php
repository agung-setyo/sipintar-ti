<?php

include_once __DIR__ . '/../../config/database.php';

$query = mysqli_query(

    $conn,

    "SELECT items.*,
    categories.name as category_name

    FROM items

    JOIN categories

    ON items.category_id =
    categories.id"
);
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navbar.php'; ?>

<div class="d-flex">

<?php include '../../includes/sidebar.php'; ?>

<div class="container p-4">

<h3>Items</h3>

<a href="create.php"
class="btn btn-primary mb-3">

Tambah Item

</a>

<table class="table table-bordered">

<tr>

<th>No</th>
<th>Code</th>
<th>Name</th>
<th>Category</th>
<th>Stock</th>
<th>Status</th>
<th>Action</th>

</tr>

<?php
$no = 1;

while($row = mysqli_fetch_assoc($query)) :
?>

<tr>

<td><?= $no++; ?></td>

<td><?= $row['item_code']; ?></td>

<td><?= $row['name']; ?></td>

<td><?= $row['category_name']; ?></td>

<td><?= $row['stock']; ?></td>

<td><?= $row['status']; ?></td>

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