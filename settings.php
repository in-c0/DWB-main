<?php
//Added file 22/09/2024
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
				$isAdmin = $row["isAdmin"];
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
}
catch (\Throwable $e)
{
	echo $e;
}

?>

<head>
<!--22/09/2024 Changed it to bootstrap !-->
	<meta name="version" content="<?php echo $_version;?>">
	
		<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
		
		<script src="js/nav.js"></script>
		<script src="js/utils.js"></script>
		<div id="freefloat" class="freefloat" style='position: fixed;top: 60%'></div>
		<?php 
		if($isAdmin)
		{
			?> 
			<script src="js/admin.js"></script>
			<?php
		}
	?>
	<title>Digital Workbook - Settings </title>
	<script>
		function openProfile()
		{
			location.href = "profile.php"; 
		}
	</script>
</head>
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


	<div class = "container">
		<label class="form-label"> User Info </label>
		<div id="userInfo"> </div>
	</div>

	<div class = "container">
		<label class="form-label">Color picker</label>
		<input type="color" class="form-control form-control-color" id="firstColor" value="#FFFF00" title="The first one of a variable" onchange='setColorStorage()' style="width:20%">
		<input type="color" class="form-control form-control-color" id="multiColor" value="#008000" title="Multiple of the same variable color" onchange='setColorStorageMulti()' style="width:20%">
	</div>
	<div class ="container">
		<h3> User options </h3>
		<div class="input-group mb-3">
			<div class="input-group-prepend">
				<div class="input-group-text">
				<input type="checkbox" id="showFileStructure" onclick=toggleShowLoc()>
				</div>
			</div>
			<span class="input-group-text"> Show file location in questions </span>

			<div class="input-group mb-3">
			<div class="input-group-prepend">
				<div class="input-group-text">
				<input type="checkbox" id="showFileStructureProfile" onclick=toggleShowLocProfile()>
				</div>
			</div>
			<span class="input-group-text"> Show file location in profile </span>
		</div>

	<script>

		readColorSettings();
		function readColorSettings()
		{
			firstColor = localStorage.getItem("firstColor");
			multiColor = localStorage.getItem("multiColor");

			if(firstColor != null)
			{
				document.getElementById("firstColor").value = firstColor;
			}

			if(multiColor != null)
			{
				document.getElementById("firstColor").value = multiColor;
			}

		}

		function getUserInfo()
		{
			var xhttp = new XMLHttpRequest();
			xhttp.open("GET", "api.php/me", true);				
			xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			xhttp.send("");
			xhttp.onreadystatechange = function() 
			{ 
				if (xhttp.readyState == 4 && xhttp.status == 200)
				{
					if(xhttp.responseText.length > 0 )
					{	
						var jsonResponse = JSON.parse(xhttp.responseText);
						document.getElementById("userInfo").innerHTML = "First Name: " + jsonResponse["firstName"] + "<br>Last Name: " + jsonResponse["lastName"] + "<br>Email: " + jsonResponse["email"] + "<br>Part of these organisations:<br>";
						for(var i =0; i < jsonResponse["org"].length; i++)
						{
							document.getElementById("userInfo").innerHTML += jsonResponse["org"][i]["name"] + "<br>";
						}
						document.getElementById("userInfo").innerHTML += "<br>";
					}
				}
			}
		}

		function setColorStorage()
		{
			localStorage.setItem("firstColor", document.getElementById("firstColor").value);
		}

		function setColorStorageMulti()
		{
			localStorage.setItem("multiColor", document.getElementById("multiColor").value);
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


		getUserInfo();
		if(getCookie("showOrgFileStructure") == 1)
		{
			document.getElementById("showFileStructure").checked = true;
		}
		if(getCookie("showFileStructureProfile") == 1)
		{
			document.getElementById("showFileStructureProfile").checked = true;
		}
		
		function toggleShowLoc()
		{
			var y = document.getElementById("showFileStructure").checked;
			console.log(y);
			if(y)
			{
				var now = new Date();
				var time = now.getTime();
				var expireTime = time + 1000*36000;
				now.setTime(expireTime);
				document.cookie = 'showOrgFileStructure=1;expires='+now.toUTCString()+';path=/';
			}
			else
			{
				document.cookie = 'showOrgFileStructure=0;expires='+time+';path=/';
			}
		}

		function toggleShowLocProfile()
		{
			var y = document.getElementById("showFileStructureProfile").checked;
			console.log(y);
			if(y)
			{
				var now = new Date();
				var time = now.getTime();
				var expireTime = time + 1000*36000;
				now.setTime(expireTime);
				document.cookie = 'showFileStructureProfile=1;expires='+now.toUTCString()+';path=/';
			}
			else
			{
				document.cookie = 'showFileStructureProfile=0;expires='+time+';path=/';
			}
		}

	</script>
	<div class ="container"><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br></div>
</body>
<footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top" id="footer">
	<script> var version = <?php echo "'".$_version."-".$_date."'"?>;</script>
	<script> var XQ51dka = <?php echo "'".$checkerItem."'" ?>; </script>
	<script src="js/footer.js"></script>
</footer>
</html>