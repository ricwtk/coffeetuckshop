<?php
	$mysqli = new mysqli("localhost", "root", "root", "coffee");
	# if password is set, check password
	# if password is wrong, show a dialog and close window
	# if password is right, proceed

	if (isset($_POST['name'])) {
		# if name is set, check if paidamt is a currency value
		# if true, convert paid amount to number of capsule
		# get capsule price
		$res = $mysqli->query("select price from capsuleprice order by date desc");
		$price = $res->fetch_assoc()['price'];
		# convert paidamt to number of capsule
		$newcash = $_POST['paidamt'];
		# add the number of capsule capsule left and save to table
		$res = $mysqli->query("select cashleft from cashleft where name = '" . $_POST['name'] ."'");
		$cleft = $res->fetch_assoc()['cashleft'];
		$cleft = $cleft + $newcash;
		$mysqli->query("update cashleft set cashleft = " . $cleft . " where name = '" . $_POST['name'] . "'");		
		# save to entry table
		$mysqli->query("insert into entry (datetime, name, action, cashleft)" 
			. " values (current_timestamp, '" . $_POST['name'] . "', "
			. "'GBP" . $_POST['paidamt'] . "=+" . $newcash . "', " . $cleft . ")");
		# reload page to prevent resending of information
		header("Location: updatepayment.php");
	}
	
?>
<!DOCTYPE html>
<html>
<head>
	<meta name="robots" content="noindex,nofollow">
	<meta name=viewport content="width=device-width, initial-scale=1">
	<title>EPG Begbroke Coffee Payment Update</title>
	<link href='https://fonts.googleapis.com/css?family=Oswald' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" type="text/css" href="style.css">
	<script type="text/javascript">
		function closeWindow() {
			window.open('','_parent','');
			window.close();
		}
	</script>
</head>

<body>
	<div class='header'>EPG Coffee Tuck Shop Payment</div>

	<div class='content center'>
	<?php
		# get all names
		$res = $mysqli->query('select * from namelist');
		# loop through all names
		while ($row = $res->fetch_assoc()) {
			$cres = $mysqli->query("select cashleft from cashleft where name = '" . $row['name'] . "'");
			$cashleft = $cres->fetch_assoc()['cashleft'];
			# get capsule price
      $res = $mysqli->query("select price from capsuleprice order by date desc");
      $price = $res->fetch_assoc()['price'];
			$capsuleleft = round($cashleft / $price, 1);
			print "
			<div class='namelist'>
				<form enctype='multipart/form-data' method='post'>
					<div class='name left'>" . $row['displayname'] . "
						<input type='text' name='name' value='" . $row['name'] . "' hidden>
					</div> 
					<div class='avail'>£ " . number_format($cashleft,2,'.','') . " (" . $capsuleleft . ")</div>
					<div class='gbppaid'>
						<input type='text' id='paidamt' name='paidamt' value=0>
					</div>
					<input type='submit' id='submitpay-" . $row['name'] . "' hidden>
					<label for='submitpay-" . $row['name'] . "' class='submitpayment button'>Pay</label>
				</form>
			</div>
			";
		}
	?>
	</div>
</body>
</html>
