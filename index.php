<?php
ini_set("auto_detect_line_endings", true);

function get_numeric($val) { 
  if (is_numeric($val)) { 
    return $val + 0; 
  } 
  return 0; 
}

function distanceCalculation($point1_lat, $point1_long, $point2_lat, $point2_long, $unit = 'km', $decimals = 6) {
	// Calculate the distance in degrees
	$degrees = rad2deg(acos((sin(deg2rad($point1_lat))*sin(deg2rad($point2_lat))) + (cos(deg2rad($point1_lat))*cos(deg2rad($point2_lat))*cos(deg2rad($point1_long-$point2_long)))));
 
	// Convert the distance in degrees to the chosen unit (kilometres, miles or nautical miles)
	switch($unit) {
		case 'km':
			$distance = $degrees * 111.13384; // 1 degree = 111.13384 km, based on the average diameter of the Earth (12,735 km)
			break;
		case 'mi':
			$distance = $degrees * 69.05482; // 1 degree = 69.05482 miles, based on the average diameter of the Earth (7,913.1 miles)
			break;
		case 'nmi':
			$distance =  $degrees * 59.97662; // 1 degree = 59.97662 nautic miles, based on the average diameter of the Earth (6,876.3 nautical miles)
	}
	return round($distance, $decimals);
}

function str_putcsv($data) {

	$fp = fopen('djaci_u_skoli.csv', 'w');

	foreach ($data as $fields) {
	    fputcsv($fp, $fields);
	}

	fclose($fp);
}

$citizen = fopen('files/citizens_1000.csv', 'r');
$skole = fopen('files/skole_srbija.csv', 'r');



// // // skip first 2 lines
fgetcsv($skole);
fgetcsv($skole);


while($data = fgetcsv($citizen, ",")) {

	// -------- remove the utf-8 BOM ----
	$str = preg_replace('/\x{FEFF}/u', '', $data);

	$point1[] = array(
		"lat" 	=> get_numeric($str[0]),
		"long"  => get_numeric($str[1])
	);
}


while($data = fgetcsv($skole, ",")) {

	$str = preg_replace('/\x{FEFF}/u', '', $data);

	if(isset($str[13])) {
		$exploded = explode(',', $str[13]);

	}

	$point2[] = array(
		"lat" 	=> (array_key_exists(0, $exploded)) ? get_numeric($exploded[0]) : 0.00,
		"long"  => (array_key_exists(1, $exploded)) ? get_numeric($exploded[1]) : 0.00,
		"id"	=> (array_key_exists(0, $str)) ? $str[0] : '',
		"naziv"	=> (array_key_exists(1, $str)) ? $str[1] : '',
		"adresa"	=> (array_key_exists(2, $str)) ? $str[2] : '',
		"pbroj"	=> (array_key_exists(3, $str)) ? $str[3] : '',
		"mesto"	=> (array_key_exists(4, $str)) ? $str[4] : '',
		"opstina"	=> (array_key_exists(5, $str)) ? $str[5] : '',
		"okrug"	=> (array_key_exists(6, $str)) ? $str[6] : '',
		"suprava"	=> (array_key_exists(7, $str)) ? $str[7] : '',
		"www"	=> (array_key_exists(8, $str)) ? $str[8] : '',
		"tel"	=> (array_key_exists(9, $str)) ? $str[9] : '',
		"fax"	=> (array_key_exists(10, $str)) ? $str[10] : '',
		"vrsta"	=> (array_key_exists(11, $str)) ? $str[11] : '',
		"max_broj_djaka"	=> (array_key_exists(12, $str)) ? (get_numeric($str[12]))*5 : 0,
		"max_broj_djaka_temp"	=> (array_key_exists(12, $str)) ? (get_numeric($str[12]))*5 : 0
	);
}

$total1 = (int)count($point1);
$total2 = (int)count($point2);


for($i = 0; $i < $total1; $i++)
{	
	for($j = 0; $j < $total2; $j++) {
		// udaljenost osoba na osnovu skole
		$km[$i][$j] = distanceCalculation($point1[$i]["lat"], $point1[$i]["long"], $point2[$j]["lat"], $point2[$j]["long"]);

	}

	asort($km[$i]);

	// b je redni broj najblizih skola djaku
	$b = array_keys($km[$i]);

	// brojac za niz najblizih skola
	$x = 0;
	while(($point2[$b[$x]]['max_broj_djaka']) <= 0) {
		$x++;
	}

	if(($point2[$b[$x]]['max_broj_djaka']) > 0) {

		$school_kid[$i]['k_lat'] = $point1[$i]['lat'];
		$school_kid[$i]['k_long'] = $point1[$i]['long'];
		$school_kid[$i]['s_lat'] = $point2[$b[$x]]['lat'];
		$school_kid[$i]['s_long'] = $point2[$b[$x]]['long'];
		$school_kid[$i]['tel'] = $point2[$b[$x]]['tel'];
		$school_kid[$i]['www'] = $point2[$b[$x]]['www'];

		$point2[$b[$x]]['max_broj_djaka']--;

		$school_name[$i] = $point2[$b[$x]]['naziv'];
	}

}

array_unshift($school_kid, array('k_lat', 'k_long', 's_lat', 's_long', 'tel', 'www'));
str_putcsv($school_kid);

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
	$array = array_count_values($school_name);
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
		$q = 0;
		foreach ($array as $k => $v) {
	?>
		<tr>
			<td><?php echo $q; ?></td>
			<td><?php echo $k; ?></td>
			<td><?php echo $v; ?></td>
		</tr>
		<?php $q++;
		}	?>
        </tr>
        </tbody>
</table>
</div>
</section>

</body>
</html>






