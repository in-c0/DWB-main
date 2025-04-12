<?php
try{
	require("conn.php");
	require("version.php");
	require("csp.php");
	global $_version;
	global $_date;
	global $conn;
	$isOwner = false;
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
			header("SQL:" .$checkIfTokenIsReal);
			$result = $conn->query($checkIfTokenIsReal);
			while ($row = $result -> fetch_assoc())
			{
				$userID = $row["id"];
				header("USERID:" .$userID);
				if($row["isAdmin"] == 1)
				{
					$isAdmin = true;
				}
				break;
			} 
			
			
			if($userID == null)
			{              
				header("Location: login.php?r=bug");
				setcookie("r","bug");
				exit();
			}   
			
		}
		else
		{
			header("Location: login.php?r=bug");
			setcookie("r","bug");
			exit();
		}
	}
	else
	{
		header("Location: login.php?r=bug");
		setcookie("r","bug");
		exit();
	}
}
catch (\Throwable $e)
{
	echo $e;
}
?>
<head>
	<meta name="version" content="<?php echo $_version;?>">
	<meta name="buildDate" content="<?php echo $_date;?>">
	<!--FACEBOOK-->
	<meta property="og:title" content="Digital Work Book" />
	<meta property="og:description" content="Report a bug" />

	<script src="js/nav.js"></script>
	<script src="js/admin.js"></script>
	<script src="js/utils.js"></script>
	<div id="freefloat" class="freefloat" style='position: fixed;top: 60%'></div>
	<title>Digital Workbook - Bug </title>
</head>

<?php
if(isset($_GET["id"]) && is_numeric($_GET["id"]))
{
	?>
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
		<div id="bugs"></div>
		<br>
		<br>
		<br>
		<br>
		
		<script> 
			var isAdmin = <?php if($isAdmin) { echo "true";} else {echo "false";} ?>;
			var isOwner = false;
			var bugId = <?php echo $_GET["id"] ?>;
			var isResolved = false;
			function openBug(id)
			{
				window.location.href = "?id=" + id;
			}

			function back(id)
			{
				window.location.href = "?";
			}
			
			async function sendMessage(params) {
				await sendMessageA();
				window.location.reload(true);
			}

			async function sendMessageA()
			{
				var msg = document.getElementById("message").value;
				if(msg != null)
				{
					var data = {};
					data["data"] = {};
					data["data"]["msg"] = msg;
					data["data"]["refrenceID"] = bugId;
					data["data"]["type"] = "bug";
					return fetch("api.php/sendMessage/?data=" + JSON.stringify(data), {
						method: "GET",
						headers: {
							"Content-type": "application/json; charset=UTF-8"
						}
					});
				}
			}

			async function resolveBug() {
				await resolveBugA();
				window.location.reload(true);
			}

			async function resolveBugA()
			{
				return fetch("api.php/resolveBug/?bugID=" + bugId, {
					method: "GET",
					headers: {
						"Content-type": "application/json; charset=UTF-8"
					}
				});
			}

			async function reopenBug() {
				await reopenBugA();
				window.location.reload(true);
			}

			async function reopenBugA()
			{
				return fetch("api.php/reopenBug/?bugID=" + bugId, {
					method: "GET",
					headers: {
						"Content-type": "application/json; charset=UTF-8"
					}
				});
			}

			function openUserInfo(id)
			{
				window.open("admin.php?user=" + id);
			}

			function getBug(id)
			{
				var xhttp = new XMLHttpRequest();
				xhttp.open("POST", "api.php/getBug/" + id , true);	
				xhttp.setRequestHeader('Content-type', 'text/json;charset=UTF-8');
				xhttp.send();
				document.getElementById("bugs").innerHTML = "";
				xhttp.onreadystatechange = function() 
				{ 
					if (xhttp.readyState == 4 && xhttp.status == 200)
					{
						if(xhttp.responseText.length > 0 )
						{	
							var jsonResponse = JSON.parse(xhttp.responseText);
							console.log(jsonResponse);
							for (var i =0; i < jsonResponse["data"].length; i++)
							{
								console.log(jsonResponse);
								if(isAdmin)
								{
									document.getElementById("bugs").innerHTML += `<button onclick=openUserInfo(`+jsonResponse["data"][i]["status"]["owner"]+`) class='btn btn-outline-primary my-2 my-sm-0'> Open User Info (Admin) </button><br>`;
								}
								if("resolved" in jsonResponse["data"][i]["status"] && jsonResponse["data"][i]["status"]["resolved"] == true)
								{
									isResolved = true;
									document.getElementById("bugs").innerHTML += "Resolved: True<br>"; 
								}
								document.getElementById("bugs").innerHTML += "BugID: " + jsonResponse["data"][i]['id'] + "<br>Title: " + jsonResponse["data"][i]["title"] + "<br>Body: " + jsonResponse["data"][i]["body"] + "<br>Severity: " + jsonResponse["data"][i]["severity"] + "<br>Created At: " + jsonResponse["data"][i]["createdAt"];					
								if(jsonResponse["data"][i]["owner"] == true)
								{
									isOwner = true;
								}
								
								document.getElementById("bugs").innerHTML += "<br><br><br>";
							}

							for (var i =0; i < jsonResponse["msg"].length; i++)
							{
								document.getElementById("bugs").innerHTML += "OWNER: " + jsonResponse["msg"][i]["owner"] + "<br>MSG: " + jsonResponse["msg"][i]["content"] + "<br>Created At: " + jsonResponse["msg"][i]["createdAt"] + "<br><br>";					
							}

							if((isOwner || isAdmin) && !isResolved)
							{
								document.getElementById("bugs").innerHTML += `
								<br><input id="message" placeholder = "Message"></input><button onclick=sendMessage() class='btn btn-outline-success my-2 my-sm-0'> Send Message </button><br><br>
								<button onclick=resolveBug() class='btn btn-outline-info my-2 my-sm-0'> Resolve </button>`;
							}
							else if((isOwner || isAdmin) && isResolved){
								document.getElementById("bugs").innerHTML += `<button onclick=reopenBug() class='btn btn-outline-info my-2 my-sm-0'> Reopen </button><br>`;
							}

							

							document.getElementById("bugs").innerHTML += "<button onclick=back() class='btn btn-outline-info my-2 my-sm-0'> Back </button>";

						}
					}
				}	
			}
			/*
			CREATE TABLE `datatrain`.`communication` (`id` INT NOT NULL AUTO_INCREMENT , `type` INT(100) NOT NULL , `linkedID` INT NOT NULL , `content` VARCHAR(500) NOT NULL , `owner` INT NOT NULL , `createdAt` VARCHAR(100) NOT NULL DEFAULT CURRENT_TIMESTAMP , PRIMARY KEY (`id`)) ENGINE = InnoDB; */
			getBug(bugId);
		</script>
	</body>

	<?php
}
else
{
	?>
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
		<div id="bugs"></div>
		<button onclick=getBugs() class='btn btn-outline-success my-2 my-sm-0'> Reload Bugs </button>
		<br>
		<span> Report a bug file this in </span><br>
		<span> <input class="form-control" id="title" placeholder="Title" style="width: 20%"> </input> </span>
		<span> Please check to see if there are no bug dup </span><br>
		<textarea class="form-control" aria-label="With textarea" id="body">Write body here</textarea><br>
		<span> Write the severity <br>
		1 Good to know <br>
		2 No very important <br>
		3 Important <br>
		4 Critical <br>
		</span><br>
		<input id="severity" type="number" max="4" min="1" value = "1" step="1"/>
		<button onclick=reportBugs() class="btn btn-outline-primary my-2 my-sm-0"> Submit Bug </button>
		<br>
		<br>
		<br>
		
		<script> 
			var isAdmin = <?php if($isAdmin) { echo "true";} else {echo "false";} ?>;
			function reportBugs()
			{
				if(document.getElementById("title").value != "" && document.getElementById("body").value != "Write the problem here")
				{
					var xhttp = new XMLHttpRequest();
					xhttp.open("POST", "api.php/reportBug/?title=" + document.getElementById("title").value + "&body=" + document.getElementById("body").value + "&severity=" + document.getElementById("severity").value , true);	
					xhttp.setRequestHeader('Content-type', 'text/json;charset=UTF-8');
					xhttp.send();
				}
				else
				{
					alert("Please fill out all options");
				}
				getBugs();
			}

			function openBug(id)
			{
				window.location.href = "?id=" + id;
			}

			function getBugs()
			{
				var xhttp = new XMLHttpRequest();
				xhttp.open("POST", "api.php/getAllBugs" , true);	
				xhttp.setRequestHeader('Content-type', 'text/json;charset=UTF-8');
				xhttp.send();
				document.getElementById("bugs").innerHTML = "";
				xhttp.onreadystatechange = function() 
				{ 
					if (xhttp.readyState == 4 && xhttp.status == 200)
					{
						if(xhttp.responseText.length > 0 )
						{	
							var jsonResponse = JSON.parse(xhttp.responseText);
							for (var i =0; i < jsonResponse["data"].length; i++)
							{
								if(jsonResponse["data"][i]["status"]["resolved"] == true)
								{
									document.getElementById("bugs").innerHTML += "Bug has been resolved<br>";
								}
								if(isAdmin)
								{
									document.getElementById("bugs").innerHTML += "Owner: " + jsonResponse["data"][i]['owner'] + "<br>";
								}
								document.getElementById("bugs").innerHTML += "BugID: " + jsonResponse["data"][i]['id'] + "<br>Title: " + jsonResponse["data"][i]["title"] + "<br>Body: " + jsonResponse["data"][i]["body"] + "<br>Severity: " + jsonResponse["data"][i]["severity"] + "<br>Created At: " + jsonResponse["data"][i]["createdAt"];
								
								document.getElementById("bugs").innerHTML += "<br><button onclick=openBug('" + jsonResponse["data"][i]['id'] + "'); class='btn btn-outline-success my-2 my-sm-0'> Open </button>";
								
								
								
								document.getElementById("bugs").innerHTML += "<br><br><br>";
							}
						}
					}
				}	
			}
			getBugs();
		</script>
	</body>
	<?php 
}
?>
<div class="container2">
	<footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top" id="footer">
		<script> var version = <?php echo "'".$_version."-".$_date."'"?>;</script>
		<script src="js/footer.js"></script>
	</footer>
</div>
</html>