<?php

	//If a search term hasn't been passed, redirect the user back to the search page
	if(!isset($_REQUEST['term']))
	{
		header('Location: index.php');
        exit;
	}

	//Getting the results per page value from the query string
	//Checking whether the number of results per page has been passed and it is a positive number
	if(isset($_REQUEST['rpp']) && intval($_REQUEST['rpp']) > 0)
	{
		//Setting the number of results per page
		$perPage = intval($_REQUEST['rpp']);
	}
	//If the number of results per page hasn't been passed or is not a positive number, set the value to 10
	else
	{
		$perPage = 10;
	}
	
	//Getting the page number from the query string
	//Checking whether the page number has been passed and it is a positive number
	if(isset($_REQUEST['page']) && intval($_REQUEST['page']) > 0)
	{
		$currPage = intval($_REQUEST['page']);
	}
	//If the page number hasn't been passed or is not a positive number, set the page number to 1
	else
	{
		$currPage = 1;
	}
	//Calculating the starting value for quering the displaying results (for the LIMIT clause)
	$resultsBegin = ($currPage-1)*$perPage;

	//New database connection
	$mysqli  = new mysqli ('localhost', 'scott', 'tiger', 'courses');
	if($mysqli->connect_errno)
	{
		die("Database connection failed");
	}
	
	$mysqli->set_charset("utf8");
	
	//If a search term has been passed in the query string 
	//Getting the search term from query string
	$term = $_REQUEST['term'];
	$term = $mysqli->real_escape_string($term);
	
	//SQL query for getting all the results for counting how many rows returned (using the search algorithm, explained below)
	$sql = "
		SELECT id, award, title, level, summary, relevance
		FROM
		(
			(SELECT id, award, title, level, summary, 1 AS relevance
			FROM course
			WHERE title LIKE '%$term%')
			UNION ALL
			(SELECT id, award, title, level, summary, 2 AS relevance
			FROM course
			WHERE summary LIKE '%$term%')
		    UNION ALL
		    (SELECT id, award, title, level, summary, 3 AS relevance
			FROM course
			WHERE overview LIKE '%$term%')
		    UNION ALL
		    (SELECT id, award, title, level, summary, 4 AS relevance
			FROM course
			WHERE subject LIKE '%$term%')    
		    UNION ALL
		    (SELECT id, award, title, level, summary, 5 AS relevance
			FROM course
			WHERE wyl LIKE '%$term%') 
		    UNION ALL
		    (SELECT id, award, title, level, summary, 6 AS relevance
			FROM course
			WHERE careers LIKE '%$term%') 
		) a
		GROUP BY id";
		
	//Getting and storing the results	
	$result = $mysqli->query($sql)
		or die($mysqli->error);

	//Calculating the total number of results by using the num_rows method
	$totalResults = $result->num_rows;
	
	//If there are any results to display
	if($totalResults)
	{
		//Calculating the total number of pages
		$numberOfPages = ceil($totalResults / $perPage);	
		
		//If there is more than one page to display
		if($numberOfPages>1)
		{		
			//Creating an array of hyperlinks to corresponding pages
			for($i=1; $i<=$numberOfPages; $i++)
			{
				//Adding a hyperlink to the necessary page to the array, keeping the search term and results per page value
				$pages[$i] = "<a href=results.php?term=$term&page=$i&rpp=$perPage>$i</a>";
			}	
		}		

		//SQL query to get the list of matching results, sorting them by relevance (determined by where search term is encountered, from 1 to 6, e.g. if search term is found in the title, gives relevance of 1, thus treating it as more important and displaying in the top). According to current page number and how many results can be listed per page, limiting the number of results with LIMIT clause
		$sql = "

			SELECT id, award, title, level, summary, relevance
			FROM
			(
				(SELECT id, award, title, level, summary, 1 AS relevance
				FROM course
				WHERE title LIKE '%$term%')
				UNION ALL
				(SELECT id, award, title, level, summary, 2 AS relevance
				FROM course
				WHERE summary LIKE '%$term%')
			    UNION ALL
			    (SELECT id, award, title, level, summary, 3 AS relevance
				FROM course
				WHERE overview LIKE '%$term%')
			    UNION ALL
			    (SELECT id, award, title, level, summary, 4 AS relevance
				FROM course
				WHERE subject LIKE '%$term%')    
			    UNION ALL
			    (SELECT id, award, title, level, summary, 5 AS relevance
				FROM course
				WHERE wyl LIKE '%$term%') 
			    UNION ALL
			    (SELECT id, award, title, level, summary, 6 AS relevance
				FROM course
				WHERE careers LIKE '%$term%') 
			) a
			GROUP BY id
			ORDER BY relevance, title ASC, level DESC
			LIMIT $resultsBegin, $perPage";
		
		//Getting the results from the database
		$result = $mysqli->query($sql)
			or die($mysqli->error);
			
		//Adding the results as a hyperlink to an array
		$resultsArray = Array();					//Creating an empty array
		while ($row = $result->fetch_assoc())		//Iterating through each row of the results
		{
			//Adding to the array a hyperlink to the course page with a paragraph for the summary with custom class
			$resultsArray[] = "<a class='courseLink'href=course.php?id=$row[id]>$row[award] $row[title] - $row[level]</a></div></div><div class='row'><div class='col-sm-8 summary'>$row[summary]</div></div>";
		}
	}
	
?>
<!DOCTYPE html>
<html lang="en">
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Courses</title>

		<script src="external/jquery/jquery.js"></script>

		<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
		<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>		

		<link rel="stylesheet" href="menu.css">
		<link rel="stylesheet" href="results.css">		
	</head>
	<body>

		<?php include 'menu.php'; ?>


		<div class = "container">
			<div class = "row">
				<div class="col-sm-10">
		  			<a href="index.php" class="btn btn-default" role="button"><span class="glyphicon glyphicon-triangle-left"></span> Back to Search</a>
		  		</div>
		  		<div class="col-sm-2">
	  			<form action="results.php">
					<input type="hidden" name="term" value="<?=$term?>"></input>
					<label for ="dropdown">Results: <br/></label>
					<select id = "dropdown" name = "rpp" onchange="this.form.submit()">
						<!-- Checking how many results are currently displayed per page, setting the selected value of the dropdown accordingly -->
						<option value = "5" <?php echo $perPage == 5 ? 'selected' : ''; ?>>5</option>
						<option value = "10" <?php echo $perPage == 10 ? 'selected' : ''; ?>>10</option>
						<option value = "25" <?php echo $perPage == 25 ? 'selected' : ''; ?>>25</option>
						<option value = "50" <?php echo $perPage == 50 ? 'selected' : ''; ?>>50</option>
					</select>
				</form>
				</div>
			</div>
			<div class = "row">
				<div class = "col-sm-12">
					<!-- Printing the total of results -->
					<div>Results found: <?=$totalResults?></div>
				</div>
			</div>

			<?php if ($totalResults)									//If there are any results to display
			{	
				foreach ($resultsArray as $course)						//Iterating through each result
				{
					echo '<div class="row topMargin"><div class = "col-sm-12">';	//Bootstrap row + full-width column with custom topMargin class
					echo $course;										//Printing the hyperlink
				}
			
				if($numberOfPages>1)							//If there's more than one page of results
				{		
					echo '<div class = "row paginationCentered"><div class = "col-sm-12"><ul class="pagination">';				//Bootstrap row, columns, using ul with pagination and custom paginationCentered class
					foreach ($pages as $page => $link)															//Iterating throuch each page in array
					{
						//If the page number is the current page, print link as .active <li>
						if ($page == $currPage)
						{
							echo '<li class="active">';
							echo "<a>$currPage</a>";						//No href for <a> so the page number isn't clickable
							echo '</li>';

						}
						//Otherwise, print link as normal <li>
						else
						{
							echo '<li>';
							echo $link;
							echo '</li>';
						}
					}
					echo '</ul></div></div>';						//Closing ul, column and row
				}
			}?>
		</div>
	</body>
</html>