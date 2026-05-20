<?php

include_once __DIR__ . '/../../config/database.php';

$id = $_GET['id'];

$data = mysqli_query(
    $conn,
    "SELECT * FROM categories
    WHERE id='$id'"
);

$row = mysqli_fetch_assoc($data);

if(isset($_POST['submit']))
{
    $name = $_POST['name'];

    $description =
    $_POST['description'];

    mysqli_query(

        $conn,

        "UPDATE categories

        SET

        name='$name',
        description='$description'

        WHERE id='$id'"
    );

    header(
        "Location: index.php"
    );
}
?>

<?php include '../../includes/header.php'; ?>

<div class="container mt-5">

<h3>Edit Category</h3>

<form method="POST">

<div class="mb-3">

<label>Name</label>

<input
type="text"
name="name"
value="<?= $row['name']; ?>"
class="form-control">

</div>

<div class="mb-3">

<label>Description</label>

<textarea
name="description"
class="form-control"><?= $row['description']; ?></textarea>

</div>

<button
type="submit"
name="submit"
class="btn btn-warning">

Update

</button>

</form>

</div>

<?php include '../../includes/footer.php'; ?>