<?php
try{
	require("conn.php");
	require("version.php");
	require("csp.php");
	global $_version;
	global $_date;
	global $conn;
	global $isDev;
	$isAdmin = false;
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
				setcookie("r","quesition");
				exit();
			}    
		}
		else
		{
			header("Location: login.php?r=question");
			setcookie("r","quesition");
			exit();
		}
	}
	else
	{
		header("Location: login.php?r=question");
		setcookie("r","quesition");
		exit();
	}
}
catch (\Throwable $e)
{
	echo $e;
}
	if(!isset($_GET["uni"]) && !isset($_GET["questionID"]))
	{
		if($isDev)
		{
			header("Location: /dev.");
		}
		else
		{
			header("Location: /main");
		}
	}

?>

<html>	
	<meta charset="UTF-8">
	<title>Digital Workbook - Create Question</title>
	<meta name="version" content="<?php echo $_version;?>">
	<meta name="buildDate" content="<?php echo $_date;?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0"> 
	<script src="js/nav.js"></script>
	<script src="js/utils.js"></script>
</head>
<body>
	<?php
	if(isset($_GET["uni"]))
	{
		?>
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
			<?php if($userID !== null)
			{ ?>
				<button class="btn btn-outline-danger my-2 my-sm-0" type="button" onclick="logout()">LOGOUT</button>
			<?php
			} else {?>				
				<button class="btn btn-outline-success my-2 my-sm-0" type="button" onclick="login()">LOGIN</button>				
			<?php } ?>		
	</nav>
	<div class="container">
		<div class="list-group" id="activeTasks"></div>
			<div id="search">
				<span> Organization </span>
				<input class="form-control rounded-pill" placeholder = "Uni" readonly id="uniName" value="">
				<input class="form-control rounded-pill" placeholder = "Uni" readonly id="uniNameId" value=<?php echo $_GET["uni"] ?> hidden>  <br>

				<span> Question Name </span>
				<input class="form-control rounded-pill" placeholder = "Question Name" id="questionName" required> <br>

				<span> Subject Name </span>
				<input class="form-control rounded-pill" id="subjectName"list="subjectOptions" placeholder="Subject name" onchange="getAllSubjects()" onkeypress = "this.onchange();" onpaste="this.onchange();" oninput="this.onchange();" required>
				<datalist id="subjectOptions"></datalist><br>

				<span> Topic Name </span>
				<input class="form-control rounded-pill" id="topicName"list="topicOptions" placeholder="Topic name" onchange="getAllTopics()" onkeypress = "this.onchange();" onpaste="this.onchange();" oninput="this.onchange();" required>
				<datalist id="topicOptions"></datalist><br>
				<span> Create Question </span>
				<div class="dropdown">
					<select class="elementTabDropDownName btn btn-outline-primary" id="getTypeOfQuestion">
						<option id='1'> From scratch </option>
						<option id='2'> From word document </option>
						<option id='3'> From question data string </option>
					</select>	
				</div> 
				<button class="btn btn-outline-success" onclick=createQuestion()> Create Question </button>
			</div>
		</div>
	</div><?php
	}
	else
	{
		if(isset($_GET["type"]) && is_numeric($_GET["type"]))
		{
			$type = $_GET["type"];
			if($type == 0)
			{
				header("location: question.php?questionID=" . $_GET["questionID"]);
			}
			else if($type == 1)
			{
				?>
				<style>
					.center {
						margin: 0;
						position: absolute;
						top: 50%;
						left: 30%;
					}
				</style>	
				<script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.2/mammoth.browser.min.js"></script><script src="js/nav.js"></script>
				<div class="container" id="holder">
					<div class="center">
						<span> Upload the word document file to use as a base for the question section </span><br>
						<span> or if you dont have on click here <a class='form-check-label' href="javascript:skipText()"> skip </a> </span><br>
						<input type="file" class="custom-file-input" id="upload" accept=".docx" />
					</div>
				</div>
				<div hidden id="word">
					<span> Remove any unessary information </span><br>
					<button class="btn btn-outline-success" onclick=saveQuestionData()> Save Question </button>
					<div class="container__right2" id ="output" contenteditable="true"></div>
				</div>
				<div hidden class="container" id="holder2">
					<div class="center">
						<span> Upload the word document file to use as a base for the answer section </span><br>
						<span> or if you dont have on click here <a class='form-check-label' href="javascript:skipText()"> skip </a> </span><br>
						<input type="file" class="custom-file-input" id="upload2" accept=".docx" />
					</div>
				</div>
				<div hidden class="container" id="final">
					<div class="center">
						<div id="main"></div>
						<div id="answer"></div>
						<button class="btn btn-outline-success" onclick=saveQuestion()> Create Question </button><br>
					</div>
				</div>
				<script>
					var id = 0;
					document.getElementById('upload').addEventListener('change', function (event) {
						let reader = new FileReader();
						reader.onload = function (event) {
							mammoth.convertToHtml({ arrayBuffer: event.target.result })
								.then(function (result) {
									console.log(result.value);
									var cleanResult = result.value;
									cleanResult.replaceAll("<table", "<table border='2'");
									document.getElementById('output').innerHTML = cleanResult;
									document.getElementById("holder").setAttribute("hidden","true");
									document.getElementById("word").removeAttribute("hidden");
									cleanDoc();
								})
								.catch(function (err) {
									console.log(err);
								});
						};
						reader.readAsArrayBuffer(event.target.files[0]);
					});

					document.getElementById('upload2').addEventListener('change', function (event) {
						let reader = new FileReader();
						reader.onload = function (event) {
							mammoth.convertToHtml({ arrayBuffer: event.target.result })
								.then(function (result) {
									console.log(result.value);
									var cleanResult = result.value;
									cleanResult.replaceAll("<table", "<table border='2'");
									document.getElementById('output').innerHTML = cleanResult;
									document.getElementById("holder2").setAttribute("hidden","true");
									document.getElementById("word").removeAttribute("hidden");
									cleanDoc();
								})
								.catch(function (err) {
									console.log(err);
								});
						};
						reader.readAsArrayBuffer(event.target.files[0]);
					});

					function skipText()
					{
						document.getElementById('output').innerHTML = "";
						saveQuestionData();
					}

					var questionData = null;
					var answerData = null;
					var translationTable = {}
					var questionID = "<?php echo strip_tags($_GET["questionID"]); ?>";
					function saveQuestionData()
					{
						if(questionData == null)
						{
							document.getElementById("holder").setAttribute("hidden","true");
							document.getElementById("word").removeAttribute("hidden");
							questionData = document.getElementById('output').innerHTML;
							document.getElementById("word").setAttribute("hidden","true");
							document.getElementById("holder2").removeAttribute("hidden");
						}
						else
						{							
							answerData = document.getElementById('output').innerHTML;
							document.getElementById("word").setAttribute("hidden","true");
							document.getElementById("holder2").setAttribute("hidden","true");
							document.getElementById("final").removeAttribute("hidden");
						}
					}

					function cleanDoc()
					{			
						var itemsToRemove = ["<h2>","</h2>","<strong>"];
						var lines = [];
						document.getElementById('output').innerHTML.split("</table>").forEach(element => {
							element = element.replaceAll("<table", `<table border="2" id="` + id + `"`).replaceAll("<td></td>", `<td>&nbsp;</td>`);
							translationTable[id] = ["", "%table{"+ '"values":[1,1], "currentID" : ' + id + '}',"table"];
							itemsToRemove.forEach(element2 => {
								element = element.replaceAll(element2,"");
							});
							id++;
							lines.push(element + '</table><br>');
						});
						console.log(lines);
						document.getElementById('output').innerHTML = lines.join("");
					}

					function saveQuestion()
					{
						var allText = removeSpecialChars(questionData);
						var answerText = removeSpecialChars(answerData);//.replace(/<\!--.*?-->/g, "");
						var cleanAllText = allText;
						document.getElementById("main").innerHTML = questionData;
						document.getElementById("answer").innerHTML = answerData;

						var cleanAnswerText = answerText;
						for (var key in translationTable) 
						{
							Array.from(document.getElementsByClassName(btoa(key))).forEach(element => {
								element.outerHTML = translationTable[key][TT_JSONVALUE];
							});
						}
						answerText = document.getElementById("answer").innerHTML;
						var answerTextC = answerText;
						console.log(allText);
						console.log(answerText);
						allText = encodeURIComponent(document.getElementById("main").innerHTML);
						document.getElementById("main").innerHTML = cleanAllText;
						document.getElementById("answer").innerHTML = cleanAnswerText;
						answerText = encodeURIComponent(answerText);
						var xhttp = new XMLHttpRequest();
						xhttp.open("POST", "api.php/updateQuestion/" , true);				
						xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
						xhttp.send("value="+btoa(allText) + "&answer=" + btoa(answerText) + "&questionID=" + questionID +"&translationTable=" + btoa(encodeURIComponent(JSON.stringify(translationTable))));
						xhttp.onreadystatechange = function() 
						{ 
							if (xhttp.readyState == 4 && xhttp.status == 200)
							{
								if(xhttp.responseText.length > 0 )
								{
									window.location = "question.php?questionID=" + questionID;
								}
							}
						}
					}

					</script>
			<?php
			}
			else if($type == 2)
			{
				?>
				<input type="text" class="text" id="questionString" placeholder="question string data" style="width:100%"/>
				<button onclick=decode()> Decode </button><br>
				<div id="data" hidden>
					<button  class="btn btn-outline-success" onclick=saveQuestion()> Create Question </button> <br>
					<span> Question Data </span>
					<div id="main" contenteditable="true">

					</div>
					<span> Answer Data </span>
					<div id="answer" contenteditable="true">

					</div>
				</div>
				<script>
					var questionID = "<?php echo strip_tags($_GET["questionID"]); ?>";
					function saveQuestion()
					{
						var allText = removeSpecialChars(document.getElementById("main").innerHTML);
						var answerText = removeSpecialChars(document.getElementById("answer").innerHTML);//.replace(/<\!--.*?-->/g, "");
						var cleanAllText = allText;
						var cleanAnswerText = answerText;
						for (var key in translationTable) 
						{
							Array.from(document.getElementsByClassName(btoa(key))).forEach(element => {
								element.outerHTML = translationTable[key][TT_JSONVALUE];
							});
						}
						answerText = document.getElementById("answer").innerHTML;
						var answerTextC = answerText;
						console.log(allText);
						console.log(answerText);
						allText = encodeURIComponent(document.getElementById("main").innerHTML);
						document.getElementById("main").innerHTML = cleanAllText;
						document.getElementById("answer").innerHTML = cleanAnswerText;
						answerText = encodeURIComponent(answerText);
						var xhttp = new XMLHttpRequest();
						xhttp.open("POST", "api.php/updateQuestion/" , true);				
						xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
						xhttp.send("value="+btoa(allText) + "&answer=" + btoa(answerText) + "&questionID=" + questionID +"&translationTable=" + btoa(encodeURIComponent(JSON.stringify(translationTable))));
						xhttp.onreadystatechange = function() 
						{ 
							if (xhttp.readyState == 4 && xhttp.status == 200)
							{
								if(xhttp.responseText.length > 0 )
								{
									window.location = "question.php?questionID=" + questionID;
								}
							}
						}
					}

					function decode()
					{
						document.getElementById("data").removeAttribute("hidden");
						var d = document.getElementById("questionString").value.replaceAll(" ","").replaceAll("\n","").replaceAll("<br>","");
						document.getElementById("questionString").value = d;
						console.log(d);
						loadData(d);
					}
				</script>
				<script src="js/staff.js">			

				</script>
				
<?php
			}
		}
	}?>
	<script>

		getOrgName(<?php echo $_GET["uni"] ?>);

		function getOrgName(id)
		{
			var xhttp = new XMLHttpRequest();
			xhttp.open("GET", "api.php/getAllSubjects/" + id, true);	
			xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			xhttp.send();
			xhttp.onreadystatechange = function() 
			{ 
				if (xhttp.readyState == 4 && xhttp.status == 200)
				{
					if(xhttp.responseText.length > 0 )
					{
						var j = JSON.parse(xhttp.responseText);
						document.getElementById("uniName").value = j["name"];
					}
				}
			}
		}
		document.getElementById("subjectName").value = '<?php echo $_GET['subjectName'] ?>';
		document.getElementById("topicName").value = '<?php echo $_GET['topicName'] ?>';

		function getUniName()
		{
			var uniName = document.getElementById("uniName").value;
			var xhttp = new XMLHttpRequest();
			xhttp.open("GET", "api.php/createQuestionBatch/?subject=" + subjectName + "&topic=" + topicName + "&org=" + uniName + "&name=" + questionName , true);	
			xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			xhttp.send();
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

		function createQuestion()
		{
			var xhttp = new XMLHttpRequest();
			var questionName = document.getElementById("questionName").value;
			var subjectName = document.getElementById("subjectName").value;
			var topicName = document.getElementById("topicName").value;
			var uniName = document.getElementById("uniNameId").value;
			var type = document.getElementById("getTypeOfQuestion").value;
			console.log(questionName,subjectName,topicName);
			if(questionName == "" && subjectName == "" && topicName == "")
			{
				if(questionName == "")
				{					
					document.getElementById("questionName").setAttribute("style","border: 2px solid red;");
				}
				
				if(subjectName == "")
				{					
					document.getElementById("subjectName").setAttribute("style","border: 2px solid red;");
				}
				
				if(topicName == "")
				{					
					document.getElementById("topicName").setAttribute("style","border: 2px solid red;");
				}
			}
			else
			{
				console.log(type);
				xhttp.open("GET", "api.php/createQuestionBatch/?subject=" + subjectName + "&topic=" + topicName + "&org=" + uniName + "&name=" + questionName , true);	
				xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				xhttp.send();
				xhttp.onreadystatechange = function() 
				{ 
					if (xhttp.readyState == 4 && xhttp.status == 200)
					{
						if(xhttp.responseText.length > 0 )
						{
							var jsonObject = JSON.parse(xhttp.responseText);
							if("questionID" in jsonObject)
							{
								if(type.toLowerCase().trim() == "from scratch")
								{
									window.location.replace("createQuestion.php?questionID=" + jsonObject["questionID"] + "&type=0");
								}
								else if(type.toLowerCase().trim() == "from word document")
								{
									window.location.replace("createQuestion.php?questionID=" + jsonObject["questionID"] + "&type=1");
								}							
								else if(type.toLowerCase().trim() == "from question data string")
								{
									window.location.replace("createQuestion.php?questionID=" + jsonObject["questionID"] + "&type=2");
								}
								
							}
						}
					}
				}
			}
		}

		function getAllSubjects() //Gets all the subjects for a given uni and writes them to a dropdown
		{
			var xhttp = new XMLHttpRequest();
			var name = document.getElementById("uniName").value;
			console.log(name);
			xhttp.open("GET", "api.php/getAllSubjects/" + name , true);	
			xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			xhttp.send();
			xhttp.onreadystatechange = function() 
			{ 
				if (xhttp.readyState == 4 && xhttp.status == 200)
				{
					if(xhttp.responseText.length > 0 )
					{
						var holder = "subjects";
						var j = JSON.parse(xhttp.responseText);
						var item = j["subjects"];
						var subjectData = document.getElementById("subjectOptions");
						subjectData.innerHTML = "";
						var dropdown=document.createElement("select");
						for(var i=0;i<item.length;i++){
							console.log(item[i]["name"]);
							var opt=document.createElement("option");
							opt.text=item[i]["name"];
							opt.value=item[i]["name"];
							subjectData.appendChild(opt);
						}
					}
				}
			}
		}

		function getAllTopics() //Gets all topics for a given subject name and writes them to a dropdown
		{
			var xhttp = new XMLHttpRequest();
			var name = document.getElementById("uniName").value;
			xhttp.open("GET", "api.php/getAllTopics/" + name , true);	
			xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			xhttp.send();
			xhttp.onreadystatechange = function() 
			{ 
				if (xhttp.readyState == 4 && xhttp.status == 200)
				{
					if(xhttp.responseText.length > 0 )
					{
						var j = JSON.parse(xhttp.responseText);
						var item = j["topics"];
						var topicData = document.getElementById("topicOptions");
						topicData.innerHTML = "";
						for(var i=0;i<item.length;i++){
							var opt=document.createElement("option");
							opt.text=item[i]["name"];
							opt.value=item[i]["name"];
							opt.setAttribute("onclick","getAllQuestions("+ item[i]["id"] + ")");
							topicData.appendChild(opt);
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

	</script>
	<br><br><br>
</body>
<footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top" id="footer">
	<script> var version = <?php echo "'".$_version."-".$_date."'"?>;</script>
	<script src="js/footer.js"></script>
</footer>

</html>