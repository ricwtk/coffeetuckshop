#!/usr/bin/php
<?php
	# activate with crontab everyday
	$mysqli = new mysqli("localhost", "root", "root", "epgcoffee");
	# get all names from namelist
	$res = $mysqli->query('select * from namelist');
	# loop through all names
	while ($row = $res->fetch_assoc()) {
		# insert new entry for every name in consumed
		$mysqli->query("insert into consumed (name, date, used) values ('"
			. $row['name'] . "', current_date, '0')");
	}
?>