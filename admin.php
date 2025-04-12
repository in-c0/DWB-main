<?php

function hasRole($role, $id)
{
	global $perms;
	if(isset($perms[$id]))
	{
		return in_array($role,$perms[$id]["roles"]);
	}
	return false;
}

function inDomainAny($target)
{
	global $perms;
	global $groups;
	foreach ($perms as $key => $value)
	{
		//echo $key;
		foreach ($perms[$key]["domain"] as $domain)
		{
			if($domain["type"] == "group")
			{
				foreach ($groups[$key][0]["domain"] as $domain2)
				{
					if($domain2["target"] === $target || $domain2["target"] === "*")
					{
						return true;
						break;
					}
				}
			}
			else
			{
				if($domain["target"] === $target || $domain["target"] === "*")
				{
					return true;
					break;
				}
			}
		}
	}
	
	return false;
}

function inDomain($target, $id)
{
	global $perms;
	global $groups;
	foreach ($perms[$id]["domain"] as $domain)
	{
		if($domain["type"] == "group")
		{
			foreach ($groups[$id][0]["domain"] as $domain2)
			{
				if($domain2["target"] === $target || $domain2["target"] === "*")
				{
					return true;
					break;
				}
			}
		}
		else
		{
			if($domain["target"] === $target || $domain["target"] === "*")
			{
				return true;
				break;
			}
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
	

	//Login section load and check the validailty of the token if failed well force redirct the user to the login page
	if(isset($_COOKIE["authToken"]) && isset($_COOKIE["authEmail"]))
	{
		$authToken = $_COOKIE["authToken"];
		$authEmail = $_COOKIE["authEmail"];
		$userID = null;
		$logedIn = false; 
		$isAdmin = false;
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
				if($isAdmin == 1 || $isAdmin == "true" || $isAdmin)
				{
					$isAdmin = true;
				}
				header("USERID:" .$userID);
				break;
			}     
			if($userID == null)
			{              
				header("Location: login.php?r=question");
				setcookie("r","quesition");
				exit();
			}    
			$groupsToSearch = [];
			$firstOrg = null;
			$permsTemp = null;
			$permsTemp2 = json_decode("{}",true);
			$sql = "SELECT * FROM `perms` WHERE `userID` = '$userID'";
			$result = $conn->query($sql);
			$response["userID"] = $userID;
			while ($row = $result -> fetch_assoc())
			{				
				$permsTemp = json_decode($row["perm"],true);
			} 
			if($permsTemp != null)
			{
				$aaa = "";
				foreach($permsTemp as $key=>$value) 
				{
					$groups[$key] = [];
					if($firstOrg == null)
					{
						$firstOrg = $key;
					}
					$permsTemp2[$key] = [];
					$permsTemp2[$key]["roles"] = $permsTemp[$key]["roles"];
					$permsTemp2[$key]["domain"] = [];
					for ($d = 0; $d < sizeof($permsTemp[$key]["domain"]); $d++)
					{
						$data = $permsTemp[$key]["domain"][$d];
						if($data["type"] == "group")
						{						
							//echo $data["target"];
							$sql = "SELECT * FROM `groups` WHERE JSON_VALUE(perm,'$.name') = '" . $data["target"] . "' AND `orgID` = '" . $key . "'";
							//echo $sql . "<br>\n";
							$result = $conn->query($sql);
							while ($row = $result -> fetch_assoc())
							{		
								if(!in_array(json_decode($row["perm"],true),$groups[$key]))
								{
									$permsTemp[$key]["name"] =  $data["target"];
									array_push($groups[$key], json_decode($row["perm"],true));
								}			
								for($iii = 0; $iii < sizeof(json_decode($row["perm"],true)["domain"]); $iii++)
								{
									$aa = json_decode($row["perm"],true)["domain"][$iii];
									$aa["subType"] = "group::" . $data["target"];
									if($data["target"] == "*")
									{
										$permsTemp2[$key]["globalAccess"] = true;
									}							

									array_push($permsTemp2[$key],$aa);
								}
							}
						}
						else
						{
							array_push($permsTemp2[$key],$data);
						}
					}
				}
				header("groups: " . $aaa);
			}
			$perms = $permsTemp;
			header("perms: " . json_encode($perms));
			if(hasRole("admin","-1"))
			{
				$isAdmin = true;
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

	if(!$isAdmin)
	{
		exit();
	}
	
	

?>
<head>
	<title> Digital Workbook - ADMIN PAGE </title>
	<meta name="version" content="<?php echo $_version;?>">
	<meta name="buildDate" content="<?php echo $_date;?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0"> 
	
	
	<!--<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
-->
	
	<script src="js/nav.js"></script>	
	<script	src="js/Chart.js"></script> 
	<script	src="js/admin.js"></script> 
	<script src="js/utils.js"></script>
	<div id="freefloat" class="freefloat" style='position: fixed;top: 50%'></div>
	<script>
		
		var xhttp = new XMLHttpRequest();
		var uniNames = {};

		function getOrgInfo()
		{
			var id = allOrgs[document.getElementById("uniName").value];
			document.location.href = "?org="+ id;
			//loadUserDataID(id);
		}

		function checkUni()
		{
			var x = document.getElementById("uniName").value;
			if(x.length > 2)
			{			
				xhttp.open("GET", "api.php/findOrg/" + x , true);	
				xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				xhttp.send();
				xhttp.onreadystatechange = function() 
				{ 
					if (xhttp.readyState == 4 && xhttp.status == 200)
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
								opt.setAttribute("onclick","getAllSubjects("+ item[i]["id"] + ")");
								uniData.appendChild(opt);
								uniNames[item[i]["name"]] = item[i]["id"];
							}
						}
					}
				}
			}
		}

		function getUserInfo()
		{

		}
		<?php
		if(isset($_GET["org"]) && is_numeric($_GET["org"]))
		{
		?>
			loadUserDataID(<?php echo $_GET["org"]?>);
		<?php
		}
		else
		{
		?>
			loadUserData();
		<?php
		}
		?>
		getAllOrgs();

		

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
					<button type="button" class="btn btn-light" onclick="goSettings()">SETTINGS</button>
				</li>
				<li class="nav-item">
					<button type="button" class="btn btn-light" onclick="openStats()">STATS</button>
				</li>
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
	<span>THIS IS THE ADMIN PAGE ACTIONS HERE CAN EFFECT ALL USERS </span><hr>

	<?php if(isset($_GET["user"]) && is_numeric($_GET["user"]))
	{
	?>
	<div id="user">
		<div class="container">
			<script> 
				var userID = <?php echo $_GET["user"] ?>;
				xhttp.open("GET", "api.php/getUserInfoID/" + userID , true);	
				xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
				xhttp.send();
				xhttp.onreadystatechange = function() 
				{ 
					if (xhttp.readyState == 4 && xhttp.status == 200)
					{
						if(xhttp.responseText.length > 0 )
						{
							var jsonData = JSON.parse(xhttp.responseText);
							document.getElementById("userStats").innerHTML = "Basic User Information<br>Name: " + jsonData["user"]["firstName"] + " " + jsonData["user"]["lastName"] + "<br>isStaff: " + jsonData["user"]["isStaff"] + "<br>isAdmin: " + jsonData["user"]["isAdmin"] + "<br>" ;
							console.log(jsonData["user"]);
							document.getElementById("userStats").innerHTML += "<br>Communications: <br>";
							for (var i = 0; i < jsonData["user"]["communications"].length; i++)
							{
								document.getElementById("userStats").innerHTML += "ID: " + jsonData["user"]["communications"][i]["id"] + "<br>Type: " + jsonData["user"]["communications"][i]["type"] + "<br>Content: " + jsonData["user"]["communications"][i]["content"] + "<br><br>";
								console.log(jsonData["user"]["communications"][i]["content"]);
							}
						}
					}
				}		
			</script>
			<span id="userStats"></span>
		</div>
	</div><hr>

	<?php
	}
	else
	{
	?>
	<div id="stats">
		<div class="container">
			<p> Org search section</p>
			<div class="row">
				<div class="col">
					<div id="userAccountsHolder" class="chart-container" style="position: relative; height:40vh; width:40vw">
						<canvas id="userAccounts"></canvas> 
					</div><hr>

					<div class="chart-container" style="position: relative; height:40vh; width:40vw">
						<canvas id="questions" ></canvas> 
					</div>
				</div>
				<div class="col">
					<ul class="list-group" id="allOrgs"> </ul>
					<input class="item" id="uniName"list="datalistOptions" placeholder="Uni name" onchange="" onkeypress = "this.onchange();" onpaste="this.onchange();" oninput="this.onchange();"class="form-control">
					<datalist id="datalistOptions"></datalist>
					<button onclick="getOrgInfo()" class="btn btn-outline-success" > Search </button>
				</div>
			</div><br>
		
	</div>
	<hr>

	<div class="container">
		<p> User search section </p>
		<input placeholder="Search Email" id="searchEmail"class="form-control"><button onclick="search()" class="btn btn-outline-success" > Search </button></input> <br>
		<span id="userStats"> </span>
	</div>
	<hr>

	<?php } ?>
	
	<div class="container">
		<p> Add new org </p>
		<input class="form-control" placeholder="New Org Name" id="newUniName"> </input> 
		<button onclick="createNewUni()"> Create </button>
	</div>

	<div class="container">
		<p> Reset password </p>
		<input class="form-control" placeholder="User email" id="userEmailForPassword"> </input> 
		<button onclick="resetPassword()"> Reset </button>
	</div>

</body>
<footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top" id="footer">
	<script> var version = <?php echo "'".$_version."-".$_date."'"?>;</script>
	<script src="js/footer.js"></script>
	<script> var XQ51dka = <?php echo "'".$checkerItem."'" ?>; </script>
</footer>