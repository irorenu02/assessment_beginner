<?php
include "../db.php";

$message = "";

$tools = mysqli_query($conn, "SELECT * FROM tools ORDER BY tool_name ASC");
$bookings = mysqli_query($conn, "SELECT b.booking_id, c.full_name, s.service_name
    FROM bookings b
    JOIN clients c ON b.client_id = c.client_id
    JOIN services s ON b.service_id = s.service_id
    ORDER BY b.booking_id DESC");

if (isset($_POST['assign'])) {
  $booking_id = $_POST['booking_id'];
  $tool_id = $_POST['tool_id'];
  $qty_used = (int)$_POST['qty_used'];

  if ($booking_id == "" || $tool_id == "" || $qty_used <= 0) {
    $message = "Please fill in all fields correctly.";
  } else {
    $tool = mysqli_fetch_assoc(mysqli_query($conn, "SELECT tool_name, quantity_available FROM tools WHERE tool_id = $tool_id"));

    if (!$tool) {
      $message = "Selected tool was not found.";
    } elseif ($tool['quantity_available'] < $qty_used) {
      $message = "Not enough stock for {$tool['tool_name']}. Available: {$tool['quantity_available']}";
    } else {
      mysqli_query($conn, "INSERT INTO booking_tools (booking_id, tool_id, qty_used) VALUES ($booking_id, $tool_id, $qty_used)");
      mysqli_query($conn, "UPDATE tools SET quantity_available = quantity_available - $qty_used WHERE tool_id = $tool_id");
      $message = "Tool assigned successfully.";
    }
  }
}

$assignments = mysqli_query($conn, "
  SELECT bt.*, t.tool_name, c.full_name, b.booking_id
  FROM booking_tools bt
  JOIN tools t ON bt.tool_id = t.tool_id
  JOIN bookings b ON bt.booking_id = b.booking_id
  JOIN clients c ON b.client_id = c.client_id
  ORDER BY bt.created_at DESC
");
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Tools</title>
</head>
<body>
<?php include "../nav.php"; ?>

<h2>Tools Inventory</h2>
<p style="color:red;"><?php echo $message; ?></p>

<form method="post">
  <label>Booking</label><br>
  <select name="booking_id" required>
    <option value="">Select booking</option>
    <?php while ($b = mysqli_fetch_assoc($bookings)) { ?>
      <option value="<?php echo $b['booking_id']; ?>"><?php echo $b['booking_id']; ?> - <?php echo $b['full_name']; ?> (<?php echo $b['service_name']; ?>)</option>
    <?php } ?>
  </select><br><br>

  <label>Tool</label><br>
  <select name="tool_id" required>
    <option value="">Select tool</option>
    <?php while ($t = mysqli_fetch_assoc($tools)) { ?>
      <option value="<?php echo $t['tool_id']; ?>"><?php echo $t['tool_name']; ?> (Available: <?php echo $t['quantity_available']; ?>)</option>
    <?php } ?>
  </select><br><br>

  <label>Quantity Used</label><br>
  <input type="number" name="qty_used" min="1" value="1"><br><br>

  <button type="submit" name="assign">Assign Tool</button>
</form>

<h3>Current Inventory</h3>
<table border="1" cellpadding="8">
  <tr>
    <th>ID</th>
    <th>Tool Name</th>
    <th>Total</th>
    <th>Available</th>
  </tr>
  <?php
    $toolsAgain = mysqli_query($conn, "SELECT * FROM tools ORDER BY tool_name ASC");
    while ($row = mysqli_fetch_assoc($toolsAgain)) {
  ?>
    <tr>
      <td><?php echo $row['tool_id']; ?></td>
      <td><?php echo $row['tool_name']; ?></td>
      <td><?php echo $row['quantity_total']; ?></td>
      <td><?php echo $row['quantity_available']; ?></td>
    </tr>
  <?php } ?>
</table>

<h3>Assigned Tools</h3>
<table border="1" cellpadding="8">
  <tr>
    <th>Booking</th>
    <th>Client</th>
    <th>Tool</th>
    <th>Qty Used</th>
  </tr>
  <?php while ($a = mysqli_fetch_assoc($assignments)) { ?>
    <tr>
      <td><?php echo $a['booking_id']; ?></td>
      <td><?php echo $a['full_name']; ?></td>
      <td><?php echo $a['tool_name']; ?></td>
      <td><?php echo $a['qty_used']; ?></td>
    </tr>
  <?php } ?>
</table>
</body>
</html>
