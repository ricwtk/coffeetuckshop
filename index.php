<?php
	$mysqli = new mysqli("localhost", "root", "root", "coffee");
	# if name is set
	if (isset($_POST['name'])) {
		# -1 from capsule left and save to table
		$res = $mysqli->query("select cashleft from cashleft where name = '" . $_POST['name'] ."'");
		$cleft = $res->fetch_assoc()['cashleft'];
		# get capsule price
		$res = $mysqli->query("select price from capsuleprice order by date desc");
		$price = $res->fetch_assoc()['price'];
		# deduct price of 1 capsule from cash left
		$cleft = $cleft - $price;
		$mysqli->query("update cashleft set cashleft = " . $cleft . " where name = '" . $_POST['name'] . "'");
		# +1 in consumed and save to table
		$res = $mysqli->query("select used from consumed where name = '" . $_POST['name'] 
			. "' and date = current_date");
		$consd = $res->fetch_assoc()['used'];
		$consd = $consd + 1;
		$mysqli->query("update consumed set used = " . $consd . " where name = '" . $_POST['name'] 
			. "' and date = current_date");
		# save to entry table
		$mysqli->query("insert into entry (datetime, name, action, capsuleleft)" 
			. " values (current_timestamp, '" . $_POST['name'] . "', '-1*£" . $price . "', " . $cleft . ")");
		# reload page to prevent resending of information
		header("Location: index.php");
	}
?>
<!DOCTYPE html>
<html>
<head>
	<meta name="robots" content="noindex,nofollow">
	<meta name=viewport content="width=device-width, initial-scale=1">
	<title>EPG Begbroke Coffee</title>
	<link href='https://fonts.googleapis.com/css?family=Oswald' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>
	<div class='header'>EPG Coffee Tuck Shop</div>

	<div class='content'>
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
					<div class='name'>" . $row['displayname'] . "
						<input type='text' name='name' value='" . $row['name'] . "' hidden>
					</div> 
					<div class='avail'>£ " . number_format($cashleft,2,'.','') . " (" . $capsuleleft . ")</div>
					<input type='submit' id='submitconsume-" . $row['name'] . "' hidden>
					<label for='submitconsume-" . $row['name'] . "' class='consume button'></label>
				</form>
			</div>
			";
		}
	?>

		<br>
		
		<div id='chart_div'></div>

		<br>

		<div class='paymentupdate'>
			<form action='updatepayment.php' target='_blank' enctype='multipart/form-data' method='post'>
			<!--<fieldset>
				Password: 
				<input type='password' id='updatepaypw'>-->
				<input type='submit' value='Update Payment' id='updatepay' hidden>
				<label for='updatepay' class='button'>Update Payment</label>
			</fieldset>
			</form>
		</div>
	</div>
</body>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
	google.charts.load('current', {'packages':['corechart']});
	google.charts.setOnLoadCallback(drawChart);
	function drawChart() {
		<?php 
			# get all names
			$res = $mysqli->query('select * from namelist');
			# loop through all names
			$n = 0;
			$columntojoin = '';
			while ($row = $res->fetch_assoc()) {
				# create 1 table for each person
				print "var data_" . $row['name'] . " = new google.visualization.DataTable();\n";
				print "data_" . $row['name'] . ".addColumn('date', 'Date');\n";
				print "data_" . $row['name'] . ".addColumn('number', '" . $row['displayname'] . "');\n";
				$consdres = $mysqli->query("select date, used from consumed where name = '" . $row['name'] . "' order by date asc");
				while ($consdrow = $consdres->fetch_assoc()) {
					print "data_" . $row['name'] . ".addRow(["
						. "new Date('" . date('Y-m-d', strtotime($consdrow['date'])) . "'), " 
						. $consdrow['used']
						. "]);\n";
				}
				# join all tables with date as keys
				if ($n == 0) {
					print "var data = data_" . $row['name'] . ";\n";
					$n += 1;
					$columntojoin .= $n;
				}
				else {
					print "data = google.visualization.data.join(data, data_" . $row['name'] 
						.", 'full', [[0,0]], [" . $columntojoin . "], [1]);\n";
					$n += 1;
					$columntojoin .= ',' . $n;
				}
			}
		?>
	  
	  // Set chart options
	  var options = {
      title: 'Coffee Consumption',
      curveType: 'function',
      legend: { position: 'bottom' },
      pointSize: 5
    };

	  // Instantiate and draw our chart, passing in some options.
	  var chart = new google.visualization.LineChart(document.getElementById('chart_div'));
	  chart.draw(data, options);
	}
</script>
</html>
