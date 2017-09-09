<?php
ini_set("auto_detect_line_endings", true);

require_once 'functions.php';

$citizen_array = csv_citizen_to_array('files/citizens_1000.csv');
$schools_array = csv_schools_to_array('files/skole_srbija.csv');

$school_kid = distance($citizen_array, $schools_array);

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Kids In School</title>

	<link rel="stylesheet" type="text/css" href="custom.css">
</head>
<body>
<?php 
	$array = array_count_values($school_kid['school_name']);
	arsort($array);
?>
<h1>Skole i broj djaka</h1>
<div class="tbl-header">
<section>
	<table cellpadding="0" cellspacing="0" border="0"">
	<thead>
		<tr>
			<th>ID</th>
			<th>Ime skole</th>
			<th>Broj djaka</th>
		</tr>
	</thead>
	</table>
</div>
<div class="tbl-content">
    <table cellpadding="0" cellspacing="0" border="0">
        <tbody>
	<?php 
		$id = 0;
		foreach ($array as $name_of_school => $number_of_students) {
	?>
		<tr>
			<td><?php echo $id; ?></td>
			<td><?php echo $name_of_school; ?></td>
			<td><?php echo $number_of_students; ?></td>
		</tr>
		<?php $id++;
		}	?>
        </tr>
        </tbody>
</table>
</div>
</section>

</body>
</html>

<?php

unset($school_kid['school_name']);

array_unshift($school_kid, array('student_lat', 'student_long', 'school_lat', 'school_long', 'tel', 'www'));
str_putcsv($school_kid);
