
<?php
require("../conn.php");
try{
require("../version.php");
global $_version;
}
catch (\Throwable $e)
{
	echo $e;
}
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
		header("SQL:" .$checkIfTokenIsReal);
		$result = $conn->query($checkIfTokenIsReal);
		while ($row = $result -> fetch_assoc())
		{
			$userID = $row["id"];
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
//header("Version: " . $_version);
//\
?>

<!DOCTYPE html>
<html> 
	<head>
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
		<script src="../js/staff.js"></script>
		<script> 
			var textBackgroundColor = "yellow";//powderblue
			var textBackgroundColorMulti = "green";
			var answer = "";
			var editing = false;
			var tax = 30;
			var tClean = "";
			var jsonRaw = {};
			var allItemsValue = {};
			var translationTable = {};
			var companyAmount = 0;
			var currentID = 0;

			function openProfile()
			{
				location.href = "profile.php"; 
			}
			
			function loadInfo(jsonInfo)
			{
				//jsonInfo = atob(jsonInfo);
				console.log(JSON.parse(jsonInfo))
				jsonInfo = JSON.parse(jsonInfo);
				allText = atob(jsonInfo["text"]);
				answerText = atob(jsonInfo["answer"]);
				questionID = atob(jsonInfo["questionID"]);
				translationTable = JSON.parse(atob(jsonInfo["translationTable"]));

				//console.log(allText, answerText, questionID, translationTable);
				
				allText = decodeURI(allText);
				answerText = decodeURI(answerText);



				console.log(allText, answerText, questionID, translationTable);
				console.log(translationTable);
				document.getElementById("main").innerHTML = allText;
				tableCreate();
			}

			

		</script>
	</head>
	<body>
		<style>
			textarea {
			font-size: 0.8rem;
			letter-spacing: 1px;
			}
			#contextMenu {
				position: fixed;
				background:teal;
				color: white;
				cursor: pointer;
				border: 1px black solid
				}
		
			#contextMenu > p {
				padding: 0 1rem;
				margin: 0
				}
		
			#contextMenu > p:hover {
				background: black;
				color: white;
				}
		
			/* Split the screen in half */
			.split {
			height: 90%;
			width: 50%;
			position: fixed;
			overflow-x: hidden;
			z-index: 1;
			top: 0;
			padding-top: 20px;
			}/**/
		
			/* Control the left side */
			.left {
				top: 5%;
				left: 0;
				height : 85%;
			}
		
			/* Control the right side */
			.right {
				top: 5%;
				right: 0;
				height : 85%;
			}
		
			.third-level-menu-stay
			{
				position: absolute;
				top: 0;
				right: -150px;
				width: 150px;
				list-style: none;
				padding: 0;
				margin: 0;
				display: none;
			}
		
			.third-level-menu-stay > li
			{
				height: 30px;
				background: #999999;
			}
		
			.third-level-menu-stay > li:hover { background: #CCCCCC; }
			.lower {
				position: absolute;
				align-items: center;
				bottom: 2%;
				left: 35%;
			}
		
			.cc {
				align-items: center;
			}
			
			.third-level-menu
			{
				position: absolute;
				top: 0;
				right: -150px;
				width: 150px;
				list-style: none;
				padding: 0;
				margin: 0;
				display: none;
			}
		
			.third-level-menu > li
			{
				height: 30px;
				background: #999999;
			}
		
			.third-level-menu > li:hover { background: #CCCCCC; }
		
			.second-level-menu
			{
				position: absolute;
				top: 30px;
				left: 0;
				width: 150px;
				list-style: none;
				padding: 0;
				margin: 0;
				display: none;
			}
		
			.second-level-menu > li
			{
				position: relative;
				margin: 2%;
				height: 30px;
				background: #999999;
			}
			.second-level-menu > li:hover { background: #CCCCCC; }
		
			.top-level-menu
			{
				list-style: none;
				padding: 0;
				margin: 0;
			}
		
			.top-level-menu > li
			{
				height: 30px;
				width: 150px;
			}
			.top-level-menu > li:hover { background: #CCCCCC; }
		
			.top-level-menu li:hover > ul
			{
				/* On hover, display the next level's menu */
				display: inline;
			}
		
			.top-level-menu-stay
			{
				list-style: none;
				padding: 0;
				margin: 0;
			}
		
			.top-level-menu-stay > li
			{
				height: 30px;
				width: 150px;
			}
			.top-level-menu-stay > li:hover { background: #CCCCCC; }
		
			.top-level-menu-stay li:hover > ul
			{
				/* On hover, display the next level's menu */
				display: inline;
			}
		
		
		
			/* Menu Link Styles */
			.top-level-menu a /* Apply to all links inside the multi-level menu */
			{
				font: bold 14px Arial, Helvetica, sans-serif;
				color: #FFFFFF;
				text-decoration: none;
				padding: 0 0 0 10px;
		
				/* Make the link cover the entire list item-container */
				display: block;
				line-height: 30px;
			}
			.top-level-menu a:hover { color: #000000; }
		
			.helpQuestionInfo {
			position: relative;
			border-bottom: 1px dotted black;
			}
		
			.helpQuestionInfo:before {
			content: attr(data-hover);
			visibility: hidden;
			opacity: 0;
			width: 140px;
			background-color: black;
			color: #fff;
			text-align: center;
			border-radius: 5px;
			padding: 5px 0;
			transition: opacity 1s ease-in-out;
		
			position: absolute;
			z-index: 1;
			left: 0;
			top: 110%;
			}
		
			.helpQuestionInfo:hover:before {
			opacity: 1;
			visibility: visible;
			}
		
		
		
		</style>
		<div>
			<div>
				<ul class="nav nav-tabs">
					<li class="nav-item">
						<button type="button" class="btn btn-light" onclick="goHome()">HOME</button>
					</li>
					<li class="nav-item">
						<button type="button" class="btn btn-light" onclick="openProfile()">PROFILE</button>
					</li>
					<li class="nav-item">					  
						<button type="button" class="btn btn-light" onclick="showStudentSide()">STUDENT VIEW</button>
					</li>
					<li class="nav-item">					  
						<button type="button" class="btn btn-light" onclick="">DEV VIEW</button>
					</li>
				  </ul>
				
				<!--25/07/2024 Added !-->
				<div class="input-group mb-3">
					<select name="varName" id="varName"></select>
					<input id="quickBar" class="form-control" type="text" aria-label="variable equation" onfocusout="checkQuickBarInput()"> </input>
					<input id="formulaEval" class="form-control" type="text" aria-label="variable output" style="width:100%;" editiable="false"> </input>	
				  </div>
			
					
				<br>
			</div>
				
				
		</div>
		<div class="container-fluid">
			<div class="row">
				<div class="col text-wrap" contenteditable="true" >
					QUESTION TEXT GOES HERE<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
				</div>
				<div class ="col">
					<div class ="row2">						
						<div id="tableHolder"> </div>
						
					</div>
					<div class="col text-wrap" contenteditable="true" >
						ANSWER TEXT GOES HERE<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
						</div>
				</div>
			</div>
		</div>
		<script>
			var translationTable = {};
			tableCreate();
		</script>
	</body>

<div class="container2">
	<div class="container">
		<div class="lower2" id ="lower2">				
			<button onclick=checkValues()> Check Values </button>
			<button onclick=loadNewQuestionAgain()> Retry </button>
			<button onclick=dumpInfo()> Dump Info </button>
		</div>
	</div>
	<footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top"></footer>
	<div class="col-md-4 d-flex align-items-center">
	<a href="/" class="mb-3 me-2 mb-md-0 text-body-secondary text-decoration-none lh-1">
		<svg class="bi" width="30" height="24"><use xlink:href="#bootstrap"></use></svg>
	</a>
	<span class="mb-3 mb-md-0 text-body-secondary">2024 Digtal Workbook</span>
	</div>

</footer>
	
</div>

