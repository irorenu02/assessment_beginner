<?php
include "../db.php";

$booking_id = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : 0;
$message = "";

$booking = mysqli_fetch_assoc(mysqli_query($conn, "
  SELECT b.*, c.full_name, s.service_name
  FROM bookings b
  JOIN clients c ON b.client_id = c.client_id
  JOIN services s ON b.service_id = s.service_id
  WHERE b.booking_id = $booking_id
"));

$paid = mysqli_fetch_assoc(mysqli_query($conn, "SELECT IFNULL(SUM(amount_paid),0) AS total_paid FROM payments WHERE booking_id = $booking_id"));
$amount_paid_so_far = (float)$paid['total_paid'];
$balance = $booking['total_cost'] - $amount_paid_so_far;

if (isset($_POST['record_payment'])) {
  $amount = (float)$_POST['amount_paid'];
  $method = $_POST['method'];

  if ($amount <= 0) {
    $message = "Payment amount must be greater than zero.";
  } else {
    mysqli_query($conn, "INSERT INTO payments (booking_id, amount_paid, method, payment_date)
      VALUES ($booking_id, $amount, '$method', NOW())");

    $new_total_paid = $amount_paid_so_far + $amount;
    $new_status = $new_total_paid >= $booking['total_cost'] ? 'PAID' : 'PARTIAL';

    mysqli_query($conn, "UPDATE bookings SET status = '$new_status' WHERE booking_id = $booking_id");

    header("Location: payments_list.php");
    exit;
  }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Process Payment</title>
</head>
<body>
<?php include "../nav.php"; ?>

<h2>Process Payment</h2>
<p style="color:red;"><?php echo $message; ?></p>

<?php if ($booking) { ?>
  <p><strong>Client:</strong> <?php echo $booking['full_name']; ?><br>
  <strong>Service:</strong> <?php echo $booking['service_name']; ?><br>
  <strong>Total Cost:</strong> ₱<?php echo number_format($booking['total_cost'], 2); ?><br>
  <strong>Paid So Far:</strong> ₱<?php echo number_format($amount_paid_so_far, 2); ?><br>
  <strong>Balance:</strong> ₱<?php echo number_format($balance, 2); ?></p>

  <form method="post">
    <label>Amount Paid</label><br>
    <input type="number" step="0.01" min="0.01" name="amount_paid" required><br><br>

    <label>Payment Method</label><br>
    <select name="method">
      <option value="CASH">Cash</option>
      <option value="CARD">Card</option>
      <option value="BANK">Bank Transfer</option>
    </select><br><br>

    <button type="submit" name="record_payment">Record Payment</button>
  </form>
<?php } else { ?>
  <p>Booking not found.</p>
<?php } ?>
</body>
</html>
