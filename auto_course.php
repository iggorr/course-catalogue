<?php

	//New database connection
	$mysqli  = new mysqli ('localhost', 'scott', 'tiger', 'courses');
	
	if($mysqli->connect_errno)
	{
		die("Database connection failed");
	}
	
	$mysqli->set_charset("utf8");
	
	//Getting the search term
	$term = $mysqli->real_escape_string($_REQUEST['term']);
	
	//Getting the results from database as an array
	$sql = "
		SELECT DISTINCT title
		FROM course
		WHERE title LIKE '%$term%'
		ORDER BY title ASC
		LIMIT 10";
		
	$return = array();
	$result = $mysqli->query($sql);
	while($row = $result->fetch_array())
	{
		array_push($return, $row[0]);
	}
	//Returning a list of found course titles
	echo json_encode($return);	
?>