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
	
	try{
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
				
			}
		}
		else
		{
			
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
		if(hasRoleAnyDomain('educator') || hasRoleAnyDomain('admin') || hasRoleAnyDomain('orgAdmin'))
		{
		}
		else
		{
			header("location: ../");
			exit();
		}
	?><meta name="version" content="<?php echo $_version;?>">
	<meta name="buildDate" content="<?php echo $_date;?>">

	<!--FACEBOOK-->
	<meta property="og:title" content="Digital Work Book" />
	<meta property="og:description" content="The one workbook" />

	<script src="js/nav.js"></script>
	<script src="js/utils.js"></script>
	<script src="js/question.js"></script>
	<?php 
		if($isAdmin)
		{
			?> 
			<script src="js/admin.js"></script>
			<?php
		}
	?>
	<script>
		var items = [];
		var orgs = {}

		var currentSelectedVarID = null;
		var currentSelectedQuestionID = null;
		function getOrgName(id)
		{
			console.log("SEARCH FOR", id);
			console.log(orgs);
			for(var i =0 ; i < orgs.length; i++)
			{
				if(orgs[i]['id'] == id)
				{
					console.log(orgs[i]['id'],id, orgs[i]['name']);
					return orgs[i]['name'];
				}	
			}
			return "";
		}

		function getOrgNames()
		{
			console.log("getORGS");
			var getORGS = new XMLHttpRequest(); 
			var url = "api.php/getAllOrgs/";
			getORGS.open("GET", url, true); 
			getORGS.onreadystatechange = function() {
				if(getORGS.readyState == 4 && getORGS.status == 200) {
					orgs = JSON.parse(getORGS.responseText)['orgs'];
					getAllChats();
				}
			};
			getORGS.send();
		}

		function toggleVis()
		{			
			document.getElementById("subMessageHolder").setAttribute("hidden","");
			document.getElementById("allMessageUsers").setAttribute("hidden","");
			document.getElementById("chatMenu").setAttribute("hidden","");
			document.getElementById("responseBar").setAttribute("hidden","");
			
			var m = document.getElementById("location");
			m.innerHTML = '';

			
			var m = document.getElementById("allMessageUsers");
			m.innerHTML = '';

			
			var m = document.getElementById("subMessageHolder");
			m.innerHTML = '';
		}

		function getAllChats()
		{
			var chatXML = new XMLHttpRequest(); 
			var url = "api.php/getChatAll/";
			document.getElementById("location").childNodes = new Array();
			chatXML.open("GET", url, true); 
			chatXML.onreadystatechange = function() {
				if(chatXML.readyState == 4 && chatXML.status == 200) {
					document.getElementById("messageHolder").innerHTML = "";
					var chats = JSON.parse(chatXML.responseText)["chats"];
					for (const [key, value] of Object.entries(chats)) {
						chats[key].forEach(message => {
							var divTextHolder = document.createElement('div');
							divTextHolder.setAttribute("class","p-3 ms-3");
							if(!items.includes(message['questionID']) && message['org'] !== undefined)
							{
								items.push(message['questionID']);
								var divHolder = document.createElement('div');
								divHolder.setAttribute("class","d-flex flex-row justify-content-start mb-4");
								divHolder.setAttribute("onclick","openChatSection('" + message['questionID'] +"')");
								divHolder.setAttribute("href","javascript:openChatSection(" + message['questionID'] +")");
								divHolder.setAttribute("style","cursor:pointer");
								divTextHolder.setAttribute("style","border-radius: 15px; background-color: rgba(181, 227, 243, 0.2);");
							
								var header = document.createElement('span');
								header.innerHTML = "Question://" + getOrgName(message['org']) + "/" + message['questionID'];
								

								divTextHolder.appendChild(header);
								divHolder.appendChild(divTextHolder);
								lastItem = divHolder;
								document.getElementById("messageHolder").appendChild(divHolder);
							}
						});
						
					}
				}
			};
			chatXML.send();
		}

		function openChatSection(questionID)
		{
			currentSelectedQuestionID = questionID;
			var itemsVarID = [];
			toggleVis();
			
			var q = document.createElement('span');			
			q.setAttribute("onclick","openChatSection('" + questionID + "')");
			q.setAttribute("style","cursor:pointer");
			q.innerHTML = questionID;
			document.getElementById("location").append(q);
			
			document.getElementById("subMessageHolder").removeAttribute("hidden");
			
			document.getElementById("allMessageUsers").innerHTML = "";
			document.getElementById("subMessageHolder").innerHTML = "";
			var chatXML = new XMLHttpRequest(); 
			var url = "api.php/getChatVars/?questionID=" + questionID;
			chatXML.open("GET", url, true); 

			chatXML.onreadystatechange = function() {
				if(chatXML.readyState == 4 && chatXML.status == 200) {
					var chats = JSON.parse(chatXML.responseText)["variableIDs"];
					chats.forEach(message => {		
										
						if(!itemsVarID.includes(message))
						{					
							var divTextHolder = document.createElement('div');
							divTextHolder.setAttribute("class","p-3 ms-3");
							var divHolder = document.createElement('div');
							divHolder.setAttribute("class","d-flex flex-row justify-content-start mb-4");
							divHolder.setAttribute("onclick","getChatLog('" + questionID +"','"+message+"')");
							divHolder.setAttribute("style","cursor:pointer");
							divTextHolder.setAttribute("style","border-radius: 15px; background-color: rgba(181, 227, 243, 0.2);");
						
							var header = document.createElement('span');
							header.innerHTML = "Variable ID://"  + message;
							

							divTextHolder.appendChild(header);
							divHolder.appendChild(divTextHolder);
							lastItem = divHolder;
							document.getElementById("subMessageHolder").appendChild(divHolder);		
							itemsVarID.push(message['variableID']);
						}
					});
				}
			};
			chatXML.send();

		}

		function getChatLog(questionID, varID)
		{
			toggleVis();
			var slash = document.createElement('span');
			slash.innerHTML = "/";

			var q = document.createElement('span');
			
			q.setAttribute("onclick","openChatSection('" + questionID + "')");
			q.setAttribute("style","cursor:pointer");
			q.innerHTML = questionID;
			document.getElementById("location").append(q);
			document.getElementById("location").append(slash);
			
			var v = document.createElement('span');
			v.setAttribute("onclick","getChatLog('" + questionID + "','"+varID+"')");
			v.setAttribute("style","cursor:pointer");
			v.innerHTML = varID;
			document.getElementById("location").append(v);

			

			document.getElementById("allMessageUsers").removeAttribute("hidden");
			var chatXML = new XMLHttpRequest();
			var users = []; 
			var url = "api.php/getChatUser/?questionID=" + questionID + "&variableID=" + varID;
			chatXML.open("GET", url, true); 
			document.getElementById("chatMenu").innerHTML = "";

			chatXML.onreadystatechange = function() {
				if(chatXML.readyState == 4 && chatXML.status == 200) {
					var chats = JSON.parse(chatXML.responseText)["chats"];
					chats.forEach(message => {						
						if(!users.includes(message['messageCreator']))
						{					
							var divTextHolder = document.createElement('div');
							divTextHolder.setAttribute("class","p-3 ms-3");
							var divHolder = document.createElement('div');
							divHolder.setAttribute("class","d-flex flex-row justify-content-start mb-4");
							divHolder.setAttribute("onclick","getChatLogFromUser('" + message['questionID'] +"','"+message['variableID']+"','" + message['messageCreator'] +"')");
							divHolder.setAttribute("style","cursor:pointer");
							divTextHolder.setAttribute("style","border-radius: 15px; background-color: rgba(181, 227, 243, 0.2);");
						
							var header = document.createElement('span');
							header.innerHTML = "User://" + message['messageCreator'];
							
							
							divTextHolder.appendChild(header);
							divHolder.appendChild(divTextHolder);
							lastItem = divHolder;
							document.getElementById("allMessageUsers").appendChild(divHolder);		
							users.push(message['messageCreator']);
						}
					});
				}
			};
			chatXML.send();

		}

		function getChatLogFromUser(questionID, varID, user)
		{
			toggleVis();
			questionID = questionID;
			varID = varID;
			currentSelectedVarID = varID;
			var slash = document.createElement('span');
			slash.innerHTML = "/";

			var q = document.createElement('span');
			q.setAttribute("onclick","openChatSection('" + questionID + "')");
			q.setAttribute("style","cursor:pointer");
			
			q.setAttribute("onclick","openChatSection('" + questionID + "')");
			q.setAttribute("style","cursor:pointer");
			q.innerHTML = questionID;
			document.getElementById("location").append(q);
			document.getElementById("location").append(slash);

			var slash = document.createElement('span');
			slash.innerHTML = "/";

			var v = document.createElement('span');
			v.setAttribute("onclick","getChatLog('" + questionID + "','"+varID+"')");
			v.setAttribute("style","cursor:pointer");
			v.innerHTML = varID;
			document.getElementById("location").append(v);
			document.getElementById("location").append(slash);
			
			var u = document.createElement('span');
			u.innerHTML = user;
			document.getElementById("location").append(u);

			document.getElementById("chatMenu").removeAttribute("hidden");
			document.getElementById("responseBar").removeAttribute("hidden");
			var chatXML = new XMLHttpRequest();
			var users = []; 
			chatSearchUser = user;
			var url = "api.php/getChat/?questionID=" + questionID + "&variableID=" + varID + "&searchUser=" + user;
			chatXML.open("GET", url, true); 
			//document.getElementById("chatMenu").innerHTML = "";
			chatXML.onreadystatechange = function() {
				if(chatXML.readyState == 4 && chatXML.status == 200) {
					var chats = JSON.parse(chatXML.responseText)["chats"];
					/*
						info texts



					*/
					var divHolder = document.createElement('div');
					var divTextHolder = document.createElement('div');
					divTextHolder.setAttribute("class","p-3 ms-3");
					divHolder.setAttribute("class","d-flex flex-row justify-content-start mb-4");
					divTextHolder.setAttribute("style","border-radius: 15px; background-color: rgba(181, 227, 243, 0.2);");
					var header = document.createElement('span');
					header.innerHTML = 'SYSTEM';

					var text = document.createElement('p');
					text.innerHTML = 'This student needs help with this question';

					divTextHolder.appendChild(header);
					divTextHolder.appendChild(text);
					divHolder.appendChild(divTextHolder);
					document.getElementById("chatTranscript").appendChild(divHolder);


					chats.forEach(message => {		
						var divTextHolder = document.createElement('div');
						divTextHolder.setAttribute("class","p-3 ms-3");
						if(JSON.parse(chatXML.responseText)['userID'] != message['messageCreator'])
						{					
							var divHolder = document.createElement('div');
							divHolder.setAttribute("class","d-flex flex-row justify-content-start mb-4");
							divTextHolder.setAttribute("style","border-radius: 15px; background-color: rgba(181, 227, 243, 0.2);");
						}
						else
						{
							var divHolder = document.createElement('div');
							divTextHolder.setAttribute("style","border-radius: 15px; background-color: rgba(57, 237, 57, 0.2);");
							divHolder.setAttribute("class","d-flex flex-row justify-content-end mb-4");
						}

						var header = document.createElement('span');
						header.innerHTML = message['messageCreator'];

						var text = document.createElement('p');
						text.innerHTML = message['content'];

						divTextHolder.appendChild(header);
						divTextHolder.appendChild(text);
						divHolder.appendChild(divTextHolder);
						lastItem = divHolder;
						document.getElementById("chatTranscript").appendChild(divHolder);
								
					});
					if(lastItem != null)
					{
						lastItem.scrollIntoView();
					}
					chatQuestionID = questionID;
					chatvarID = varID;
					document.getElementById("questionFrame").src = "question.php?questionID=" + questionID;
					document.getElementById("chatLogH").setAttribute("hidden","")
					document.getElementById('send').setAttribute("onclick","send('msg','" + questionID + "','" + varID + "','" + user+ "')");
					document.getElementById("chatLog").removeAttribute("hidden");
					chatMenuOpened = true;
					loadNewQuestion(questionID,-1);
					document.getElementById('chatTitle').innerHTML = questionID + "/" + varID;
					document.getElementById(varID).scrollIntoView();
				}
			};
			chatXML.send();

		}
		getOrgNames();

		var textBackgroundColor = "yellow";
		var textBackgroundColorMulti = "green";
		readColorSettings();
		function readColorSettings()
		{
			firstColor = localStorage.getItem("firstColor");
			multiColor = localStorage.getItem("multiColor");

			if(firstColor != null)
			{
				textBackgroundColor = firstColor;
			}

			if(multiColor != null)
			{
				textBackgroundColorMulti = multiColor;
			}

		}

	</script>
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
				<span id="questionName" hidden> </span>
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
	<style>
	
	.screen {
		display: flex;
		height: 80vh;
	}

	.chatMenuOption {
		width: 20%;
		overflow-x: hidden;
	}

	#chatLog {
		width: 20%;
		background-color: rgba(255, 255, 255, 0.9);
	}

	.right {
		width: 50%;
		overflow-x: hidden;
	}

	.left {
		width: 50%;
		overflow-x: hidden;
	}

	.question {
		width:80%;
		overflow-x: hidden;	
	}
			
	</style>
	<script src="js/staff.js"></script>
	<script src="js/chat.js"></script>
	<div class="screen">			
		<div class="chatMenuOption">
			<div id="messageHolder"> </div>
		</div>
		<div class="chatLog" id="chatLogH"> 
			<div id="location"> </div>
			<div id="subMessageHolder"> </div>
			<div id="allMessageUsers" hidden> </div>
			<div id="chatMenu" style="overflow-y: scroll; height:85%;" hidden> </div>
			<div id="responseBar" hidden style="height:10%;" >
				<input id="chatMessage" placeholder="Message" class="border-radius: 15px; form-control" >
				<button class="btn btn-outline-primary my-2 my-sm-0"> Send </button>
			</div>
		</div>
			<div id="questionFrame" class="question">
				<div class="screen">			
					<div class="left">				
						<div id="main" class="col text-wrap" >
							
						</div>
					</div>
					<div class="right">				
						<div class="col text-wrap" id="answer">
						</div>
					</div>
				</div>
			</div>
	</div>		

<div id="chatLog" class="window" style="width:20%; height:60%; left:40%; top: 5%;" hidden>
	<div class="window-top">
		<span id="chatTitle"> Title </span>
	</div>
	<div class="window-content" id="chatTranscript" style="overflow-y: scroll; ">
	</div>
	<div class="input-group mb-3">
		<input id="msg" class="form-control" type="text" placeholder="..."/>
		<button id="send"> Send </button>
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

<script>

types = {};
function makeDraggable (element) {
    // Make an element draggable (or if it has a .window-top class, drag based on the .window-top element)
    let currentPosX = 0, currentPosY = 0, previousPosX = 0, previousPosY = 0;

		// If there is a window-top classed element, attach to that element instead of full window
    if (element.querySelector('.window-top')) {
        // If present, the window-top element is where you move the parent element from
        element.querySelector('.window-top').onmousedown = dragMouseDown;
    } 
    else {
        // Otherwise, move the element itself
        element.onmousedown = dragMouseDown;
    }

    function dragMouseDown (e) {
        // Prevent any default action on this element (you can remove if you need this element to perform its default action)
        e.preventDefault();
        // Get the mouse cursor position and set the initial previous positions to begin
        previousPosX = e.clientX;
        previousPosY = e.clientY;
        // When the mouse is let go, call the closing event
        document.onmouseup = closeDragElement;
        // call a function whenever the cursor moves
        document.onmousemove = elementDrag;
    }

    function elementDrag (e) {
        // Prevent any default action on this element (you can remove if you need this element to perform its default action)
        e.preventDefault();
        // Calculate the new cursor position by using the previous x and y positions of the mouse
        currentPosX = previousPosX - e.clientX;
        currentPosY = previousPosY - e.clientY;
        // Replace the previous positions with the new x and y positions of the mouse
        previousPosX = e.clientX;
        previousPosY = e.clientY;
        // Set the element's new position
        element.style.top = (element.offsetTop - currentPosY) + 'px';
        element.style.left = (element.offsetLeft - currentPosX) + 'px';
    }

    function closeDragElement () {
        // Stop moving when mouse button is released and release events
        document.onmouseup = null;
        document.onmousemove = null;
    }
}
makeDraggable(document.querySelector('#chatLog'));


//Close the window on click of a red button
document.addEventListener('click', e => {
	if (e.target.closest('.round.red')) {
		e.target.closest('.window').remove();
	}
});
</script>


</body>
<footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top" id="footer">
	<script> var version = <?php echo "'".$_version."-".$_date."'"?>;</script>
	<script src="js/footer.js"></script>
	<script> var XQ51dka = <?php echo "'".$checkerItem."'" ?>; </script>
</footer>
</html>