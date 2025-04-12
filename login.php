<?php
	try {
		require("conn.php");
		require("version.php");
		require("csp.php");
		global $_version;
		global $conn;
		global $isDev;
		global $isLocalTest;

		$urlAdder = "";
		if(isset($_COOKIE["r"])) //Redirct mananger
		{
			$cookie = $_COOKIE["r"];
			if($cookie == "question" && isset($_COOKIE['question']))
			{
				$urlAdder = "question.php?questionID=" . $_COOKIE['question'];
			}
			else if($cookie == "index")
			{
				$urlAdder = "";
			}
			else if($cookie == "profile")
			{
				$urlAdder = "profile.php";
			}
			else if($cookie == "course")
			{
				$urlAdder = "course.php";
			}
			else if($cookie == "bug")
			{
				$urlAdder = "bug.php";
			}
			header("urlAdder: " .$urlAdder);
		}

		if(isset($_GET["logout"]))
		{		
			if(isset($_COOKIE["authToken"]) && isset($_COOKIE["authEmail"]))
			{
				$logedIn = false;
				$checkIfTokenIsReal = $conn->prepare("SELECT * FROM `users` WHERE `authToken` = ? AND `email` = ?");
				$checkIfTokenIsReal->bind_param("ss", $_COOKIE["authToken"], $_COOKIE["authEmail"]);
				$checkIfTokenIsReal->execute();
				$result = $checkIfTokenIsReal->get_result(); 
				if($result->num_rows == 1) {   
					while ($row = $result->fetch_assoc()) {
						$data = json_decode("{}",true);
						$data["email"] = $row["email"];
						$data["userID"] = $row["id"];
						$data["ip"] = $_SERVER['REMOTE_ADDR'];
						$data["message"] = "Logout success";
						$stmt = $conn->prepare("INSERT INTO `login_status`(`data`) VALUES (?)");
						$dataString = json_encode($data);
						$stmt->bind_param("s", $dataString);
						$stmt->execute();
						break;
					}
				}
			}

			//Delete all cookie
			unset($_COOKIE['authToken']); 
			setcookie('authToken', '', -1, '/'); 
			unset($_COOKIE['authEmail']); 
			setcookie('authEmail', '', -1, '/');
			if($isDev)
			{
				if(!$isLocalTest)
				{
					header("Location: /");
				}
				else
				{					
					header("Location: /");
				}
			}
			else
			{
				if(!$isLocalTest)
				{
					header("Location: /");
				}
				else
				{					
					header("Location: /");
				}
			}
			exit(); 
		}
		$logedIn = false; 
		if(isset($_COOKIE["authToken"]) && isset($_COOKIE["authEmail"]))
		{
			$authToken = $_COOKIE["authToken"];
			$authEmail = $_COOKIE["authEmail"];
			$userID = null;
			$userFound = false;
			$notFound = false;
			$userName = null;
			$response = json_decode('{}',true);
			if(!empty($authToken) && !empty($authEmail))
			{
				$checkIfTokenIsReal = $conn->prepare("SELECT * FROM `users` WHERE `authToken` = ? AND `email` = ?");
				$checkIfTokenIsReal->bind_param("ss", $_COOKIE["authToken"], $_COOKIE["authEmail"]);
				$checkIfTokenIsReal->execute();
				$result = $checkIfTokenIsReal->get_result(); 
				if($result->num_rows == 1) {   
					while ($row = $result->fetch_assoc()) 
					{
						$logedIn = true;
						$userID = $row["id"];
						header("USERID:" .$userID);
								
						//setcookie("authToken",$authToken,time()+(60 * 60 * 24),"/");
						//setcookie("authEmail",$email,time()+(60 * 60 * 24),"/");	
						if(!$isLocalTest)
						{
							header("Location: /" . $urlAdder);
						}
						else
						{					
							header("Location: /dev./" . $urlAdder);
						}
						break;
					}     
				}
			}
		}
		if($logedIn == false)
		{			
			unset($_COOKIE['authToken']); 
			setcookie('authToken', '', -1, '/'); 
			unset($_COOKIE['authEmail']); 
			unset($_COOKIE['r']); 
			setcookie('authEmail', '', -1, '/');
			if(isset($_GET["logout"]))
			{		
				//Delete all cookie
				
				if(!$isLocalTest)
				{
					header("Location: /");
				}
				else
				{					
					header("Location: /dev./");
				}
				exit(); 
			}
			else
			{
				
				
				if(isset($_POST["signUp"]) && isset($_POST["email"]) && isset($_POST["firstName"]) && isset($_POST["lastName"]) && isset($_POST["pswd"]) && isset($_POST["uniName"]))
				{
					echo "a";
					try {			
						$email = $_POST["email"];
						$pswd = $_POST["pswd"];
						$sql = "SELECT * FROM `users` WHERE `email` = '$email'";
						$result = $conn->query($sql);
						while ($row = $result -> fetch_assoc())
						{							
							$authToken = random_strings(32);
							$sql = "UPDATE `users` SET `authToken`='$authToken' WHERE `email` = '$email' AND `passwd` = '$pswd'";
							$result = $conn->query($sql);
							//header("A:".$sql);
							setcookie("authToken",$authToken,time()+(60 * 60 * 24),"/");
							setcookie("authEmail",$email,time()+(60 * 60 * 24),"/");	

							setcookie("showOrgFileStructure",1,time()+(60 * 60 * 24),"/");	
							setcookie("showFileStructureProfile",1,time()+(60 * 60 * 24),"/");
							header("Location: /");				
							exit();
							break;
						}
						$firstName = $_POST["firstName"];
						$lastName = $_POST["lastName"];
						$orgName = $_POST["uniName"];
						$authToken = random_strings(32);

						$orgID = null;
						$getUniOrg = "SELECT * FROM `org` WHERE `name` = '$orgName'";
						$result = $conn->query($getUniOrg);
						while ($row = $result -> fetch_assoc())
						{
							$orgID = $row['id'];
						}

						if($orgID != null)
						{
							//Creates the default perm for the user, with the orgID being the org chosen in the signup
							$orgPerm = json_decode("{}",true);
							$orgPerm[$orgID] = json_decode("{}",true);
							$orgPerm[$orgID]["org"] = $orgID;
							$orgPerm[$orgID]["roles"] = ["learner"];
							$orgPerm[$orgID]["domain"] = [];
							$defaultTarget = json_decode("{}",true);
							$defaultTarget["type"] = "target";
							$defaultTarget["target"] = "*";
							
							$org = json_decode("{}",true);
							$org["org"] = [];
							array_push($org["org"],(int)$orgID);
							array_push($orgPerm[$orgID]["domain"], $defaultTarget);
							
							$sql = "INSERT INTO `users`(`email`, `passwd`, `authToken`, `org`, `firstName`, `lastName`) VALUES ('$email','$pswd','$authToken','" . json_encode($org) . "','$firstName','$lastName')";
							header("SQL:" .$sql);
							$result = $conn->query($sql);

							setcookie("authToken",$authToken,time()+(60 * 60 * 24),"/");
							setcookie("authEmail",$email,time()+(60 * 60 * 24),"/");

							$checkIfTokenIsReal = "SELECT * FROM `users` WHERE `authToken` = '$authToken' AND `email` = '$email'";
							
							$result = $conn->query($checkIfTokenIsReal);
							while ($row = $result -> fetch_assoc())
							{
								//var_dump($row);
								$userID = $row["id"];
								$isStaff = $row["isStaff"];
								$isAdmin = $row["isAdmin"];
								$orgs = json_decode($row["org"],true);
								if($isStaff === 1 || $isStaff || $isAdmin === 1 || $isAdmin)
								{
									$isStaff = true;
								}
								header("USERID:" .$userID);
								break;
							}     
							$perm = json_decode("{}",true);
							//var_dump($orgs["org"]);
							/*for ($i = 0; $i < sizeof($orgs["org"]); $i++)
							{
								$perm[$orgs["org"][$i]] = json_decode("{}",true);
								$perm[$orgs["org"][$i]]["org"] = $orgs["org"][$i];
								$perm[$orgs["org"][$i]]["roles"] = [];
								array_push($perm[$orgs["org"][$i]]["roles"],"learner");
								$perm[$orgs["org"][$i]]["domain"] = [];
							}*/
							$sql = "INSERT INTO `perms`(`userID`, `perm`) VALUES ($userID,'".json_encode($orgPerm)."')";	
							header("SQL:" .$sql);
							$result = $conn->query($sql);

							
							if(!$isLocalTest)
							{
								header("Location: /".$urlAdder);
							}
							else
							{					
								header("Location: /dev./".$urlAdder);
							}
						}
						else
						{
							header("E:E");
						}
					} catch (\Throwable $th) {
						throw $th;
					}
					header("Version: " . $_version);
				}
				else if(isset($_POST["login"]))
				{
					if(isset($_POST["email"]) && isset($_POST["pswd"])) //Checks if the email and pswd has been set
					{
						$email = $_POST["email"];
						$pswd = $_POST["pswd"];
						$sql = "SELECT * FROM `users` WHERE `email` = '$email' AND `passwd` = '$pswd'";
						$result = $conn->query($sql);
						$f = false;
						while ($row = $result -> fetch_assoc())
						{
							//Sets the new authtoken on the backend and as a cookie
							$f = true;
							$authToken = random_strings(32);
							$sql = "UPDATE `users` SET `authToken`='$authToken' WHERE `email` = '$email' AND `passwd` = '$pswd'";
							$result = $conn->query($sql);
							//header("A:".$sql);
							setcookie("authToken",$authToken,time()+(60 * 60 * 244),"/");
							setcookie("authEmail",$email,time()+(60 * 60 * 244),"/");

							setcookie("showOrgFileStructure",1,time()+(60 * 60 * 24),"/");	
							setcookie("showFileStructureProfile",1,time()+(60 * 60 * 24),"/");
							header("Version: " . $_version);
							$data = json_decode("{}",true);
							$data["email"] = $email;
							$data["userID"] = $row["id"];
							$data["ip"] = $_SERVER['REMOTE_ADDR'];
							$data["message"] = "Login success";
							$stmt = $conn->prepare("INSERT INTO `login_status`(`data`) VALUES (?)");
							$dataString = json_encode($data);
							$stmt->bind_param("s", $dataString);
							$stmt->execute();
							$baseUrl = "/";
							if($isLocalTest)
							{
								$baseUrl = "/dev./";
							}
				
							if($isDev)
							{
								header("Location: $baseUrl".$urlAdder);
							}
							else
							{
								header("Location: $baseUrl".$urlAdder);
							}
							break;
							exit();
						}
						if($f === false)
						{
							$data = json_decode("{}",true);
							$data["email"] = $email;
							$data["userID"] = -1;
							$data["ip"] = $_SERVER['REMOTE_ADDR'];
							$data["message"] = "Login failed";
							$stmt = $conn->prepare("INSERT INTO `login_status`(`data`) VALUES (?)");
							$dataString = json_encode($data);
							$stmt->bind_param("s", $dataString);
							$stmt->execute();
						}
					}
				}
			}
		}
	} catch (\Throwable $th) {
		throw $th;
	}

	function random_strings($length_of_string)
	{
		$str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		return substr(str_shuffle($str_result), 0, $length_of_string);
	}

?>

<!DOCTYPE html>
<html lang="en" >
<head>
	<meta charset="UTF-8">
	<title>Digital Workbook - Login</title>
	<script src="js/utils.js"> </script>
	<meta name="version" content="<?php echo $_version;?>">
</head>
<body>
<!DOCTYPE html>
<html>
<body class="">
	<script>
		function showTab(id)
		{
			console.log(id);
			hideAllTabs();
			document.getElementById(id).removeAttribute("hidden");
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

		function forgotPassword()
		{

		}

		

	</script>
	<div class="tab" style="height: 250px;" id="registerTab">
		<div class="container2 ">  	
			<form action="" method="post" target="_self">
				<div class="position-absolute top-50 start-50 translate-middle">
					<div class="signup ">
						<label class="form-label" for="form2Example1">Sign Up</label>
						<div data-mdb-input-init class="form-outline mb-4">
							<input type="text" id="firstName" class="form-control"  placeholder="First Name" name="firstName" required=""/>
						</div>
						<div data-mdb-input-init class="form-outline mb-4">
							<input type="text" id="lastName" class="form-control"  placeholder="Last Name" name="lastName" required=""/>
						</div>
						<div data-mdb-input-init class="form-outline mb-4">
							<input type="email" id="email" class="form-control"  placeholder="Email address" name="email" required=""/>
						</div>
						<div data-mdb-input-init class="form-outline mb-4">
							<input type="password" id="password" class="form-control" placeholder="Password" name="pswd" required=""/>
						</div>
						<input class="form-control" id="uniName" name="uniName" list="datalistOptions" placeholder="Uni name" onchange="checkUni()" onkeypress="this.onchange();" onpaste="this.onchange();" oninput="this.onchange();" required="">
						<datalist id="datalistOptions"></datalist>
						<input type="hidden" name="signUp" placeholder="Password"><br>
					</div>
					<div class="row mb-4">
						<div class="col d-flex justify-content-center">
							<div class="form-check">
								<p> Already have an account? <a class="form-check-label" href="javascript:showTab('loginTab')"> Login </a></p><br><br>
								<input class="form-check-input" type="checkbox" value="" id="form2Example31" checked />
								<label class="form-check-label" for="form2Example31"> Remember me </label><br>
							</div>
						</div>
						<button type="submit" class="btn btn-primary btn-block mb-4">Sign up</button>
					</div>
					<div class="container2">
						<footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top footer" id="footer">
							<script> var version = <?php echo "'".$_version."-".$_date."'"?>;</script>
							<script> var XQ51dka = <?php echo "'".$checkerItem."'" ?>; </script>
							<script src="js/footer.js"></script>
						</footer>
					</div>
				</form>				
			</div>
		</div>
	</div>
	<div class="tab" hidden id="loginTab">
		<div class="container2">  	
			<form action="" method="post" target="_self">
				<div class="position-absolute top-50 start-50 translate-middle">
					<div class="sigin">
						<label class="form-label" for="form2Example1">Login</label>
						<div data-mdb-input-init class="form-outline mb-4">
							<input type="email" id="email" class="form-control" placeholder="Email address" name="email" required=""/>
						</div>
						<div data-mdb-input-init class="form-outline mb-4">
							<input type="password" id="password" class="form-control" placeholder="Password" name="pswd" required=""/>
						</div>
						
						<input type="hidden" name="login"><br>
					</div>
					<div class="row mb-4">
						<div class="col d-flex justify-content-center">
							<div class="form-check">
								<p> Dont have an account? <a class="form-check-label" href="javascript:showTab('registerTab')"> Register </a></p><br><br>
								<input class="form-check-input" type="checkbox" value="" id="form2Example31" checked />
								<label class="form-check-label" for="form2Example31"> Remember me </label><br>
							</div>
						</div>
						<button type="submit" class="btn btn-primary btn-block mb-4">Sign in</button>
					</div>
					<div class="container2">						
						<footer class="d-flex flex-wrap justify-content-between align-items-center py-3 my-4 border-top footer" id="footer">
							<script> var version = <?php echo "'".$_version."-".$_date."'"?>;</script>
							<script> var XQ51dka = <?php echo "'".$checkerItem."'" ?>; </script>
							<script src="js/footer.js"></script>
						</footer>
					</div>				
				</div>
			</form>
		</div>
	</div>
	<script>
		var xhttp = new XMLHttpRequest();
		var uniNames = {};
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
							/*document.getElementById("info").innerHTML = "";
							var j = JSON.parse(xhttp.responseText);
							var item = j["uni"];
							var holder = "uni";
							document.getElementById(holder).innerHTML = "";
							var dropdown=document.createElement("select");
							for(var i=0;i<item.length;i++){
								console.log(item[i]["name"]);
								var opt=document.createElement("option");
								opt.text=item[i]["name"];
								opt.value=item[i]["name"];
								opt.setAttribute("onclick","getAllSubjects("+ item[i]["id"] + ")");
								dropdown.options.add(opt)
							}
							var container=document.getElementById(holder);
							container.appendChild(dropdown);*/
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
		</script>
</body>
</html>
