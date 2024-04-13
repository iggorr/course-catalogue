<?php

	//New database connection
	$mysqli  = new mysqli ('localhost', 'scott', 'tiger', 'courses');
	if($mysqli->connect_errno)
	{
		die("Database connection failed");
	}
	
	$mysqli->set_charset("utf8");
	
	//If a course id hasn't been passed or is empty, redirect the user back to the search page
	if(!isset($_REQUEST['id']) || empty($_REQUEST['id']))
	{
		header('Location: index.php');
        exit;
	}
	//If a course id has been passed in the query string 
	else
	{
		//Getting the course id from query string
		$id = $_REQUEST['id'];
		$id = $mysqli->real_escape_string($id);
		
		//SQL query for getting the details of the course
		$sql = "
			SELECT id, title, level, award, summary, overview, wyl, careers
			FROM course
			WHERE id = '$id'";
		
		//Getting the data from database
		$result = $mysqli->query($sql)
			or die($mysqli->error);
			
		$row = $result->fetch_assoc();
		
		//SQL query for getting the list of ten most popular modules studied on the course (popularity determined by num). Getting the year of study through SCQF level
		$sql = "
			SELECT id, title, level-6 AS year
			FROM module JOIN cm ON id = module
			WHERE course = '$id'
			ORDER BY num DESC
			LIMIT 10";
			
		//Getting the results from the database
		$result = $mysqli->query($sql)
			or die($mysqli->error);	
			
		//Adding the results as a hyperlink to an array
		$moduleArray = Array();							//Creating an empty array
		while ($modules = $result->fetch_assoc())		//Iterating through each row of the results
		{
			//Adding to the array a hyperlink to the module descriptor for the current module
			$moduleArray[] = "<a href='http://www.modules.napier.ac.uk/Module.aspx?ID=$modules[id]' target='_blank' >$modules[title] (Year $modules[year])</a>";
		}

		//SQL query for getting the list of ten most related courses. The inner SELECT gets the ids of modules taught on the currently selected course. The outer SELECT gets the details of the courses that take the modules determined by the inner SELECT (not including the currently selected course, of course). When grouped by id, COUNT(*) can be used to allow the display of courses that share more modules with the current course than others, thus allowing to list them by relevance.
		$sql = "
			SELECT id, title, award
			FROM course JOIN cm ON id = course
			WHERE module IN
			(
				SELECT id
				FROM module JOIN cm ON id = module
				WHERE course = '$id'
			)
			AND id != '$id'
			GROUP BY id
			ORDER BY COUNT(*) DESC
			LIMIT 10";

		//Getting the results from the database
		$result = $mysqli->query($sql)
			or die($mysqli->error);	
			
		//Adding the results as a hyperlink to an array
		$courseArray = Array();							//Creating an empty array
		while ($courses = $result->fetch_assoc())		//Iterating through each row of the results
		{
			//Adding a hyperlink to the course page to the array
			$courseArray[] = "<a href=course.php?id=$courses[id]>$courses[award] $courses[title]</a>";
		}
	}
?>

<!DOCTYPE html>
<html lang="en">
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?=$row['title']?></title>

		<script src="external/jquery/jquery.js"></script>		

		<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
		<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>			

		<link rel="stylesheet" href="menu.css">
		<link rel="stylesheet" href="course.css">	
	</head>
	<body>

		<?php include 'menu.php'; ?>

		<div class = "container">

			<div class = "row">
				<div class="col-sm-12">
		  			<a href="index.php" class="btn btn-default" role="button"><span class="glyphicon glyphicon-triangle-left"></span> Back to Search</a>
		  		</div>
	  		</div>

			<div class="row">
				<div class="col-sm-12">
					<h1 id="courseTitle"><?php echo "$row[award] $row[title]" ?></h1>
				</div>
			</div>

	  		<div class="row">
	  			<div class="col-sm-3 text-left">
	  				<span class="courseDetails"><span class="bold">Napier Code: </span><?=$row['id']?></span> 
	  			</div>
	  			<div class="col-sm-3 text-center">	
	  				<span class="courseDetails"><span class="bold">Award: </span><?=$row['award']?></span> 
	  			</div>
	  			<div class="col-sm-3 text-right">
	  				<span class="courseDetails"><span class="bold">Study Level: </span><?=$row['level']?></span>  
	  			</div>
	  		</div>

			<div class="row">
				<div class="col-sm-9">
					<div id="summary"><?=$row['summary']?></div>
				</div>
			</div>

			<div class="row">
				<div class="col-sm-8">
					<div id="overview"><?=$row['overview']?></div>
				</div>
			</div>
			
			<!-- Displaying additional data in tabs -->
			<div id="tabs">

				<ul class="nav nav-tabs nav-justified">
					<li class="active"><a data-toggle="tab" href="#wyl">What you'll learn</a></li>
					<li><a data-toggle="tab" href="#careers">Careers</a></li>
					<li><a data-toggle="tab" href="#modules">Modules</a></li>	
					<li><a data-toggle="tab" href="#relCourses">Related Courses</a></li>		
				</ul>

				<div class="row">
					<div class="col-sm-8">
						<div class="tab-content">

							<div id="wyl" class="tab-pane fade in active">
								<p><?=$row['wyl']?></p>
							</div>

							<div id="careers" class="tab-pane fade">
								<p><?=$row['careers']?></p>
							</div>

							<div id="modules" class="tab-pane fade">
								<div class="tabHeader bold">Key modules include: </div>
								<?php foreach ($moduleArray as $module)			//Iterating through each module
								{
									echo $module;								//Printing the hyperlink
									echo '<br/>';								//Printing a newline
								}?>
							</div>

							<div id="relCourses" class="tab-pane fade">
								<div class="tabHeader bold">You might also consider the following similar courses: </div>
								<?php foreach ($courseArray as $course)			//Iterating through each similar course
								{
									echo $course;								//Printing the hyperlink
									echo '<br/>';								//Printing a newline
								}?>
							</div>
						</div>	
					</div>
				</div>		
			</div>	
		</div>	
	</body>
</html>