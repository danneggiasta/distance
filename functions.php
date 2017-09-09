<?php

function get_numeric($val) { 
  if (is_numeric($val)) { 
    return $val + 0; 
  } 
  return 0; 
}

function distanceCalculation($citizen_array_lat, $citizen_array_long, $schools_array_lat, $schools_array_long, $decimals = 6) {
	// Calculate the distance in degrees
	$degrees = rad2deg(acos((sin(deg2rad($citizen_array_lat))*sin(deg2rad($schools_array_lat))) + (cos(deg2rad($citizen_array_lat))*cos(deg2rad($schools_array_lat))*cos(deg2rad($citizen_array_long-$schools_array_long)))));

	$distance = $degrees * 111.13384;

	return round($distance, $decimals);
}

function str_putcsv($data) {

	$fp = fopen('djaci_u_skoli.csv', 'w');

	foreach ($data as $fields) {
	    fputcsv($fp, $fields);
	}

	fclose($fp);
}

function csv_citizen_to_array($file) {
	
	$fp = fopen($file, 'r');

	while(!feof($fp)) {		

		$data = fgetcsv($fp, ",");

		// -------- remove the utf-8 BOM ----
		$data = preg_replace('/\x{FEFF}/u', '', $data);

		$citizen_array[] = array(
			'lat'	=> get_numeric($data[0]),
			'long'	=> get_numeric($data[1])
		);
	}

	return $citizen_array;

}

function csv_schools_to_array($file) {

	$fp = fopen($file, 'r');

	fgetcsv($fp);
	fgetcsv($fp);

	while(!feof($fp)) {		

		$data = fgetcsv($fp, ",");

		// -------- remove the utf-8 BOM ----
		$data = preg_replace('/\x{FEFF}/u', '', $data);

		// separate latitude and longitude, if latitude and longitude exists, because all schools doesn't have provided both
		if(isset($data[13])) {
			$exploded = explode(',', $data[13]);

			if(array_key_exists(0, $exploded) && array_key_exists(1, $exploded) && get_numeric($exploded[0]) !== 0 && get_numeric($exploded[1]) !== 0) {
				
				$lat 	= (float)trim($exploded[0]);
				$long 	= (float)trim($exploded[1]);
				$name 	= trim($data[1]);
				$tel	= trim($data[9]); 
				$www	= trim($data[8]);
				$max_num_students	= get_numeric(trim($data[12])) * 5;

					// 	echo '<pre>';
					// var_dump($max_num_students);
					// echo '</pre>';

				// some latitudes and longitudes aren't correct
				if(($lat > 39 && $lat < 51) && ($long > 18 && $long < 31)) {
					$schools_array[] = array(
						'lat'	=> $lat,
						'long'	=> $long,
						'name'	=> $name,
						'tel'	=> $tel,
						'www'	=> $www,
						'max_num_students'	=> $max_num_students,
					);
				}
			}
		}		
	}

	return $schools_array;
}

function distance($citizen_array, $schools_array) {


	$total1 = (int)count($citizen_array);
	$total2 = (int)count($schools_array);

	for($i = 0; $i < $total1; $i++)
	{	
		for($j = 0; $j < $total2; $j++) {
			// udaljenost osoba na osnovu skole
			$km[$i][$j] = distanceCalculation($citizen_array[$i]["lat"], $citizen_array[$i]["long"], $schools_array[$j]["lat"], $schools_array[$j]["long"]);

		}

		asort($km[$i]);

		// b je redni broj najblizih skola djaku
		$b = array_keys($km[$i]);

		// brojac za niz najblizih skola
		$x = 0;
		while(($schools_array[$b[$x]]['max_num_students']) <= 0) {
			$x++;
		}

		if(($schools_array[$b[$x]]['max_num_students']) > 0) {

			$school_kid[$i]['k_lat'] = $citizen_array[$i]['lat'];
			$school_kid[$i]['k_long'] = $citizen_array[$i]['long'];
			$school_kid[$i]['s_lat'] = $schools_array[$b[$x]]['lat'];
			$school_kid[$i]['s_long'] = $schools_array[$b[$x]]['long'];
			$school_kid[$i]['tel'] = $schools_array[$b[$x]]['tel'];
			$school_kid[$i]['www'] = $schools_array[$b[$x]]['www'];

			$schools_array[$b[$x]]['max_num_students']--;

			$school_kid['school_name'][$i] = $schools_array[$b[$x]]['name'];
		}
	}

	return $school_kid;
}