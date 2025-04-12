<?php
try{
	require("../conn.php");
	require("../version.php");
	require("../csp.php");
	global $_version;
	global $_date;
	global $conn;
	$userID = null;
	//Login section load and check the validailty of the token if failed well force redirct the user to the login page
	if(isset($_COOKIE["authToken"]) && isset($_COOKIE["authEmail"]))
	{
		$authToken = $_COOKIE["authToken"];
		$authEmail = $_COOKIE["authEmail"];
		
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
				/*          
				header("Location: ../login.php?r=question");
				setcookie("r","quesition");
				exit();*/
			}    
		}
		else
		{
			/*
			header("Location: ../login.php?r=question");
			setcookie("r","quesition");
			exit();*/
		}
	}
	else
	{
		/*
		header("Location: ../login.php?r=question");
		setcookie("r","quesition");
		exit();*/
	}
}
catch (\Throwable $e)
{
	echo $e;
}
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);
//header("Version: " . $_version);
//\
?>

<!DOCTYPE html>
<html> 
<head>
	<title>Digital Workbook - Join a session</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0"> 
	<meta name="version" content="<?php echo $_version;?>">
	<meta name="buildDate" content="<?php echo $_date;?>">
	<!--FACEBOOK-->
	<meta property="og:title" content="Digital Work Book - Join a session" />
	<meta property="og:description" content="Join a session with your session code" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0"> 
	<script src="../js/jquery-3.3.1.slim.min.js"></script>
	<script src="../js/utils.js"></script>
	<style>
		.center {
			text-align: center;
			position: relative;
		}

		.bot
		{
			position: absolute;
			top: 87%;
			width: 99%;
		}

		.middel {
			left: 40%;
			top: 25%;
		}

		.midText{
			left: 30%;
			top: 40%;
			position: absolute;
		}

		#txbox{
			border: 1px solid black;
			box-shadow: 1px 2px 2px black;
			width: 24%;
			
			height: auto;
			padding:2%;
			position: absolute;
			border-radius: 25px;
		}

		.signup{
			font-size: 1em;
			text-align: center;
		}

</style>
</head>
<body>
	<div class="">
		<nav class="navbar navbar-expand-lg navbar-light bg-light">
			<div class="navbar-collapse" id="navbarSupportedContent">
				<div style="position: absolute;left:80%; top:4%"> 
					<?php if(isset($userID) && $userID !== null)
					{ ?>
						<button class="btn btn-outline-danger my-2 my-sm-0" type="button" onclick="logout()">Logout</button>
					<?php
					} else {?>				
						<button class="btn btn-outline-success my-2 my-sm-0" type="button" onclick="login()">Login</button>				
					<?php } ?>
					<span> </span>
					<button class="btn btn-outline-danger my-2 my-sm-0 signup" type="button" onclick="signup()" hidden>Signup</button>	
				</div>
			</div>
		</nav>	

	</div>
	<div>
		<div class="midText2" hidden>
			<span> Welcome to digital workbook! <br>What do you want to learn today? </span>
		</div>
		<div id="txbox" class="middel" >
			<div class="center">
				<h4> Enter a code to join </h4><br>
				<input id='shortCode' placeholder="Code looks like this: 123456" class="form-control" type="text" maxlength=6 required> </input><br>
				<button type="button" class="btn btn-success" onclick=joinSession() > JOIN </button><br><br>
				<div id="showCurrentSessions"> </div>
				<?php
					if($userID !== null)
					{
						?>
						<script> 
						function getCurrentSessions()
						{
							var xhttp = new XMLHttpRequest();
							xhttp.open("GET", "../api.php/getCurrentSessions/", true);			
							xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
							xhttp.send("");
							xhttp.onreadystatechange = function() 
							{ 
								if (xhttp.readyState == 4 && xhttp.status == 200)
								{
									if(xhttp.responseText.length > 0 )
									{
										jsonRaw = JSON.parse(xhttp.responseText);
										console.log(jsonRaw["sessionIDs"]);
										if(jsonRaw["sessionIDs"].length > 0)
										{
											document.getElementById("showCurrentSessions").innerHTML = "Current Active Sessions";
											document.getElementById("showCurrentSessions").appendChild(document.createElement("br"));
											jsonRaw["sessionIDs"].forEach(element => {
												console.log(element);
												var a = document.createElement("a");
												a.setAttribute("href","sessionCreator.php?sessionID=" + element[0]);
												a.innerHTML = element[0] + "(Code: " + element[1] + ")";
												document.getElementById("showCurrentSessions").appendChild(a);
												document.getElementById("showCurrentSessions").appendChild(document.createElement("br"));
											});											
										}
									}
								}
							}
						}
						getCurrentSessions();
						</script>
						<?php
					}
				?>
			</div>
		</div>
	</div>
	<script>
		var shortCode = '<?php 
			if(isset($_GET['code']) && is_numeric($_GET['code'])) {
				echo $_GET['code'];
			}
			else {
				echo -1;
			}
			?>';
			
		function joinSession()
		{
			var shortCode = document.getElementById("shortCode").value;
			if(shortCode.length == 6 && !isNaN(shortCode))
			{
				console.log(shortCode);
				var xhttp = new XMLHttpRequest();
				xhttp.open("GET", "../api.php/getSessionID/?shortCode="+shortCode, true);			
				xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				xhttp.send("");
				xhttp.onreadystatechange = function() 
				{ 
					if (xhttp.readyState == 4 && xhttp.status == 200)
					{
						if(xhttp.responseText.length > 0 )
						{
							jsonRaw = JSON.parse(xhttp.responseText);
							sessionID = jsonRaw['sessionID'];
							if(sessionID !== null)
							{
								document.location.href = "../question.php?sessionID=" + sessionID;
							}
						}
					}
				}
			}
		}
		if(shortCode != -1 && shortCode.length == 6 && !isNaN(shortCode))
		{
			var xhttp = new XMLHttpRequest();
			xhttp.open("GET", "../api.php/getSessionID/?shortCode="+shortCode, true);			
			xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			xhttp.send("");
			xhttp.onreadystatechange = function() 
			{ 
				if (xhttp.readyState == 4 && xhttp.status == 200)
				{
					if(xhttp.responseText.length > 0 )
					{
						jsonRaw = JSON.parse(xhttp.responseText);
						sessionID = jsonRaw['sessionID'];
						if(sessionID !== null)
						{
							document.location.href = "../question.php?sessionID=" + sessionID;
						}
					}
				}
			}
		}

		function login()
		{
			window.location.href = "../login.php"; 
		}

		function signup()
		{
			window.location.href = "../login.php?signup"; 
		}

		function logout()
		{
			window.location.href = "../login.php?logout=true"; 			
		}
	</script>

</body>
<footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top bot" style="" id="footer">
	<script> var version = <?php echo "'".$_version."-".$_date."'"?>;</script>
	<script> var XQ51dka = <?php echo "'".$checkerItem."'" ?>; </script>
	<script src="../js/footer.js"></script>
</footer>
</html>