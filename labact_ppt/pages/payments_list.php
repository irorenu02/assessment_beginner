<?php
include "../db.php";

$payments = mysqli_query($conn, "
  SELECT p.*, b.booking_id, c.full_name, s.service_name, b.total_cost
  FROM payments p
  JOIN bookings b ON p.booking_id = b.booking_id
  JOIN clients c ON b.client_id = c.client_id
  JOIN services s ON b.service_id = s.service_id
  ORDER BY p.payment_date DESC
");
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Payments</title>
</head>
<body>
<?php include "../nav.php"; ?>

<h2>Payments</h2>
<p><a href="/labact_ppt/pages/bookings_list.php">Back to Bookings</a></p>

<table border="1" cellpadding="8">
  <tr>
    <th>Payment ID</th>
    <th>Booking</th>
    <th>Client</th>
    <th>Service</th>
    <th>Amount</th>
    <th>Method</th>
    <th>Date</th>
  </tr>
  <?php while ($row = mysqli_fetch_assoc($payments)) { ?>
    <tr>
      <td><?php echo $row['payment_id']; ?></td>
      <td><?php echo $row['booking_id']; ?></td>
      <td><?php echo $row['full_name']; ?></td>
      <td><?php echo $row['service_name']; ?></td>
      <td>₱<?php echo number_format($row['amount_paid'], 2); ?></td>
      <td><?php echo $row['method']; ?></td>
      <td><?php echo $row['payment_date']; ?></td>
    </tr>
  <?php } ?>
</table>
</body>
</html>
