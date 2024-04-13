<!DOCTYPE html>
<html lang="en">
<html>
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">		
		<title>Courses</title>	
		
		<script src="external/jquery/jquery.js"></script>

		<link rel="stylesheet" href="jquery-ui.min.css">
		<script src="jquery-ui.min.js"></script>		

		<!-- Script for Autocomplete using jQuery UI -->
		<script>
			$(function() {
				$( "#searchBox" ).autocomplete({
					minLength: 3,
					source: "auto_course.php"
				});
			});
		</script>

		<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
		<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

		<link rel="stylesheet" href="menu.css">
		<link rel="stylesheet" href="index.css">		
	</head>
	<body>
	
	<?php include 'menu.php'; ?>

	<div class = "container">
		<div class = "row">
			<div class="col-sm-12">
				<h1>Courses</h1>
			</div>
		</div>
		<div class = "row">
			<div class = "col-sm-12">
				<!-- Search bar -->
				<form action='results.php'>
					<label for ='searchBox'><b>Search for Courses: </b><br/></label>
					<div class="input-group">
						<input id ='searchBox' class="form-control input-lg"name='term' type='search' placeholder= 'I want to study...'/>
						<span class="input-group-btn">
							<input type ='submit' class="btn btn-danger btn-lg" value='Search' />
						</span>
					</div>
				</form>
			</div>
		</div>
	</div>
	</body>
</html>