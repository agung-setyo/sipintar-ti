<?php

include_once __DIR__ . '/../../config/database.php';

$categories = mysqli_query(
    $conn,
    "SELECT * FROM categories"
);

if(isset($_POST['submit']))
{
    $category_id = $_POST['category_id'];

    $item_code = $_POST['item_code'];

    $name = $_POST['name'];

    $description = $_POST['description'];

    $stock = $_POST['stock'];

    $condition = $_POST['condition'];

    $location = $_POST['location'];

    $status = $_POST['status'];

    mysqli_query(

        $conn,

        "INSERT INTO items
        (
            category_id,
            item_code,
            name,
            description,
            stock,
            `condition`,
            location,
            status
        )

        VALUES
        (
            '$category_id',
            '$item_code',
            '$name',
            '$description',
            '$stock',
            '$condition',
            '$location',
            '$status'
        )"
    );

    header(
        "Location: index.php"
    );
}
?>

<?php include '../../includes/header.php'; ?>

<div class="container mt-5">

<h3>Tambah Item</h3>

<form method="POST">

<div class="mb-3">

<label>Category</label>

<select
name="category_id"
class="form-control">

<?php while($cat = mysqli_fetch_assoc($categories)) : ?>

<option value="<?= $cat['id']; ?>">

<?= $cat['name']; ?>

</option>

<?php endwhile; ?>

</select>

</div>

<div class="mb-3">

<label>Item Code</label>

<input
type="text"
name="item_code"
class="form-control">

</div>

<div class="mb-3">

<label>Name</label>

<input
type="text"
name="name"
class="form-control">

</div>

<div class="mb-3">

<label>Description</label>

<textarea
name="description"
class="form-control"></textarea>

</div>

<div class="mb-3">

<label>Stock</label>

<input
type="number"
name="stock"
class="form-control">

</div>

<div class="mb-3">

<label>Condition</label>

<select
name="condition"
class="form-control">

<option value="baik">
Baik
</option>

<option value="rusak_ringan">
Rusak Ringan
</option>

<option value="rusak">
Rusak
</option>

</select>

</div>

<div class="mb-3">

<label>Location</label>

<input
type="text"
name="location"
class="form-control">

</div>

<div class="mb-3">

<label>Status</label>

<select
name="status"
class="form-control">

<option value="available">
Available
</option>

<option value="unavailable">
Unavailable
</option>

</select>

</div>

<button
type="submit"
name="submit"
class="btn btn-primary">

Simpan

</button>

</form>

</div>

<?php include '../../includes/footer.php'; ?>