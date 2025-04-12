<?php
try{
	require("conn.php");
	require("version.php");
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
			header("SQL:" .$checkIfTokenIsReal);
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
				setcookie("r","profile");
				exit();
			}    
		}
		else
		{
			header("Location: login.php");
			setcookie("r","profile");
			exit();
		}
	}
	else
	{
		header("Location: login.php");
		setcookie("r","profile");
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
<!--22/09/2024 Changed it to bootstrap !-->
	<meta name="version" content="<?php echo $_version;?>">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
		<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>	
		<script src="js/nav.js"></script>
		<?php 
		if($isAdmin)
		{
			?> 
			<script src="js/admin.js"></script>
			<?php
		}
		?>
	<title>Digital Workbook - Profile </title>
</head>
<body>	
<!--22/09/2024 Changed it to bootstrap !-->
	<nav class="navbar navbar-expand-lg navbar-light bg-light">
		<div class="navbar-collapse" id="navbarSupportedContent">
			<ul class="navbar-nav mr-auto">
				<li class="nav-item">
					<button type="button" class="btn btn-light" onclick="goHome()">HOME</button>
				</li>
				<?php
				if($isStaff)
				{
				?>
				<li class="nav-item">
					<button type="button" class="btn btn-light" onclick="newQuestion()">NEW QUESTION</button>
				</li>
				<?php
				}
				?>
				<li class="nav-item">
					<button type="button" class="btn btn-light" onclick="goSettings()">SETTINGS</button>
				</li>
				<li class="nav-item">
					<button type="button" class="btn btn-light" onclick="openStats()">STATS</button>
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
			<form class="form-inline my-2 my-lg-0">
				<?php if($userID !== null)
				{ ?>
					<button class="btn btn-outline-danger my-2 my-sm-0" type="button" onclick="logout()">LOGOUT</button>
				<?php
				} else {?>				
					<button class="btn btn-outline-success my-2 my-sm-0" type="button" onclick="login()">LOGIN</button>				
				<?php } ?>				
			</form>
		</div>
	</nav>

	<script>	
		
		var showFileStructureProfile = false;		

		var djaidja = getCookie("showFileStructureProfile");
		if(djaidja != null)
		{
			if(djaidja == 1)
			{
				showFileStructureProfile = true;
			}
		}


		var xhttp = new XMLHttpRequest();
		
		function getTasks()
		{
			var xhttp2 = new XMLHttpRequest();
			xhttp2.open("GET", "api.php/getTasks/" , true);	
			xhttp2.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			xhttp2.send();
			xhttp2.onreadystatechange = function() 
			{ 
				if (xhttp2.readyState == 4 && xhttp2.status == 200)
				{
					if(xhttp2.responseText.length > 0 )
					{
						var holder = "activeTasks";
						var j = JSON.parse(xhttp2.responseText);
						var item = j["results"];
						var uni = {};						
						displayQuestions(item,holder);
					}
				}
			}
		}

		//https://stackoverflow.com/questions/5968196/how-do-i-check-if-a-cookie-exists
		function getCookie(name) {
			var dc = document.cookie;
			var prefix = name + "=";
			var begin = dc.indexOf("; " + prefix);
			if (begin == -1) {
				begin = dc.indexOf(prefix);
				if (begin != 0) return null;
			}
			else
			{
				begin += 2;
				var end = document.cookie.indexOf(";", begin);
				if (end == -1) {
				end = dc.length;
				}
			}
			return decodeURI(dc.substring(begin + prefix.length, end));
		} 

		function getCompletedQuestions()
		{
			var xhttp2 = new XMLHttpRequest();
			xhttp2.open("GET", "api.php/getCompletedQuestions/" , true);	
			xhttp2.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			xhttp2.send();
			xhttp2.onreadystatechange = function() 
			{ 
				if (xhttp2.readyState == 4 && xhttp2.status == 200)
				{
					if(xhttp2.responseText.length > 0 )
					{
						var holder = "completedQuestions";
						var j = JSON.parse(xhttp2.responseText);
						var item = j["data"];
						var uni = {};						
						displayQuestions(item,holder);
					}
				}
			}
		}

		getCompletedQuestions();
		getTasks();
		<?php
		if($isStaff)
		{
		?>
		function newQuestion()
		{
			window.open("question.php?editing=true&gen=true","_blank");		
		}
		var uni = {};
		function makeUniDict(item, holder, editing)
		{
			for(var i=0;i<item.length;i++){
				if(item[i]['org'] in uni)
				{
					
				}
				else
				{
					uni[item[i]['org']] = {};
					uni[item[i]['org']]["name"] = item[i]['orgName'];
				}

				if(item[i]["subjectID"] in uni[item[i]['org']])
				{}
				else{
					uni[item[i]['org']][item[i]["subjectID"]] = {};
					uni[item[i]['org']][item[i]["subjectID"]] ["name"] = item[i]["subjectName"];
				}

				if(item[i]["topicID"] in uni[item[i]['org']][item[i]["subjectID"]])
				{}
				else{
					uni[item[i]['org']][item[i]["subjectID"]][item[i]["topicID"]] = {};
					uni[item[i]['org']][item[i]["subjectID"]][item[i]["topicID"]]["name"] = item[i]["topicName"];
					uni[item[i]['org']][item[i]["subjectID"]][item[i]["topicID"]]["questions"] = [];
				}
				
				var sItems = uni[item[i]['org']][item[i]["subjectID"]][item[i]["topicID"]];
				var raw = {};
				var newI = document.createElement("a");
				newI.setAttribute("class",'list-group-item list-group-item-action');
				if(editing)
				{
					newI.setAttribute("href",'question.php?questionID='+item[i]["questionID"] + "&editing=true");
				}
				else
				{
					newI.setAttribute("href",'question.php?questionID='+item[i]["questionID"]);
				}
				if(item[i]["questionName"] == null || item[i]["questionName"] == "")
				{
					item[i]["questionName"] = "No Name Set";
				}
				if(showFileStructureProfile)
				{								
					newI.innerHTML = item[i]["orgName"] + "/" + item[i]["subjectName"] + "/"  + item[i]["topicName"] + "/"  + item[i]["questionName"];
					raw["name"] = item[i]["orgName"] + "/" + item[i]["subjectName"] + "/"  + item[i]["topicName"] + "/"  + item[i]["questionName"];
				}
				else
				{
					newI.innerHTML = item[i]["questionName"];
					raw["name"] =  item[i]["questionName"];
				}
				raw["id"] = item[i]["questionID"];
				raw["item"] = newI;

				sItems["questions"].push(raw);			
			}
		}

		function openQuestion(questionId,editing)
		{
			console.log("OPENING QUESTION",questionId);
			window.location.href = "question.php?questionID=" + questionId + "&editing=" + editing;
		}

		function showUnis(holder)
		{
			for (const [key, value] of Object.entries(uni)) {
				var test = document.createElement("div");//d-flex flex-column justify-content-center w-75 
				test.innerHTML = `<div class="mx-auto"> <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#`+key+`"> `+value["name"]+` </button></div>		`;
				var divHolder = document.createElement('div');
				test.appendChild(divHolder);
				document.getElementById(holder).appendChild(test);
				divHolder.outerHTML = `<div class="collapse mt-4 p-3" id="`+key+`">`;
				for (const [key2, value2] of Object.entries(uni[key])) {
					if(typeof (value2) != "string")
					{
						console.log(key2, value2);
						var subject = document.createElement('div');			
						subject.innerHTML = `<div class="mx-auto"> <button class="btn btn-danger" data-bs-toggle="collapse" data-bs-target="#`+value2["name"].replaceAll(" ","")+`"> Course: `+value2["name"]+` </button></div><div class="collapse mt-4 p-3" id="`+value2['name'].replaceAll(" ","")+`">`;
						subjectDivHolder = document.createElement('div');
						document.getElementById(key).appendChild(subject);
						for (const [key3, value3] of Object.entries(uni[key][key2])) {
							if(typeof (value3) != "string")
							{
								var topicT = document.createElement("div");
								topicT.innerHTML = `<div class="mx-auto"> <button class="btn btn-success" data-bs-toggle="collapse" data-bs-target="#`+value3["name"].replaceAll(" ","")+`"> Topic: `+value3["name"]+` </button></div><div class="collapse mt-4 p-3" id="`+value3['name'].replaceAll(" ","")+`">`;
								document.getElementById(value2["name"].replaceAll(" ","")).appendChild(topicT);


								if("questions" in uni[key][key2][key3])
								{
									for (var x = 0; x < uni[key][key2][key3]["questions"].length; x++)
									{
										var questionD = document.createElement("div");
										questionD.innerHTML = `<div class="mx-auto"> <a class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#`+uni[key][key2][key3]["questions"][x]["name"].replaceAll(" ","")+`" onclick2=openQuestion('`+uni[key][key2][key3]["questions"][x]["id"]+`',false) href="question.php?questionID=` + uni[key][key2][key3]["questions"][x]["id"] + `&editing=false"> Question: `+uni[key][key2][key3]["questions"][x]["name"]+` </button></div>`;
										//questionD.outerHTML = uni[key][key2][key3]["questions"][x]["item"];
										document.getElementById(value3["name"].replaceAll(" ","")).appendChild(questionD);
									}
								}
							}
						}
						//subject.innerHTML += "<h5>SUBJECT: " + value2["name"] + "</h5>";
					}
				}
					
			}
		}

		function displayQuestions(item, holder, editing)
		{
			var uni = {};
			console.log(item, item.length);
			for(var i=0;i<item.length;i++){
				if(item[i]['org'] in uni)
				{
					
				}
				else
				{
					uni[item[i]['org']] = {};
					uni[item[i]['org']]["name"] = item[i]['orgName'];
				}

				if(item[i]["subjectID"] in uni[item[i]['org']])
				{}
				else{
					uni[item[i]['org']][item[i]["subjectID"]] = {};
					uni[item[i]['org']][item[i]["subjectID"]] ["name"] = item[i]["subjectName"];
				}

				if(item[i]["topicID"] in uni[item[i]['org']][item[i]["subjectID"]])
				{}
				else{
					uni[item[i]['org']][item[i]["subjectID"]][item[i]["topicID"]] = {};
					uni[item[i]['org']][item[i]["subjectID"]][item[i]["topicID"]]["name"] = item[i]["topicName"];
					uni[item[i]['org']][item[i]["subjectID"]][item[i]["topicID"]]["questions"] = [];
				}
				
				var sItems = uni[item[i]['org']][item[i]["subjectID"]][item[i]["topicID"]];
				var raw = {};
				var newI = document.createElement("a");
				newI.setAttribute("class",'list-group-item list-group-item-action');
				if(editing)
				{
					newI.setAttribute("href",'question.php?questionID='+item[i]["questionID"] + "&editing=true");
				}
				else
				{
					newI.setAttribute("href",'question.php?questionID='+item[i]["questionID"]);
				}
				if(item[i]["questionName"] == null || item[i]["questionName"] == "")
				{
					item[i]["questionName"] = "No Name Set";
				}
				if(showFileStructureProfile)
				{								
					newI.innerHTML = item[i]["orgName"] + "/" + item[i]["subjectName"] + "/"  + item[i]["topicName"] + "/"  + item[i]["questionName"];
					raw["name"] = item[i]["orgName"] + "/" + item[i]["subjectName"] + "/"  + item[i]["topicName"] + "/"  + item[i]["questionName"];
				}
				else
				{
					newI.innerHTML = item[i]["questionName"];
					raw["name"] =  item[i]["questionName"];
				}
				raw["item"] = newI;

				sItems["questions"].push(raw);			
			}
			console.log("PLACING THEM FILTERED");
			console.log(uni, uni.length);
			for (const [key, value] of Object.entries(uni)) {
				console.log(key, value);
				var uniT = document.createElement("div");
				uniT.innerHTML = "<h4> UNI: " + value["name"] + "</h4>";
				uniT.setAttribute("class",'list-group-item list-group-item-action');
				document.getElementById(holder).appendChild(uniT);
				for (const [key2, value2] of Object.entries(uni[key])) {
					if(typeof (value2) != "string")
					{
						console.log(key2, value2);
						var t = document.createElement("div");
						t.innerHTML = "<h5>SUBJECT: " + value2["name"] + "</h5>";
						t.setAttribute("class",'list-group-item list-group-item-action');
						document.getElementById(holder).appendChild(t);
						console.log("ITEMS IN KEY2",uni[key][key2]);
						for (const [key3, value3] of Object.entries(uni[key][key2])) {
							if(typeof (value3) != "string")
							{
								var topicT = document.createElement("div");
								topicT.innerHTML = "<h6> TOPIC: " + value3["name"] + "</h6>";
								topicT.setAttribute("class",'list-group-item list-group-item-action');
								document.getElementById(holder).appendChild(topicT);
								console.log(value3["name"], key3, value3);
								if("questions" in uni[key][key2][key3])
								{
									for (var x = 0; x < uni[key][key2][key3]["questions"].length; x++)
									{
										document.getElementById(holder).appendChild(uni[key][key2][key3]["questions"][x]["item"]);	
									}
								}
							}
						}
					}
				}
				if(Object.entries(uni).length > 1)
				{
					var uniT = document.createElement("div");
					uniT.innerHTML = "";
					uniT.setAttribute("class",'list-group-item list-group-item-action');
					document.getElementById(holder).appendChild(uniT);
				}
			}
		}


		function getAllQuestions()
		{
			xhttp.open("GET", "api.php/getAllQuestionsUser/" , true);	
			xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp.send();
			xhttp.onreadystatechange = function() 
			{ 
				if (xhttp.readyState == 4 && xhttp.status == 200)
				{
					if(xhttp.responseText.length > 0 )
					{
						var holder = "questions";
						var j = JSON.parse(xhttp.responseText);
						var item = j["questions"];
						document.getElementById(holder).innerHTML = "<h3> All your questions </h3>";
						makeUniDict(item,holder,true);
						showUnis(holder);


					}
				}
			}
		}		
		getAllQuestions();
		<?php
		}
		?>
	</script>
	<div class="container">
		<div id="completedQuestions" class="list-group item"> 
			<h3> Completed Questions </h3>
		</div>
		<div id="activeTasks" class="list-group item"> 
			<h3> Active Taks </h3>
		</div>
	
		<div id="search"></div><ul class="list-group item" id="questions2"></ul></div>
		<div class="container">
			<div class="d-flex flex-column justify-content-center w-75 mx-auto" id="questions">
				
			</div>
		</div>
	</div>
</body>
<div class="container2">
	<footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top"></footer>
		<div class="col-md-4 d-flex align-items-center">
			<span class="mb-3 mb-md-0 text-body-secondary">2024 Digital Workbook - V<?php echo $_version."-".$_date?></span>
		</div>
	</footer>
</div>
</html>