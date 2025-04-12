<?php
try{
	require("../conn.php");
	require("../version.php");
	require("../csp.php");
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

if(isset($_GET['questionID']))
{
	$questionID = $_GET['questionID'];
}
else if(isset($_GET['sessionID']))
{
	$sessionID = $_GET['sessionID'];
}
else
{
	header("location: /");
	exit();
}

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
	<script src="../js/nav.js"></script>
	<script src="../js/jquery-3.3.1.slim.min.js"></script>
	<script src="../js/utils.js"></script>
	<style>
		.center {
			left: 40%;
			position: absolute;
			top: 30%;
		}

		.bot
		{
			position: absolute;
			top: 87%;
			width: 99%;
		}
	</style>
	<script> 
		var questionID = '<?php 
			if(isset($_GET["questionID"])) {
				echo $_GET["questionID"];
			}
			else {
				echo -1;
			}
			?>';
		var sessionID = '<?php 
			if(isset($_GET["sessionID"])) {
				echo $_GET["sessionID"];
			}
			else {
				echo -1;
			}
			?>';
		var allQuestions = [];

		function addToSession(questionID = -1)
		{
			if(questionID == -1)
			{
				questionID = document.getElementById("questionIDHolder").value;
			}
			if(!allQuestions.includes(questionID))
			{
				var buttonHolder = document.createElement("div");
				var nameButton = document.createElement("button");
				var closeButton = document.createElement("button");
				var breakItem = document.createElement("br");

				buttonHolder.setAttribute("class","btn-group");
				buttonHolder.setAttribute("id",questionID);
				
				nameButton.innerHTML = questionID;
				nameButton.setAttribute("class","btn btn-primary");
				closeButton.innerHTML = "x";
				closeButton.setAttribute("class","btn btn-secondary");
				closeButton.setAttribute("onclick","closeButton('" + questionID + "')");

				buttonHolder.appendChild(nameButton);
				buttonHolder.appendChild(closeButton);
				document.getElementById("questions").appendChild(buttonHolder);
				allQuestions.push(questionID);
				//document.getElementById("questions").appendChild(breakItem);
			}
		
		}

		function getSessionInfo()
		{
			if(sessionID != -1)
			{
				console.log(document.getElementById("createSessionButton"));
				document.getElementById("createSessionButton").setAttribute("hidden","");
				var xhttp = new XMLHttpRequest();
				xhttp.open("GET", "../api.php/getSessionInfo/?sessionID="+sessionID, true);				
				xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				xhttp.send("");
				xhttp.onreadystatechange = function() 
				{ 
					if (xhttp.readyState == 4 && xhttp.status == 200)
					{
						if(xhttp.responseText.length > 0 )
						{
							var jsonData = JSON.parse(xhttp.responseText);
							jsonData['sessionStats']['questionIDs'].forEach(element => {
								addToSession(element)
							});
							document.getElementById("baseValuesCheckbox").checked = jsonData['sessionStats']['useBaseValues'];
						}
					}
				}
			}
		}

		function closeButton(questionID)
		{
			allQuestions.splice(allQuestions.indexOf(questionID),1);
			document.getElementById(questionID).outerHTML = "";
		}

		function updateSession()
		{
			var xhttp = new XMLHttpRequest();
			data = {"data" : allQuestions};
			data["useBaseValues"] = document.getElementById("baseValuesCheckbox").checked;
			console.log(data);
			
			console.log(JSON.stringify(data));
			xhttp.open("GET", "../api.php/updateSession/?sessionID="+sessionID+"&data=" + JSON.stringify(data), true);				
			xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			xhttp.send("");
			xhttp.onreadystatechange = function() 
			{ 
				if (xhttp.readyState == 4 && xhttp.status == 200)
				{
					if(xhttp.responseText.length > 0 )
					{
					}
				}
			}
		}

		function createSession()
		{
			var xhttp = new XMLHttpRequest();
			data = {"data" : allQuestions};
			data["useBaseValues"] = document.getElementById("baseValuesCheckbox").checked;
			console.log(data);
			
			console.log(JSON.stringify(data));
			xhttp.open("GET", "../api.php/makeNewSession/?questionIDs=" + JSON.stringify(data), true);				
			xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			xhttp.send("");
			xhttp.onreadystatechange = function() 
			{ 
				if (xhttp.readyState == 4 && xhttp.status == 200)
				{
					if(xhttp.responseText.length > 0 )
					{
						var JSONData = JSON.parse(xhttp.responseText);
						var sessionID = JSONData["sessionID"];
						window.open("../question.php?sessionID=" + sessionID + "&shortCode=" + JSONData["shortCode"],'_blank');					
						document.location.href = "sessionCreator.php?sessionID=" + sessionID;
					}
				}
			}
		}

		function openSession()
		{
			window.open("../question.php?sessionID=" + sessionID,'_blank');			
		}
	</script>
</head>
<body>
	<div class="center">
		<h4> Added Questions </h4>
		<div id="questions">
			
		</div>
		<br>
		<input placeholder = "The question id" class="form-control" type="text" id="questionIDHolder"> 
		<br>
		<button type="button" class="btn btn-success" onclick=addToSession() > Add Question </button>
		<br>
		<hr>
		<span> Session Options </span><br>
		<input type="checkbox" id="baseValuesCheckbox" checked="true"> Use base values </input>
		<hr>
		<div id="createSessionButton">
			<button id="" type="button" class="btn btn-success" onclick=createSession() > Create Session </button><br><br>
		</div>
		<div id="updateSessionButton">
			<button id="" type="button" class="btn btn-success" onclick=updateSession() > Update Session </button><br><br>
			<button id="" type="button" class="btn btn-success" onclick=openSession() > Open Session </button><br><br>
		</div>
	</div>
	<script>
		if(questionID != -1)
		{
			document.getElementById("updateSessionButton").setAttribute("hidden","");
			addToSession(questionID);
		}
		else
		{
			getSessionInfo();
		}
	</script>
</body>
<footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top bot" style="" id="footer">
	<script> var version = <?php echo "'".$_version."-".$_date."'"?>;</script>
	<script> var XQ51dka = <?php echo "'".$checkerItem."'" ?>; </script>
	<script src="../js/footer.js"></script>
</footer>
</html>