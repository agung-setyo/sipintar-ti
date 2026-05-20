<?php

include_once __DIR__ . '/../../config/database.php';

$id = $_GET['id'];

$itemQuery = mysqli_query(
    $conn,
    "SELECT * FROM items WHERE id='$id'"
);

$item = mysqli_fetch_assoc($itemQuery);

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
        "UPDATE items SET
            category_id='$category_id',
            item_code='$item_code',
            name='$name',
            description='$description',
            stock='$stock',
            `condition`='$condition',
            location='$location',
            status='$status'
        WHERE id='$id'"
    );

    header(
        "Location: index.php"
    );
    exit;
}
?>

<?php include '../../includes/header.php'; ?>

<div class="container mt-5">

<h3>Edit Item</h3>

<form method="POST">

<div class="mb-3">

<label>Category</label>

<select
name="category_id"
class="form-control">

<?php while($cat = mysqli_fetch_assoc($categories)) : ?>

<option value="<?= $cat['id']; ?>" <?= $cat['id'] === $item['category_id'] ? 'selected' : ''; ?>>

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
value="<?= htmlspecialchars($item['item_code']); ?>"
class="form-control">

</div>

<div class="mb-3">

<label>Name</label>

<input
type="text"
name="name"
value="<?= htmlspecialchars($item['name']); ?>"
class="form-control">

</div>

<div class="mb-3">

<label>Description</label>

<textarea
name="description"
class="form-control"><?= htmlspecialchars($item['description']); ?></textarea>

</div>

<div class="mb-3">

<label>Stock</label>

<input
type="number"
name="stock"
value="<?= htmlspecialchars($item['stock']); ?>"
class="form-control">

</div>

<div class="mb-3">

<label>Condition</label>

<select
name="condition"
class="form-control">

<option value="baik" <?= $item['condition'] === 'baik' ? 'selected' : ''; ?>>
Baik
</option>

<option value="rusak_ringan" <?= $item['condition'] === 'rusak_ringan' ? 'selected' : ''; ?>>
Rusak Ringan
</option>

<option value="rusak" <?= $item['condition'] === 'rusak' ? 'selected' : ''; ?>>
Rusak
</option>

</select>

</div>

<div class="mb-3">

<label>Location</label>

<input
type="text"
name="location"
value="<?= htmlspecialchars($item['location']); ?>"
class="form-control">

</div>

<div class="mb-3">

<label>Status</label>

<select
name="status"
class="form-control">

<option value="available" <?= $item['status'] === 'available' ? 'selected' : ''; ?>>
Available
</option>

<option value="unavailable" <?= $item['status'] === 'unavailable' ? 'selected' : ''; ?>>
Unavailable
</option>

</select>

</div>

<button
type="submit"
name="submit"
class="btn btn-primary">

Update

</button>

</form>

</div>
