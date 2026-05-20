<?php

include_once __DIR__ . '/../../config/session.php';
include_once __DIR__ . '/../../config/database.php';
include '../../middleware/auth.php';
include '../../middleware/admin.php';

global $conn;

$query = mysqli_query(
    $conn,
    "SELECT * FROM security_events
    ORDER BY id DESC"
);
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navbar.php'; ?>

<div class="d-flex">

<?php include '../../includes/sidebar.php'; ?>

<div class="container p-4">

<h3>Security Events</h3>

<table class="table table-bordered">

<tr>

<th>No</th>
<th>Event Type</th>
<th>Severity</th>
<th>IP Address</th>
<th>Description</th>
<th>Date</th>

</tr>

<?php
$no = 1;

while($row = mysqli_fetch_assoc($query)) :
?>

<tr>

<td><?= $no++; ?></td>

<td><?= htmlspecialchars($row['event_type']); ?></td>

<td><?= htmlspecialchars($row['severity']); ?></td>

<td><?= htmlspecialchars($row['ip_address']); ?></td>

<td><?= htmlspecialchars($row['description']); ?></td>

<td><?= htmlspecialchars($row['created_at']); ?></td>

</tr>

<?php endwhile; ?>

</table>

</div>

</div>

<?php include '../../includes/footer.php'; ?>