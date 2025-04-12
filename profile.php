<?php
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
			#
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
		
		
		
		<script src="js/utils.js"></script>
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
<div id="freefloat" class="freefloat" style='position: fixed;top: 50%;right:50%'></div>
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
				<li class="nav-item">
					<button type="button" class="btn btn-light" onclick="showTab('questionTab')">QUESTIONS</button>
				</li>
				<li class="nav-item">
					<button type="button" class="btn btn-light" onclick="showTab('orgTab')">MANAGE</button>
				</li>
				<li class="nav-item">
					<button type="button" class="btn btn-light" onclick="showTab('taskTab')">TASKS</button>
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
	<style>

		#more {display: none;}
	</style>

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

		var tasks = [];
		var unactiveTasks = [];
		var activeTasks = [];
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
						tasks = j["results"];
						var uni = {};						
						//displayQuestions(item,holder);
						if(tasks.length > 0)
						{
									
						}
						tasks.forEach(element => {
							raw = {};
							raw["name"] = element["orgName"] + "/" + element["subjectName"] + "/"  + element["topicName"] + "/"  + element["questionName"] +"<br>";
							
							var info = document.createElement("span");


							info.innerHTML = "Question ID: " + element["questionID"] +"<br>Created At: " + timeConverter(element["createdAt"]) + "<br>Due By: " + timeConverter(element["dueBy"]) + "<br>Created By: " + element["creatorName"] + "<br>";
							info.setAttribute("style","display:none")
							
							const d = new Date();
							
							var b = document.createElement("span");
							b.innerHTML = "";
							
							divH = document.createElement("div");
							
							var newI = document.createElement("a");
							//newI.setAttribute("class",'list-group-item list-group-item-action');
							newI.innerHTML = raw["name"];
							newI.setAttribute("href","question.php?questionID=" + element["questionID"]);
							newI.setAttribute("class","list-group-item-action");
							
							divH.setAttribute("id", btoa(raw["name"] + d.getTime()));
							divH.setAttribute("class", "list-group-item list-group-item-action");
							divH.appendChild(b);
							divH.appendChild(newI);
							divH.appendChild(info);
							
							var showMore = document.createElement("button");
							showMore.innerHTML = "Show More";
							showMore.setAttribute("onclick", "showTaskInfo('" + btoa(raw["name"] + d.getTime()) + "')");
							showMore.setAttribute("class","btn btn-outline-primary my-2 my-sm-0");

							divH.appendChild(showMore);
							raw["item"] = divH;
							document.getElementById(holder).appendChild(divH);
							/*var newI = document.createElement("div");
							newI.setAttribute("class",'btn-group');
							newI.setAttribute("role","group");

							var name = document.createElement("button");
							name.setAttribute("class",'btn btn-primary');
							name.innerHTML = "Name: " + element["questionName"];

							var createDay = document.createElement("button");
							createDay.setAttribute("class",'btn btn-primary');
							createDay.innerHTML = "Created At: " + Date(element["createAt"]);

							var dueDay = document.createElement("button");
							dueDay.setAttribute("class",'btn btn-primary');
							dueDay.innerHTML = "Due By: " + Date(element["dueBy"]);

							newI.appendChild(name);
							//newI.appendChild(createDay);
							newI.appendChild(dueDay);

							if(element["dueBy"] < Math.floor(Date.now() / 1000))
							{								
								unactiveTasks.push(element);
								//newI.innerHTML = "Name: " + element["questionName"] + 
								document.getElementById("oldTasks").appendChild(newI);
							}
							else
							{
								activeTasks.push(element);
								//var newI = document.createElement("a");
								//newI.setAttribute("class",'list-group-item list-group-item-action');
								//newI.innerHTML = "Name: " + element["questionName"] + " Due By: " + Date(element["dueBy"]);
								document.getElementById("activeTasks").appendChild(newI);
							}*/
						});
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
		currentTabOpen = null;
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
				if(id == "orgTab")
				{}
				else if(id == "questionTab")
				{	
				}
				else if(id == "publishTab")
				{							
				}
			}
			//loadFocus();
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
						if(item.length > 0)
						{				
							displayQuestions(item,holder);
						}
						else
						{
							var newI = document.createElement("p");
							newI.innerHTML = "No Completed Questions";
							document.getElementById(holder).appendChild(newI);
						}
					}
				}
			}
		}

		getCompletedQuestions();
		getTasks();
		<?php
		if($isStaff)
		{}
		?>
		function newQuestion()
		{
			window.open("question.php?editing=true&gen=true","_blank");		
		}
		var uni = {};

		var allQuestions = [];

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
				allQuestions.push(raw);
				sItems["questions"].push(raw);			
			}
			allQuestions.forEach(element => {				
				document.getElementById("allQuestionDropdown").innerHTML += `<li><a class="dropdown-item" onclick=getTasksFromQuestion('` + element["id"] + `')>` + element["name"] + "</a></li>";
			});
		}

		function getTasksFromQuestion(questionID)
		{
			console.log(questionID);
			//document.getElementById("allQuestionDropdownText").innerHTML = questionID;
			var xhttp2 = new XMLHttpRequest();
			xhttp2.open("GET", "api.php/getTasksQuestionID/?questionID=" + questionID , true);	
			xhttp2.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			xhttp2.send();
			xhttp2.onreadystatechange = function() 
			{ 
				if (xhttp2.readyState == 4 && xhttp2.status == 200)
				{
					if(xhttp2.responseText.length > 0 )
					{
						var j = JSON.parse(xhttp2.responseText);
						var xhttp3 = new XMLHttpRequest();
						xhttp3.open("GET", "api.php/getUsers/" + j["org"] , true);	
						xhttp3.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
						xhttp3.send();
						xhttp3.onreadystatechange = function() 
						{ 
							if (xhttp3.readyState == 4 && xhttp3.status == 200)
							{
								if(xhttp3.responseText.length > 0 )
								{
									var j2 = JSON.parse(xhttp3.responseText);
									var o = document.getElementById("userHolderTask");
									userHolderTask.innerHTML = "";
									var jsonResponse = JSON.parse(xhttp2.responseText);
									for (const [key, value] of Object.entries(j2["data"])) {
										console.log(value);
										var l = document.createElement("li");
										l.innerHTML = '<button class="dropdown-item" onclick=changeTargetedUserTask("' + btoa(value["name"]) + '")> ' +value["name"] +' </button>';
										//l.innerHTML = '<button class="dropdown-item" onclick=changeTargetedUser2("' + btoa(value["name"]) + '","' + value["id"]+ '","' + currentOrgIDUser + '")>'+value["name"]+'</button>';
										o.appendChild(l);	
									}
								}
							}
						}
					}
				}
			}
		}

		function changeTargetedUserTask(name)
		{
			document.getElementById("allQuestionDropdownText").innerHTML = atob(name);
			document.getElementById("groupNameHolderUserTask").innerHTML = atob(name);
		}


		function getAllUserFromDomain(orgID)
		{
			var xhttp2 = new XMLHttpRequest();
			xhttp2.open("GET", "api.php/getUsers/" + orgID , true);	
			xhttp2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp2.send();
			xhttp2.onreadystatechange = function() 
			{ 
				if (xhttp2.readyState == 4 && xhttp2.status == 200)
				{
					if(xhttp2.responseText.length > 0 )
					{
					}
				}
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
										questionD.innerHTML = `<div class="mx-auto"> <a class="btn btn-secondary" data-bs-toggle="collapse" data-bs-target="#`+uni[key][key2][key3]["questions"][x]["name"].replaceAll(" ","")+`" onclick=openQuestion('`+uni[key][key2][key3]["questions"][x]["id"]+`',false) href="question.php?questionID=` + uni[key][key2][key3]["questions"][x]["id"] + `&editing=false"> Question: `+uni[key][key2][key3]["questions"][x]["name"]+` </button></div>`;
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
				var divH = document.createElement("div");
				var newI = document.createElement("a");
				newI.setAttribute("class",'list-group-item-action');
				divH.setAttribute("class",'list-group-item list-group-item-action');
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
				//if(showFileStructureProfile)
				//{								
					newI.innerHTML = item[i]["orgName"] + "/" + item[i]["subjectName"] + "/"  + item[i]["topicName"] + "/"  + item[i]["questionName"]+"<br>";
					raw["name"] = item[i]["orgName"] + "/" + item[i]["subjectName"] + "/"  + item[i]["topicName"] + "/"  + item[i]["questionName"];
			//	}
				/*else
				{
					newI.innerHTML = item[i]["questionName"];
					raw["name"] =  item[i]["questionName"];
				}*/
				var info = document.createElement("span");//"<br>Created At: " + item[i]["createdAt"] + "<br>Due By: " + item[i]["dueBy"] + "<br>Created By: " + item[i]["creatorName"] 
				info.innerHTML = "<br>Question ID: " + item[i]["questionID"] + "<br>";
				info.setAttribute("style","display:none")
				const d = new Date();
				var b = document.createElement("span");
				b.innerHTML = "";

				divH.setAttribute("id", btoa(raw["name"] + Date.now()));
				divH.appendChild(b);
				divH.appendChild(newI);
				divH.appendChild(info);

				var showMore = document.createElement("button");
				showMore.innerHTML = "Show More";
				showMore.setAttribute("onclick", "showTaskInfo('" + btoa(raw["name"] + Date.now()) + "')");
				showMore.setAttribute("class","btn btn-outline-primary my-2 my-sm-0");
				divH.appendChild(showMore);
				if("dueBy" in item[i])
				{
				}
				raw["item"] = divH;
				
				sItems["questions"].push(raw);			
			}
			console.log("PLACING THEM FILTERED");
			console.log(uni, uni.length);
			if("type" in item && item[i]['type'] == "task")
			{
			}
			else
			{
			for (const [key, value] of Object.entries(uni)) {
				console.log(key, value);
				var uniT = document.createElement("div");
				uniT.innerHTML = "<h4> UNI: " + value["name"] + "</h4>";
				uniT.setAttribute("class",'list-group-item list-group-item-action');
				//document.getElementById(holder).appendChild(uniT);
				for (const [key2, value2] of Object.entries(uni[key])) {
					if(typeof (value2) != "string")
					{
						console.log(key2, value2);
						var t = document.createElement("div");
						t.innerHTML = "<h5>SUBJECT: " + value2["name"] + "</h5>";
						t.setAttribute("class",'list-group-item list-group-item-action');
						//document.getElementById(holder).appendChild(t);
						console.log("ITEMS IN KEY2",uni[key][key2]);
						for (const [key3, value3] of Object.entries(uni[key][key2])) {
							if(typeof (value3) != "string")
							{
								var topicT = document.createElement("div");
								topicT.innerHTML = "<h6> TOPIC: " + value3["name"] + "</h6>";
								topicT.setAttribute("class",'list-group-item list-group-item-action');
								//document.getElementById(holder).appendChild(topicT);
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
		var perm = {};
		var groups = {};
		function getPerms()
		{
			var xhttp2 = new XMLHttpRequest();
			xhttp2.open("GET", "api.php/getPerms/" , true);	
			xhttp2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp2.send();
			xhttp2.onreadystatechange = function() 
			{ 
				if (xhttp2.readyState == 4 && xhttp2.status == 200)
				{
					if(xhttp2.responseText.length > 0 )
					{
						groups = JSON.parse(xhttp2.responseText)["groups"];
						
						perm = JSON.parse(xhttp2.responseText)["perm"];
					}
				}
			}
		}

		var newGroup = {"name": "","domain":[]};
		var newPerm = {};
		var groupName = "";

		function setGroupName()
		{
			var xhttp2 = new XMLHttpRequest();
			xhttp2.open("GET", "api.php/searchGroup/" + currentOrgID + "/?name=" + document.getElementById("groupName").value , true);	
			xhttp2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp2.send();
			xhttp2.onreadystatechange = function() 
			{ 
				if (xhttp2.readyState == 4 && xhttp2.status == 200)
				{
					if(xhttp2.responseText.length > 0 )
					{
						if(JSON.parse(xhttp2.responseText)["used"] == false)
						{
							newGroup["name"] = document.getElementById("groupName").value;
						}
						else
						{
							messageCreate("Name has already been used for a group","ERROR");
						}
					}
				}
			}
		}
		var orgs = {};
		
		async function searchGroup(name)
		{
			var xhttp2 = new XMLHttpRequest();
			xhttp2.open("GET", "api.php/searchGroup/" + currentOrgID + "/?name=" + name , true);	
			xhttp2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp2.send();
			xhttp2.onreadystatechange = function() 
			{ 
				if (xhttp2.readyState == 4 && xhttp2.status == 200)
				{
					if(xhttp2.responseText.length > 0 )
					{
						return JSON.parse(xhttp2.responseText)["used"];
					}
				}
			}
		}

		async function addDomain()
		{
			var name = document.getElementById("domainName").value;
			var isTaken = false;
			var group = false;
			if(name.startsWith("group::"))
			{
				name = name.substr(7);
				group = true;
				var xhttp2 = new XMLHttpRequest();
				xhttp2.open("GET", "api.php/searchGroup/" + currentOrgID + "/?name=" + name , true);	
				xhttp2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				xhttp2.send();
				xhttp2.onreadystatechange = function() 
				{ 
					if (xhttp2.readyState == 4 && xhttp2.status == 200)
					{
						if(xhttp2.responseText.length > 0 )
						{
							if(JSON.parse(xhttp2.responseText)["used"] == true)
							{
								var d = document.createElement("div");
								d.setAttribute("class","btn-group");
								d.setAttribute("role","group");
								d.setAttribute("id", btoa(name));			
								d.innerHTML = '<button type="button" class="btn btn-success">'+name+'</button><button type="button" class="btn btn-secondary" onclick=removeDomain("' +btoa(name) +'")>x</button>';
								document.getElementById("domain").appendChild(d);
								var i = {"type" : "target", "subType" : "group", "target" : name};
								newGroup["domain"].push(i);						
							}
							else
							{
								messageCreate("Couldn't find group","ERROR");
							}
						}
					}
				}
				console.log(isTaken);
			}
			else
			{
				if(!isTaken)
				{
					var d = document.createElement("div");
					d.setAttribute("class","btn-group");
					d.setAttribute("role","group");
					d.setAttribute("id", btoa(name));
					if(group)
					{					
						d.innerHTML = '<button type="button" class="btn btn-success">'+name+'</button><button type="button" class="btn btn-secondary" onclick=removeDomain("' +btoa(name) +'")>x</button>';
					}
					else
					{					
						d.innerHTML = '<button type="button" class="btn btn-primary">'+name+'</button><button type="button" class="btn btn-secondary" onclick=removeDomain("' +btoa(name) +'")>x</button>';
					}
					document.getElementById("domain").appendChild(d);
					var i = {"type" : "target", "subType" : "group", "target" : name};
					newGroup["domain"].push(i);
				}
			}
		}

		async function addDomainI(item, type, baseItem)
		{
			console.log(item,type);
			var name = item;
			var isTaken = false;
			var group = false;
		
			var d = document.createElement("div");
			d.setAttribute("class","btn-group");
			d.setAttribute("role","group");
			d.setAttribute("id", btoa(name));
			//if(type == "group")
			//{					
			//	d.innerHTML = '<button type="button" class="btn btn-success">'+name+'</button><button type="button" class="btn btn-secondary" onclick=removeDomain("' +btoa(name) +'")>x</button>';
			//}
			//else
			//{					
				d.innerHTML = '<button type="button" class="btn btn-primary">'+name+'</button><button type="button" class="btn btn-secondary" onclick=removeDomain("' +btoa(name) +'")>x</button>';
			//}
			document.getElementById("domain").appendChild(d);
			var i = {"type" : "target", "subType" : "group", "target" : name};
			newGroup["domain"].push(i);
			i = {"type" : "target", "target" : name};
			if(type == "group")
			{
				i["type"] = "group";
				i["target"] = baseItem;
			}
			
		
			
		}

		function removeDomain(id)
		{
			console.log("Removing with id", id);
			for (const [key, value] of Object.entries(newGroup["domain"])) {
				if(value["target"] == atob(id))
				{
					newGroup["domain"].splice(key, 1);
					break;
				}
			}
			document.getElementById(id).outerHTML = "";
		}

		var currentOrg = "";
		var currentOrgID = -1;

		function getOrg()
		{
			var xhttp2 = new XMLHttpRequest();
			xhttp2.open("GET", "api.php/me/" , true);	
			xhttp2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp2.send();
			xhttp2.onreadystatechange = function() 
			{ 
				if (xhttp2.readyState == 4 && xhttp2.status == 200)
				{
					if(xhttp2.responseText.length > 0 )
					{
						orgs = JSON.parse(xhttp2.responseText)["org"];
						var o = document.getElementById("orgsHolder");
						var o2 = document.getElementById("orgsHolderUser");
						for (const [key, value] of Object.entries(orgs)) {
							var l = document.createElement("li");
							l.innerHTML = '<button class="dropdown-item" onclick=changeOrgName("'+btoa(value["name"])+'")>'+value["name"]+'</button>';
							console.log(l);
							o.appendChild(l);	
							l = document.createElement("li");
							l.innerHTML = '<button class="dropdown-item" onclick=changeOrgNameUser("'+btoa(value["name"])+'")>'+value["name"]+'</button>';
							
							o2.appendChild(l);						
						}
					}
				}
			}			
		}

		var currentGroupID = -1;

		
		function changeGroupNameUser(item,id)
		{			
			currentGroupID = id;
			item = atob(item);
			newGroup["name"] = item;
			console.log("ITEM",item);
			document.getElementById("domain").innerHTML = "";
			document.getElementById("groupNameHolderUser").innerHTML = item;
			document.getElementById("groupNameUser").value = item;
			document.getElementById("domain").innerHTML = "";
			for (const [key2, value] of Object.entries(groups[currentOrgID][currentGroupID]["domain"])) 
			{
				console.log("GROUP NAME", value, item);
				if(value.hasOwnProperty("subType") && value["subType"] == "group")
				{
					addDomainI(value["target"],"group",item);
				}
				else
				{					
					addDomainI(value["target"],"n",null);
				}			
			}
		}

		function changeGroupName(item,id)
		{			
			currentGroupID = id;
			item = atob(item);
			newGroup["name"] = item;
			console.log("ITEM",item);
			document.getElementById("domain").innerHTML = "";
			document.getElementById("groupNameHolder").innerHTML = item;
			document.getElementById("groupName").value = item;
			document.getElementById("domain").innerHTML = "";
			for (const [key2, value] of Object.entries(groups[currentOrgID][currentGroupID]["domain"])) 
			{
				console.log("GROUP NAME", value, item);
				if(value.hasOwnProperty("subType") && value["subType"] == "group")
				{
					addDomainI(value["target"],"group",item);
				}
				else
				{					
					addDomainI(value["target"],"n",null);
				}			
			}
		}

		var currentOrgIDUser = -1;
		function changeOrgNameUser(name)
		{
			document.getElementById("domain").innerHTML = "";
			currentOrg = atob(name);
			
			document.getElementById("groupNameHolderUser").innerHTML = "None";
			document.getElementById("userHolder").innerHTML = "";
			document.getElementById("roleUser").innerHTML = "";
			
			for (const [key, value] of Object.entries(orgs)) {
				if(value["name"] == atob(name))
				{
					currentOrgIDUser = (Number(key) + 1);		
				}

			}
			newPerm = {};
			newPerm["perm"] = {};
			newPerm["org"] = currentOrgIDUser;
			newPerm["perm"][currentOrgIDUser] = {"org": currentOrgIDUser};
			newPerm["perm"][currentOrgIDUser]["roles"] = [];
			newPerm["perm"][currentOrgIDUser]["domain"] = [];
			var xhttp2 = new XMLHttpRequest();
			xhttp2.open("GET", "api.php/getUsers/" + currentOrgIDUser , true);	
			xhttp2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp2.send();
			xhttp2.onreadystatechange = function() 
			{ 
				if (xhttp2.readyState == 4 && xhttp2.status == 200)
				{
					if(xhttp2.responseText.length > 0 )
					{
						var o = document.getElementById("userHolder");
						var jsonResponse = JSON.parse(xhttp2.responseText);
						for (const [key, value] of Object.entries(jsonResponse["data"])) {
							console.log(value);
							var l = document.createElement("li");
							l.innerHTML = '<button class="dropdown-item" onclick=changeTargetedUser("' + btoa(value["name"]) + '","' + value["id"]+ '","' + currentOrgIDUser + '")>'+value["name"]+'</button>';
							o.appendChild(l);	
						}
					}
				}
				else if(xhttp2.readyState == 4 && xhttp2.status == 401)
				{
					messageCreate("Dont have access to that org", "ERROR");
				}
			}
			document.getElementById("orgNameHolderUser").innerHTML = atob(name);
		}

		async function addDomainIT(item, type, baseItem)
		{
			console.log(item,type);
			var name = item;
			var isTaken = false;
			var group = false;
		
			var d = document.createElement("div");
			d.setAttribute("class","btn-group");
			d.setAttribute("role","group");
			d.setAttribute("id", btoa(name));
			if(type == "group")
			{					
				d.innerHTML = '<button type="button" class="btn btn-success">'+name+'</button><button type="button" class="btn btn-secondary" onclick=removeDomain("' +btoa(name) +'")>x</button>';
			}
			else
			{					
				d.innerHTML = '<button type="button" class="btn btn-primary">'+name+'</button><button type="button" class="btn btn-secondary" onclick=removeDomain("' +btoa(name) +'")>x</button>';
			}
			document.getElementById("domainUser").appendChild(d);
			var i = {"type" : "target", "subType" : "group", "target" : name};
			newGroup["domain"].push(i);
			i = {"type" : "target", "target" : name};
			if(type == "group")
			{
				i["type"] = "group";
				i["target"] = name;
			}
			newPerm["perm"][currentOrgIDUser]["domain"].push(i);
		}


		async function addDomainUser()
		{
			var name = document.getElementById("domainNameUser").value;
			console.log(name);
			type = "target";
			if(name.startsWith("group::"))
			{
				type = "group";		
				name = name.replace("group::","");
			}
			//console.log(item,type);
			var isTaken = false;
			var group = false;
		
			var d = document.createElement("div");
			d.setAttribute("class","btn-group");
			d.setAttribute("role","group");
			d.setAttribute("id", btoa(name));
			if(type == "group")
			{					
				d.innerHTML = '<button type="button" class="btn btn-success">'+name+'</button><button type="button" class="btn btn-secondary" onclick=removeDomain("' +btoa(name) +'")>x</button>';
			}
			else
			{					
				d.innerHTML = '<button type="button" class="btn btn-primary">'+name+'</button><button type="button" class="btn btn-secondary" onclick=removeDomain("' +btoa(name) +'")>x</button>';
			}
			document.getElementById("domainUser").appendChild(d);
			var i = {"type" : "target", "subType" : "group", "target" : name};
			newGroup["domain"].push(i);
			i = {"type" : "target", "target" : name};
			if(type == "group")
			{
				i["type"] = "group";
				i["target"] = name;
			}
			newPerm["perm"][currentOrgIDUser]["domain"].push(i);
		}

		function removeRole(id)
		{
			console.log("Removing with id", id);
			for (const [key, value] of Object.entries(newPerm["perm"][currentOrgIDUser]["roles"])) {
				if(value == atob(id).replace("role::",""))
				{
					console.log(value, atob(id).replace("role::",""));
					newPerm["perm"][currentOrgIDUser]["roles"].splice(key, 1);
					break;
				}
			}
			document.getElementById(id).outerHTML = "";
		}

		async function addRole(name, canRemove = true)
		{
			var d = document.createElement("div");
			d.setAttribute("class","btn-group");
			d.setAttribute("role","role");
			d.setAttribute("id", btoa("role::" + name));
			console.log(canRemove);
			if(canRemove)
			{	
				d.innerHTML = '<button type="button" class="btn btn-primary">'+name+'</button><button type="button" class="btn btn-secondary" onclick=removeRole("' +btoa("role::" + name) +'")>x</button>';
			}
			else
			{
				d.innerHTML = '<button type="button" class="btn btn-primary">'+name+'</button>';
			}
			document.getElementById("roleUser").appendChild(d);
			
		}


		function changeTargetedUser(name,id,orgIDUser)
		{
			document.getElementById("groupNameHolderUser").innerHTML = atob(name);
			document.getElementById("domainUser").innerHTML = "";
			document.getElementById("roleUser").innerHTML = "";
			newPerm["perm"][currentOrgIDUser]["domain"] = [];
			console.log(name,id,orgIDUser);
			
			var xhttp2 = new XMLHttpRequest();
			xhttp2.open("GET", "api.php/getUserPerms/" + orgIDUser + "/" + id, true);	
			xhttp2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp2.send();
			xhttp2.onreadystatechange = function() 
			{ 
				if (xhttp2.readyState == 4 && xhttp2.status == 200)
				{
					if(xhttp2.responseText.length > 0 )
					{
						newPerm["userID"] = id;
						console.log("SET USERID",id, orgIDUser);
						var o = document.getElementById("userHolder");
						var jsonResponse = JSON.parse(xhttp2.responseText);
						for (const [key, value] of Object.entries(jsonResponse["data"])) {
							if(value["org"] == orgIDUser)
							{
								newPerm["perm"][orgIDUser]["roles"] = jsonResponse["data"][key]["roles"];
								for (const [key2, value2] of Object.entries(value["domain"])) {
									console.log(value2);
									addDomainIT(value2["target"],value2["type"],"");
								}
								for (const [key2, value2] of Object.entries(value["roles"])) {
									console.log(value2,perm[orgIDUser]["roles"],perm[orgIDUser]);
									if(perm[orgIDUser]["roles"].includes(value2))
									{
										addRole(value2,true);
									}
									else if(perm[orgIDUser]["roles"].includes("admin"))
									{
										addRole(value2,false);
									}
									else if(perm[orgIDUser]["roles"].includes("orgAdmin"))
									{
										if(value2 == "educator" || value2 == "learner")
										{
											
											addRole(value2);
										}
										else
										{
											addRole(value2,false);
										}
									}
									else
									{
										addRole(value2,false);
									}
								}
							}
						}
						console.log(perm[orgIDUser]["roles"]);
						var o = document.getElementById("userPermissionHolder");
						document.getElementById("userPermissionHolder").innerHTML = "";
						if(perm[orgIDUser]["roles"].includes("orgAdmin"))
						{
							var i = document.createElement("li");
							i.innerHTML = '<button class="dropdown-item" onclick=setType("orgAdmin")> orgAdmin </button>';
							o.appendChild(i);

							i = document.createElement("li");
							i.innerHTML = '<button class="dropdown-item" onclick=setType("educator")> educator </button>';
							o.appendChild(i);

							i = document.createElement("li");
							i.innerHTML = '<button class="dropdown-item" onclick=setType("learner")> learner </button>';
							o.appendChild(i);
						}
						else
						{							
							var i = document.createElement("li");
							i.innerHTML = '<button class="dropdown-item" onclick=setType("educator")> educator </button>';
							o.appendChild(i);

							i = document.createElement("li");
							i.innerHTML = '<button class="dropdown-item" onclick=setType("learner")> learner </button>';
							o.appendChild(i);
						}
						var jsonResponse = JSON.parse(xhttp2.responseText);					
					}
				}
				else if(xhttp2.readyState == 4 && xhttp2.status == 401)
				{
					messageCreate("Dont have permission","ERROR");
				}
			}
		}

		function setType(type)
		{
			if(!newPerm["perm"][currentOrgIDUser]["roles"].includes(type))
			{				
				newPerm["perm"][currentOrgIDUser]["roles"].push(type);
				addRole(type);
			}			
		}

		function changeOrgName(name)
		{
			document.getElementById("domain").innerHTML = "";
			currentOrg = atob(name);
			document.getElementById("groupNameHolder").innerHTML = "None";			
			document.getElementById("roleUser").innerHTML = "";
			document.getElementById("groupHolder").innerHTML = "";
			for (const [key, value] of Object.entries(orgs)) {
				if(value["name"] == atob(name))
				{
					currentOrgID = value["id"];		
				}

			}
			console.log(currentOrgID);
			if(Number(currentOrgID) in perm)
			{
				newGroup["org"] = currentOrgID;
				
				if(currentOrgID in groups)
				{				
					var o = document.getElementById("groupHolder");
					for (const [key, value] of Object.entries(groups[currentOrgID])) {
						var l = document.createElement("li");
						var div = document.createElement("div");
						l.innerHTML = '<button class="dropdown-item" href="javascript:changeGroupName("'+btoa(value["name"])+'","'+ key+'")" onclick=changeGroupName("'+btoa(value["name"])+'","'+key+'")>'+value["name"]+'</button>';
						console.log("Added to group options", value, l);
						o.appendChild(l);						
						o.appendChild(div);								
					}
				}
				else
				{				
					var o = document.getElementById("groupHolder");
					document.getElementById("groupNameHolder").innerHTML = "None";
					o.innerHTML = "";
				}
				/*var it = document.getElementsByClassName("orgNameHolder");
				for (var jj = 0; it < it.length; jj++)
				{
					it[jj].innerHTML = atob(name);
				}*/
				document.getElementById("orgNameHolder").innerHTML = atob(name);
			}
			else
			{
				messageCreate("Dont have permission to access this org", "ERROR");
			}
		}

		function submitGroup()
		{
			if(newGroup["name"] != "")
			{
				console.log(newGroup);
				var xhttp2 = new XMLHttpRequest();
				xhttp2.open("GET", "api.php/createGroup/"+currentOrgID+"/?data=" + JSON.stringify(newGroup) , true);	
				xhttp2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
				xhttp2.send();
			}
		}

		function submitUser()
		{
			console.log(newPerm);
			var xhttp2 = new XMLHttpRequest();
			xhttp2.open("GET", "api.php/createPerm/"+currentOrgIDUser+"/?data=" + JSON.stringify(newPerm) , true);	
			xhttp2.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			xhttp2.send();
			
		}

		function createNewTask()
		{
			var dueBy = Date.parse(document.getElementById("taskTime").value) / 1000;
			console.log(dueBy);
		}

		getAllQuestions();
		getPerms();
		getOrg();
		<?php
		
		?>
	</script>
	<div id="orgTab" class="tab" hidden> <!-- 23/10/2024 Added !-->
		<div class="container">
			<h3> Your Access </h3>
			<p> You can only change permission or create groups if you have permission in that organisation </p>
			<h3> Manage User </h3>
			<h3> Create new group </h3>
			<p> Chose which org you are editing </p>
			<div class="dropdown">
				<button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="orgNameHolder">
					Orgs
				</button>
				<ul class="dropdown-menu" id="orgsHolder">
					
				</ul>
			</div>
			<p> Your current domain reach is {$domain} </p>
			<p> If you want to change a current group select it from below </p>
			<div class="dropdown">
				<button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="groupNameHolder">
					Groups
				</button>
				<ul class="dropdown-menu" id="groupHolder">
					
				</ul>
			</div>

			<div class="input-group mb-3">
				<input placeholder="Group Name" id="groupName" class="input-group-text"><button onclick=setGroupName() type="button" class="btn btn-primary">Set</button><br><br>
			</div>
			<p> To add a group to this group write group:: and then the group name for example group::admin </p>
			<div class="input-group mb-3">
				<input placeholder="Search Domain" id="domainName" class="input-group-text"><button type="button" class="btn btn-primary" onclick=addDomain()>Set</button><br><br>
			</div>
			<div class="container" id="domain">
			</div><br>
			<button class="btn btn-secondary" onclick=submitGroup()> Submit </button>


			<h3>Assign permission to a user </h3>
			<p> Chose which org you are editing </p>
			<div class="dropdown">
				<button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="orgNameHolderUser">
					Orgs
				</button>
				<ul class="dropdown-menu" id="orgsHolderUser">
					
				</ul>
			</div>
			<p> Your current domain reach is {$domain} </p>
			<p> Which user are you changing perms for </p>
			<div class="dropdown">
				<button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="groupNameHolderUser">
					Users
				</button>
				<ul class="dropdown-menu" id="userHolder">
					
				</ul>
			</div>
			<p> Change permission type </p>
			<div class="dropdown">
				<button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="permissionTypeHolderUser">
					Type
				</button>
				<ul class="dropdown-menu" id="userPermissionHolder">
					
				</ul>
			</div><br>
			<div class="container" id="roleUser">
			</div><br>

			<p> To add a group to this group write group:: and then the group name for example group::admin </p>
			<div class="input-group mb-3">
				<input placeholder="Search Domain" id="domainNameUser" class="input-group-text"><button type="button" class="btn btn-primary" onclick=addDomainUser()>Set</button><br><br>
			</div>
			<div class="container" id="domainUser">
			</div><br>
			<button class="btn btn-secondary" onclick=submitUser()> Submit </button>	

			<span>
				Change Course Name 
			</span>
			<div class="input-group mb-3">
				<input placeholder="New Name" id="newCourseName" class="input-group-text"><button type="button" class="btn btn-primary" onclick=addDomainUser()>Set</button><br><br>
			</div>
			<div class="container" id="domainUser">
			</div><br>
			<button class="btn btn-secondary" onclick=submitUser()> Submit </button>	


		</div>
	</div>
	<div id="questionTab" class="tab">
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
	</div>
	<div id="taskTab" class="tab" hidden>
		<div class="container">
			<div>
				<h1> Your active tasks </h1>
				<div id="activeTasks" class="list-group item"> </div> 
				<h1> Your old tasks </h1>
				<div id="oldTasks" class="list-group item"> </div>
				<h1> Create new task </h1>
				<div id="createTask" class="list-group item">
					<div class="dropdown">
						<button class="btn btn-secondary dropdown-toggle" id="allQuestionDropdownText" type="button" data-bs-toggle="dropdown" aria-expanded="false">
							Dropdown button
						</button>
						<ul class="dropdown-menu" id ="allQuestionDropdown">
						</ul>
					</div>
					<h2> Current Tasks </h2>
					<div class="container">
						<div class="dropdown">
							<button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="groupNameHolderUserTask">
								Users
							</button>
							<ul class="dropdown-menu" id="userHolderTask">								
							</ul>
						</div>
					</div>
					<input placeholder = "unixTime Ending" id="taskTime" type="date"> </input> 
					<button onclick=createNewTask()> Submit </button>
				</div>
			</div>
		</div>
	</div>
</body>
<footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top" id="footer">
	<script> var version = <?php echo "'".$_version."-".$_date."'"?>;</script>
	<script> var XQ51dka = <?php echo "'".$checkerItem."'" ?>; </script>
	<script src="js/footer.js"></script>
</footer>
</html>