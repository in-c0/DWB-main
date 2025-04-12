<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Digital Workbook</title>

	<?php
	$userID = null;
	$isAdmin = false;
	
	function hasRole($role, $id)
	{
		global $perms;
		global $isAdmin;
		if(isset($perms[-1]) || $isAdmin)
		{
			return true;
		}
		if(isset($perms[$id]))
		{
			return in_array($role,$perms[$id]["roles"]);
		}
		return false;
	}

	function hasRoleAnyDomain($role)
	{
		global $perms;
		global $isAdmin;
		if(isset($perms[-1]) || $isAdmin)
		{
			return true;
		}
		foreach ($perms as $key => $value)
		{
			if(isset($perms[$key]))
			{
				return in_array($role,$perms[$key]["roles"]);
			}
		}
		return false;
	}
	
	try
	{
		require("conn.php");
		require("version.php");
		require("csp.php");
		global $_version;
		global $_date;
		global $conn;
		global $isAdmin;
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
				
				$result = $conn->query($checkIfTokenIsReal);
				while ($row = $result -> fetch_assoc())
				{
					$userID = $row["id"];
					if($row["isAdmin"] == 1)
					{
						$isAdmin = true;
					}
					break;
				}     
				if($userID == null)
				{              
					
				}    
			}
			else
			{
				header("location: session/");
			}
		}
		else
		{
			header("location: session/");
		}
	}
	catch (\Throwable $e)
	{
		echo $e;
	}
		
	require("userPerms.php");
	if(hasRole("admin","-1"))
	{
		$isAdmin = true;
	}
		/*
<?php try{ if($isDev){ 
	echo '<a href="changelog.txt"> Change log </a> <a href="todo.txt"> To do list </a>';
}}catch(\Throwable $e){echo $e;}?>
	*/
	?>
	<meta name="version" content="<?php echo $_version;?>">
	<meta name="buildDate" content="<?php echo $_date;?>">
	<!--FACEBOOK-->
	<meta property="og:title" content="Digital Work Book" />
	<meta property="og:description" content="The one workbook" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0"> 
	<!--<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
-->
	
	<script src="treejs/tree.js"></script>
	<script src="js/nav.js"></script>
	<script src="js/utils.js"></script>
	<?php 
		if($isAdmin)
		{
			?> 
			<script src="js/admin.js"></script>
			<?php
		}
	?>
	<style>
		.container {
			width: 100%;
			height: 100%;
		}
		#search {
			left:50%;
			text-align:center;
		}
		.folder {
			background-position: 5px center;
			list-style-image: url('folder.png');
		}

		.newly-added {
			animation: flyin 1.2s ease forwards;
			opacity: 0;
			transform: scale(2);
			filter: blur(4px);
		}

		@keyframes flyin {
			to { 
				filter: blur(0);
				transform: scale(1);
				opacity: 1;
			}
		}

	</style>
</head>
<body> <!-- collapse for nav-coll -->
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
					<button type="button" class="btn btn-light" onclick="openJoinSession()">Join Session</button>
				</li>
				<!--
				<li class="nav-item">
					<button type="button" class="btn btn-light" onclick="newQuestion()">MAKE NEW QUESTION</button>
				</li>
	-->
				<?php
					if(hasRoleAnyDomain('educator') || hasRoleAnyDomain('admin') || hasRoleAnyDomain('orgAdmin'))
					{
						?>
						<li class="nav-item">
							<button type="button" class="btn btn-light" onclick="openTutor()">TUTORS</button>
						</li>
						<?php
					}
				?>

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
		<div class="profileItem">
			<a href="#">
				<img src="pics/profile.svg" onclick="openProfile()">
			</a>
			<br>
		</div>
		
		<?php if($userID !== null)
		{ ?>
			<button class="btn btn-outline-danger my-2 my-sm-0" type="button" onclick="logout()">LOGOUT</button>
		<?php
		} else {?>				
			<button class="btn btn-outline-success my-2 my-sm-0" type="button" onclick="login()">LOGIN</button>				
		<?php } ?>	
			
	</nav>
				
	<div id="freefloat" class="freefloat" style = "position: fixed;	top: 50%;">	</div>

	<?php 
		try{ 
			if($isDev){ 
				echo 'Welcome to the dev build, this build is running the newest version of the code. As such it may be buggy and it could cause any work to be corrupted. Its recommended to backup any questions before loading them in the dev build. This can be done by clicking the dump info in the question editor. If you find any bugs you can report them at the bug page or by press ctrl+alt+b<br>';
				//echo '<br><a href="changelog.txt"> Change log </a><br> <a href="todo.txt"> To do list </a><br> <a href="bugs.txt"> Bugs </a><br><a href="bug.php"> Report bug </a>';
			}
		}catch(\Throwable $e){echo $e;}?>
		

	<div class="container">
		<div class="list-group" id="activeTasks"></div>
		<div id="search">
			<h3> Search Questions </h3>
			<div>
				<input class="form-control rounded-pill" id="uniName"list="datalistOptions" placeholder="Uni name" onchange="checkUni()" onkeypress = "this.onchange();" onpaste="this.onchange();" oninput="this.onchange();">
				<datalist id="datalistOptions"></datalist>
			</div>
			
			<br>
			<br>
			<div id="subjectSearch" hidden="true">
				<input class="form-control rounded-pill" id="subjectName"list="subjectOptions" placeholder="Subject name" onchange="" onkeypress = "this.onchange();" onpaste="this.onchange();" oninput="this.onchange();">
				<datalist id="subjectOptions"></datalist>
			</div>
			<br>
			<br>
			<div id="topicSearch" hidden="true">
				<input class="form-control rounded-pill" id="topicName"list="topicOptions" placeholder="Topic name" onchange="" onkeypress = "this.onchange();" onpaste="this.onchange();" oninput="this.onchange();">
				<datalist id="topicOptions"></datalist>
			</div>
			<br>
			<br>


			<button onclick="searchForQuestion()"  class="btn btn-primary" >SEARCH</button>
			
			<div class="item" id="uni2"></div>
			<div class="item" id="uni"></div>
			<div class="item" id="subjects"></div>
			<div class="item" id="topics"></div>
			<div id="questions"></div>
		</div>
		<div class = "container overflow-auto">
			<ul class="list-group item" id="info"></ul>
		</div> 
	<div>
	
	<div id="main"></div>

		
	<script>
		//Get tasks asigned to the user
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
						try {
							var holder = "activeTasks";
							var j = JSON.parse(xhttp2.responseText);
							var item = j["results"];
							var inRange = 0;
							if(j["results"].length > 0)
							{
								document.getElementById(holder).innerHTML += "<h3> Active Tasks </h3>";
							}
							for(var i=0;i<item.length;i++){
								if(item[i]["dueBy"] >= Date.now())
								{
									inRange ++;
									var newI = document.createElement("a");
									newI.setAttribute("class",'list-group-item list-group-item-action');
									newI.setAttribute("href",'question.php?questionID='+item[i]["questionID"]);
									newI.innerHTML = item[i]["questionName"];
									document.getElementById(holder).appendChild(newI);
								}
							}	
							if(inRange == 0)
							{
								document.getElementById(holder).innerHTML = "";
							}	
						}
						catch
						{

						}				
					}
				}
			}
		}
		getTasks();

		function newQuestion()
		{
			location.replace("question.php?editing=true&gen=true");
		}

		function openTutor()
		{
			location.replace("tutor.php");
		}

		var allIdeas = [];
		class Node
		{
			constructor(name, parent, type,allIdeas)
			{
				this.id = allIdeas.length;
				allIdeas.push(this.id);
				this.name = name;
				this.parent = parent;
				this.type = type;
				var d = document.createElement("li");
				d.setAttribute("onclick","a()");
				d.setAttribute("class",type);
				d.setAttribute("id",this.id);
				var s = document.createElement("span");
				var u = document.createElement("ul");
				s.innerHTML = name;
				d.appendChild(s);
				d.appendChild(u);
				this.element = u;
				if(parent == null)
				{
					document.getElementById("main").appendChild(d);
				}
				else
				{
					parent.element.appendChild(d);
				}
			}
		}
		var s = [];
	//	const root = new Node("root",null, "folder",allIdeas);
	//	const a = new Node("a",root, "folder",allIdeas);
	//	const b = new Node("b",a, "folder",allIdeas);
		
	//	var c = new Node("c",root, "folder",allIdeas);
	//	c = new Node("c",a, "file",allIdeas);
	//	h = new Node("h",root, "folder",allIdeas);
		//c = new Node("c",h, "folder",allIdeas);
	//	c = new Node("c",c, "folder",allIdeas);
		//c = new Node("c",c, "file",allIdeas);
		//console.log(b);

		/*
		var count = 1;

		var root = new TreeNode("root");
			var n1 = new TreeNode("1");
				var n11 = new TreeNode("1.1");
			var n2 = new TreeNode("2");
			var n3 = new TreeNode("3");
				var n31 = new TreeNode("3.1");
				var n32 = new TreeNode("3.2");
					var n321 = new TreeNode("3.2.1");
				var n33 = new TreeNode("3.3");

		root.addChild(n1);
		root.addChild(n2);
		root.addChild(n3);
		console.log(n11.innerHTML);
		n1.addChild(n11);

		n3.addChild(n31);
		n3.addChild(n32);
		n3.addChild(n33);


		n32.addChild(n321);var view = new TreeView(root, "#container");
		/*
		<style media="screen">


		header h1{
			margin: 1em;
			display: block;
			text-align: center;
			font-size: 3rem;
			color: #fff;
		}

		header{
			padding: 5px;
			margin-bottom: 1em;

			background-color: #159957;
			background-image: -webkit-linear-gradient(30deg, #155799, #159957);
			background-image: -o-linear-gradient(30deg, #155799, #159957);
			background-image: linear-gradient(120deg, #155799, #159957);
		}

		main{
			margin: 0 1em;
			text-align: center;
		}

		#container{
			width: 200px;
			border: 1px solid #ccc;
			margin: 0 auto;
			margin-bottom: 1em;
		}

		.btn{
			display: inline-block;
			color: #fff;
			padding: 10px 20px;
			background-color: #159957;
			background-image: -webkit-linear-gradient(30deg, #155799, #159957);
			background-image: -o-linear-gradient(30deg, #155799, #159957);
			background-image: linear-gradient(120deg, #155799, #159957);
			border-color: #fff3;
			border-style: solid;
			border-width: 1px;
			border-radius: 0.3rem;
			transition: background-image 0.2s;
			text-decoration: none !important;
			margin-bottom: 10px;
		}

		.btn:hover{
			background-image: -webkit-linear-gradient(30deg, #155799, #5b9915);
			background-image: -o-linear-gradient(30deg, #155799, #5b9915);
			background-image: linear-gradient(120deg, #155799, #5b9915);
		}

		#description{
			margin-top: 1em;
		}

		a{
			color: #0085fc;
		}
		</style>
		*/
	</script>



	<script>
		var xhttp = new XMLHttpRequest();
		var uniNames = {};

		function createSubject()
		{

		}

		function createQuestion(uniID)
		{
			subjectName = document.getElementById("subjectName").value;
			topicName = document.getElementById("topicName").value;
			window.location.replace("createQuestion.php?uni=" + uniID + "&subjectName=" + subjectName + "&topicName=" + topicName);
		}

		function checkUni() //Well check if the uni exits, and if yes write the names to the dropdown. 
		{
			var uniName = document.getElementById("uniName").value;
			if(uniName.length > 2)
			{			
				xhttp.open("GET", "api.php/findOrg/" + uniName , true);	
				xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				xhttp.send();
				xhttp.onreadystatechange = function() 
				{ 
					if (this.readyState == 4 && this.status == 200)
					{
						if(xhttp.responseText.length > 0 )
						{
							var j = JSON.parse(xhttp.responseText);
							var item = j["uni"];
							var holder = "uni";
							var uniData = document.getElementById("datalistOptions");
							uniData.innerHTML = "";
							for(var i=0;i<item.length;i++){
								console.log(item[i]["name"]);
								var opt=document.createElement("option");
								opt.text=item[i]["name"];
								opt.value=item[i]["name"];
								//opt.setAttribute("onclick","getAllSubjects("+ item[i]["id"] + ")");
								uniData.appendChild(opt);
								uniNames[item[i]["name"]] = item[i]["id"];
							}
							console.log(uniNames);
							var xhttp2 = new XMLHttpRequest();
							xhttp2.open("GET", "api.php/getAllData/" + uniNames[uniName] , true);	
							xhttp2.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
							xhttp2.send();
							xhttp2.onreadystatechange = function() 
							{ 
								if (xhttp2.readyState == 4 && xhttp2.status == 200)
								{
									if(xhttp2.responseText.length > 0 )
									{
										var raw = JSON.parse(xhttp2.responseText);
										document.getElementById("subjectSearch").removeAttribute("hidden");
										document.getElementById("subjectSearch").setAttribute("class","newly-added");

										document.getElementById("topicSearch").removeAttribute("hidden");
										document.getElementById("topicSearch").setAttribute("class","newly-added");
										var localSubjects = raw["subjects"];

										//Add topics to auto complete
										var subjectData = document.getElementById("subjectOptions");
										subjectData.innerHTML = "";
										for(var i=0;i<raw["subjects"].length;i++){
											console.log("ADDING TO SUBJECT",raw["subjects"][i]["name"]);
											var opt=document.createElement("option");
											opt.text=raw["subjects"][i]["name"];
											opt.value=raw["subjects"][i]["name"];
											subjectData.appendChild(opt);
										}

										//Add topics to auto complete
										var topicData = document.getElementById("topicOptions");
										topicData.innerHTML = "";
										for(var i=0;i<raw["topics"].length;i++){
											console.log("ADDING TO TOPIC",raw["topics"][i]["name"]);
											var opt=document.createElement("option");
											opt.text=raw["topics"][i]["name"];
											opt.value=raw["topics"][i]["name"];
											topicData.appendChild(opt);
										}

										var JSONResponse = raw["data"];
										console.log(JSONResponse);
										var div = document.createElement("div");//d-flex flex-column justify-content-center w-75 
										//<button class="btn btn-primary" data-bs-toggle="collapse" onclick=> Create Question </button> 
											div.innerHTML = `<div class="mx-auto"> <span> Cant find the question? Then<a href=javascript:createQuestion(`+uniNames[uniName]+`)>  create it </a></span></div>`;
											console.log("NO ITEMS FOUND");
											document.getElementById("uni2").innerHTML = "";
											document.getElementById("uni2").appendChild(div);
										if(JSONResponse == {} || JSONResponse == "{}" || JSONResponse == [] || JSONResponse == "[]" || JSONResponse.length == 0)
										{
											
										}
										else
										{
											for (const [key, value] of Object.entries(JSONResponse)) {
												var div = document.createElement("div");//d-flex flex-column justify-content-center w-75 
												div.innerHTML = `<div class="mx-auto"> <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#`+localSubjects[key].replaceAll(" ","")+`"> Topic: `+key+` </button></div>	<div class="collapse mt-4 p-3" id="`+localSubjects[key].replaceAll(" ","")+`"></div>`;
												console.log(key);
												//var divHolder = document.createElement('div');
												//divHolder.outerHTML = `<div class="collapse mt-4 p-3" id="`+key+`">`;
												//div.appendChild(divHolder);
												document.getElementById("uni2").appendChild(div);
												for (const [key2, value2] of Object.entries(value)) {
													if(typeof (value2) != "string")
													{
														console.log(key, key2, value2);
														var subject = document.createElement('div');			
														subject.innerHTML = `<div class="mx-auto"> <button class="btn btn-danger" data-bs-toggle="collapse" data-bs-target="#`+key2.replaceAll(" ","")+`"> Course: `+key2+` </button></div><div class="collapse mt-4 p-3" id="`+key2.replaceAll(" ","")+`">`;
														subjectDivHolder = document.createElement('div');
														document.getElementById(localSubjects[key].replaceAll(" ","")).appendChild(subject);
													
														value2.forEach(element => {
															console.log(element);
															var questionD = document.createElement("div");
															questionD.innerHTML = `<div class="mx-auto"> <a class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#`+element["name"].replaceAll(" ","")+`" onclick=openQuestion('`+element["questionID"]+`',false) href="question.php?questionID=` + element["questionID"] + `&editing=false"> Question: `+element["name"]+` </button></div>`;
															document.getElementById(key2.replaceAll(" ","")).appendChild(questionD);
														});																			
													}
												}
											}
										}
									}
								}
							}
						}
					}
					else if(this.status == 302)
					{
						console.log("CHANGING");
						document.location.href = "login.php";
					}
				}				
			}
		}

		function openQuestion(questionId,editing)
		{
			console.log("OPENING QUESTION",questionId);
			window.location.href = "question.php?questionID=" + questionId + "&editing=" + editing;
		}

		var allSubjects = {};
		function getAllSubjects() //Gets all the subjects for a given uni and writes them to a dropdown
		{
			var name = document.getElementById("uniName").value;
			console.log(name);
			if(name in uniNames)
			{
				xhttp.open("GET", "api.php/getAllSubjects/" + uniNames[name] , true);	
				xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				xhttp.send();
				xhttp.onreadystatechange = function() 
				{ 
					if (xhttp.readyState == 4 && xhttp.status == 200)
					{
						if(xhttp.responseText.length > 0 )
						{
							document.getElementById("info").innerHTML = "";
							var holder = "subjects";
							var j = JSON.parse(xhttp.responseText);
							var item = j["subjects"];
							document.getElementById(holder).innerHTML = "";
							var subjectData = document.getElementById("subjectOptions");
							subjectData.innerHTML = "";
							var dropdown=document.createElement("select");
							for(var i=0;i<item.length;i++){
								console.log(item[i]["name"]);
								var opt=document.createElement("option");
								opt.text=item[i]["name"];
								opt.value=item[i]["name"];
								opt.setAttribute("onclick","getAllTopics("+ item[i]["id"] + ")");
								dropdown.options.add(opt);
								subjectData.appendChild(opt);
								allSubjects[item[i]["name"]] = item[i]["id"];
							}
							//var container=document.getElementById(holder);
							//container.appendChild(dropdown);
						}
					}
				}
				
			}
		}

		function searchSubject()
		{
			var name = document.getElementById("subjectName").value;
			console.log(name);
			var xhttp2 = new XMLHttpRequest();
			xhttp2.open("GET", "api.php/searchSubject/" + name , true);	
			xhttp2.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			xhttp2.send();
			xhttp2.onreadystatechange = function() 
			{ 
				if (xhttp2.readyState == 4 && xhttp2.status == 200)
				{
					if(xhttp2.responseText.length > 0 )
					{
						var raw = JSON.parse(xhttp2.responseText);
						var localSubjects = raw["subjects"];
						var JSONResponse = raw["data"];
						for (const [key, value] of Object.entries(JSONResponse)) {
							var div = document.createElement("div");//d-flex flex-column justify-content-center w-75 
							div.innerHTML = `<div class="mx-auto"> <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#`+localSubjects[key].replaceAll(" ","")+`"> Subject: `+key+` </button></div>	<div class="collapse mt-4 p-3" id="`+localSubjects[key].replaceAll(" ","")+`"></div>`;
							console.log(key);
							//var divHolder = document.createElement('div');
							//divHolder.outerHTML = `<div class="collapse mt-4 p-3" id="`+key+`">`;
							//div.appendChild(divHolder);
							document.getElementById("uni2").innerHTML = "";
							document.getElementById("uni2").appendChild(div);
							for (const [key2, value2] of Object.entries(value)) {
								if(typeof (value2) != "string")
								{
									console.log(key, key2, value2);
									var subject = document.createElement('div');			
									subject.innerHTML = `<div class="mx-auto"> <button class="btn btn-danger" data-bs-toggle="collapse" data-bs-target="#`+key2.replaceAll(" ","")+`"> Course: `+key2+` </button></div><div class="collapse mt-4 p-3" id="`+key2.replaceAll(" ","")+`">`;
									subjectDivHolder = document.createElement('div');
									document.getElementById(localSubjects[key].replaceAll(" ","")).appendChild(subject);
								
									value2.forEach(element => {
										console.log(element);
										var questionD = document.createElement("div");
										questionD.innerHTML = `<div class="mx-auto"> <a class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#`+element["name"].replaceAll(" ","")+`" onclick=openQuestion('`+element["questionID"]+`',false) href="question.php?questionID=` + element["questionID"] + `&editing=false"> Question: `+element["name"]+` </button></div>`;
										document.getElementById(key2.replaceAll(" ","")).appendChild(questionD);
									});																			
								}
							}
						}
					}
				}
			}
		}

		
		function searchTopic()
		{
			var name = document.getElementById("topicName").value;
			if(name.length > 3)
			{
				console.log(name);
				var xhttp2 = new XMLHttpRequest();
				xhttp2.open("GET", "api.php/searchTopic/" + name , true);	
				xhttp2.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				xhttp2.send();
				xhttp2.onreadystatechange = function() 
				{ 
					if (xhttp2.readyState == 4 && xhttp2.status == 200)
					{
						if(xhttp2.responseText.length > 0)
						{
							var raw = JSON.parse(xhttp2.responseText);
							var localTopics = raw["topics"];
							var JSONResponse = raw["data"];
							for (const [key, value] of Object.entries(JSONResponse)) {
								var div = document.createElement("div");//d-flex flex-column justify-content-center w-75 
								div.innerHTML = `<div class="mx-auto"> <button class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#`+localTopics[key].replaceAll(" ","")+`"> Topic: `+key+` </button></div>	<div class="collapse mt-4 p-3" id="`+localTopics[key].replaceAll(" ","")+`"></div>`;
								console.log(key);
								document.getElementById("uni2").innerHTML = "";
								document.getElementById("uni2").appendChild(div);
								for (const [key2, value2] of Object.entries(value)) {
									if(typeof (value2) != "string")
									{
										console.log(key, key2, value2,localTopics[key].replaceAll(" ",""));	
										var questionD = document.createElement("div");
										questionD.innerHTML = `<div class="mx-auto"> <a class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#`+value2["name"].replaceAll(" ","")+`" onclick=openQuestion('`+value2["questionID"]+`',false) href="question.php?questionID=` + value2["questionID"] + `&editing=false"> Question: `+value2["name"]+` </button></div>`;
										document.getElementById(localTopics[key].replaceAll(" ","")).appendChild(questionD);																	
									}
								}
							}
						}
					}
					else if(xhttp2.status == 302)
					{
						document.getElementById("uni2").innerHTML = "";
					}
				}
			}
		}


		var allTopics = {};
		function getAllTopics() //Gets all topics for a given subject name and writes them to a dropdown
		{
			var name = document.getElementById("subjectName").value;
			if(name in allSubjects)
			{
				xhttp.open("GET", "api.php/getAllTopics/" + allSubjects[name] , true);	
				xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				xhttp.send();
				xhttp.onreadystatechange = function() 
				{ 
					if (xhttp.readyState == 4 && xhttp.status == 200)
					{
						if(xhttp.responseText.length > 0 )
						{
							document.getElementById("info").innerHTML = "";
							var holder = "topics";
							var j = JSON.parse(xhttp.responseText);
							var item = j["topics"];
							document.getElementById(holder).innerHTML = "";
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
								
								allTopics[item[i]["name"]] = item[i]["id"];
								topicData.appendChild(opt);
							}
							//var container=document.getElementById(holder);
							//container.appendChild(dropdown);
						}
					}
				}
			}
		}

		var questions = [];

		function searchForQuestion()
		{
			var uniName = document.getElementById("uniName").value;
			var subjectName = document.getElementById("subjectName").value;
			var topicName = document.getElementById("topicName").value;
			if(uniName in uniNames)
			{
				xhttp.open("GET", "api.php/searchForQuestions/?uniID=" + uniNames[uniName] + "&subjectName=" + subjectName + "&topicName=" + topicName, true);	
				xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				xhttp.send();
				xhttp.onreadystatechange = function() 
				{ 
					if (xhttp.readyState == 4 && xhttp.status == 200)
					{
						if(xhttp.responseText.length > 0 )
						{
							document.getElementById("info").innerHTML = "";
							var holder = "questions";
							var j = JSON.parse(xhttp.responseText);
							var item = j["questions"];
							questions = item;
							document.getElementById(holder).innerHTML = "";
							for(var i=0;i<item.length;i++){
								if(item[i]["questionName"] == null || item[i]["questionName"] == "")
								{
									item[i]["questionName"] = "No Name Set";
								}
								console.log(item[i]["questionName"]);

								var info = document.createElement("span");
								info.innerHTML = "<br>Org Name: " + item[i]["orgName"] + "<br>Question ID: " + item[i]["questionID"] + "<br>Subject ID: " + item[i]["subjectID"] + "<br>Topic: " + item[i]["topicID"] + "<br>Created At: " + timeConverter(item[i]["createdAt"]) + "<br>";
								info.setAttribute("style","display:none")
								const d = new Date();
								var b = document.createElement("span");
								b.innerHTML = "";

								var divH = document.createElement("div");
								var newI = document.createElement("a");
								newI.setAttribute("class",'list-group-item-action');
								newI.setAttribute("href",'question.php?questionID=' + item[i]["questionID"]);
								newI.innerHTML = item[i]["subjectName"] + "\\" + item[i]["topicName"] + "\\" + item[i]["questionName"]+"<br>";

								divH.setAttribute("class",'list-group-item list-group-item-action');
								divH.setAttribute("id", btoa(item[i]["subjectName"] + "\\" + item[i]["topicName"] + "\\" + item[i]["questionName"] + Date.now()));
								divH.appendChild(b);
								divH.appendChild(newI);
								divH.appendChild(info);

								var showMore = document.createElement("button");
								var textSpan = document.createElement("span");
								textSpan.innerHTML = "Read More";
								textSpan.setAttribute("style","width:60px;height:30px;");
								showMore.appendChild(textSpan);
								showMore.setAttribute("onclick", "showTaskInfo('" + btoa(item[i]["subjectName"] + "\\" + item[i]["topicName"] + "\\" + item[i]["questionName"] + Date.now()) + "')");
								showMore.setAttribute("class","btn btn-outline-primary my-2 my-sm-0");
								//showMore.setAttribute("style","width:60px;height:30px;")
								divH.appendChild(showMore);
								document.getElementById("info").appendChild(divH);//.innerHTML += "<a class='list-group-item list-group-item-action' href=question.php?questionID=" + questions[i]["questionID"] + "> " + questions[i]["name"] + "-" + questions[i]["questionID"] + "</a><br>";
							}
						}
					}
					else if(xhttp.status == 302)
					{
						document.location.href = "login.php";
					}
				}
			}
		}

		function showTaskInfo(id) {
			var holder = document.getElementById(id);
			console.log(holder.children[0]);
			var dots = holder.children[0];
			var moreText = holder.children[2];
			var btnText = holder.children[3];

			if (dots.style.display === "none") {
				dots.style.display = "inline";
				btnText.innerHTML = "Read more";
				moreText.style.display = "none";
			} else {
				dots.style.display = "none";
				btnText.innerHTML = "Read less";
				moreText.style.display = "inline";
			}
		}

		function getAllQuestions()  //Gets all topics for a given topic and writes them to a dropdown
		{			
			var name = document.getElementById("topicName").value;
			if(name in allTopics)
			{
				xhttp.open("GET", "api.php/getAllQuestions/" + allTopics[name] , true);	
				xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				xhttp.send();
				xhttp.onreadystatechange = function() 
				{ 
					if (xhttp.readyState == 4 && xhttp.status == 200)
					{
						if(xhttp.responseText.length > 0 )
						{
							document.getElementById("info").innerHTML = "";
							var holder = "questions";
							var j = JSON.parse(xhttp.responseText);
							var item = j["questions"];
							questions = item;
							document.getElementById(holder).innerHTML = "";
							//var dropdown=document.createElement("select");
							for(var i=0;i<item.length;i++){
								console.log(item[i]["name"]);
								//var opt=document.createElement("option");
								//opt.text=item[i]["name"];
								//opt.value=item[i]["name"];
								//opt.setAttribute("onclick","showInfo("+ i + ")");
								//dropdown.options.add(opt);
								document.getElementById("info").innerHTML += "<a class='list-group-item list-group-item-action' href=question.php?questionID=" + questions[i]["questionID"] + "> " + questions[i]["name"] + "-" + questions[i]["questionID"] + "</a><br>";
							}/*
							var container=document.getElementById(holder);
							container.appendChild(dropdown);*/

						}
					}else if(xhttp.status == 302)
					{
						document.location.href = "login.php";
					}
				}
			}
		}

		function showInfo(item)
		{			
			console.log(item, questions);
			console.log(questions[item]);
			document.getElementById("info").innerHTML = "<a class='list-group-item list-group-item-action' href=question.php?questionID=" + questions[item]["questionID"] + "> " + questions[item]["name"] + " " + questions[item]["questionID"] + " open question </a>";
		}
	</script>
</body>
<footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top" id="footer">
	<script> var version = <?php echo "'".$_version."-".$_date."'"?>;</script>
	<script src="js/footer.js"></script>
	<script> var XQ51dka = <?php echo "'".$checkerItem."'" ?>; </script>
</footer>
</html>