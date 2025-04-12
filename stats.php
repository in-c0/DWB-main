<?php
//Added file 28/09/2024
try{
	require("conn.php");
	require("version.php");
	require("csp.php");
	global $_version;
	global $conn;
	global $_date;
	//Login section load and check the validailty of the token if failed well force redirct the user to the login page
	if(isset($_COOKIE["authToken"]) && isset($_COOKIE["authEmail"]))
	{
		$authToken = $_COOKIE["authToken"];
		$authEmail = $_COOKIE["authEmail"];
		$userID = null;
		$isStaff = false;
		$logedIn = false; 
		$userFound = false;
		$userName = null;
		$response = json_decode('{}',true);
		if(!empty($authToken) && !empty($authEmail))
		{
			$logedIn = true;
			$checkIfTokenIsReal = "SELECT * FROM `users` WHERE `authToken` = '$authToken' AND `email` = '$authEmail'";
			//header("SQL:" .$checkIfTokenIsReal);
			$result = $conn->query($checkIfTokenIsReal);
			while ($row = $result -> fetch_assoc())
			{
				$userID = $row["id"];
				$isStaff = $row["isStaff"];
				$isAdmin = $row["isAdmin"];
				if($isStaff === 1 || $isStaff || $isAdmin === 1 || $isAdmin)
				{
					$isStaff = true;
				}
				header("USERID:" .$userID);
				break;
			}     
			if($userID == null)
			{              
				header("Location: login.php");
				exit();
			}    
		}
		else
		{
			header("Location: login.php");
			exit();
		}
	}
	else
	{
		header("Location: login.php");
		exit();
	}
}
catch (\Throwable $e)
{
	echo $e;
	exit();
}


?>
<head>
	<!--28/09/2024 Added it !-->
	<meta name="version" content="<?php echo $_version;?>">

	<script src="js/utils.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
	
	
	<script src="js/nav.js"></script>		
	<script	src="js/Chart.js"></script> 
	<?php 
	if($isAdmin)
	{
		?> 
		<script src="js/admin.js"></script>
		<?php
	}
	?>
	<title>Digital Workbook - Profile </title>
	<div id="freefloat" class="freefloat" style='position: fixed;top: 60%'></div>
</head>

<body>	
	<nav class="navbar navbar-expand-lg navbar-light bg-light">
		<div class="navbar-collapse" id="navbarSupportedContent">
			<ul class="navbar-nav mr-auto">
				<li class="nav-item">
					<button type="button" class="btn btn-light" onclick="goHome()">HOME</button>
				</li>
				<li class="nav-item">
					<button type="button" class="btn btn-light" onclick="openProfile()">PROFILE</button>
				</li>
				<li class="nav-item">
					<button type="button" class="btn btn-light" onclick="goSettings()">SETTINGS</button>
				</li>
				<?php 
					if($isAdmin)
					{
						?>
						<li class="nav-item">
							<button type="button" class="btn btn-light" onclick="openAdmin()">ADMIN</button>
						</li>
						<?php
					}
				?>
			</ul>
		</div>
		<form class="form-inline my-2 my-lg-0">
			<?php if($userID !== null)
			{ ?>
				<button class="btn btn-outline-danger my-2 my-sm-0" type="button" onclick="logout()">LOGOUT</button>
			<?php
			} else {?>				
				<button class="btn btn-outline-success my-2 my-sm-0" type="button" onclick="login()">LOGIN</button>				
			<?php } ?>				
		</form>
	</nav>
	<div class="container">		
		<canvas id="average" style="width:100%;max-width:700px"></canvas> 
		<script>
			var xhttp = new XMLHttpRequest();			

			function getUserStats()
			{
				var xhttp2 = new XMLHttpRequest();
				xhttp2.open("GET", "api.php/getUserStats/" , true);	
				xhttp2.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				xhttp2.send();
				xhttp2.onreadystatechange = function() 
				{ 
					if (xhttp2.readyState == 4 && xhttp2.status == 200)
					{
						if(xhttp2.responseText.length > 0 )
						{
							jsonData = JSON.parse(xhttp2.responseText);
							
							var xValuesTotal = [];
							var yValues = [];
							var yValuesTotal = [];
							var yValuesTotalScore = [];
							var names = [];
							var mostCommonWrong = {};
							var cols = ["#0074D9", "#FF4136", "#2ECC40", "#FF851B", "#7FDBFF", "#B10DC9", "#FFDC00", "#001f3f", "#39CCCC", "#01FF70", "#85144b", "#F012BE", "#3D9970", "#111111", "#AAAAAA"];
				
							for(var i in jsonData["results"])
							{
								for(v in jsonData["results"][i]["data"]["rAnswer"])
								{
									if(jsonData["results"][i]["data"]["rAnswer"][v] == false)
									{
										if(v in mostCommonWrong)
										{
											mostCommonWrong[v]++;
										}
										else
										{
											mostCommonWrong[v] = 1;
										}
									}
								}

								if(xValuesTotal.includes(jsonData["results"][i]["questionID"]))
								{
									yValuesTotal[xValuesTotal.indexOf(jsonData["results"][i]["questionID"])]++;
									yValuesTotal[xValuesTotal.indexOf(jsonData["results"][i]["questionID"])] += jsonData["results"][i]["data"]["total"];
									yValuesTotalScore[xValuesTotal.indexOf(jsonData["results"][i]["questionID"])] += jsonData["results"][i]["data"]["correct"];
								}
								else
								{
									names.push(jsonData["results"][i]["questionName"]);
									xValuesTotal.push(jsonData["results"][i]["questionID"]);
									yValuesTotal.push(jsonData["results"][i]["data"]["total"]);
									yValuesTotalScore.push(jsonData["results"][i]["data"]["correct"]);
								}
							}

							for (var c = 0; c < yValuesTotal.length; c++)
							{
								if(yValuesTotalScore[c] > 0)
								{
									yValues.push(yValuesTotalScore[c] / yValuesTotal[c]);
								}
								else
								{
									yValues.push(0);
								}
							}
							console.log(yValues);

							// Get the canvas element by its ID
							var ctx = document.getElementById('average').getContext('2d');

							// Create a new Chart.js instance and configure the chart
							var myBarChart = new Chart(ctx, {
								type: 'bar',  // Specify the type of chart: 'bar' for a bar chart
								data: {
									labels: names,									
									datasets: [{
										label: 'Average score pre question',
										data: yValues,
										backgroundColor: cols
									}]
								},
								options: {
									responsive: true,  // Makes the chart responsive to window resizing
									scales: {
										y: {
											beginAtZero: true  // Ensure that the y-axis starts at 0
										}
									},
									title: {
										display: true,
										text: "Average score pre question"
									}
								}
							});

						}
					}
				}
			}
			getUserStats();

		</script>
	</div>

</body>