<?php
try{
	require("conn.php");
	require("version.php");
	require("csp.php");
	global $_version;
	global $_date;
	global $conn;
	//Login section load and check the validailty of the token if failed well force redirct the user to the login page
	if(isset($_COOKIE["authToken"]) && isset($_COOKIE["authEmail"]))
	{
		$authToken = $_COOKIE["authToken"];
		$authEmail = $_COOKIE["authEmail"];
		$userID = null;
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
				header("USERID:" .$userID);
				break;
			}     
			if($userID == null)
			{              
				header("Location: login.php?r=course");
				setcookie("r","quesition");
				exit();
			}    
		}
		else
		{
			header("Location: login.php?r=course");
			setcookie("r","quesition");
			exit();
		}
	}
	else
	{
		header("Location: login.php?r=course");
		setcookie("r","quesition");
		exit();
	}
}
catch (\Throwable $e)
{
	echo $e;
}
?>

<!DOCTYPE html>
<html> 
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"> 
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
		<script src="js/nav.js"></script>
		<script src="js/utils.js"></script>
		<div id="freefloat" class="freefloat" style='position: fixed;top: 50%'></div>

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
			</ul>
		</div>
		<div class="form-inline my-2 my-lg-0">
			<?php if($userID !== null)
			{ ?>
				<button class="btn btn-outline-danger my-2 my-sm-0" type="button" onclick="logout()">LOGOUT</button>
			<?php
			} else {?>				
				<button class="btn btn-outline-success my-2 my-sm-0" type="button" onclick="login()">LOGIN</button>				
			<?php } ?>				
		</div>
	</nav>
	<div class="container">
		
		<span> Search a topic to go through question create for that topic </span>
		<input class="item" id="topicName"list="topicOptions" placeholder="Topic name">
		<datalist id="topicOptions"></datalist>

		<script>
			function getAllTopics()
			{
				var xhttp = new XMLHttpRequest();
				xhttp.open("GET", "api.php/getAllTopics/" + 2 , true);	
				xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				xhttp.send();
				xhttp.onreadystatechange = function() 
				{ 
					if (xhttp.readyState == 4 && xhttp.status == 200)
					{
						if(xhttp.responseText.length > 0 )
						{
							var holder = "topics";
							var j = JSON.parse(xhttp.responseText);
							var item = j["topics"];
							document.getElementById("topicOptions").innerHTML = "";
							var dropdown=document.createElement("select");
							var topicData = document.getElementById("topicOptions");
							topicData.innerHTML = "";
							for(var i=0;i<item.length;i++){
								console.log(item[i]["name"]);
								var opt=document.createElement("option");
								opt.text=item[i]["name"];
								opt.value=item[i]["name"];
								opt.setAttribute("onclick","getAllQuestions("+ item[i]["id"] + ")");
								dropdown.options.add(opt);
								topicData.appendChild(opt);
							}
						}
					}
				}
			}
			getAllTopics();
		</script>
	</div>
</body>