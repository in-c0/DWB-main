
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
				header("Location: login.php?r=question");
				setcookie("r","question");
				if(isset($_GET['questionID']))
				{
					setcookie("questionID",$_GET['questionID']);
				}
				exit();
			}    
		}
		else
		{
			if(!isset($_GET['sessionID']))
			{
				header("Location: login.php?r=question");
				setcookie("r","quesition");
				if(isset($_GET['questionID']))
				{
					setcookie("quesitionID",$_GET['questionID']);
				}
				exit();
			}
		}
	}
	else
	{
		if(!isset($_GET['sessionID']))
		{
			header("Location: login.php?r=question");
			setcookie("r","quesition");
			if(isset($_GET['questionID']))
			{
				setcookie("quesitionID",$_GET['questionID']);
			}
			exit();
		}
	}
}
catch (\Throwable $e)
{
	echo $e;
}
//header("Version: " . $_version);
//\
?>

<!DOCTYPE html>
<html> 
	<head>
		<meta name="viewport" content="width=device-width, initial-scale=1.0"> 
		
		<!--<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
-->
		
	
		<script src="js/nav.js"></script>
		<script src="js/utils.js"></script>
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
			console.log(sessionID);	 
		</script>
<?php
	

	//Well output this if one is in the editing view
	if(isset($_GET["editing"]) && $_GET["editing"] === "true")
	{
	?>
		
		<?php
		/*
		if(isset($_GET["questionID"]))
		{
			echo $_GET["questionID"];
			?>
			loadNewQuestion(<?php $_GET["questionID"]; ?>);
			<?php
		}

		*/
	}
	else
	{
		?>
		<script src="js/student.js"></script>
		<script src="js/animation.js"></script>
		<script> console.log(sessionID) </script>
		<script src="js/chat.js"></script><?php
	}
	
	//Base functions used by all users regardless of perms
?>
	<script>
		var element = document.getElementById('main');
		var resizer = document.createElement('div');
		resizer.className = 'resizer';
		resizer.style.width = '10px';
		resizer.style.height = '10px';
		resizer.style.background = 'red';
		resizer.style.position = 'absolute';
		resizer.style.right = 0;
		resizer.style.bottom = 0;
		resizer.style.cursor = 'se-resize';
		//element.appendChild(resizer);
		resizer.addEventListener('mousedown', initResize, false);

		function initResize(e) {
			window.addEventListener('mousemove', Resize, false);
			window.addEventListener('mouseup', stopResize, false);
		}
		function Resize(e) {
			element.style.width = (e.clientX - element.offsetLeft) + 'px';
			element.style.height = (e.clientY - element.offsetTop) + 'px';
		}
		function stopResize(e) {
			window.removeEventListener('mousemove', Resize, false);
			window.removeEventListener('mouseup', stopResize, false);
		}
	</script>
	<meta name="viewport" content="width=device-width, initial-scale=.8">
	<script>
			

		</script>
	</head>
	<body>
		<style>
			#cursorText{
				position:absolute;
				border:1px solid blue; /* You can remove it*/
			}
			textarea {
			font-size: 0.8rem;
			letter-spacing: 1px;
			}
			table, th, td {
				border: 1px solid black;
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
			.screen {
				display: flex;
				height: 70%;
			}

			.left {
				width: 50%;
				overflow-x: hidden;
			}


			@media (max-width: 768px) {
				.screen {
					flex-direction: column;
				}

				.left2, .right2 {
					width: 100%;
					padding: 10px;
				}
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
			
			.topHolder
			{    
				background-color: #c0c0c0;
				position:relative;
				top:0;
			}

			.vertical-break
			{
				border-left: 6px solid black;
  				height: 100%;
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

			

			body
			{
				width: 100%;
			}

			#main {
				width: 100%;
				overflow: auto;
			}
			
			.footera{
				position:fixed;
				bottom:0;
				width:100%;
				height:auto;
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
				width: auto;
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

			.baseTable
			{
				padding: 15px;
				text-align: left;
				overflow-x:auto;
				
			}

			.baseTable table
			{
				border: 1px solid;
				border-color: rgb(230, 230, 230);
			}

			.baseTable tr:nth-child(even) {background-color: transparent;}
			.baseTable tr:nth-child(odd) {background-color: transparent;}

			.baseTable tr:hover
			{
				background-color: rgb(240, 240, 240);
			}
				
			.baseTable th, td {
				padding: 5px;
				text-align: left;		
			}

			
		</style>
		<div>
			<div id="topPage">
				<div id="topHolder" class="topHolder">
					<div>
						<!--22/09/2024 Changed it to bootstrap !-->
						<nav class="navbar navbar-expand-lg navbar-light bg-light">
							<div class="navbar-collapse" id="navbarSupportedContent">
								<ul class="navbar-nav mr-auto">
									<?php if(isset($userID))
									{?>
									<li class="nav-item">
										<button type="button" class="btn btn-light" onclick="goHome()">Home</button>
									</li>
									<li class="nav-item">
										<button type="button" class="btn btn-light" onclick="openProfile()">Profile</button>
									</li>
									<?php
									}
									else if(isset($_GET['sessionID']))
									{
										?>
											
										<?php
									}
									if(isset($_GET["editing"]) && $_GET["editing"] === "true")
									{?>
									
									<li class="nav-item">
										<button type="button" class="btn btn-light" onclick='showTab("publishTab")' > Publish Info </button>
									</li>
									<li class="nav-item">
										<button type="button" class="btn btn-light" onclick='showTab("insertTab")' > Insert </button>
									</li>
									<li class="nav-item">
										<button type="button" class="btn btn-light" onclick='showTab("elementTab")' > Elements </button>
									</li>
									<li class="nav-item">
										<button type="button" class="btn btn-light" onclick='showTab("viewTab")' > View </button>
									</li>
									
									<?php }
									else {
										?>
									<li class="nav-item">
										<button type="button" class="btn btn-light"  onclick=checkValues()> Submit Values </button>
									</li>
									<li>	
									<button type="button" class="btn btn-light" onclick=loadNewQuestionAgain()> Retry </button>
									<button id="toggleRandom" type="button" class="btn btn-light" onclick=toggleRandom()> Toggle Random </button>
									<button id="slideOrAll" type="button" class="btn btn-secondary" onclick=toggleSplitView()> Slide View </button>
									<?php 
										if(isset($_GET['sessionID']))
										{
											?>		
											<button type="button" class="btn btn-light" onclick=previousQuestion() <?php if(!isset($_GET['sessionIndex']) || $_GET['sessionIndex'] == 0) {echo "disabled"; }?>> Previous Question </button>
											<button id="toggleRandom" type="button" class="btn btn-light" onclick=nextQuestion()> Next Question </button>
											<?php
										}
									?>
									<script src="js/qrcode.js"></script>
									<button type="button" class="btn btn-light" id='shortCode' onclick=saveQuickCode()> </button>
									</li>
										<?php
									} ?>
								</ul>
								
							</div>
							<?php if(isset($userID))
							{ ?>
							<div class="profileItem">
								<a href="#">
									<img src="pics/profile.svg" onclick="openProfile()">
								</a>
								<br>
							</div>
							<?php }
							?>
							<div class="form-inline my-2 my-lg-0">
								
				
								<?php if(isset($_GET["editing"]) && $_GET["editing"] === "true")
								{?>
								<button class="btn btn-outline-success my-2 my-sm-0"  type="button" id="dumpInfoVis" onclick="dumpInfoVis()">DUMP INFO</button>
								<button class="btn btn-outline-success my-2 my-sm-0"  type="button" id="saveButton" onclick="submit()">SAVE</button>
								<button type="button" id="publishButton" onclick="makeQuestionPublic()">PRIVATE</button>
								<?php
									if(isset($userID))
									{
										if($userID !== null)
										{ ?>
											<button class="btn btn-outline-danger my-2 my-sm-0" type="button" onclick="logout()">Logout</button>
										<?php
										} else {?>				
											<button class="btn btn-outline-success my-2 my-sm-0" type="button" onclick="login()">Login</button>				
										<?php }
									}
									else{ ?>
										<button class="btn btn-outline-success my-2 my-sm-0" type="button" onclick="login()">Login</button>	
										<?php
									}
								}
								else
								{
									if(isset($userID))
									{
										if($userID !== null)
										{ ?>
											<button class="btn btn-outline-danger my-2 my-sm-0" type="button" onclick="logout()">Logout</button>
										<?php
										} else {?>				
											<button class="btn btn-outline-success my-2 my-sm-0" type="button" onclick="login()">Login</button>				
										<?php }
									}
									else{ ?>
										<button class="btn btn-outline-success my-2 my-sm-0" type="button" onclick="login()">Login</button>	
										<?php
									}
								}
								?>
								<br>		
							</div>
						</nav>
						
					</div>
				</div>
				<div id="tabHolder">
					<div id="insertTab" class="tab bg-light" hidden>
						<nav class="navbar navbar-expand-lg navbar-light bg-light">
							<!--
							<ul class="nav nav-pills">
								<li class="nav-item dropdown">
									<a class="nav-link dropdown-toggle data-toggle=dropdown" href="javascript:loadFocus()" role="button" aria-haspopup="true" onclick="loadFocus()">INSERT VARIABLE</a>
									<div class="dropdown-menu" id="insertItems2">
										
									</div>
								</li>
							</ul>-->
							<div class="dropdown">
								<button class="btn btn-secondary dropdown-toggle btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
									Insert Variable
								</button>
								<div class="dropdown-menu" id="insertItems">								
								</div>
							</div>
							<div class="dropdown">
								<button class="btn btn-secondary dropdown-toggle  btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
									Insert Table
								</button>
								<ul class="dropdown-menu" id="insertItems3">
									<li><input type="number" id="colTableInsert" min="1" value="1" class="form-control" placeholder="columns"> </input></li>
									<li><input type="number" id="rowTableInsert"  min="1" value="1" class="form-control" placeholder="rows"> </input></li>
									<li><div class="dropdown-divider"></div></li>
									<li><button class="btn btn-outline-success" type="button" onclick=insertTable()> Insert </button></li>
								</ul>
							</div>
							<div class="dropdown">
								<button class="btn btn-secondary dropdown-toggle btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
									Insert From Document
								</button>
								<div class="dropdown-menu" id="insertItems">								
								</div>
							</div>
							<!--
							<ul class="nav nav-pills">
								<li class="nav-item dropdown">
									<a class="nav-link dropdown-toggle data-toggle=dropdown" href="javascript:loadFocus()" role="button" aria-haspopup="true" onclick="loadFocus()">INSERT TABLE</a>
									<div class="dropdown-menu">
										<input type="number" id="colTableInsert" min="1" value="1" class="form-control"> </input>
										<input type="number" id="rowTableInsert"  min="1" value="1" class="form-control"> </input>
										<div class="dropdown-divider"></div>
										<button class="btn btn-outline-success" type="button" onclick=insertTable()> INSERT </button>
									</div>
								</li>
							</ul>-->
							<!--<button class="btn btn-light" onclick=""> INSERT TABLE </button>-->
						</nav>
					</div>
					<div id="viewTab" class="tab" hidden>
						<nav class="navbar navbar-expand-lg navbar-light bg-light">
							<button class="btn btn-primary" onclick="showDev(0)"> Question View </button>
							<button class="btn btn-secondary" onclick="showDev(1)"> Stats View </button>
							<button class="btn btn-danger" onclick="showLearningMode()"> Learning Mode </button>		
							<button class="btn btn-danger" onclick="showTestMode()"> Test Mode </button>		
							<button class="btn btn-danger" onclick="showPresentationMode()"> Presentation Mode </button>				
							<button class="btn btn-outline-secondary" onclick="toggleVerboseView()"> Toggle Verbose View </button>					
						</nav>
					</div>

					<div id="publishTab" class="tab" hidden>
						<nav class="navbar navbar-expand-lg navbar-light bg-light">
							<button onclick="makeQuestionPrivate()" type="button" class="btn btn-outline-success" > Make Question Private </button>
							<button onclick="makeQuestionPublic()" type="button" class="btn btn-outline-danger" > Make Question Public </button>
							<div>
								<div class="dropdown">
									<button class="btn btn-secondary dropdown-toggle btn-light" type="button" data-bs-toggle="dropdown" aria-expanded="false">
										Change Topic
									</button>
									<ul class="dropdown-menu" id="topicOptions">
										
									</ul>
								</div>
							</div>
							<button class="btn btn-secondary" type="button" aria-expanded="false" onclick=removeQuestion()>
								Delete Question
							</button>
						</nav>
					</div>

					<div id="elementTab" class="tab" hidden>
						<div class="row align-items-start navbar-light bg-light">
							<div class="col">
								<button class="btn btn-outline-secondary" onclick=showSubTab("changeVarTypeHolder")> Change variable type </button>
								<button class="btn btn-outline-secondary" onclick=showSubTab("changeParNameHolder")> Change Parameter label </button>
								<button class="btn btn-outline-secondary" onclick=showSubTab("formatTool")> Format Text </button>
								<button class="btn btn-outline-secondary" onclick=showSubTab("formatTable")> Format Table </button>
								<button class="btn btn-outline-secondary" onclick=showSubTab("animationSection")> Animations </button>
							</div>
						</div>
						<div>
							<nav class="navbar navbar-expand-lg navbar-light bg-light subtab" id='changeVarTypeHolder' hidden>
								<select class="elementTabDropDownName btn" id="elementTabDropDownNameChangeType"> </select>	
								<select class="elementTabDropDownType btn" id="elementTabDropDownTypeChangeType"> </select>							
								<button class="btn btn-outline-secondary" onclick="changeTypeCall()"> Change Variable Type </button>		
							</nav>
							<nav class="navbar navbar-expand-lg navbar-light bg-light input-group mb-3 subtab" id='changeParNameHolder' hidden>
								<select class="elementTabDropDownName btn" id="elementTabChangeVarOldName"> </select>		
								<input type="text" id="elementTabChangeVarName" class="form-control"> </input>					
								<button class="btn btn-outline-secondary" onclick="changeVarName()"> Change Parameter label </button>		
							</nav>
							<nav class="navbar navbar-expand-lg navbar-light bg-light input-group mb-3 subtab" id='formatTool' hidden>
								<ul class="list-group">
									<li class="list-group-item">
										<p style="text-align: center;">Text Options </p>
									</li>
									<li class="list-group-item">
										<button class="btn" style = "font-weight = 'bold';" onclick=formatManager('bold')><b> B </b> </button>
										<button class="btn" style = "font-weight = 'bold';" onclick=formatManager('underline')><u> U </u> </button>
										<button class="btn" style = "font-weight = 'bold';" onclick=formatManager('italic')><i> I </i> </button>
										<button class="btn" style = "font-weight = 'bold';" onclick=formatManager('left')><i> |-- </i> </button>
										<button class="btn" style = "font-weight = 'bold';" onclick=formatManager('center')><i> |-| </i> </button>
										<button class="btn" style = "font-weight = 'bold';" onclick=formatManager('right')><i> --| </i> </button>
										<select id="textSize" > </select>
									</li>
								</ul>
								<ul class="list-group">
									<li class="list-group-item">
										<p style="text-align: center;"> Change background color </p>
									</li>
									<li class="list-group-item">
										<input type="color" id="backgroundColor" value="#FFFFFF"> </input><button class="btn" style = "font-weight = 'bold';" onclick=changeBackground()>Set</button>
									</li>
								</ul>
							
							</nav>
							<nav class="navbar navbar-expand-lg navbar-light bg-light input-group mb-3 subtab" id='formatTable' hidden>
								<ul class="list-group">
									<li>
										<p style="text-align: center;"> Set table colors</p>
									</li>
									<li class="list-group-item">
										<span> Even row color </span><input type="color" id="evenRowColor" value="#FFFFFF"> </input>
										<span> Odd row color </span><input type="color" id="oddRowColor" value="#FFFFFF"> </input>
										<button class="btn" style = "font-weight = 'bold';" onclick=changeTableFormat()> Set </button>
										<button class="btn" onclick=applyDefaultTableFormat()> Set Default Table Format </button>
									</li>
								</ul>
								<ul class="list-group">
									<li class="list-group-item">
										<p style="text-align: center;"> Change Row Width </p>
									</li>
									<li class="list-group-item"><button onclick=changeRowWidth(-1)> - </button> <span id="currentRowWidth"> 0% </span><button onclick=changeRowWidth(1)> + </button></li>
									
								</ul>
								
								<ul class="list-group">
									<li class="list-group-item"><p style="text-align: center;">Change Table width</p></li>
									<li class="list-group-item"><button onclick=changeTableSize(-1)> - </button> <span id="currentTableWidth"> 0% </span><button onclick=changeTableSize(1)> + </button></li>
								</ul>

							</nav>
							<nav class="navbar navbar-expand-lg navbar-light bg-light input-group mb-3 subtab" id='animationSection' hidden>
								<span> Animation order </span> <select id="animationOrders"> </select>
								<span> Animation type </span> <select id="animationTypes"> </select> 
								<button class="btn" onclick=addAnimation()> Add animation </button>
								<button class="btn" onclick=removeAnimation() > Remove animation </button>
							</nav>
						</div>
					</div>
				</div>
				<div id = "devHolder" hidden=true>			
					<div class = "container">
						<span> Question Name <input id="questionNameDev" type="text"> </input><button onclick=setQuestionName() class="btn btn-outline-success">Set Question Name</button> </span>	<br>		
						<span id = "topicNameHolder"> </span><br>
						<span id = "questionStats"> </span><br> 
						<script	src="js/Chart.js"></script> 
						<div class="container">
							<div id="questionResultsHolder">
							<button class="btn btn-outline-success" onclick=loadQuestionResults()> Get Question Results </button>
							<canvas id="total" style="width:100%;max-width:700px"></canvas> 
							<canvas id="average" style="width:100%;max-width:700px"></canvas> 
							<canvas id="maxmin" style="width:100%;max-width:700px"></canvas> 
							<canvas id="mostWrong" style="width:100%;max-width:700px"></canvas> 
							</div>
						</div>
					</div>				
				</div>
				<?php
					if(isset($_GET["editing"]) && $_GET["editing"] === "true")
					{
						?>
						<div class="input-group mb-2" id="fileStructure" hidden>
							<span class="input-group-text">Location://</span>
							<span class="input-group-text" id="orgName">/</span>
							<span class="input-group-text" id="">/</span>
							<span class="input-group-text" id="subjectName">/</span>
							<span class="input-group-text" id="">/</span>
							<span class="input-group-text" id="topicName">/</span>
							<span class="input-group-text" id="">/</span>
							<span class="input-group-text" id="questionIDGo" >/</span>
						</div>
						
						<div class="input-group">
							<div class="input-group topitem">
								<select name="varName" id="varName" class="btn btn-light dropdown-toggle" onclick="showQuickBarVarItem()"></select>
								<select name="varValueOptions" id="varValueOptions" class="btn btn-light dropdown-toggle" onclick=changeVarInputType()></select>
								<input id="quickBar" class="form-control quickBarOptions" type="text" aria-label="variable equation" style="width:65%" onfocusout="">
								<input id="listQuickBar" class="form-control quickBarOptions" type="text" aria-label="variable equation" style="width:65%" onfocusout="" placeholder ="seperate all items with | for example a|1|n"hidden>
								<div class="quickBarOptions input-group mb-3" hidden>
									<span> Min <input id="quickBar" class="form-control" type="text" aria-label="variable equation" style="width:65%" onfocusout="" > Max
									<input id="quickBar" class="form-control" type="text" aria-label="variable equation" style="width:65%" onfocusout="" ></span>
								</div>
								<input type="text" class="quickBarOptions form-control randomQuickBarOptionDropDown" placeholder="Set Incorrect Anwsers" style="width:65%" aria-label="" id="values" aria-describedby="basic-addon1" hidden>
								<input type="text" class="quickBarOptions form-control randomQuickBarOptionDropDown" placeholder="Set Correct Anwser" style="width:25%" aria-label="" id="setBaseValue" aria-describedby="basic-addon1" hidden>
								
								<input type="text" class="quickBarOptions form-control randomQuickBarOption" placeholder="Min" aria-label="" id="randomQuickBarOptionMin" aria-describedby="basic-addon1" onfocusout="()" hidden>
								<input type="text" class="quickBarOptions form-control randomQuickBarOption" placeholder="Max" aria-label="" id="randomQuickBarOptionMax" aria-describedby="basic-addon1" onfocusout="()" hidden>
								<button type="button" class="btn btn-outline-success" onclick="checkQuickBarInput()"> Set </button>
								<div class="navbar-collapse" id="navbarSupportedContent">
									<span id="formulaEval" class="input-group-text" type="text" aria-label="variable output"style="width:100%" editiable="false">  </span>	
								</div>
							</div>
						</div>
							
						<?php
						}
					?>			
			</div>
			<div id = "allHolder" >
				<!--29/05/2024 Added !-->
				<!--22/09/2024 Changed it to bootstrap !-->
							
				<br>
				

				
				<!--22/09/2024 Changed it to bootstrap !-->
				<div class="screen" id="screen">			
					<div id="left" class="left" style="width: 50%;resize:horizontal;">					
						<div class="centered2">						
							<div id="itemHolder" class="row2">
							<?php
								try {
								if(isset($_GET["editing"]) && $_GET["editing"] === "true" && !isset($_GET['studentShow'])) {
									?>																			
										<!-- Polished Question Content Area (Left Column) -->
										<div class="container my-4">
										<div class="card shadow-sm">
											<!-- Card Header: Optional Question Title -->
											<div class="card-header bg-primary text-white">
											<h4 id="questionTitle" class="mb-0">Question Title</h4>
											</div>
											<!-- Card Body: Display the Question Text -->
											<div class="card-body">
											<div id="main" class="card-text" contenteditable="<?php echo (isset($_GET["editing"]) && $_GET["editing"] === "true" ? 'true' : 'false'); ?>" style="min-height: 200px;">
												<?php
												// You can output your dynamic question text here.
												echo "Insert dynamic question content here.";
												?>
											</div>
											</div>
											<!-- Card Footer: Additional info like a timestamp or question number -->
											<div class="card-footer text-muted text-end">
											<small>Question 1 of 10</small>
											</div>
										</div>
										</div>


									<?php	
								} else {
									?>
																			
										<!-- Polished Question Content Area (Left Column) -->
										<div class="container my-4">
										<div class="card shadow-sm">
											<!-- Card Header: Optional Question Title -->
											<div class="card-header bg-primary text-white">
											<h4 id="questionTitle" class="mb-0">Question Title</h4>
											</div>
											<!-- Card Body: Display the Question Text -->
											<div class="card-body">
											<div id="main" class="card-text" contenteditable="<?php echo (isset($_GET["editing"]) && $_GET["editing"] === "true" ? 'true' : 'false'); ?>" style="min-height: 200px;">
												<?php
												// You can output your dynamic question text here.
												echo "Insert dynamic question content here.";
												?>
											</div>
											</div>
											<!-- Card Footer: Additional info like a timestamp or question number -->
											<div class="card-footer text-muted text-end">
											<small>Question 1 of 10</small>
											</div>
										</div>
										</div>


									<div class="slides" hidden>
									<div id="mainSlidesContent"> </div>
									<div id="mainSlidesOptions"> </div>
									</div>
									<?php
								}
								} catch(\Throwable $e) {
								echo $e;
								}
							?>
							</div>
						</div>
						</div>

					<div id="right" class="right" style="width: 50%; overflow-x: hidden;resize: horizontal;">	
						<div class="centered2">
							<div id="itemHolder" class="row2">
								<div class ="col2">
								<?php
								try{
									if(isset($_GET["editing"]) && $_GET["editing"] === "true" && !isset($_GET['studentShow']))
									{
										?> 
										<!-- //5/08/2024 Added better way-->
										<span> Question Name <input id="questionName" type="text"> </input><button onclick=setQuestionName() class="btn btn-success">Set Question Name</button> </span> <button onclick=openParamaterWindow() class="btn btn-success">Create Paramater</button>
										<hr>
										<div id="newVar" hidden><div id="selectItemHolder">
											<span> Make a new parameter </span><br><br>
											<hr>
											<span> Parameter name </span><input placeholder="Parameter label" id="variableNameHolder" class="`+type+`"><br>
					
											<hr>
											<br>
											<span> Upload the file with the value </span>
											<input id="uploadedValue" type ="file">
											<span> Or type it in manually </span>
											<input id="setItemValue" placeholder="Default Value"> </input> <br>
											<hr>
											<span> Parameter type </span><select id="typeView" onchange="checkNewItem();" class="btn btn-outline-secondary dropdown-toggle"></select>
											<div id="typeCreateDiv" hidden> 
												<input id = "typeName" placeholder="Type name">
												<button onclick=createType() class="btn btn-outline-success"> Create Type </button>
											</div>

											<button onclick="setItemName()" class="btn btn-outline-secondary"> Save Parameter </button><br>
											<hr>
											<span> Add a parameter </span><br><br>
											<button onclick="previousSelectItem()" class="btn btn-outline-secondary"> Previous Selected Item </button>
											<button onclick="nextSelectItem()" class="btn btn-outline-secondary"> Next Selected Item </button><br>
											<button onclick="addSelectedItem()" class="btn btn-outline-secondary"> Add Item </button>
											<button onclick="closeSelectItems()" class="btn btn-outline-secondary">Close </button>
											<hr>
										</div>
									</div>
										<?php
									}
								}
								catch(\Throwable $e)
								{
									echo $e;
								}
								?>
							<div class ="row2">	
								<div class="collapse2" id="collapseExample">
									<div class="card2 card-body2">
										<!--<button class="btn btn-outline-success" onclick=exportParamaterList()> Export </button>-->
										<div id="tableHolder"> </div>
									</div>
								</div>
							</div>
							<div class="slides" hidden>
								<div class="col text-wrap" id="answerSlidesContent"> </div>
								<div class="col text-wrap" id="answerSlidesOptions"> </div>
							</div>
							<?php
								try{
									if(isset($_GET["editing"]) && $_GET["editing"] == "true" && !isset($_GET['studentShow']))
									{
									?>
										<div class="col text-wrap" id="answer" contenteditable="true" onchange="setAnswer()"  style="padding-left:2%;width:100%;overflow-x:scroll;">
											SOLUTION TEXT GOES HERE<br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>
										</div>
									<?php
									}
									else
									{
										?>		
										<script>
											
											function loadNewQuestionAgain()
											{
												console.log(Url.get.instanceId);
												
												console.log(Url.get.instanceId2);
												if(urlParams.get('instanceId') != null)
												{
													var instanceId = urlParams.get('instanceId');
												}
												else
												{

													var instanceId = randomIntFromInterval(10000,100000);
												}
												console.log(instanceId);
												document.getElementById("answer").innerHTML = "";
												loadNewQuestion(questionID,instanceId);
											}
										</script>					
										<div class="col text-wrap" id="answer" contenteditable="false" onchange="setAnswer()" style="padding-left:2%;width:100%;overflow-x:scroll;">
										<?php
									}
								}
								catch(\Throwable $e)
								{
									echo $e;
								}
							?>
						</div>
					</div>
				</div>
				
				<script>
					var translationTable = {};
					tableCreate();				
				</script>
		
			</div>
		</div>
		</div>
		<footer class="footera" id="footer">
			<script> var version = <?php echo "'".$_version."-".$_date."'"?>;</script>
			<script> var XQ51dka = <?php echo "'".$checkerItem."'" ?>; </script>
			<script src="js/footer.js"></script>
		</footer>
		<script src="js/question.js"></script>
	
		<?php
			if(isset($_GET['editing']) && $_GET["editing"] == "true")
			{
				?>
				<style>
					#topPage
					{
						position:relative;
						top:0;
						width:100%;
					}

					#allHolder
					{
						position:relative;
						width:100%;
						height:auto;
					}
					</style>
					
				<?php
			}
			else
			{
				?>
				<script>
					var screenItem = document.getElementById("screen");
					screenItem.style.height = "85%";

				</script>

				<style>
					#topPage
					{
						position:relative;
						top:0;
						width:100%;
					}

					#allHolder
					{
						position:relative;
						width:100%;
						height:auto;
					}
				</style>
					
				<?php
			}
		?>
		
		<style>
			#chatMenu2 {
					position: absolute;
					z-index: 9;
					border: 1px solid #d3d3d3;
					top: 20%;
				}

				#mydivheader2 {
					padding: 10px;
					cursor: move;
					z-index: 10;
					background-color: #2196F3;
					color: #fff;
			}	
		</style>
	
		<div id="freefloat" class="freefloat" style = "position: fixed;	top: 50%;">	</div>
		<div id="chatMenu" class="freefloat" style="position: fixed; top: 20%;" hidden>	
			<div class="toast-header" role="alert">
				<div class="d-flex justify-content-center align-items-center w-400" id="mydivheader">
					<div class="toast show">
						<div class="toast-header">
							<strong class="me-auto" id="chatTitle">Chat Log</strong>
							<button type="button" class="btn" onclick="closeChatMenu()">X</button>
						</div>
						<div class="toast-body">
							<div style="overflow-y: scroll; height:400px;">
							<span id="chatTranscript" style="overflow-y: scroll; height:400px;"></span>
							</div>
							<p><textarea placeholder="Message" id="chatMessage" class="border-radius: 15px; "></textarea></p>		
							<button onclick="sendMessage()" class="btn btn-outline-primary my-2 my-sm-0"> Send Message</button>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script>
			
		</script>
<?php

	if(isset($_GET["editing"]) && $_GET["editing"] === "true")
	{
	?>
		<script src="js/staff.js"></script>
		<script>
			var editing = true;
			
			var questionID = "";
			var selectedText = "";
		<?php
			if(isset($_GET["questionID"]))
			{
			?> 
				questionID = '<?php echo $_GET["questionID"];?>';
				getQuestionRaw(questionID);
				showTab(localStorage.getItem("currentTabOpen"));
			<?php
			}
		?>
		
		if(public)
		{					
			document.getElementById("publishButton").setAttribute("class","btn btn-outline-success");
			document.getElementById("publishButton").setAttribute("onclick","makeQuestionPrivate()");
			document.getElementById("publishButton").innerHTML = "PUBLIC";
		}
		else
		{					
			document.getElementById("publishButton").setAttribute("class","btn btn-outline-danger");
			document.getElementById("publishButton").setAttribute("onclick","makeQuestionPublic()");
			document.getElementById("publishButton").innerHTML = "PRIVATE";
		}

		//document.getElementById('main').addEventListener('paste', handlePaste);
		/**document.getElementById("lower2").innerHTML += `<button onclick="newQuestion()"> New Question </button>
			<button onclick="submit()"> Save </button> <button onclick="showStudentSide()"> Show Student View (Opens new tab) </button> `;
		*/	
		var currentTabOpen = "";

		function hideAllTabs()
		{
			var tabs = document.getElementsByClassName("tab");
			for (var tab = 0; tab < tabs.length; tab++)
			{
				console.log(tab);
				console.log(tabs[tab]);
				tabs[tab].setAttribute("hidden","");			
			}
		}

		function hideAllSubTabs()
		{
			var tabs = document.getElementsByClassName("subtab");
			for (var tab = 0; tab < tabs.length; tab++)
			{
				console.log(tab);
				console.log(tabs[tab]);
				tabs[tab].setAttribute("hidden","");			
			}
		}


		var lastMousePointer = null;

		//Added 30/09/2024
		//https://stackoverflow.com/questions/2920150/insert-text-at-cursor-in-a-content-editable-div
		function insertTextAtCaret(text) {
			var sel, range;
			console.log("SEL",sel);
			if (window.getSelection) {
				var selectedItem = window.getSelection().focusNode.id;
				console.log("SELECTED ITEM",selectedItem, window.getSelection().focusNode);
				if(1 == 1)
				{
					sel = window.getSelection();
					console.log("WIN",window,window.getSelection().focusNode.id);
					if (sel.getRangeAt && sel.rangeCount) {
						//last = range;
						range = sel.getRangeAt(0);
						range.deleteContents();
						range.insertNode(document.createTextNode(text) );
					}
				}
				else
				{
					/*
					sel = window.getSelection();
					console.log("WIN",window,window.getSelection().focusNode.id);
					if (sel.getRangeAt && sel.rangeCount) {
						range = sel.getRangeAt(0);
						range.deleteContents();
						range.insertNode(document.createTextNode(text.replaceAll("%","")));
					}*/
				}
			} else if (document.selection && document.selection.createRange) {
				document.selection.createRange().text = text;
				console.log("DOC",document.selection);
			}
		}

		function getTextSizeOptions()
		{
			s = "";
			for(var i = 6; i < 44; i += 2)
			{
				if(i == 16)
				{
					s += `<option onclick='formatManager("changeFont",`+i+`)' selected>` + i + "</option>";
				}
				else
				{
					s += `<option onclick='formatManager("changeFont",`+i+`)'>` + i + "</option>";
				}
			}
			return s;
		}


		var currentSubTabOpen = "";
		function showSubTab(id)
		{
			hideAllSubTabs();
			document.getElementById(id).removeAttribute("hidden");
			if(id == currentSubTabOpen)
			{
				hideAllSubTabs();
				currentSubTabOpen = null;
			}
			else{	
				if(id == "animationSection")
				{
					showAnimation = true;
				}
				else
				{
					showAnimation = false;
				}
				
				propogateChangeText();
				for(key in translationTable)
				{
					propogateChangeText(key);
				}
				
				currentSubTabOpen = id;
				elementTableNameReload();		
				elementTableTypeReload();		
			}
			localStorage.setItem("currentSubTabOpen", currentSubTabOpen);
			loadFocus();
		}

		function showTab(id)
		{
			hideAllTabs();
			document.getElementById(id).removeAttribute("hidden");
			if(id == currentTabOpen)
			{
				hideAllTabs();
				currentTabOpen = null;
			}
			else{			
				currentTabOpen = id;
				if(id == "insertTab")
				{
					insertItemsReload();
				}
				else if(id == "elementTab")
				{
					elementTableNameReload();		
					elementTableTypeReload();	
					document.getElementById("textSize").innerHTML = getTextSizeOptions();	
					document.getElementById("animationTypes").innerHTML = getAnimationTypes();	
					document.getElementById("animationOrders").innerHTML = getAnimationOrders();	
					document.getElementById("animationTypes").innerHTML = getAnimationTypes();	
					document.getElementById("animationOrders").innerHTML = getAnimationOrders();	
				}
				else if(id == "publishTab")
				{					
					getAllTopics();					
				}
			}
			localStorage.setItem("currentTabOpen", currentTabOpen);
			loadFocus();
		}

		
		document.getElementById("uploadedValue").addEventListener("change", fileToList, false);

		function changeVarName()
		{
			var newName = document.getElementById("elementTabChangeVarName").value;
			var oldName = document.getElementById("elementTabChangeVarOldName").value;
			var oldKey = nameToKey(oldName);
			console.log(newName,oldName,oldKey);
			newName = "$" + newName.replaceAll("$","").trim();
			if(newName == "$")
			{
				messageCreate("Can't provided an empty name","WARNING");
			}
			else
			{
				var dupCheck = nameToKey(newName);
				if(dupCheck == -1)
				{
					translationTable[oldKey][0] = newName;
					tableCreate();
					elementTableNameReload();
					elementTableTypeReload();
					propogateChangeText(oldKey);
					document.getElementById("elementTabChangeVarOldName").value = newName;
				}
				else
				{
					messageCreate("Parameter label already exist","WARNING");
				}
			}
		}

		function loadFocus()
		{			
			document.getElementById("main").focus();
		}

		function elementTableNameReload()
		{			
			var x = document.getElementsByClassName("elementTabDropDownName");
			for(var c = 0; c < x.length; c++)
			{	
				var names = [];
				x[c].innerHTML = "";
				for(key in translationTable)
				{	 
					//y.setAttribute("class","dropdown-item");
					//y.setAttribute("onclick","insertVar('"+key+"')");
					if(translationTable[key][TT_VARIABLETYPE] != "table")
					{
						names.push(translationTable[key][TT_VARIABLENAME]);
						
					}
					
				}
				names.sort();
				names.forEach(name => {
					var y = document.createElement("option");
					y.innerHTML = name;
					x[c].appendChild(y);
				});
				console.log(names);
			}	
			loadFocus();
		}

		function elementTableTypeReload()
		{			
			var x = document.getElementsByClassName("elementTabDropDownType");
			console.log(x);
			for(var c = 0; c < x.length; c++)
			{
				x[c].innerHTML = "";
				for(key in types)
				{	 
					console.log(key);
					var y = document.createElement("option");
					//y.setAttribute("class","dropdown-item");
					//y.setAttribute("onclick","insertVar('"+key+"')");
					y.innerHTML = key;
					x[c].appendChild(y);
				}
			}
			loadFocus();
		}

		function changeTypeCall()
		{
			var id = nameToKey(document.getElementById("elementTabDropDownNameChangeType")[document.getElementById("elementTabDropDownNameChangeType").selectedIndex].value);
			var newType = document.getElementById("elementTabDropDownTypeChangeType")[document.getElementById("elementTabDropDownTypeChangeType").selectedIndex].value;
			console.log(id);
			changeType(id,'type', newType);		
			var u = document.getElementsByClassName(btoa(id));	
			Array.from(document.getElementsByClassName(btoa(id))).forEach(element => {
				console.log("CHANGE TYPE", element);
				element.innerHTML = "(" + translationTable[id][0] + "," + getJSON(id)["baseText"] + "," + translationTable[id][2] + ")";
			});
		}

		function insertItemsReload()
		{
			var x = document.getElementById("insertItems");
			x.innerHTML = "";
			console.log("insertItemsReload");
			var names = [];
			for(key in translationTable)
			{	 
				console.log(key);
				if(translationTable[key][TT_VARIABLETYPE] != "table")
				{		
					//console.log(translationTable[key][TT_VARIABLENAME]);		
					names.push([translationTable[key][TT_VARIABLENAME],key]);
					//console.log(names);	
				}
			}
			
			//console.log(names);
			names.sort((a, b) => a[0].localeCompare(b[0]));
			//console.log(names);
			names.forEach(element => {
				console.log("INSERT ITEMS RELOAD", translationTable[element[1]][TT_VARIABLETYPE]);
				var y = document.createElement("button");
				y.setAttribute("class","dropdown-item");
				y.setAttribute("onclick","insertVar('"+element[1]+"')");
				y.innerHTML = translationTable[element[1]][TT_VARIABLENAME];
				x.appendChild(y);
			});
			loadFocus();
		}


	</script>
	<?php
	}
	else
	{
		?>
		<script>
		function createSections()
		{
			var y = document.getElementById("main").innerHTML.split("%section%"); 
			var c = 0;
			console.log(y);
			for (var i =0; i < y.length; i++)
			{
				if(y[i].includes("%endsection%"))
				{
					console.log(y[i].split("%endsection%")[0],c);
					y[c] = "<h1>" + y[i].split("%endsection%")[0] + y[i].split("%endsection%").slice(1,y[i].split("%endsection%").length).join("") + "</h1>";
				}
				c ++;
			}
			console.log(y);
			document.getElementById("main").innerHTML = y.join("");
		}
		console.log(editing);
		if(editing == false)
		{
			if(questionID != -1)
			{
				loadNewQuestion(questionID,instanceId);
			}
			else if(sessionID != -1)
			{
				loadNewQuestion(sessionID,instanceId, true);
			}
		}
		</script>
		<?php
	}
	
?>
<?php

if(isset($_GET["gen"]) && $_GET["gen"] == true)
{
	if(isset($_GET["editing"]))
	{
		?>
			<script> newQuestion();</script>
		<?php
	}
	else
	{
	?> 
	<p1> No valid questionID given </p1>
	<?php
	}
}
	?>
</div>
</div>
</div>
</div>

<style>

.window {
	position: fixed;
	border-radius: 10px;
	border: none;
	background: #fff;
}

.window-content {
	height: 100%;
	font-family: monospace;
	padding: 5px;
}

.window-input {
	display: block;
	font-family: monospace;
	width: calc(100% - 1%);
	color: #fff;
	border: 4px solid #fff;
	border-bottom-right-radius: 5px;
	border-bottom-left-radius: 5px;
	padding: 2px;
	position: relative;
	bottom: 0;
	left: 0;
	right: 0;
	outline: 0;
}

.window-top, .window-top-no-bind {
	cursor: move;
	text-align: center;
	height: 5%;
	border-bottom: 1px solid rgba(0,0,0,0.5);
	border-top-right-radius: 5px;
	border-top-left-radius: 5px;
}

.window-top-no-bind {
	cursor: inherit;
}

.round {
	height: 16px;
	width: 16px;
	border-radius: 50%;
	border: none;
	margin-right: 6px;
	box-shadow: 1px 1px 2px #000;
}

#myWindow {
	z-index: 999;
}

#myWindow2 {
	top: 0;
	left: 500px;
}

</style>

<style>
	.footer {
  position: relative;
  left: 0;
  bottom: 0;
  height: 5%;
  width: 100%;
  text-align: center;
}
</style>
<script>
	function setTableStyle()
	{
		console.log("setTableStyle","STARTING");
		var getItems = Array.from(document.getElementsByClassName("baseTable"));
		getItems.forEach(element => {
			var table = element;
			console.log(table);
			if(table !== null)
			{
				console.log("setTableStyle", table);
				if(table.hasAttribute("even-color"))
				{
					var bgColor = table.getAttribute('even-color');
					console.log(table, "has even color",bgColor);
					var nAdder = 0;
					if(table.hasAttribute("even-n-adder"))
					{
						nAdder = "+" + table.getAttribute("even-n-adder");
						
						var evenRows = table.querySelectorAll('tr:nth-child(n' + nAdder + ')');
						evenRows.forEach((row, index) => {
							if ((index + 1) % 2 === 0) {
								row.style.backgroundColor = bgColor;
							}
						});
					}
					else
					{								
						var evenRows = table.querySelectorAll('tr:nth-child(even)');
						evenRows.forEach(row => {
							row.style.backgroundColor = bgColor;
						});
					}
					
				}

				if(table.hasAttribute("odd-color"))
				{
					var bgColor = table.getAttribute('odd-color');
					console.log(table, "has odd color", bgColor);
					var nAdder = 0;
					if(table.hasAttribute("odd-n-adder"))
					{
						nAdder = "+" + table.getAttribute("odd-n-adder");
						
						var evenRows = table.querySelectorAll('tr:nth-child(n' + nAdder + ')');
						evenRows.forEach((row, index) => {
							if ((index + 1) % 2 !== 0) {
								row.style.backgroundColor = bgColor;
							}
						});
					}
					else
					{								
						var evenRows = table.querySelectorAll('tr:nth-child(odd)');
						evenRows.forEach(row => {
							row.style.backgroundColor = bgColor;
						});
					}
					
					/*if(table.hasAttribute("odd-n-adder"))
					{
						nAdder = "+" + table.getAttribute("odd-n-adder")
					}
					var evenRows = table.querySelectorAll('tr:nth-child(odd' + nAdder + ')');
					evenRows.forEach(row => {
						row.style.backgroundColor = bgColor;
					});*/
				}
			}
		});
		
			
		
	}
</script>
</body>

</html>