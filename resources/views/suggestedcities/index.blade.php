<?php

// remove the header columns //city	state	pop	lat	lon
array_shift($citiesArr);

// Submitted coordinates
$inputLat = 33.9850;
$inputLon = -118.4695;

echo "<html><body>";
echo "Input Lat: " . $inputLat . " &nbsp; ";
echo "Input Lon: " . $inputLon . "\n";


$distArr = array();
$popArr = array();
foreach($citiesArr as $cityIndex => $row) {

	if (empty($row)) {
		continue;
	}

	$arr = explode("\t", $row);
	
	// Create an array of population sizes using the same key as the key
	// in the citiesArr in order to sort easily by population size
	$population = $arr[2];
	$popArr[$cityIndex] = $population;
	
	$latDB = $arr[3];
	$lonDB = $arr[4];
	$distance = distance($inputLat, $inputLon, $latDB, $lonDB);
	// Create an array of distances using the same key as the key
	// in the citiesArr in order to sort easily by distance
	$distArr[$cityIndex] = $distance;
	
}

// Set the number of nearest cities to examine
$nearestNum = 100;
// Sort the cities by closest to furthest away
asort($distArr);
// Extract the nearest cities into their own array for comparison purposes 
$nearestCitiesIndexArr = array_slice($distArr, 0, $nearestNum, true);

// Extract the 20 most populated cities from the nearest cities index
$count = 0;
arsort($popArr);
$mostPopArr = array();
foreach($popArr as $cityIndex => $pop) {

	if (isset($nearestCitiesIndexArr[$cityIndex])) {
		$count++;
		$mostPopArr[] = $cityIndex;
		if ($count == 20) {
			break;
		}
	}	
}

// Display the 20 most populated cities found in the nearest data set
echo "<p>The 20 most populated cities nearest to the submitted coordinates.</p>"; 
displayTable($mostPopArr, $citiesArr, $nearestCitiesIndexArr);
// Find the 20 nearest cities that are closest to the median population number

// Get the median population number
$tmpPopArr = $popArr;
rsort($tmpPopArr); 
$middle = round(count($tmpPopArr) / 2); 
$median = $tmpPopArr[$middle-1]; 
unset($tmpPopArr);

// Set the difference of the population size of each city from the median into
// a sortable array
$medianDiffArr = array();
foreach($popArr as $cityIndex => $pop) {
	$medianDiffArr[$cityIndex] = abs($median - $pop);
}
// Sort the median array so that the smallest differences are first
asort($medianDiffArr);

// Set the 20 nearest cities that are closest to the median popluation number 
// into an array
$count = 0;
$mostPopArr = array();
foreach($medianDiffArr as $cityIndex => $val) {

	if (isset($nearestCitiesIndexArr[$cityIndex])) {
		$count++;
		$medianCityArr[] = $cityIndex;
		if ($count == 20) {
			break;
		}
	}	
}


// Display the 20 nearest cities that are closest to the median population number
echo "<p>The 20 nearest cities that are closest to the median population size: " . number_format($median) . ".</p>"; 
displayTable($medianCityArr, $citiesArr, $nearestCitiesIndexArr);

echo "</body></html>";

// Simple function for displaying data in a table 
function displayTable($arr, $citiesArr, $nearestCitiesIndexArr) {

	echo "<table cellpadding='4' cellspacing='0' border='1'>";
	echo "<tr><td> &nbsp; </td><td>City, State</td><td>Population</td><td>Distance</td></tr>";
	foreach($arr as $j => $cityIndex) {
		$row = explode("\t", $citiesArr[$cityIndex]);
		echo "<tr><td>" . ($j + 1) . "</td><td>";
		echo $row[0] . ', ' . $row[1];
		echo "</td><td align='right'>";
		echo number_format($row[2]);
		echo "</td><td align='right'>";
		echo number_format($nearestCitiesIndexArr[$cityIndex]);
		echo "</td></tr>";
	}
	echo "</table>";

}


//https://www.geodatasource.com/developers/php
function distance($lat1, $lon1, $lat2, $lon2, $unit = "M") {

	$theta = $lon1 - $lon2;
	$dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));

	$dist = acos($dist);
	$dist = rad2deg($dist);

	$miles = $dist * 60 * 1.1515;
	$unit = strtoupper($unit);

	if ($unit == "K") {
		return ($miles * 1.609344);
	} else if ($unit == "N") {
		return ($miles * 0.8684);
	} else {
		return $miles;
	}
	
}
