<?php
$response = json_decode("{}",true);
require("conn.php");
require("version.php");
require("csp.php");
global $_version;
global $isDev;
global $conn;
global $kakdoaw;
global $isLocalTest;

$presentationMode = false;

$sessionOwner = false;
function inDict($data,$valueToFind)
{
	//echo "DICT";
	//echo "<br>";
	//var_dump($data);
	foreach($data as $key => $value) {

		//echo "VALUE:" . $value . " " . $key . " VTF " . $valueToFind . " <br>";
		if($data[$key] === $valueToFind)
		{
			return true;
		}
	}
	return false;
}
$debug = false;
if(isset($_GET['debug']))
{
	$debug = true;
}
header("debug: " . $debug);
$allItemsValue = json_decode("{}",true);
$companies = ["TLC","BHP","APA","COM","WDS","WES"];
$isAdmin = false;
$isStaff = false;
$userORG = json_decode("[]",true);
$rA = true;

$operators = ["+","-","/","*","%","**"];
$acceptedSpecialChars = ["(",")"];

function safeEval($expression) {
	global $operators;
	global $acceptedSpecialChars;
	global $response;
	$good = false;
	
	foreach($operators as $key)
	{
		$expression = str_replace($key, " " . $key . " ",$expression);
	}

	foreach($acceptedSpecialChars as $key)
	{
		$expression = str_replace($key, " " . $key . " ",$expression);
	}

	$expression = str_replace(",", "",$expression);

	array_push($response['stack'],"EQUATION KEY: ".$expression);
    foreach (explode(" ",$expression) as $key) {
		if(!is_numeric($key) && !in_array($key,$operators) && !in_array($key,$acceptedSpecialChars) && !empty($key))
		{
			$good = false;
			array_push($response['stack'],"BROKEN KEY".$key);
		}
		else
		{
			$good = true;
		}
	}
	if($good)
	{
		return eval('return ' . $expression . ';');
	}
	return "NOT VALID EQUATION";
}

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

/*
CREATE TABLE `datatrain`.`login_status` (`id` INT NOT NULL AUTO_INCREMENT , `data` JSON NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
ALTER TABLE `datatrain`.`questions` ADD `extra` JSON NOT NULL DEFAULT '{}' AFTER `raw`; 
ALTER TABLE `datatrain`.`login_status` ADD `date` DATE NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `data`; 
ALTER TABLE `datatrain`.`login_status` CHANGE `date` `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP; 
*/


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

function inOrg($org)
{
	global $perms;
	if(isset($perms[-1]) || $isAdmin)
	{
		return true;
	}
	return isset($perms[$org]);
}

function getOrgFromQuestionID($questionID)
{
	global $conn;
	$sql = "SELECT * FROM `subject` WHERE id = (SELECT `subject` FROM topic WHERE id = (SELECT `topic` FROM `questions` WHERE `questionID` = '$questionID'))";
	$result = $conn->query($sql);
	while ($row = $result -> fetch_assoc())
	{
		return $row["org"];
	}
}

header("Version: " . $_version);
header("X-Powered-By: ". $_version);

$response["stack"] = [];
try
{
	$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$uri = explode('/', $uri);
	$response["uri"] = $uri;
	$apiPoint = "";
	$URLParams = [];
	if($isLocalTest)
	{
		$apiPoint = $uri[3];
		$URLParams = array_slice($uri,4);
	}
	else
	{
		$apiPoint = $uri[2];
		$URLParams = array_slice($uri,3);
	}

	$response["URLParams"] = $URLParams;
	$response["apiPoint"] = $apiPoint;
	$response["isLocalTest"] = $isLocalTest;
	header("apiPointStart: $apiPoint");
	if(isset($apiPoint) && $apiPoint === "findOrg" && isset($URLParams[0]))
	{
		$sql = "SELECT * FROM `org` WHERE `name` LIKE '%". urldecode($URLParams[0]) . "%' ORDER BY `name` LIMIT 25 ";
		if($debug)
		{				
			$response["MSG"] = $sql;
		}
		$response["uni"] = [];
		$result = $conn->query($sql);
		while ($row = $result -> fetch_assoc())
		{
			$c = json_decode("{}",true);
			$c["id"] = $row["id"];
			$c["name"] = $row["name"];
			array_push($response["uni"],$c);
		} 
		echo json_encode($response);
		exit();
	}
	else if(isset($apiPoint) && $apiPoint === "getSessionID"){
		if(isset($_GET['shortCode']) && is_numeric($_GET['shortCode']))
		{
			$shortCode = $_GET['shortCode'];
			$stmt = $conn->prepare("SELECT * FROM `sessions` WHERE JSON_VALUE(`data`,'$.shortCode') = ?");
			$stmt->bind_param("s", $shortCode);
			$found = false;
			$makeNew = false;
			$sessionID = null;
			if($debug)
			{
				array_push($response['stack'], "got short code: " . $shortCode);
			}
			if ($stmt->execute())  
			{
				$result = $stmt->get_result(); 
				while ($row = $result->fetch_assoc()) {
					if($debug)
					{
						array_push($response['stack'], "searching short code sessions: " . $row['sessionID']);
					}
					if(json_decode($row['data'],true)["validUntil"] >= strtotime(gmdate('r', time())))
					{
						$found = true; 
						$sessionID = $row['sessionID'];
						$userID = random_strings(16);
						if(isset($_COOKIE["sessionUserID"]))
						{
							$userID = $_COOKIE["sessionUserID"];
							if(isset(json_decode($row['data'],true)["allUsers"][$userID]))
							{
								$response["sessionUserID"] = $userID;
								break;
							}
						}
						
						while (isset(json_decode($row['data'],true)["allUsers"][$userID]))
						{							
							$userID = random_strings(16);
						}
						$sessionInfo = json_decode($row['data'],true);
						$sessionInfo["allUsers"][$userID] = "temp";
						$sessionInfo = json_encode($sessionInfo);
						$rowID = $row["id"];
						$stmtUpdate = $conn->prepare("UPDATE `sessions` SET `data` = ? WHERE `id` = ?");
						$stmtUpdate->bind_param("ss", $sessionInfo,$rowID);
						$stmtUpdate->execute();
						$response["sessionUserID"] = $userID;
						setcookie("sessionUserID",$userID,time()+7200, "/");
					}
				}									
			}
			if($found)
			{
				$response["sessionID"] = $sessionID;
			}
			else
			{
				$response["sessionID"] = $sessionID;
				$response["msg"] = "Couldn't find session linked to short code";
				http_response_code(404);
			}
		}
		else
		{
			$response["sessionID"] = $sessionID;
			$response["msg"] = "Couldn't find session linked to short code";
			http_response_code(404);
		}
		echo json_encode($response);
		exit();
	}
	else if($apiPoint === "getShortCode" && isset($URLParams[0]))	{
		$sessionID = urldecode($URLParams[0]);
		if($debug)
		{
			array_push($response['stack'], 'sessionID: '. $sessionID);
		}
		$stmt = $conn->prepare("SELECT * FROM `sessions` WHERE JSON_VALUE(`data`,'$.sessionID') = ?");
		$stmt->bind_param("s", $sessionID);
		$found = false;
		$makeNew = false;
		$shortCode = null;
		if ($stmt->execute())  
		{
			$result = $stmt->get_result(); 
			while ($row = $result->fetch_assoc()) {
				if(json_decode($row['data'],true)["validUntil"] >= strtotime(gmdate('r', time())))
				{
					if($debug)
					{
						array_push($response['stack'], 'found session');
					}
					$found = true; 
					$shortCode = json_decode($row['data'],true)['shortCode'];
				}
			}									
		}
		$response["shortCode"] = $shortCode;
		echo json_encode($response);
		exit();
	}
	else if(isset($_COOKIE["authToken"]) && isset($_COOKIE["authEmail"]))	{
		$authToken = $_COOKIE["authToken"];
		$authEmail = $_COOKIE["authEmail"];
		$userID = null;
		$name = null;
		$logedIn = false; 
		$userFound = false;
		$userName = null;
		$perms = json_decode('{}',true);
		$groups = json_decode('{}',true);
		if(!empty($authToken) && !empty($authEmail))
		{
			$logedIn = true;
			$checkIfTokenIsReal = "SELECT * FROM `users` WHERE `authToken` = '$authToken' AND `email` = '$authEmail'";
			$result = $conn->query($checkIfTokenIsReal);
			while ($row = $result -> fetch_assoc())
			{
				$userID = $row["id"];
				$name = $row["firstName"] . " " . $row["lastName"];
				$isStaff = $row["isStaff"];
				$userORG = json_decode($row["org"],true);
				if($row["isAdmin"] == 1)
				{
					$isAdmin = true;
					$sessionOwner = true;
				}
				if($isStaff == 1 || $isStaff || $isAdmin == 1 || $isAdmin)
				{
					$isStaff = true;
				}
				
				break;
			}    
			
			if($isDev)
			{
				header("userID: $userID");
			}
			if($userID == null)
			{                
				if($isDev)
				{
					header("Location: /dev./login.php");
				}
				else
				{					
					header("Location: /main/login.php");
				}
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
				//var_dump($permsTemp);
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
					//echo $key."\n";
					//echo json_encode($permsTemp[$key])."\n";
					for ($d = 0; $d < sizeof($permsTemp[$key]["domain"]); $d++)
					{
						//var_dump($permsTemp[$key]["domain"][$d]);
						$data = $permsTemp[$key]["domain"][$d];
						//var_dump($permsTemp[$key]["domain"][$d]);
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
			//echo"\n\n";
			//echo json_encode($permsTemp)."\n\n";
			//echo json_encode($permsTemp2)."\n\n";
			$perms = $permsTemp;
			header("perms: " . json_encode($perms));
			if(hasRole("admin","-1"))
			{
				$isAdmin = true;
			}
			
			if($isAdmin)
			{
				//setcookie("isAdmin",$isAdmin);
			}

			$isQOwner = false;
			$questionTopicInfo = null;
			$questionOrgInfo = null;
			if(isset($_GET["questionID"]))
			{				
				$questionID = $_GET["questionID"];
				$sql = "SELECT * FROM `questions` WHERE `questionID` = '$questionID' AND `userID` = '$userID'";
				$response["cSQL"] = $sql;
				$result = $conn->query($sql);
				$v = false;
				while ($row = $result -> fetch_assoc())
				{
					$v = true;
				}
				if($v == true)
				{
					$isQOwner = true;
				}


				$stmt = $conn->prepare("SELECT s.org, t.id as 'topic' FROM `subject` s, `topic` t, `questions` q WHERE t.subject = s.id AND t.id = q.topic AND q.questionID = ?");
				$stmt->bind_param("s", $questionID);
				if ($stmt->execute())  
				{
					$result = $stmt->get_result();   
					while ($row = $result->fetch_assoc()) {
						$questionTopicInfo = $row['topic'];
						$questionOrgInfo = $row['org'];
					}
				}
				header('questionTopicInfo: '. $questionTopicInfo);
				header('questionORG: '. $questionOrgInfo);
				
			}
			if($isDev)
			{
				$response["isStaff"] = $isStaff;
				$response["isAdmin"] = $isAdmin;
				$response["isQOwner"] = $isQOwner;
			}
 			if($isAdmin)
			{
				$isQOwner = true;
			}


			if(isset($apiPoint))
			{				
				$response["API"] = $apiPoint;
				if($apiPoint === "me")
				{
					$out = json_decode("{}",true);
					$sql = "SELECT * FROM `users` WHERE `id` = '$userID'";
					$result = $conn->query($sql);
					while ($row = $result -> fetch_assoc())
					{
						try{
							$response["firstName"] = $row["firstName"];
							$response["lastName"] = $row["lastName"];
							$response["email"] = $row["email"];
							$response["org2"] = $userORG;
							$response["org"] = [];
							foreach ($userORG["org"] as $Lid) 
							{
								$sql = "SELECT `name` FROM `org` WHERE `id` = '" . $Lid . "'";
								$response["sql"] = $sql;
								$result = $conn->query($sql);
								while ($row = $result -> fetch_assoc())
								{
									$item = json_decode("{}",true);
									$item["name"] = $row["name"];
									$item['id'] = (int)$Lid;
									array_push($response["org"],$item);
								}
							}
							$response["perm"] = $perms;
							$response["userORG"] = $userORG;
						}
						catch(\Throwable $e)
						{
							echo $e;
						}
					}
					//echo json_encode($out);
				}
				else if($apiPoint === "getQuestionRaw")
				{
					if(isset($_GET["questionID"]))
					{
						if($isAdmin || $isStaff || inDomainAny($questionID))
						{
							$b = json_decode("{}",true);
							$questionID = $_GET["questionID"];
							$sql = "SELECT * FROM `questions` WHERE `questionID` = '$questionID'";
							$response["sql"] = $sql;
							$response["topicID"] = null;
							$response["topicName"] = null;
							$response["public"] = false;
							$result = $conn->query($sql);
							$found = false;
							$question = "";
							while ($row = $result -> fetch_assoc())
							{
								try{
									$found = true;
									$question = urldecode(base64_decode(utf8_decode(urldecode($row["question"]))));
									$question = mb_convert_encoding($question, 'UTF-8', 'UTF-8');
									$question = str_replace("\n","<br>", $question);
									$answer = urldecode(base64_decode(utf8_decode(urldecode($row["answer"]))));
									$answer = mb_convert_encoding($answer, 'UTF-8', 'UTF-8');
									$answer = str_replace("\n","<br>", $answer);
									//echo $row["name"];
									if($row["isPublic"] == 1)
									{
										$response["public"] = true;
									}
									$response["topicID"] = (int)$row["topic"];
									try
									{
										$sqlGetTopic = "SELECT * FROM `topic` WHERE `id` = '" . $row['topic']. "'";
										$result2 = $conn->query($sqlGetTopic);
										while ($row2 = $result2 -> fetch_assoc())
										{
											$response["topicName"] = $row2["name"];
											$sqlGetTopic = "SELECT * FROM `subject` WHERE `id` = '" . $row2['subject']. "'";
											$response["subject"] = $row2['subject'];
											$result3 = $conn->query($sqlGetTopic);
											while ($row3 = $result3 -> fetch_assoc())
											{											
												$response["subjectName"] = $row3['name'];
												$response["org"] = $row3["org"];
												$sqlGetOrg = "SELECT * FROM `org` WHERE `id` = '" . $row3['org']. "'";
												$result3 = $conn->query($sqlGetOrg);
												while ($row4 = $result3 -> fetch_assoc())
												{												
													$response["orgName"] = $row4["name"];
												}
											}
										}
									}
									catch(\Throwable $e){
										echo $e;
									}
									$response["name"] = $row["name"];
									$response["raw"] = $question;
									$response["answer"] = $answer;
									//echo $row["translationTable"];
									$response["translationTable"] = base64_decode(urldecode($row["translationTable"]));
									//$b["translationTable"] = base64_decode($row["translationTable"]);
									$questionID = $row["questionID"];
								}
								catch(\Throwable $e)
								{
									echo $e;
								}
								break;
							}
							if($found === true)
							{
								$sql = "SELECT * FROM `questions` WHERE `questionSubID` = '$questionID'";
								$result = $conn->query($sql);
								while ($row = $result -> fetch_assoc())
								{
									$questionR = urldecode(base64_decode(utf8_decode(urldecode($row["question"]))));
									$question = $question . mb_convert_encoding($questionR, 'UTF-8', 'UTF-8');
									$response["raw"] = $question;
									$response["title"] = $row["name"];

									$answerR = urldecode(base64_decode(utf8_decode(urldecode($row["answer"]))));
									$answer = $answer . mb_convert_encoding($answerR, 'UTF-8', 'UTF-8');
									$response["answerraw"] = $answer;
									$questionID = $row["questionID"];
									break;
								}


								$response["raw"] = base64_encode($response["raw"]);
								//echo json_encode($response);
								//$aa = base64_encode($response["raw"]);
								//$b["raw"] = $aa;
								//$b["answer"] = $answer;
								//$b["sql"] = $response["sql"];
								//$b["name"]= $response["name"];
								//echo json_encode($b);//$questionData["text"];
								//exit();
							}
							else
							{
								$response["error"] = true;
								$response["msg"] = "Cant find questions";
								http_response_code(404);
							}
						}
						else
						{
							$response["msg"] = "Dont have permission";
							http_response_code(401);
						}
					}
					else
					{
						$response["msg"] = "VARS NOT SET";
					}
				}
				
				
				else if ($apiPoint === "makeNewSession" && isset($_GET["questionIDs"]))	{
					if($isStaff || hasRole("orgAdmin",$firstOrg) || hasRole("educator",$firstOrg) || hasRole("admin",$firstOrg))
					{
						$jsonData = json_decode($_GET["questionIDs"],true);
						$questionIDs = $jsonData['data'];
						$useBaseValues = $jsonData['useBaseValues'];
						$tempID = random_strings(16);
						$shortCode = rand(100000,999999);
						array_push($response['stack'],'creatingSesion ID : ' . $tempID);

						$stmt = $conn->prepare("SELECT * FROM `sessions` WHERE JSON_VALUE(`data`,'$.shortCode') = ?");
						$stmt->bind_param("s", $shortCode);
						$dup = false;
						$makeNew = false;
						if ($stmt->execute())  
						{
							$result = $stmt->get_result(); 
							while ($row = $result->fetch_assoc()) {
								if(json_decode($row['data'],true)["validUntil"] >= strtotime(gmdate('r', time())))
								{
									$makeNew = true;
								}
							}									
						}

						for ($v = 0; $v < 10; $v++)
						{
							$shortCode = rand(100000,999999);
							$stmt = $conn->prepare("SELECT * FROM `sessions` WHERE JSON_VALUE(`data`,'$.shortCode') = ?");
							$stmt->bind_param("s", $shortCode);
							$dup = false;
							array_push($response['stack'], $shortCode);
							if ($stmt->execute())  
							{
								$makeNew = false;
								$result = $stmt->get_result(); 
								while ($row = $result->fetch_assoc()) {
									if(json_decode($row['data'],true)["validUntil"] >= strtotime(gmdate('r', time())))
									{
										array_push($response['stack'], json_decode($row['data'],true)["validUntil"] . " " . strtotime(gmdate('r', time()))). " " .json_decode($row['data'],true)["validUntil"] >= strtotime(gmdate('r', time()));
										$makeNew = true;										
									}
								}									
							}
							if(!$makeNew)
							{
								break;
							}
						}
						if($makeNew)
						{
							$response['msg'] = "Can't make session at this time";
							echo json_encode($response);
							exit();
						}

						if($dup === false)
						{
							$stmt = $conn->prepare("INSERT INTO `sessions`(`sessionID`,`data`) VALUES (?,?)");
							$jsonData = json_decode('{}',true);
							$jsonData["questionIDs"] = $questionIDs;
							$jsonData["useBaseValues"] = $useBaseValues;
							$jsonData["creatorID"] = $userID;
							$jsonData["sessionID"] = $tempID;
							$jsonData["instanceID"] = rand(100000,999999);
							$jsonData["shortCode"] = $shortCode;
							$jsonData["validUntil"] = strtotime(gmdate('r', time() + 3 * 60 * 60));
							$jsonData["sessionResetCount"] = 0;
							$jsonData["sessionIndex"] = 0;
							$jsonData["allUsers"] = json_decode("[]");
							$jsonData["allUsers"][$userID] = "admin";
							$stmtUpdate = $conn->prepare("UPDATE `sessions` SET `data` = ? WHERE `id` = ?");
							$stmtUpdate->bind_param("ss", $sessionInfo,$rowID);
							$stmtUpdate->execute();
							$response["sessionUserID"] = $userID;
							setcookie("sessionUserID",$userID,time()+7200, "/");
							$jsonEncode = json_encode($jsonData);
							$stmt->bind_param("ss", $tempID,$jsonEncode);
							$dup = false;
							if ($stmt->execute())  
							{
								
							}
						}
						$response['sessionID'] = $tempID;
						$response['shortCode'] = $shortCode;
					}
					else
					{
						$response["error"] = "Do not have permission";
						http_response_code(401);
					}
					
				}
				else if($apiPoint === "getNewQuestion" && (isset($_GET["questionID"]) || isset($_GET['sessionID'])))
				{
					$questionID = null;
					
					$randomActive = true;
					
					if(isset($_GET["randomlock"]))
					{
						$randomActive = false;
						$rA = false;
					}
					
					$instanceId = null;
					if(isset($_GET["questionID"]))
					{
						$questionID = $_GET["questionID"];
					}
					if(($isQOwner || $isAdmin) && (!str_contains($_SERVER['HTTP_REFERER'], "learningMode=true") && !str_contains($_SERVER['HTTP_REFERER'], "presentationMode=true") && !isset($_GET['sessionID']) && !str_contains($_SERVER['HTTP_REFERER'], "testMode=true") && !str_contains($_SERVER['HTTP_REFERER'],'tutor.php')))
					{
						$response["url"] = "question.php?questionID=".$questionID."&editing=true";
						http_response_code(202);
					}
					else
					{
						/*CREATE TABLE `datatrain`.`sessions` (`id` INT NOT NULL AUTO_INCREMENT , `sessionID` VARCHAR(32) NULL DEFAULT NULL , `data` JSON NOT NULL DEFAULT '{}' , PRIMARY KEY (`id`)) ENGINE = InnoDB; */
						//$response["validUntil"] = strtotime(gmdate('r', time() + 3 * 60 * 60));
						if(isset($_GET['sessionID']))
						{
							$sessionIndex = 0;
							if(isset($_GET['sessionIndex']))
							{
								$sessionIndex = $_GET['sessionIndex'];
							}

							if(!isset($_COOKIE["sessionUserID"]))
							{
								setcookie("sessionUserID",$userID,time()+7200, "/");
							}


							header("sessionIndex: $sessionIndex");
							array_push($response['stack'],'found session id');
							$sessionID = $_GET['sessionID'];
							$stmt = $conn->prepare("SELECT * FROM `sessions` WHERE JSON_VALUE(`data`,'$.sessionID') = ?");
							$stmt->bind_param("s", $sessionID);
							$found = false;
							if ($stmt->execute())  
							{
								$result = $stmt->get_result(); 
								while ($row = $result->fetch_assoc()) {
									if(json_decode($row['data'],true)["validUntil"] >= strtotime(gmdate('r', time())))
									{
										$sessionStats = json_decode($row['data'],true);
										$questionIDs = $sessionStats['questionIDs'];
										$found = true;
										if(isset($sessionStats["instanceID"]))
										{
											$instanceId = $sessionStats["instanceID"];
										}
										if(isset($sessionStats["useBaseValues"]))
										{
											$randomActive = !$sessionStats["useBaseValues"];
										}
										if($sessionStats["creatorID"] == $userID)
										{
											$sessionOwner = true;
										}
									}
								}									
							}
							if($found)
							{
								$presentationMode = true;
								$response['sessionOwner'] = $sessionOwner;
							}
							else
							{
								$response["msg"] = 'sessionID not found or expired';
								$response['sessionID'] = $sessionID;
								echo json_encode($response);
								exit();
							} 
							array_push($response['stack'],'got question ids: '.$questionIDs[$sessionIndex]);
							$questionID = $questionIDs[$sessionIndex];
						}
						if(str_contains($_SERVER['HTTP_REFERER'], "presentationMode=true") && !isset($_GET['sessionID']))
						{
							$response["msg"] = 'sessionID not given';
							echo json_encode($response);
							exit();
						}
						if(inDomainAny($questionID) || 1 == 1)
						{
							if($instanceId == null)
							{
								$instanceId = $_GET["instanceId"];
							}
							array_push($response['stack'],'got instanceId: '. $instanceId);
							if(isset($instanceId))
							{
								srand($instanceId);
								header("instanceID: " . $instanceId);
							}
							header("random: " . $randomActive);
							//echo $randomActive;
							if($questionID != null)
							{
								$questionData = json_decode("{}",true);
								$translationTable = json_decode("{}",true);
								$answerData = json_decode("{}",true);
								$tax = rand(1,99);
								$sql = "SELECT * FROM `questions` WHERE `questionID` = '$questionID'";
								$result = $conn->query($sql);
								
								$response["sql"] = $sql;
								//echo $sql;
								$found = false;
								$question = "";
								$questionID = "";
								$answer = "";
								$response["raw"] = "TEMP";
								while ($row = $result -> fetch_assoc())
								{
									$found = true;
									$question = urldecode(base64_decode(utf8_decode(urldecode($row["question"]))));
									$question = mb_convert_encoding($question, 'UTF-8', 'UTF-8');
									$translationTable = urldecode(base64_decode(utf8_decode(urldecode($row["translationTable"]))));
									$response["raw"] = $question;
									$response["translationTable"] = $translationTable;
									$questionData["raw"] = $question;
									$response["title"] = $row["name"];

									$answer = urldecode(base64_decode(utf8_decode(urldecode($row["answer"]))));
									$answer = mb_convert_encoding($answer, 'UTF-8', 'UTF-8');
									$response["answerraw"] = $answer;
									$questionID = $row["questionID"];
									break;
								}
								$sql = "SELECT * FROM `questions` WHERE `questionSubID` = '$questionID'";
								$result = $conn->query($sql);
								while ($row = $result -> fetch_assoc())
								{
									$questionR = urldecode(base64_decode(utf8_decode(urldecode($row["question"]))));
									$question = $question . mb_convert_encoding($questionR, 'UTF-8', 'UTF-8');
									$response["raw"] = $question;
									$response["title"] = $row["name"];

									$answerR = urldecode(base64_decode(utf8_decode(urldecode($row["answer"]))));
									$answer = $answer . mb_convert_encoding($answerR, 'UTF-8', 'UTF-8');
									$response["answerraw"] = $answer;
									$questionID = $row["questionID"];
									break;
								}
								
								//var_dump($allItemsValue);
								if($found === true)
								{
									$translationTable = json_decode($translationTable,true);
									
									foreach ($translationTable as $key => $value) {
										addItemToArray($value[1], $questionData);
									}
									$t = $question;
									//echo strpos($t,"%c[0,0]");
									//echo "<br>";
									$t = str_replace("\n","<br>", $t);
									$response["text222"] = $answer;
									$response["answer"] = $t;
									$questionData["text"] = $t . "|||||" . $answer;
									$answerData["text"] = $answer;
									//echo json_encode($response);
									//$allItems = explode("%n", $questionData["text"]);
									
									//echo $questionData["text"];
									try{
										$questionData = parseText($questionData);
										$questionData = secondParseTextLayer($questionData);
									}
									catch (\Throwable $e)
									{
										echo $e;
									}
									//echo $questionData["text"];
									//var_dump(explode("|||||",$questionData["text"]));
									$answerData["text"] = explode("|||||",$questionData["text"])[1];
									$questionData["text"] = explode("|||||",$questionData["text"])[0];
									//$response["AAAA"] = $questionData["aaaa"];
									$response["ALL"] = $allItemsValue;
									$scripts = [];
									if($presentationMode)
									{
										//$questionData["text"] = $questionData["text"] . "<script src='js/presentation.js'></script>";
										//array_push($scripts,'js/presentation.js');
									}
									if($sessionOwner)
									{
										//$questionData["text"] = $questionData["text"] . "<script src='js/staff.js'></script>";
										array_push($scripts,'js/presentation.js');
									}
									$aa = base64_encode($questionData["text"]);
										
									$b = json_decode("{}",true);
									$b["text"] = $aa;
									$b["raw"] = base64_encode($t);
									$response["text"] = $questionData["text"];
									$response["answer"] = $answerData["text"];
									$response["loadScripts"] = $scripts;
									//$response["ADAD"] = $questionData["ADAD"];//$questionData["text"];
									echo json_encode($response);
									exit();
								}
							}
						}
					}
				}
				else if($apiPoint === "getSessionStats" && isset($URLParams[0]))
				{
					$sessionID = urldecode($URLParams[0]);
					array_push($response['stack'], $sessionID);
					$sessionResetCount = 0;
					$totalSessionUsers = 0;
					$stmt = $conn->prepare("SELECT * FROM `sessions` WHERE JSON_VALUE(`data`,'$.sessionID') = ?");
					$stmt->bind_param("s", $sessionID);
					if ($stmt->execute())  
					{
						$result = $stmt->get_result(); 
						while ($row = $result->fetch_assoc()) 
						{
							$sessionStats = json_decode($row['data'],true);
							array_push($response['stack'], $sessionStats);
							$totalSessionUsers = count($sessionStats["allUsers"]);
							$sessionResetCount = $sessionStats["sessionResetCount"];
						}
					}

					array_push($response['stack'], $sessionResetCount);


					$stmt = $conn->prepare("SELECT * FROM `question_stats` WHERE JSON_VALUE(`resultData`,'$.sessionID') = ?");
					$stmt->bind_param("s", $sessionID);
					$variableID = null;
					if(isset($_GET['variableID']))
					{
						$variableID = $_GET['variableID'];
					}
					$found = false;
					$makeNew = false;
					$results = [];
					$providedAwnsers = json_decode("{}",true);
					$varResults = json_decode("{}",true);
					$correctResults = json_decode("{}",true);
					if ($stmt->execute())  
					{
						$result = $stmt->get_result(); 
						while ($row = $result->fetch_assoc()) {
							addToStack(json_decode($row['resultData'],true));
							addToStack(json_decode($row['resultData'],true)["sessionResetCount"]);
							addToStack(json_decode($row['resultData'],true)["sessionResetCount"] == $sessionResetCount);
							if(json_decode($row['resultData'],true)["sessionResetCount"] == $sessionResetCount)
							{
								array_push($results, json_decode($row['resultData'],true));
								foreach (json_decode($row['resultData'],true)["rAnswer"] as $key => $value) {
									addToStack($value);
									if($value !== "")
									{
										if(!isset($varResults[$key]))
										{
											$varResults[$key] = json_decode("{}",true);
											$varResults[$key]['total'] = 0;
											$varResults[$key]['correct'] = 0;
										}
										if($value)
										{
											$varResults[$key]['correct'] ++;
										}
										
										$varResults[$key]['total'] ++;
									}
								}
								foreach (json_decode($row['resultData'],true)["providedAnswer"] as $key => $value) {

									if(!isset($providedAwnsers[$key]))
									{
										$providedAwnsers[$key] = json_decode("{}",true);
									}
									if(!isset($providedAwnsers[$key][$value]))
									{
										$providedAwnsers[$key][$value] = json_decode("{}",true);
										$providedAwnsers[$key][$value]['count'] = 0;
										$providedAwnsers[$key][$value]['value'] = $value;
									}
									$providedAwnsers[$key][$value]['count'] ++;
								}
								$correctResults = json_decode($row['resultData'],true)["rAnswerT"];
							}
						}
					}
					if($debug)
					{
						array_push($response['stack'],$varResults);
					}					
					$response['total'] = $totalSessionUsers;
					if($variableID != null && isset($varResults[$variableID]))
					{
						$response['results'] = $varResults[$variableID];
						$response['rawResults'] = $providedAwnsers[$variableID];
						$response['correctResults'] = $correctResults[$variableID];
					}
					else
					{
						$response['results'] = $varResults;
						$response['rawResults'] = $providedAwnsers;
						$response['correctResults'] = $correctResults;
					}
					
				}
				else if(isset($apiPoint) && $apiPoint === "searchSubject" && isset($URLParams[0]))
				{
					$sql = "SELECT * FROM `subject` WHERE `name` LIKE '%". urldecode($URLParams[0]). "%' ORDER BY `name` LIMIT 50";
					if($debug)
					{				
						$response["MSG"] = $sql;
					}
					$response["subjects"] = json_decode("{}",true);
					$response["topics"] = [];
					$response["questions"] = [];
					$response["data"] = json_decode("{}",true);
					$result = $conn->query($sql);
					while ($row = $result -> fetch_assoc())
					{
						$c = json_decode("{}",true);
						$c["id"] = $row["id"];
						$c["name"] = $row["name"];
						$response["data"][$c["name"]] = json_decode("{}",true);
						$response["subjects"][$c["name"]] = $c["id"];
						$sql2 = "SELECT * FROM `topic` WHERE `subject` = '". $c["id"] . "'";
						
						$result2 = $conn->query($sql2);
						while ($row2 = $result2 -> fetch_assoc())
						{							
							$c2 = json_decode("{}",true);
							$c2["id"] = $row2["id"];
							$c2["name"] = $row2["name"];
							$sql3 = "SELECT * FROM `questions` WHERE `topic` = '". $c2["id"] . "'";
							$response["data"][$c["name"]][$c2["name"]] = [];
							array_push($response["topics"],$c2);
							$result3 = $conn->query($sql3);
							while ($row3 = $result3 -> fetch_assoc())
							{	
								$c3 = json_decode("{}",true);
								$c3["questionID"] = $row3["questionID"];
								$c3["name"] = $row3["name"];
								if($c3["name"] === null || $c3["name"] === "")
								{
									$c3["name"] = "No name set";
								}
								array_push($response["data"][$c["name"]][$c2["name"]],$c3);
								array_push($response["questions"],$c3);
							}
						}
					} 
					
					echo json_encode($response);
					exit();
				}
				else if(isset($apiPoint) && $apiPoint === "searchTopic" && isset($URLParams[0]))
				{
					$sql = "SELECT * FROM `topic` WHERE `name` LIKE '%". urldecode($URLParams[0]). "%' ORDER BY `name` LIMIT 50";
					if($debug)
					{				
						$response["MSG"] = $sql;
					}
					$response["subjects"] = json_decode("{}",true);
					$response["topics"] = [];
					$response["questions"] = [];
					$response["data"] = json_decode("{}",true);
					$result = $conn->query($sql);
					while ($row = $result -> fetch_assoc())
					{
						$c = json_decode("{}",true);
						$c["id"] = $row["id"];
						$c["name"] = $row["name"];
						$response["data"][$c["name"]] = json_decode("{}",true);
						$response["topics"][$c["name"]] = $c["id"];
						$sql2 = "SELECT * FROM `questions` WHERE `topic` = '". $c["id"] . "'";
						
						$result2 = $conn->query($sql2);
						while ($row2 = $result2 -> fetch_assoc())
						{					
							$c2 = json_decode("{}",true);
							$c2["questionID"] = $row2["questionID"];
							$c2["name"] = $row2["name"];
							if($c2["name"] === null || $c2["name"] === "")
							{
								$c2["name"] = "No name set";
							}
							array_push($response["data"][$c["name"]],$c2);
							array_push($response["questions"],$c2);
						}
					} 
					echo json_encode($response);
					exit();
				}
				else if(isset($apiPoint) && $apiPoint === "searchForQuestions" && isset($_GET["uniID"]) && is_numeric($_GET["uniID"]) && isset($_GET["subjectName"]) && isset($_GET["topicName"]))				{
					$uniID = $_GET["uniID"];
					if(inOrg($uniID))
					{
						$subjectName = $_GET["subjectName"];
						$topicName = $_GET["topicName"];
						$response["uniID"] = $uniID;
						$questions = json_decode("[]");

						$subjectIDs = json_decode("[]");
						$topicIDs = json_decode("[]");
						$topicID = null;
						if($subjectName != null)
						{	
							$stmt = $conn->prepare("SELECT s.*, o.name as oName FROM `subject` s, `org` o WHERE s.`org` = ? AND s.`name` = ? AND s.org = o.id");
							$stmt->bind_param("ss", $uniID, $subjectName);
						}
						else
						{
							$stmt = $conn->prepare("SELECT s.*, o.name  as oName FROM `subject` s , `org` o WHERE `org` = ? AND s.org = o.id");
							$stmt->bind_param("s", $uniID);
						}
						$dup = false;
						if ($stmt->execute())  
						{
							$result = $stmt->get_result(); 
							while ($row = $result->fetch_assoc()) {
								array_push($subjectIDs,[$row["id"],$row["name"], $row["oName"]]);
							}
							
						}
						
						$response["subjectName"] = $subjectName;
						$response["subjectIDs"] = $subjectIDs;

						foreach ($subjectIDs as $id) {
							if($topicName != null)
							{
								$stmt = $conn->prepare("SELECT * FROM `topic` WHERE `name` = ? AND `subject` = ?");
								$stmt->bind_param("ss", $topicName, $id[0]);
							}
							else
							{
								$stmt = $conn->prepare("SELECT * FROM `topic` WHERE `subject` = ?");
								$stmt->bind_param("s", $id[0]);
							}
							$dup = false;
							if ($stmt->execute())  
							{
								$result = $stmt->get_result(); 
								while ($row = $result->fetch_assoc()) {
									array_push($topicIDs,[$row["id"],$id[0],$row["name"], $id[1], $id[2]]);
								}					
							}
						}

						foreach ($topicIDs as $id) {
							$stmt = $conn->prepare("SELECT * FROM `questions` WHERE `topic` = ?");
							$stmt->bind_param("s", $id[0]);
							$dup = false;
							if ($stmt->execute())  
							{
								$result = $stmt->get_result(); 
								while ($row = $result->fetch_assoc()) {
									$a = json_decode("{}",true);
									$a["orgName"] = $id[4];
									$a["questionID"] = $row["questionID"];
									$a["questionName"] = $row["name"];
									$e = json_decode($row["extra"],true);
									if(isset($e["createdAt"]))
									{
										$a["createdAt"] = json_decode($row["extra"],true)["createdAt"];
									}
									$a["subjectID"] = $id[1];
									$a["subjectName"] = $id[3];
									$a["topicID"] = $row["topic"];
									$a["topicName"] = $id[2];
									array_push($questions,$a);
								}
							}
						}

						$response["subjectIDs"] = $subjectIDs;
						$response["topicIDs"] = $topicIDs;
						$response["questions"] = $questions;
						echo json_encode($response);
						exit();
					}

				}
				else if(isset($apiPoint) && $apiPoint === "searchQuestion" && isset($URLParams[0]))
				{

				}
				else if($apiPoint === "checkValue")	{
					if(isset($_GET["answer"]) && (isset($_GET["questionID"]) || isset($_GET['sessionID'])) && isset($_GET["elementId"]))
					{
						header("StartedAPIPoint: $apiPoint");
						checkValue($_GET["elementId"]);						
					}
				}
				else if($apiPoint === "checkValues"){
					if(isset($_GET["answer"]) && (isset($_GET["questionID"]) || isset($_GET['sessionID'])))
					{
						checkValues();
					}
				}
				else if($apiPoint === "getQuestionResults" && isset($_GET["questionID"])){
					$out = json_decode("{}",true);
					if($isStaff)
					{
						$questionID = $_GET["questionID"];
						$sql = "SELECT * FROM `questions` WHERE `questionID` = '$questionID'";
						$f = false;
						$response["sql"] = $sql;
						$result = $conn->query($sql);
						while ($row = $result -> fetch_assoc())
						{
							if($isAdmin === 1 || $isAdmin || $row["userID"] == $userID)
							{
								$f = true;
							}
						}
						if($f)
						{						
							$questionID = $_GET["questionID"];
							$response["results"] = [];
							$sql = "SELECT * FROM `question_stats` WHERE `questionID` = '$questionID'";
							$result = $conn->query($sql);
							while ($row = $result -> fetch_assoc())
							{
								$t = json_decode("{}",true);
								$t["userID"] = $row['userID'];
								$sql2 = "SELECT * FROM `users` WHERE `id` = '". $row['userID'] . "'";
								$result2 = $conn->query($sql2);
								$nn = "";
								while ($row2 = $result2 -> fetch_assoc())
								{
									$nn = $row2['firstName'] . " " . $row2['lastName'];
								}
								$t['name'] = $nn;
								$t["data"] = json_decode($row["resultData"],true);
								array_push($response["results"], $t);
							}
						}
						else
						{
							$response["error"] = "Not valid question or do not have permissions to do this action";
							http_response_code(401);
						}
					}
					else
					{
						$response["error"] = "Do not have permissions to do this action";
						http_response_code(401);
					}
				}
				else if($apiPoint === "getAllOrgs")	{
					$sql = "SELECT * FROM `org` ORDER BY `name`";
					if($debug)
					{				
						$response["MSG"] = $sql;
					}
					$response["orgs"] = [];
					$result = $conn->query($sql);
					while ($row = $result -> fetch_assoc())
					{
						$c = json_decode("{}",true);
						$c["id"] = $row["id"];
						$c["name"] = $row["name"];
						array_push($response["orgs"],$c);
					} 
				}
				else if($apiPoint === "getAllData" && isset($URLParams[0]) && is_numeric($URLParams[0]))
				{
					if(inOrg($URLParams[0]))
					{
						$sql = "SELECT * FROM `subject` WHERE `org` = ". $URLParams[0]. " LIMIT 50";
						if($debug)
						{				
							$response["MSG"] = $sql;
						}
						$response["subjects"] = [];
						$response["topics"] = [];
						$response["questions"] = [];
						$response["data"] = json_decode("{}",true);
						$result = $conn->query($sql);
						while ($row = $result -> fetch_assoc())
						{
							$c = json_decode("{}",true);
							$c["id"] = $row["id"];
							$c["name"] = $row["name"];
							$response["data"][$c["name"]] = json_decode("{}",true);
							array_push($response["subjects"], $c);
							$sql2 = "SELECT * FROM `topic` WHERE `subject` = '". $c["id"] . "'";
							
							$result2 = $conn->query($sql2);
							while ($row2 = $result2 -> fetch_assoc())
							{							
								$c2 = json_decode("{}",true);
								$c2["id"] = $row2["id"];
								$c2["name"] = $row2["name"];
								$sql3 = "SELECT * FROM `questions` WHERE `topic` = '". $c2["id"] . "' AND `isPublic` = 1";
								$response["data"][$c["name"]][$c2["name"]] = [];
								array_push($response["topics"],$c2);
								$result3 = $conn->query($sql3);
								while ($row3 = $result3 -> fetch_assoc())
								{	
									$c3 = json_decode("{}",true);
									$c3["questionID"] = $row3["questionID"];
									$c3["name"] = $row3["name"];
									if($c3["name"] === null || $c3["name"] === "")
									{
										$c3["name"] = "No name set";
									}
									array_push($response["data"][$c["name"]][$c2["name"]],$c3);
									array_push($response["questions"],$c3);
								}
							}
						} 
					}
				}
				else if($apiPoint === "getOrgFromId" && isset($URLParams[0]))
				{
					$sql = "SELECT * FROM `org` WHERE `id` = ? LIMIT 50";
					if($debug)
					{				
						$response["MSG"] = $sql;
					}
					$response["subjects"] = [];
					$result = $conn->query($sql);
					while ($row = $result -> fetch_assoc())
					{
						$c = json_decode("{}",true);
						$c["id"] = $row["id"];
						$c["name"] = $row["name"];
						array_push($response["subjects"],$c);
					} 
				}
				else if($apiPoint === "getAllSubjects" && isset($URLParams[0]))
				{
					$stmt = $conn->prepare("SELECT * FROM `org` WHERE `id` =  ?");
					$stmt->bind_param("s", $URLParams[0]);
					$dup = false;
					$response["name"] = null;
					if ($stmt->execute())  
					{
						$result = $stmt->get_result(); 
						if($result->num_rows == 1) {   
							while ($row = $result->fetch_assoc())
							{
								$response["name"] = $row["name"];
							}
						}
					}
				}			
				else if($apiPoint === "getAllTopics" && isset($URLParams[0]))
				{
					$sql = "SELECT * FROM `topic` WHERE `subject` = ". $URLParams[0] . " LIMIT 50";
					if($debug)
					{				
						$response["MSG"] = $sql;
					}
					$response["topics"] = [];
					$result = $conn->query($sql);
					while ($row = $result -> fetch_assoc())
					{
						$c = json_decode("{}",true);
						$c["id"] = $row["id"];
						$c["name"] = $row["name"];
						array_push($response["topics"],$c);
					} 
				}
				else if($apiPoint === "getAllQuestions" && isset($URLParams[0]))
				{					
					$sql = "SELECT * FROM `questions` WHERE `topic` = ". $URLParams[0] . " LIMIT 50";
					if($debug)
					{				
						$response["MSG"] = $sql;
					}
					$response["questions"] = [];
					$result = $conn->query($sql);
					while ($row = $result -> fetch_assoc())
					{
						$c = json_decode("{}",true);
						//$c["id"] = $row["id"];
						$c["name"] = $row["name"];
						$c["questionID"] = $row["questionID"];
						array_push($response["questions"],$c);
					} 
				}
				else if($apiPoint === "getAllQuestionsOrg" && isset($URLParams[0]))
				{					
					$sql = "SELECT * FROM `questions` WHERE `topic` = ". $URLParams[0] . " LIMIT 50";
					if($debug)
					{				
						$response["MSG"] = $sql;
					}
					$response["questions"] = [];
					$result = $conn->query($sql);
					while ($row = $result -> fetch_assoc())
					{
						$c = json_decode("{}",true);
						//$c["id"] = $row["id"];
						$c["name"] = $row["name"];
						$c["questionID"] = $row["questionID"];
						array_push($response["questions"],$c);
					} 
				}
				else if($apiPoint === "getAllQuestionsUser")
				{
					if($isAdmin === 1 || $isAdmin)
					{
						$sql = "SELECT * FROM `questions` LIMIT 100";
					}
					else
					{
						$sql = "SELECT * FROM `questions` WHERE `userID` = ". $userID . " LIMIT 50";
					}
					if($debug)
					{				
						$response["MSG"] = $sql;
					}
					$response["questions"] = [];
					$result = $conn->query($sql);
					while ($row = $result -> fetch_assoc())
					{
						$raw = json_decode("{}",true);
						//$raw["id"] = $row["questionID"];
						//$raw["name"] = $row["name"];
						$raw["questionID"] = $row["questionID"];
						$raw["questionName"] = $row["name"];
						if(1 == 1) //isset($_COOKIE["showFileStructureProfile"]) && $_COOKIE["showFileStructureProfile"] == "1"
						{
							$raw["topicID"] = $row["topic"];
							try
							{
								$sqlGetTopic = "SELECT * FROM `topic` WHERE `id` = '" . $row['topic']. "'";
								$result3 = $conn->query($sqlGetTopic);
								while ($row3 = $result3 -> fetch_assoc())
								{
									$raw["topicName"] = $row3["name"];
									$sqlGetTopic = "SELECT * FROM `subject` WHERE `id` = '" . $row3['subject']. "'";
									$raw["subjectID"] = $row3['subject'];
									$result4 = $conn->query($sqlGetTopic);
									while ($row4 = $result4 -> fetch_assoc())
									{											
										$raw["subjectName"] = $row4['name'];
										$raw["org"] = $row4["org"];
										$sqlGetOrg = "SELECT * FROM `org` WHERE `id` = '" . $row4['org']. "'";
										$result5 = $conn->query($sqlGetOrg);
										while ($row5 = $result5 -> fetch_assoc())
										{												
											$raw["orgName"] = $row5["name"];
										}
									}
								}
							}
							catch(\Throwable $e){
								echo $e;
							}
						}
						



						array_push($response["questions"],$raw);
					} 
				}
				else if($apiPoint === "getCompletedQuestions"){
					$sql = "SELECT DISTINCT * FROM `question_stats` WHERE `userID` = '$userID' GROUP BY `questionID`;";
					$result = $conn->query($sql);
					$c = json_decode("[]",true);
					while ($row = $result -> fetch_assoc())
					{
						$raw = json_decode("{}",true);
						$sql2 = "SELECT * FROM `questions` WHERE `questionID` = '".$row["questionID"]."';";
						$result2 = $conn->query($sql2);
						while ($row2 = $result2 -> fetch_assoc())
						{
							$raw["questionName"] = $row2["name"];
							if(1 == 1)//isset($_COOKIE["showFileStructureProfile"]) && $_COOKIE["showFileStructureProfile"] == "1"
							{
								$raw["topicID"] = $row2["topic"];
								try
								{
									$sqlGetTopic = "SELECT * FROM `topic` WHERE `id` = '" . $row2['topic']. "'";
									$result3 = $conn->query($sqlGetTopic);
									while ($row3 = $result3 -> fetch_assoc())
									{
										$raw["topicName"] = $row3["name"];
										$sqlGetTopic = "SELECT * FROM `subject` WHERE `id` = '" . $row3['subject']. "'";
										$raw["subject"] = $row3['subject'];
										$result4 = $conn->query($sqlGetTopic);
										while ($row4 = $result4 -> fetch_assoc())
										{											
											$raw["subjectName"] = $row4['name'];
											$raw["org"] = $row4["org"];
											$sqlGetOrg = "SELECT * FROM `org` WHERE `id` = '" . $row4['org']. "'";
											$result5 = $conn->query($sqlGetOrg);
											while ($row5 = $result5 -> fetch_assoc())
											{												
												$raw["orgName"] = $row5["name"];
											}
										}
									}
								}
								catch(\Throwable $e){
									echo $e;
								}
							}
						}
						


						$raw["questionID"] = $row["questionID"];
						array_push($c,$raw);
					}
					$response["data"] = $c;
				}
				else if($apiPoint === "getUserStats")
				{
					$response["results"] = [];
					$sql = "SELECT * FROM `question_stats` WHERE `userID` = '$userID'";
					$result = $conn->query($sql);
					while ($row = $result -> fetch_assoc())
					{
						$t = json_decode("{}",true);
						$t["userID"] = $row['userID'];
						$sql = "SELECT `name` FROM `questions` WHERE `questionID` = '".$row["questionID"]."'";
						$result2 = $conn->query($sql);
						while ($row2 = $result2 -> fetch_assoc())
						{
							$t["questionName"] = $row2['name'];
						}
						$t['name'] = $name;
						$t['questionID'] = $row['questionID'];
						$t["data"] = json_decode($row["resultData"],true);
						array_push($response["results"], $t);
					}
				}
				else if($apiPoint === "getTasks")
				{
					$sql = "SELECT * FROM `tasks` WHERE `executor` = '$userID'";
					$getTasks = $conn->prepare("SELECT * FROM `tasks` WHERE `executor` = ?");
					$getTasks->bind_param("s", $userID);
					$allTasks = json_decode("[]",true);
					$response["tasks"] = [];
					$response["results"] = [];
					$response["sql"] = $sql;
					if ($getTasks->execute())  
					{
						$result = $getTasks->get_result(); 
						$response["RAN"] = true;
						while ($row = $result -> fetch_assoc())
						{
							if(isset($row["task"]))
							{
								$task = json_decode($row["task"],true);
								//echo $ii["questionID"];SELECT `name` FROM `questions` WHERE `questionID` = '3sBdjzfOx6AhWlRU' UNION SELECT `subject` as s FROM `topic` WHERE id = (SELECT `topic` FROM `questions` WHERE `questionID` = '3sBdjzfOx6AhWlRU') UNION SELECT `id` FROM `subject` WHERE id = s
								$getInfo = "SELECT * FROM `topic` WHERE id = (SELECT `topic` FROM `questions` WHERE `questionID` = '?')";
								$getInfo = $conn->prepare("SELECT q.name as questionName, q.questionID, t.id as topicId, t.name as topicName, t.subject as subjectId, s.id, s.name as subjectName, s.org as org, o.name as orgName FROM questions q, topic t, subject s, org o WHERE q.topic = t.id AND q.questionID = ? AND t.subject = s.id AND s.org = o.id; ");
								$getInfo->bind_param("s", $task["questionID"]);
								if ($getInfo->execute())  
								{
									$result2 = $getInfo->get_result(); 
									$response["RAN"] = true;
									while ($row2 = $result2 -> fetch_assoc())
									{
										$task["type"] = "task";
										$task["questionName"] = $row2["questionName"];
										$task["topicId"] = $row2["topicId"];										
										$task["topicName"] = $row2["topicName"];
										$task["subjectId"] = $row2["subjectId"];										
										$task["subjectName"] = $row2["subjectName"];								
										$task["orgName"] = $row2["orgName"];
										array_push($response["tasks"],$row2);
									}										
									$getName = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
									$getName->bind_param("s", $task["creator"]);
									if ($getName->execute())  
									{
										$result2 = $getName->get_result();
										if($result2->num_rows == 1) {   
											while ($row2 = $result2->fetch_assoc()) 
											{
												$task["creatorName"] = $row2['firstName'] . " " . $row2["lastName"];
											}
										}
									}
									
									array_push($response["results"],$task);	
								}
							}
						}
					}
					
					/*if ($getTasks->execute())  
					{
						$result = $getTasks->get_result(); 
						
						while ($row = $result -> fetch_assoc())
						{
							array_push($response["tasks2"],$row);
							if(isset($row["task"]))
							{
								$raw = json_decode($row["task"],true);
								//echo $ii["questionID"];
								$sql2 = "SELECT * FROM `questions` WHERE `questionID` = '".$raw["questionID"]."'";
								/*$result2 = $conn->query($sql2);
								while ($row2 = $result2 -> fetch_assoc())
								{							
									$raw["questionName"] = $row2["name"];
									if(1 == 1)//isset($_COOKIE["showFileStructureProfile"]) && $_COOKIE["showFileStructureProfile"] == "1"
									{
										$raw["topicID"] = $row2["topic"];
										try
										{
											$sqlGetTopic = "SELECT * FROM `topic` WHERE `id` = '" . $row2['topic']. "'";
											$result3 = $conn->query($sqlGetTopic);
											while ($row3 = $result3 -> fetch_assoc())
											{
												$raw["topicName"] = $row3["name"];
												$sqlGetTopic = "SELECT * FROM `subject` WHERE `id` = '" . $row3['subject']. "'";
												$raw["subjectID"] = $row3['subject'];
												$result4 = $conn->query($sqlGetTopic);
												while ($row4 = $result4 -> fetch_assoc())
												{											
													$raw["subjectName"] = $row4['name'];
													$raw["org"] = $row4["org"];
													$sqlGetOrg = "SELECT * FROM `org` WHERE `id` = '" . $row4['org']. "'";
													$result5 = $conn->query($sqlGetOrg);
													while ($row5 = $result5 -> fetch_assoc())
													{												
														$raw["orgName"] = $row5["name"];
													}
												}
											}
										}
										catch(\Throwable $e){
											echo $e;
										}
									}
								}*/
								/*
								$getName = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
								$getName->bind_param("s", $raw["creator"]);
								if ($getName->execute())  
								{
									$result = $getName->get_result();
									if($result->num_rows == 1) {   
										while ($row = $result->fetch_assoc()) 
										{
											$raw["creatorName"] = $row['firstName'] . " " . $row["lastName"];
										}
									}
								}

								$result = $conn->query($sql);
								array_push($response["tasks"],$raw);	
								if(count($response["tasks"]) > 20)
								{
									break;
								}
							}		
							
						}		
					}*/
					//$response["results"] = $allTasks;
				}
				else if($apiPoint === "getTasksQuestionID" && isset($_GET["questionID"]))
				{
					$questionID = $_GET["questionID"];
					$sql = "SELECT * FROM `tasks` WHERE JSON_VALUE(`task`,'$.questionID') = '$questionID'";
					$response["sql"] = $sql;
					$allTasks = json_decode("[]",true);
					$result = $conn->query($sql);
					while ($row = $result -> fetch_assoc())
					{
						$i = json_decode("{}",true);
						$i["id"] = $row["id"];
						$i["creator"] = $row["creator"];
						$i["executor"] = $row["executor"];
						$i["task"] = json_decode($row["task"],true);
						array_push($allTasks, $i);
					}
					$response["results"] = $allTasks;

					$sql = "SELECT `topic` FROM `questions` WHERE `questionID` = '$questionID'";
					$response["sql"] = $sql;

					$result = $conn->query($sql);
					while ($row = $result -> fetch_assoc())
					{
						$response["topic"] = $row["topic"];
						$sql2 = "SELECT `subject` FROM `topic` WHERE `id` = '".$row["topic"]."'";
						$response["sql"] = $sql2;

						$result2 = $conn->query($sql2);
						while ($row2 = $result2 -> fetch_assoc())
						{							
							$response["subject"] = $row2["subject"];

							$sql2 = "SELECT `org` FROM `subject` WHERE `id` = '".$row2["subject"]."'";
							$response["sql"] = $sql2;

							$result3 = $conn->query($sql2);
							while ($row3 = $result3 -> fetch_assoc())
							{	
								$response["org"] = $row3["org"];
							}
						}
					}
				}
				else if($apiPoint === "getAllItems")
				{
					$items = json_decode("{}",true);
					$response["userORGS"] = $userORG;
					$response["items"] = json_decode("{}",true);
					$response["itemsAll"] = json_decode("{}",true);
					$response["name"] = []; 
					$response["items"]["org"] = json_decode("[]",true);
					for ($i = 0; $i < count($response["userORGS"]["org"]); $i++)
					{
						$aa = json_decode("{}",true);
						$aa["name"] = $response["userORGS"]["org"][$i];
						$aa["id"] = $response["userORGS"]["org"][$i];
						$aa["subjects"] = json_decode("[]",true);
						array_push($response["items"]["org"], $aa);
						
						$sql = "SELECT * FROM `subject` WHERE `org` = '" . $response["userORGS"]["org"][$i] . "'";
						$response["sql"] = $sql;
						$result = $conn->query($sql);
						while ($row = $result -> fetch_assoc())
						{
							try
							{
								$ayy = json_decode("{}",true);
								$ayy["name"] = $row["name"];
								$ayy["id"] = (int)$row["id"];
								$ayy["topics"] = json_decode("[]",true);
								array_push($response["items"]["org"][$i]["subjects"], $ayy);
								
								//$response["items"]["org"][$i]["subject"] = 
								//$response["items"]["subjects"][$response["userORGS"]["org"][$i]]["topic"][$row["id"]]["name"] = 
								$s = "SELECT * FROM `topic` WHERE `subject` = '" . $row["id"] . "'";
								$c = 0;
								//array_push($response["name"], $row["name"]);
								$response["sql2"] = $s;
								$result = $conn->query($s);
								$v = 0;
								while ($row2 = $result -> fetch_assoc())
								{
									$ayy2 = json_decode("{}",true);
									$ayy2["org"] = (int)$response["userORGS"]["org"][$i];
									$ayy2["subjectID"] = (int)$row["id"];
									$ayy2["subjectName"] = $row["name"];
									$ayy2["topicID"] = (int)$row2["id"];
									$ayy2["topicName"] = $row2["name"];
									array_push($response["itemsAll"], $ayy2);
									array_push($response["items"]["org"][$i]["subjects"][$c]["topics"], $ayy2);

									$c += 1;
								}
							}
							catch(\Throwable $e)
							{
								$response["ERROR"] = $e;
							}
						}
					}

				}
				else if($apiPoint === "getAllBugs")
				{
					$sql = "SELECT * FROM `bugs`";
					$response["sql"] = $sql;
					$data = [];
					$result = $conn->query($sql);
					while ($row = $result -> fetch_assoc())
					{
						$raw = json_decode("{}",true);
						$raw["id"] = $row["id"];
						$raw["title"] = $row["title"];
						$raw["body"] = $row["body"];
						$raw["severity"] = $row["severity"];
						$raw["createdAt"] = $row["createdAt"];
						$raw["status"] = json_decode($row["status"],true);
						if($isAdmin)
						{							
							$raw["owner"] = (int)$row["owner"];
						}
						array_push($data,$raw);
					}
					$response["data"] = $data;
				}
				else if($apiPoint === "getBug" && isset($URLParams[0]) && is_numeric($URLParams[0]))
				{
					$sql = "SELECT * FROM `bugs` WHERE `id` = '" . $URLParams[0] . "'";
					$response["sql"] = $sql;
					$data = [];
					$msg = [];
					$result = $conn->query($sql);
					while ($row = $result -> fetch_assoc())
					{
						$raw = json_decode("{}",true);
						$raw["id"] = $row["id"];
						$raw["title"] = $row["title"];
						$raw["body"] = $row["body"];
						$raw["severity"] = $row["severity"];
						$raw["createdAt"] = $row["createdAt"];
						$raw["status"] = json_decode($row["status"]);
						$raw["owner"] = ((int)$row["owner"] == $userID);
						array_push($data,$raw);
					}

					
					$sql = "SELECT * FROM `communication` WHERE `linkedID` = '" . $URLParams[0] . "'";
					$result = $conn->query($sql);
					while ($row = $result -> fetch_assoc())
					{
						$raw = json_decode("{}",true);
						$raw["content"] = $row["content"];
						$raw["owner"] = $row["owner"];
						$raw["createdAt"] = $row["createdAt"];
						array_push($msg,$raw);
					}
					$response["data"] = $data;
					$response["msg"] = $msg;
				}
				else if($apiPoint === "getPerms")
				{
					$response["perm"] = $perms;
					$response["groups"] = $groups;
				}
				else if($apiPoint === "inDomain" && isset($URLParams[0]) && is_numeric($URLParams[0]) && isset($_GET["data"]))
				{
					try {
						$data = json_decode($_GET["data"],true);
						if(isset($perms[$data["org"]]))
						{
							if($perms[$data["org"]]["domain"][0] == "*")
							{
							}
							else
							{
							}
						}
					} catch (\Throwable $th) {
						//throw $th;
					}
				}
				else if($apiPoint === "getGroups")
				{
					$jj = json_decode("{}",true);
					$globalFound = false;
					foreach ($groups as $key => $value)
					{
						for($i = 0; $i < sizeof($groups[$key]); $i++)
						{
							//var_dump($groups[$key][$i]); 
							for($c = 0; $c < sizeof($groups[$key][$i]["domain"]); $c++)
							{
								if($groups[$key][$i]["domain"][$c]["target"] == "*")
								{
									if(!isset($jj[$key]))
									{
										$jj[$key] = json_decode("{}",true);
									}
									$sql = "SELECT * FROM `groups` WHERE `orgID` = '" . $key . "'";
									$response["sql"] = $sql;
									$result = $conn->query($sql);
									while ($row = $result -> fetch_assoc())
									{
										if(!in_array(json_decode($row["perm"],true), $jj[$key]))
										{
											array_push($jj[$key], json_decode($row["perm"],true));
										}
									}
									$globalFound = true;
								}
							}							
						}
					}
					if(!$globalFound)
					{
						$response["groups"] = $groups;
					}
					else
					{
						$response["groups"] = $jj;
					}
				}
				else if($apiPoint === "searchGroup" && isset($URLParams[0]) && is_numeric($URLParams[0]) && isset($_GET["name"]))
				{
					$sql = "SELECT * FROM `groups` WHERE `orgID` = '" . $URLParams[0] . "' AND JSON_VALUE(`perm`, '$.name') = '".$_GET["name"]."'";
					$response["sql"] = $sql;
					$result = $conn->query($sql);
					$response["used"] = false;	
					while ($row = $result -> fetch_assoc())
					{
						$response["used"] = true;
						break;
					}
				}
				else if ($apiPoint === "getUsers" && isset($URLParams[0]) && is_numeric($URLParams[0]))
				{
					$response["perms"] = $perms;
					if((isset($perms[$URLParams[0]]) && isset($perms[$URLParams[0]]["roles"]) && (in_array("orgAdmin",$perms[$URLParams[0]]["roles"]) || in_array("admin",$perms[$URLParams[0]]["roles"]))) || $isAdmin)
					{
						$sql = "SELECT * FROM `users`";
						$response["sql"] = $sql;
						$response["data"] = [];
						$result = $conn->query($sql);
						while ($row = $result -> fetch_assoc())
						{
							$v = json_decode($row["org"],true)["org"];
							if(in_array($URLParams[0],$v))
							{
								$aa = json_decode("{}",true);	
								$aa["id"] = $row["id"];
								$aa["name"] = $row["firstName"] . " " . $row["lastName"];	
								array_push($response["data"],$aa);
							}
						}
					}
					else
					{
						$response["msg"] = "don't have permissions to acess this data";
						http_response_code(401);
					}
				}
				else if($apiPoint === "getUserPerms" && isset($URLParams[0]) && is_numeric($URLParams[0]) && isset($URLParams[1]) && is_numeric($URLParams[1]))
				{
					if((isset($perms[$URLParams[0]]) && isset($perms[$URLParams[0]]["roles"]) && (in_array("orgAdmin",$perms[$URLParams[0]]["roles"]) || in_array("admin",$perms[$URLParams[0]]["roles"]))) || $isAdmin)
					{
						$sql = "SELECT * FROM `perms` WHERE `userID` = '" . $URLParams[1] . "'";
						$response["sql"] = $sql;
						$response["data"] = [];
						$result = $conn->query($sql);
						while ($row = $result -> fetch_assoc())
						{
							$v = json_decode($row["perm"],true);
							if(isset($v[$URLParams[0]]))
							{
								array_push($response["data"],$v[$URLParams[0]]);	
							}						
						}
					}
				}
				else if($apiPoint === "getChatUser" && isset($_GET["questionID"]) && isset($_GET['variableID']))
				{
					$questionID = $_GET["questionID"];
					$variableID = $_GET["variableID"];
					if($questionOrgInfo != null)
					{	
						if(hasRole('educator',$questionOrgInfo) || hasRole('admin',$questionOrgInfo) || hasRole('orgAdmin',$questionOrgInfo))
						{
							$stmt = $conn->prepare("SELECT * FROM `chat` WHERE json_VALUE(`data`,'$.questionID') = ? AND json_VALUE(`data`,'$.variableID') = ?");
							$stmt->bind_param("ss", $questionID,$variableID,);
							$more = true;
							$response["chats"] = json_decode("[]");
							if ($stmt->execute())  
							{
								$result = $stmt->get_result();   
								while ($row = $result->fetch_assoc()) {
									$stmt2 = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
									$stmt2->bind_param("s", $row['messageCreator']);
									$data = json_decode("{}",true);
									if($more === false)
									{
										$rowData = json_decode($row["data"],true);
										if($rowData !== null)
										{
											$data["content"] = $rowData["content"];
											$data["messageCreator"] = $rowData["messageCreator"];
										}
									}
									else
									{
										$data = json_decode($row["data"]);
									}
									array_push($response["chats"], $data);
								}						
							}
						}
						
					}
				}
				else if($apiPoint === "getChat" && isset($_GET["questionID"]) && isset($_GET['variableID']))
				{
					$questionID = $_GET["questionID"];
					$variableID = $_GET["variableID"];
					if($questionOrgInfo != null)
					{	
						$searchUser = null;		
						if(isset($_GET['searchUser']))
						{
							$searchUser = $_GET['searchUser'];
						}
						$more = false;		
						if(hasRole('educator',$questionOrgInfo) || hasRole('admin',$questionOrgInfo) || hasRole('orgAdmin',$questionOrgInfo))
						{
							if($searchUser !== null)
							{
								$messageID = base64_encode($_GET["questionID"] . $_GET['variableID'] . $searchUser);
								$stmt = $conn->prepare("SELECT JSON_QUERY(`data`,'$.messageChain') as 'messageChain' FROM `chat` WHERE (json_VALUE(`data`,'$.messageID') = ?)");
								$stmt->bind_param("s", $messageID);
								$more = true;
							}
							else
							{
								$stmt = $conn->prepare("SELECT JSON_QUERY(`data`,'$.messageChain') as 'messageChain'  FROM `chat` WHERE json_VALUE(`data`,'$.messageCreator') = ? AND json_VALUE(`data`,'$.questionID') = ? AND json_VALUE(`data`,'$.variableID') = ?");
								$stmt->bind_param("sss", $userID, $questionID,$variableID);
							}
						}
						else
						{
							if($searchUser == null)
							{
								$stmt = $conn->prepare("SELECT JSON_QUERY(`data`,'$.messageChain') as 'messageChain'  FROM `chat` WHERE json_VALUE(`data`,'$.messageCreator') = ? AND json_VALUE(`data`,'$.questionID') = ? AND json_VALUE(`data`,'$.variableID') = ?");
								$stmt->bind_param("sss", $userID, $questionID,$variableID);
							}
							else
							{
								$messageID = base64_encode($_GET["questionID"] . $_GET['variableID'] . $searchUser);
								$stmt = $conn->prepare("SELECT JSON_QUERY(`data`,'$.messageChain') as 'messageChain' FROM `chat` WHERE (json_VALUE(`data`,'$.messageID') = ?)");
								$stmt->bind_param("s", $messageID);
							}
						}
						$response["chats"] = json_decode("[]");
						if ($stmt->execute())  
						{
							$result = $stmt->get_result();   
							while ($row = $result->fetch_assoc()) {
								$stmt2 = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
								$stmt2->bind_param("s", $row['messageCreator']);
								$data = json_decode("[]",true);
								if($more === false)
								{
									$rowData = json_decode($row["messageChain"],true);
									if($rowData !== null)
									{
										for($v = 0; $v < sizeof($rowData); $v++)
										{
											$item = json_decode("{}",true);
											$item["content"] = $rowData[$v]["content"];
											$item["messageCreator"] = $rowData[$v]["messageCreator"];
											array_push($response["chats"], $item);
										}										
									}
								}
								else
								{
									$data = json_decode($row["messageChain"],true);
									if($data !== null)
									{
										for($v = 0; $v < sizeof($data); $v++)
										{
											array_push($response["chats"], $data[$v]);
										}										
									}
								}
								//array_push($response["chats"], $data);
							}						
						}
					}
					else
					{
						$response["error"] = true;
						$response["msg"] = "Cant find question";
					}
				}
				else if($apiPoint === "getChatStaff" && isset($_GET["questionID"]))
				{
					$questionID = $_GET["questionID"];
					$variableID = $_GET["variableID"];
					if($questionOrgInfo != null)
					{	
						$searchUser = null;		
						if(isset($_GET['searchUser']))
						{
							$searchUser = $_GET['searchUser'];
						}
						$more = false;		
						if(hasRole('educator',$questionOrgInfo) || hasRole('admin',$questionOrgInfo) || hasRole('orgAdmin',$questionOrgInfo))
						{
							if($searchUser !== null)
							{
								$stmt = $conn->prepare("SELECT JSON_QUERY(`data`,'$.messageChain') as 'messageChain' FROM `chat` WHERE json_VALUE(`data`,'$.questionID') = ? AND json_VALUE(`data`,'$.variableID') = ? AND (json_VALUE(`data`,'$.messageCreator') = ? OR json_VALUE(`data`,'$.messageCreator') = ?)");
								$stmt->bind_param("ssss", $questionID,$variableID,$searchUser, $userID);
								$more = true;
							}
							else
							{
								$stmt = $conn->prepare("SELECT JSON_QUERY(`data`,'$.messageChain') as 'messageChain'  FROM `chat` WHERE json_VALUE(`data`,'$.messageCreator') = ? AND json_VALUE(`data`,'$.questionID') = ? AND json_VALUE(`data`,'$.variableID') = ?");
								$stmt->bind_param("sss", $userID, $questionID,$variableID);
							}
						}
						else
						{
							if($searchUser == null)
							{
								$stmt = $conn->prepare("SELECT JSON_QUERY(`data`,'$.messageChain') as 'messageChain'  FROM `chat` WHERE json_VALUE(`data`,'$.messageCreator') = ? AND json_VALUE(`data`,'$.questionID') = ? AND json_VALUE(`data`,'$.variableID') = ?");
								$stmt->bind_param("sss", $userID, $questionID,$variableID);
							}
							else
							{
								$stmt = $conn->prepare("SELECT JSON_QUERY(`data`,'$.messageChain') as 'messageChain'  FROM `chat` WHERE json_VALUE(`data`,'$.messageCreator') = ? AND json_VALUE(`data`,'$.questionID') = ? AND json_VALUE(`data`,'$.variableID') = ?");
								$stmt->bind_param("sss", $searchUser, $questionID,$variableID);
							}
						}
						$response["chats"] = json_decode("[]");
						if ($stmt->execute())  
						{
							$result = $stmt->get_result();   
							while ($row = $result->fetch_assoc()) {
								$stmt2 = $conn->prepare("SELECT * FROM `users` WHERE `id` = ?");
								$stmt2->bind_param("s", $row['messageCreator']);
								$data = json_decode("[]",true);
								if($more === false)
								{
									$rowData = json_decode($row["messageChain"],true);
									if($rowData !== null)
									{
										for($v = 0; $v < sizeof($rowData); $v++)
										{
											$item = json_decode("{}",true);
											$item["content"] = $rowData[$v]["content"];
											$item["messageCreator"] = $rowData[$v]["messageCreator"];
											array_push($response["chats"], $item);
										}										
									}
								}
								else
								{
									$data = json_decode($row["messageChain"],true);
									if($data !== null)
									{
										for($v = 0; $v < sizeof($data); $v++)
										{
											array_push($response["chats"], $data[$v]);
										}										
									}
								}
								//array_push($response["chats"], $data);
							}						
						}
					}
					else
					{
						$response["error"] = true;
						$response["msg"] = "Cant find question";
					}
				}
				else if($apiPoint === "getChatVars" && isset($_GET['questionID']))
				{
					if(hasRole('educator',$questionOrgInfo) || hasRole('admin',$questionOrgInfo) || hasRole('orgAdmin',$questionOrgInfo))
					{
						$stmt = $conn->prepare("SELECT JSON_VALUE(`data`,'$.variableID') as 'variableID' FROM `chat` WHERE json_VALUE(`data`,'$.questionID') = ?");
						$stmt->bind_param("s", $_GET['questionID']);
						$more = true;
						$response['variableIDs'] = json_decode("[]",true);
						if ($stmt->execute())  
						{
							$result = $stmt->get_result();   
							while ($row = $result->fetch_assoc()) 
							{
								array_push($response['variableIDs'], $row['variableID']);
							}
						}
					}
				}
				else if($apiPoint === "getChatAll")
				{	
					$response["chats"] = json_decode("{}",true);

					if(hasRole('educator',-1))
					{
						$stmt = $conn->prepare("SELECT * FROM `chat`");
						$more = true;
					
						if ($stmt->execute())  
						{
							$result = $stmt->get_result();  
							while ($row = $result->fetch_assoc()) {
								//var_dump($row);
								$data = json_decode($row["data"],true);
								
								if(isset($data['org']))
								{
									if(!isset($response["chats"][$data['org']]))
									{
										$response["chats"][$data['org']] = json_decode("[]",true);
									}						
									array_push($response["chats"][$data['org']], json_decode($row["data"]));
								}
							}						
						}
					}
					else
					{
						foreach ($perms as $org => $value)
						{
							$response["chats"][$org] = json_decode("[]",true);
							if(hasRole('educator',$org) || hasRoleAnyDomain('admin',$org) || hasRoleAnyDomain('orgAdmin',$org))
							{
								
								$stmt = $conn->prepare("SELECT * FROM `chat` WHERE JSON_VALUE(`data`,'$.org') = ?");
								$stmt->bind_param("s", $org);
								$more = true;
							
								if ($stmt->execute())  
								{
									$result = $stmt->get_result();  
									while ($row = $result->fetch_assoc()) {
										$data = json_decode("{}",true);
										$data = $row["data"];									
										array_push($response["chats"][$org], json_decode($data));
									}						
								}
							}
						}
					}
				}
				else if($apiPoint === "getSessionInfo" && isset($_GET["sessionID"])){
					$sessionID = $_GET['sessionID'];				
					$stmt = $conn->prepare("SELECT * FROM `sessions` WHERE JSON_VALUE(`data`,'$.sessionID') = ?");
					$stmt->bind_param("s", $sessionID);
					$found = false;
					$rowID = null;
					if ($stmt->execute())  
					{
						$result = $stmt->get_result(); 
						while ($row = $result->fetch_assoc()) {
							if(json_decode($row['data'],true)["validUntil"] >= strtotime(gmdate('r', time())))
							{
								$rowID = $row['id'];
								$sessionStats = json_decode($row['data'],true);
								$questionIDs = $sessionStats['questionIDs'];
								$found = true;
								$response['sessionStats'] = $sessionStats;
							}
						}									
					}				
				}
				else if($apiPoint === "getCurrentSessions")
				{
					$stmt = $conn->prepare("SELECT * FROM `sessions` WHERE JSON_VALUE(`data`,'$.creatorID') = ?");
					$stmt->bind_param("s", $userID);
					$found = false;
					$rowID = null;
					$response["sessionIDs"] = [];
					if ($stmt->execute())  
					{
						$result = $stmt->get_result(); 
						while ($row = $result->fetch_assoc()) {
							if(json_decode($row['data'],true)["validUntil"] >= strtotime(gmdate('r', time())))
							{
								array_push($response["sessionIDs"], [json_decode($row['data'],true)["sessionID"],json_decode($row['data'],true)["shortCode"]]);
							}
						}
					}
				}
				/*
					SET/UPDATE API'S
				*/
				else if($apiPoint === "updateQuestion")
				{
					if(isset($_POST["questionID"]) && $_POST["questionID"] != "undefined")
					{
						if(isset($_POST["value"]) && isset($_POST["answer"]) && isset($_POST["questionID"]) && $_POST["questionID"] != null && strlen($_POST["questionID"]) > 15 && isset($_POST["translationTable"]))
						{
							$v = urldecode($_POST["value"]);
							$answer = urldecode($_POST["answer"]);
							$questionID = $_POST["questionID"];
							$translationTable = $_POST["translationTable"];
							$checker = "SELECT * FROM `questions` WHERE `questionID` = '$questionID'";
							$result = $conn->query($checker);
							$Subf = false;
							$canChange = false;
							$localUserID = $userID;
							//echo $localUserID;
							//echo $checker;
							while ($row = $result -> fetch_assoc())
							{
								//echo "AAAADad";
								if(($isAdmin || $isAdmin == 1) && $userID != $row["userID"])
								{
									$localUserID = $row["userID"];
									$canChange = true;
								}
								if($userID == $row["userID"] || $isAdmin === 1 || $isAdmin)
								{
									$canChange = true;
								}
								//echo $userID;
								//echo $row["userID"];
								$Subf = true;
								break;
							}
							if($canChange || $isAdmin === 1 || $isAdmin)
							{
								$response["size"] = strlen($v);
								if(strlen($v) > 1000000)
								{				
									$v1 = substr($v,0,1000000);
									$v2 = substr($v,1000000);
									if(!$Subf)
									{
										$sql = "INSERT INTO `questions`(`questionID`, `questionSubID`, `userID`, `question`) VALUES ('xxxxxx','$questionID','$localUserID','$v2')";
										$result = $conn->query($sql);
									}
									else
									{
										$sql = "UPDATE `questions` SET `question`='$v2' WHERE `questionSubID` = '$questionID'";
										$result = $conn->query($sql);
									}
									$sql = "UPDATE `questions` SET `question`='$v1' WHERE `questionID` = '$questionID'";
									$result = $conn->query($sql);
								}
								else
								{
									$sql = "UPDATE `questions` SET `question`='$v' WHERE `questionID` = '$questionID'";
									$response["sql"] = $sql;
									$result = $conn->query($sql);

									$sql = "UPDATE `questions` SET `question`='' WHERE `questionSubID` = '$questionID'";
									$result = $conn->query($sql);
								}
								//echo $sql;

								$sql = "UPDATE `questions` SET `answer` = '$answer' WHERE `questionID` = '$questionID'";					
								//$response["sql1"] = $sql;
								$result = $conn->query($sql);

								$sql = "UPDATE `questions` SET `translationTable` = '$translationTable' WHERE `questionID` = '$questionID'";
								//$response["sql2"] = $sql;
								$result = $conn->query($sql);
							}
							else
							{
								$response["error"] = "Do not have permissions to do this action";
								http_response_code(401);
							}
						}
						else{
							$response["error"] = "Not valid data provided";
							http_response_code(401);
						}
					}
					else
					{						
						$response["error"] = "Invalid question ID given";
						http_response_code(401);
					}
				}
				else if($apiPoint === "updateSession" && isset($_GET['sessionID']) && isset($_GET["data"])){
					$sessionID = $_GET['sessionID'];					
					$jsonData = json_decode($_GET["data"],true);
					$stmt = $conn->prepare("SELECT * FROM `sessions` WHERE JSON_VALUE(`data`,'$.sessionID') = ?");
					$stmt->bind_param("s", $sessionID);
					$found = false;
					$rowID = null;
					$sessionResetCount = 0;
					if ($stmt->execute())  
					{
						$result = $stmt->get_result(); 
						while ($row = $result->fetch_assoc()) {
							if(json_decode($row['data'],true)["validUntil"] >= strtotime(gmdate('r', time())))
							{
								$rowID = $row['id'];
								$sessionStats = json_decode($row['data'],true);
								$questionIDs = $sessionStats['questionIDs'];
								$found = true;
								if(isset($sessionStats["instanceID"]))
								{
									$instanceId = $sessionStats["instanceID"];
								}
								if(isset($sessionStats["useBaseValues"]))
								{
									$randomActive = !$sessionStats["useBaseValues"];
								}
								if($sessionStats["creatorID"] == $userID)
								{
									$sessionOwner = true;
								}
								$sessionResetCount = $sessionStats["sessionResetCount"];
							}
						}									
					}
					if($found && $sessionOwner && $rowID !== null)
					{
						$presentationMode = true;
						$response['sessionOwner'] = $sessionOwner;
						if($jsonData['useBaseValues'] !== $sessionStats["useBaseValues"])
						{
							$sessionStats["instanceID"] = rand(100000,999999);
						}
						$sessionStats["useBaseValues"] = $jsonData['useBaseValues'];
						$sessionStats["questionIDs"] = $jsonData['data'];
						$sessionStats["sessionResetCount"] = $sessionResetCount + 1;
						$response['ddd'] = $jsonData['data'];
						$sessionStats = json_encode($sessionStats);
						$response['id'] = $rowID;
						$stmt = $conn->prepare("UPDATE `sessions` SET `data` = ? WHERE `id` = ?");
						$stmt->bind_param("ss", $sessionStats, $rowID);
						$found = false;
						if ($stmt->execute())  
						{
						}
					}
					else
					{
						$response["msg"] = 'sessionID not found or expired';
						$response['sessionID'] = $sessionID;
						echo json_encode($response);
						exit();
					} 
				}
				else if($apiPoint === "setQuestionName")
				{
					//if(hasRole("orgAdmin",$firstOrg) || hasRole("educator",$firstOrg) || hasRole("admin",$firstOrg))
					//{
						$name = $_GET["name"];		
						$questionID = $_GET["questionID"];
						$canChange = false;
						if($isQOwner || $isAdmin)
						{
							$canChange = true;
						}
						else
						{
							$org = getOrgFromQuestionID($questionID);
							$response["domain"] = inDomain($questionID, $org);
							$response["role"] = (hasRole("educator", $org) || hasRole("admin", $org) || hasRole("orgAdmin", $org));
							if((hasRole("educator", $org) || hasRole("admin", $org) || hasRole("orgAdmin", $org)) && inDomain($questionID, $org))
							{
								$canChange = true;
							}
						}
						if($canChange)
						{
							if($isAdmin)
							{
								$sql = "UPDATE `questions` SET `name` = '$name' WHERE `questionID` = '$questionID'";
							}
							else
							{
								$sql = "UPDATE `questions` SET `name` = '$name' WHERE `questionID` = '$questionID' AND `userID` = '$userID'";
							}
							$result = $conn->query($sql);
							$response["SQL"] =  $sql;
						}
						else
						{					
							$response["error"] = "Do not have permissions to do this action";
							http_response_code(401);
						}
					/*}
					else
					{					
						$response["error"] = "Do not have permissions to do this action";
						http_response_code(401);
					}*/
				}
				else if($apiPoint === "setQuestionTopic")
				{
					if($isStaff)
					{
						if(isset($_GET["newT"]) && isset($_GET["questionID"]))
						{
							/*
							$questionID = $_GET["questionID"];
							$valid = false;
							if(!$isAdmin)
							{
								$checkOS = "SELECT * FROM `questions` WHERE `userID` = '$userID' AND `questionID` = '$questionID'";
								$result = $conn->query($checkOS);
								while ($row = $result -> fetch_assoc())
								{
									$valid = true;
								}
							}
							else{								
								$valid = true;
							}
							if($valid)
							{*/
							if($isQOwner)
							{
								$newT = $_GET["newT"];
								$checkT = "SELECT * FROM `topic` WHERE `id` = '$newT'";
								$result = $conn->query($checkT);
								
								$response["SQL"] = $checkT;
								$valid = false;
								$response["msg"] = "VALID OS";
								while ($row = $result -> fetch_assoc())
								{
									$valid = true;
								}
								if($valid)
								{
									$sql = "UPDATE `questions` SET `topic`='$newT' WHERE `questionID` = '$questionID'";
									$result = $conn->query($sql);
								}
							}
						}
						else
						{
							$response["error"] = "Not all information provided";
							http_response_code(404);
						}
					}
					else
					{
						http_response_code(404);
					}
				}
				else if($apiPoint === "setQuestionOrg")
				{					
					if(isset($_GET["newO"]) && isset($_GET["questionID"]) && inOrg($_GET["newO"]))
					{
						if(hasRole("educator", $_GET["newO"]) || hasRole("orgAdmin",$_GET["newO"]) || hasRole("admin",$_GET["newO"]))
						{
							if($isQOwner)
							{
								$newT = $_GET["newO"];
								$checkT = "SELECT * FROM `org` WHERE `id` = '$newT'";
								$result = $conn->query($checkT);
								
								$response["SQL"] = $checkT;
								$valid = false;
								$response["msg"] = "VALID OS";
								while ($row = $result -> fetch_assoc())
								{
									$valid = true;
								}
								if($valid)
								{
									$sql = "UPDATE `questions` SET `org`='$newT' WHERE `questionID` = '$questionID'";
									$result = $conn->query($sql);
								}
							}
						}
						else
						{
							$response["error"] = "Not all information provided";
							http_response_code(404);
						}
					}
					else
					{
						http_response_code(404);
					}
				}
				else if($apiPoint === "setQuestionOST")
				{
					if($isStaff)
					{
						if(isset($_GET["newOST"]))
						{
							try
							{
								$newOST = json_decode($_GET["newOST"],true);
								if(isset($newOST["O"]) && isset($newOST["S"]) && isset($newOST["T"]))
								{
									$newO = $newOST["O"];
									$newS = $newOST["S"];
									$newT = $newOST["T"];

									$checkOS = "SELECT * FROM `subject` WHERE `id` = '$newS' AND `org` = '$newO'";
									$result = $conn->query($checkOS);
									$valid = false;
									while ($row = $result -> fetch_assoc())
									{
										$valid = true;
									}

									

									if($valid)
									{
										$checkT = "SELECT * FROM `topic` WHERE `id` = '$newT' AND `subject` = '$newS'";
										$result = $conn->query($checkT);
										
										$response["SQL"] = $checkT;
										$valid = false;
										$response["msg"] = "VALID OS";
										while ($row = $result -> fetch_assoc())
										{
											$valid = true;
										}
										if($valid)
										{
											$response["msg"] = "Changed OST";
											http_response_code(200);
										}else
										{
											$response["error"] = "OST is not valid";
											http_response_code(404);
										}
									}
									else
									{
										$response["error"] = "OST is not valid";
										http_response_code(404);
									}
								}
								else
								{
									$response["error"] = "Didn't provid the needed vars";
									http_response_code(503);
								}
							}
							catch(\Throwable $e)
							{
								$response["error"] = "Didn't provid a JSON OBJECT";
								http_response_code(503);
							}
							$sql = "SELECT * FROM `topic` WHERE `id` = '";
						}
						else
						{
							$response["error"] = "Didn't provid a JSON OBJECT";
							http_response_code(503);
						}
					}
					else
					{					
						$response["error"] = "Do not have permissions to do this action";
						http_response_code(401);
					}
				}
				else if($isStaff && $apiPoint === "makeQuestionPublic" && isset($_GET["questionID"]))
				{
					$questionID = $_GET["questionID"];
					//ALTER TABLE `questions` ADD `isPublic` BOOLEAN NOT NULL AFTER `translationTable`; 
					if($isAdmin)
					{						
						$sql = "UPDATE `questions` SET `isPublic`='1' WHERE `questionID` = '$questionID'";
						$result = $conn->query($sql);
					}
					else
					{						
						$sql = "UPDATE `questions` SET `isPublic`='1' WHERE `questionID` = '$questionID' AND `userID` = '$userID'";
						$result = $conn->query($sql);
					}
					
				}
				else if($isStaff && $apiPoint === "makeQuestionPrivate" && isset($_GET["questionID"]))
				{
					$questionID = $_GET["questionID"];
					//ALTER TABLE `questions` ADD `isPublic` BOOLEAN NOT NULL AFTER `translationTable`; 
					if($isAdmin)
					{						
						$sql = "UPDATE `questions` SET `isPublic`='0' WHERE `questionID` = '$questionID'";
						$result = $conn->query($sql);
					}
					else
					{						
						$sql = "UPDATE `questions` SET `isPublic`='0' WHERE `questionID` = '$questionID' AND `userID` = '$userID'";
						$result = $conn->query($sql);
					}
					
				}
				else if($apiPoint === "resolveBug" && isset($_GET["bugID"]) && is_numeric($_GET["bugID"])){
					$sql = "";
					if($isAdmin)
					{
						$sql = "SELECT * FROM `bugs` WHERE `id` = '" . $_GET["bugID"] . "'";
						//echo $sql."\n<br>";
					}
					else
					{
						$sql = "SELECT * FROM `bugs` WHERE `id` = '" . $_GET["bugID"] . "' AND `owner` = '" . $userID . "'";
					}
					$response["sql"] = $sql;
					//echo $sql;
					$result = $conn->query($sql);
					//var_dump($result);
					while ($row = $result -> fetch_assoc())
					{
						//var_dump($row);
						$status = json_decode($row["status"],true);
						$status["active"] = false;
						$status["resolved"] = true;
						$sql = "UPDATE `bugs` SET `status`='".json_encode($status)."' WHERE `id` = '" . $_GET["bugID"] ."'";
						$response["sql"] = $sql;
						$result2 = $conn->query($sql);
					}
				}
				else if($apiPoint === "reopenBug" && isset($_GET["bugID"]) && is_numeric($_GET["bugID"])){
					$sql = "";
					if($isAdmin)
					{
						$sql = "SELECT * FROM `bugs` WHERE `id` = '" . $_GET["bugID"] . "'";
						//echo $sql."\n<br>";
					}
					else
					{
						$sql = "SELECT * FROM `bugs` WHERE `id` = '" . $_GET["bugID"] . "' AND `owner` = '" . $userID . "'";
					}
					$response["sql"] = $sql;
					//echo $sql;
					$result = $conn->query($sql);
					//var_dump($result);
					while ($row = $result -> fetch_assoc())
					{
						//var_dump($row);
						$status = json_decode($row["status"],true);
						$status["active"] = true;
						$status["resolved"] = false;
						$sql = "UPDATE `bugs` SET `status`='".json_encode($status)."' WHERE `id` = '" . $_GET["bugID"] ."'";
						$response["sql"] = $sql;
						$result2 = $conn->query($sql);
					}
				}
				/*
					CREATE API's
				*/
				else if($apiPoint === "newQuestionID"){
					if($isStaff || hasRole("orgAdmin",$firstOrg) || hasRole("educator",$firstOrg) || hasRole("admin",$firstOrg))
					{
						$tempID = random_strings(16);
						$sql = "SELECT * FROM `questions` WHERE `questionID` = '$tempID'";
						$result = $conn->query($sql);
						
						$response["sql"] = $sql;
						$notDup = true;
						while ($row = $result -> fetch_assoc())
						{
							$notDup = false;
							$response["error"] = true;
							break;
						}
						if($notDup === true)
						{
							$sql = "INSERT INTO `questions`(`questionID`,`questionSubID`,`userID`,`translationTable`,`topic`) VALUES ('$tempID','','$userID','{}',1)";
							$response["sql"] = $sql;
							$result = $conn->query($sql);
							$response["questionID"] = $tempID;
						}
					}
					else
					{
						$response["error"] = "Do not have permission";
						http_response_code(401);
					}
				}
				else if($apiPoint === "createTaskForStudent" && isset($_GET["executor"]) && isset($_GET["questionID"]) && isset($_GET["dueBy"])){
					if($isStaff)
					{
						$executor = $_GET["executor"];
						$questionID = $_GET["questionID"];
						$task = json_decode("{}",true);
						$task["questionID"] = $questionID;
						$task["creator"] = $userID;
						$task["executor"] = $executor;
						$task["createdAt"] = time();
						$task["dueBy"] = (int)$_GET["dueBy"];
						$task["done"] = false;
						$task["disabled"] = false;
						if($task["dueBy"] < $task["createdAt"])
						{								
							$response["error"] = "Can't set due day to be less then current time";
							http_response_code(500);
						}
						else
						{
							$sql = "SELECT * FROM `users` WHERE `id` = '".$executor."'";
							$result = $conn->query($sql);
							$valid = false;
							while ($row = $result -> fetch_assoc())
							{
								$valid = true;
								break;
							}
							if($valid)
							{
								$sql = "SELECT * FROM `tasks` WHERE `executor` = '$executor'";
								$result = $conn->query($sql);
								$valid = true;
								//echo $sql;
								while ($row = $result -> fetch_assoc())
								{
									$d = json_decode($row["task"],true);
									//echo $d["questionID"];
									if($d["questionID"] == $questionID)
									{
										$response["error"] = "Already have a assigned that task to user";
										http_response_code(401);
										$valid = false;
									}
								}
								if($valid)
								{
									$sql = "INSERT INTO `tasks`(`creator`, `executor`, `task`) VALUES ('$userID','$executor','".json_encode($task)."')";
									$result = $conn->query($sql);
									$response["msg"] = "Created task";
								}
								//$sql = "INSERT INTO `tasks`(`creator`, `executor`, `task`) VALUES ('$userID','$executor','".json_encode($task)."')";
							}
							else
							{
								$response["error"] = "User was not found";
								http_response_code(404);
							}
							if($isDev)
							{
								$task["sql"] = $sql;
							}
							$response["data"] = $task;
						}
					}
					else
					{					
						$response["error"] = "Do not have permissions to do this action";
						http_response_code(401);
					}
				}
				else if($apiPoint === "createTaskForStudent" && isset($_GET["questionID"]) && isset($_GET["dueBy"]))
				{
					if($isStaff)
					{
						$executor = $userID;
						$questionID = $_GET["questionID"];
						$task = json_decode("{}",true);
						$task["questionID"] = $questionID;
						$task["creator"] = $userID;
						$task["executor"] = $executor;
						$task["createdAt"] = time();
						$task["dueBy"] = (int)$_GET["dueBy"];
						$task["done"] = false;
						$task["disabled"] = false;
						if($task["dueBy"] < $task["createdAt"])
						{								
							$response["error"] = "Can't set due day to be less then current time";
							http_response_code(500);
						}
						else
						{
							$sql = "SELECT * FROM `users` WHERE `id` = '".$executor."'";
							$result = $conn->query($sql);
							$valid = false;
							while ($row = $result -> fetch_assoc())
							{
								$valid = true;
								break;
							}
							if($valid)
							{
								$sql = "SELECT * FROM `tasks` WHERE `executor` = '$executor'";
								$result = $conn->query($sql);
								$valid = true;
								//echo $sql;
								while ($row = $result -> fetch_assoc())
								{
									$d = json_decode($row["task"],true);
									//echo $d["questionID"];
									if($d["questionID"] == $questionID)
									{
										$response["error"] = "Already have a assigned that task to user";
										http_response_code(401);
										$valid = false;
									}
								}
								if($valid)
								{
									$sql = "INSERT INTO `tasks`(`creator`, `executor`, `task`) VALUES ('$userID','$executor','".json_encode($task)."')";
									$result = $conn->query($sql);
									$response["msg"] = "Created task";
								}
								//$sql = "INSERT INTO `tasks`(`creator`, `executor`, `task`) VALUES ('$userID','$executor','".json_encode($task)."')";
							}
							else
							{
								$response["error"] = "User was not found";
								http_response_code(404);
							}
							if($isDev)
							{
								$task["sql"] = $sql;
							}
							$response["data"] = $task;
						}
					}
					else
					{					
						$response["error"] = "Do not have permissions to do this action";
						http_response_code(401);
					}
				}
				else if ($apiPoint === "reportBug" && isset($_GET["title"]) && isset($_GET["body"]) && isset($_GET["severity"]))
				{
					$title = $_GET["title"];
					$body = $_GET["body"];
					$severity = $_GET["severity"];
					$status = json_decode("{}",true);
					$status["active"] = true;
					$status["public"] = true;
					$status["resolved"] = false;
					$status["owner"] = $userID;
					$sql = "INSERT INTO `bugs`(`title`, `body`, `severity`,`owner`,`status`) VALUES ('$title','$body','$severity','$userID','".json_encode($status)."')";
					$response["sql"] = $sql;

					$result = $conn->query($sql);
				}
				else if($apiPoint === "sendMessage" && isset($_GET["data"]))
				{
					try {
						$data = json_decode($_GET["data"],true);
						if(isset($data["data"]["msg"]) && isset($data["data"]["refrenceID"]) && is_numeric($data["data"]["refrenceID"]) && isset($data["data"]["type"]))
						{
							if($data["data"]["type"] == "bug")
							{
								$sql = "INSERT INTO `communication`(`type`, `linkedID`, `content`,`owner`) VALUES ('bug','".$data["data"]["refrenceID"]."','".$data["data"]["msg"]."','$userID')";
								$result = $conn->query($sql);
								$response["msg"] = "SENT MESSAGE";
								if($isDev)
								{									
									$response["sql"] = $sql;
								}
							}
							else
							{
								$response["error"] = "Invalid type";
								http_response_code("404");
							}
						}
						else
						{
							if(!isset($data["data"]["msg"]))
							{
								$response["error"] = "Missing MSG values";
							}

							if(!isset($data["data"]["refrenceID"]))
							{
								$response["error"] .= "Missing refrenceID values";
							}

							if(!isset($data["data"]["type"]))
							{
								$response["error"] .= "Missing type values";
							}
							http_response_code("404");
						}
					} catch (\Throwable $th) {						
						$response["error"] = "None valid JSON string send".$th;
						http_response_code("503");
					}
				}
				else if($apiPoint === "createGroup" && isset($URLParams[0]) && is_numeric($URLParams[0]) && isset($_GET["data"]))
				{
					try {
						$response["perms"] = $perms;
						if((isset($perms[$URLParams[0]]) && isset($perms[$URLParams[0]]["roles"]) && (hasRole("orgAdmin",$URLParams[0]) || hasRole("admin",$URLParams[0]))) || $isAdmin)
						{
							$data = json_decode($_GET["data"],true);
							$response["API"] = "createGroup";
							$name = $data["name"];
							$org = $data["org"];
							$domain = $data["domain"];
							$dupCheck = "SELECT * FROM `groups` WHERE JSON_VALUE(`perm`,'$.name') = '$name'";
							$result = $conn->query($dupCheck);
							$dup = false;
							$id = -1;
							while ($row = $result -> fetch_assoc())
							{
								$dup = true;
								$id = $row["id"];
								break;
							}
							if(!$dup)
							{
								$sql = "INSERT INTO `groups`(`orgID`, `perm`) VALUES ('$org','".json_encode($data)."')";
							}
							else
							{
								$sql = "UPDATE `groups` SET `perm`='".json_encode($data)."' WHERE `id` = '$id'";
							}
							$response["sql"] = $sql;
							$result = $conn->query($sql);
						}
						else
						{	$response["msg"] = "don't have permissions to acess this data";
							http_response_code(401);
						}
					} catch (\Throwable $th) {
						throw $th;
					}
				}
				else if($apiPoint === "createPerm" && isset($URLParams[0]) && is_numeric($URLParams[0]) && $URLParams[0] > -1 && isset($_GET["data"]))
				{
					try {
						$response["perms"] = $perms;
						if((isset($perms[$URLParams[0]]) && isset($perms[$URLParams[0]]["roles"]) && (in_array("orgAdmin",$perms[$URLParams[0]]["roles"]) || in_array("admin",$perms[$URLParams[0]]["roles"]))) || $isAdmin)
						{
							$data = json_decode($_GET["data"],true);
							$response["API"] = "createPerm";
							$targetUser = $data["userID"];
							$org = $URLParams[0];
							$perm = $data["perm"];
							$dupCheck = "SELECT * FROM `perms` WHERE `userID` = '$targetUser'";
							$result = $conn->query($dupCheck);
							$perms = null;
							$dup = false;
							$id = -1;
							while ($row = $result -> fetch_assoc())
							{
								$dup = true;
								$id = $row["id"];
								$perms = json_decode($row["perm"],true);
								$perms[$org] = $perm[$org];
								break;
							}
							if(!$dup)
							{
								$sql = "INSERT INTO `perms`(`userID`, `perm`) VALUES ('$targetUser','".json_encode($perm)."')";
							}
							else
							{
								$sql = "UPDATE `perms` SET `perm`='".json_encode($perms)."' WHERE `id` = '$id'";
							}
							$response["sql"] = $sql;
							$result = $conn->query($sql);
						}
						else
						{	$response["msg"] = "don't have permissions to acess this data";
							http_response_code(401);
						}
					} catch (\Throwable $th) {
						throw $th;
					}
				}
				else if($apiPoint === "createQuestionBatch" && isset($_GET['org']) && is_numeric($_GET['org']) && isset($_GET['subject']) && isset($_GET["name"]))
				{
					$id = $_GET["org"];
					if(inOrg($id) === true)
					{
						$subjectName = urldecode($_GET["subject"]);
						$subjectID = null;
						$stmt = $conn->prepare("SELECT * FROM `subject` WHERE `name` = ? AND `org` = ?");
						$stmt->bind_param("ss", $subjectName, $id);
						$dup = false;
						if ($stmt->execute())  
						{
							$result = $stmt->get_result(); 
							if($result->num_rows == 1) {   
								while ($data = $result->fetch_assoc()) {
									$dup = true;  
									$subjectID = $data['id'];
								}
							}
						}

						if($dup === false)
						{
							$stmt = $conn->prepare("INSERT INTO `subject`(`name`, `org`) VALUES (?,?)");
							$stmt->bind_param("ss", $subjectName, $id);
							if ($stmt->execute())  
							{
								$getID = $conn->prepare("SELECT * FROM `subject` WHERE `name` = ? AND `org` = ?");
								$getID->bind_param("ss", $subjectName, $id);
								if ($getID->execute())  
								{
									$result = $getID->get_result(); 
									if($result->num_rows == 1) {   
										while ($data = $result->fetch_assoc()) {
											$subjectID = $data['id'];
										}
									}
								}
								else
								{
									array_push($response["stack"], "Couldn't find the newly created item");
								}
							}
							else
							{
								$response["erorr"] = true;
								$response["msg"] = "COULDN'T RUN SQL QUERY";
								array_push($response["stack"], "Couldnt run sql query");
							}
						}
						else
						{
							array_push($response["stack"], "Already have this subject");
						}

						$topicName = urldecode($_GET["topic"]);
						if($subjectID !== null)
						{
							$topicID = null;
							$stmt = $conn->prepare("SELECT * FROM `topic` WHERE `name` = ? AND `subject` = ?");
							$stmt->bind_param("ss", $topicName, $subjectID);
							$dup = false;
							if ($stmt->execute())  
							{
								$result = $stmt->get_result(); 
								if($result->num_rows == 1) {   
									while ($data = $result->fetch_assoc()) {
										$dup = true;  
										$topicID = $data['id'];
									}
								}
							}

							if($dup === false)
							{
								$stmt = $conn->prepare("INSERT INTO `topic`(`name`, `subject`) VALUES (?,?)");
								$stmt->bind_param("ss", $topicName, $subjectID);
								if ($stmt->execute())  
								{
								}
								else
								{
									$response["erorr"] = true;
									$response["msg"] = "COULDN'T RUN SQL QUERY";
								}
							}
							else
							{
								array_push($response["stack"], "Already have this topic");
							}
						}
						else
						{							
							array_push($response["stack"], "topic id null");
						}

						if($topicID !== null)
						{
							$name = $_GET["name"];
							$dup = false;
							$stmt = $conn->prepare("SELECT * FROM `questions` WHERE `name` = ? AND `topic` = ? AND `userID` = ?");
							$stmt->bind_param("sss", $name, $topicID, $userID);
							if ($stmt->execute())  
							{
								$result = $stmt->get_result(); 
								if($result->num_rows == 1) {   
									while ($data = $result->fetch_assoc()) {
										$dup = true;  
									}
								}
							}
							if($dup === false)
							{
								$tempID = random_strings(16);
								$sql = "SELECT * FROM `questions` WHERE `questionID` = '$tempID'";
								$result = $conn->query($sql);
								
								$response["sql"] = $sql;
								$notDup = true;
								while ($row = $result -> fetch_assoc())
								{
									$notDup = false;
									$response["error"] = true;
									break;
								}
								if($notDup === true)
								{
									$extra = json_decode("{}",true);
									$currentTime = time();
									$extra["userID"] = $userID;
									$extra["questionID"] = $tempID;
									$extra["createdAt"] = $currentTime;
									$extra["ip"] = $_SERVER['REMOTE_ADDR'];
									$encodedExtra = json_encode($extra);
									$stmt = $conn->prepare("INSERT INTO `questions`(`questionID`,`questionSubID`,`userID`,`translationTable`,`topic`,`name`,`extra`) VALUES (?,'',?,'{}',?,?,?)");
									$stmt->bind_param("sssss", $tempID, $userID, $topicID, $name, $encodedExtra);
									if ($stmt->execute())  
									{
										$response["questionID"] = $tempID;									
									}
								}
							}
							else
							{
								array_push($response["stack"], "Question with this data exists");
							}
							
						}


					}
					else
					{
						array_push($response["stack"], "Dont have perm");
					}

				}
				else if($apiPoint === "createMessage2" && isset($_GET["questionID"]) && isset($_GET["variableID"]) && is_numeric($_GET["variableID"]) && isset($_GET['content'])){
					$linkID = null;
					if(isset($_GET["linkID"]) && is_numeric($_GET["linkID"]))
					{
						$linkID = $_GET["linkID"];
					}
					$chatItem = json_decode("{}",true);
					$chatItem["isQOwner"] = $isQOwner;
					$chatItem["messageCreator"] = (int)$userID;
					$chatItem["questionID"] = $_GET["questionID"];
					$chatItem["variableID"] = (int)$_GET["variableID"];
					$chatItem["content"] = urldecode($_GET["content"]);
					$chatItem["type"] = "messsage";
					$chatItem["org"] = $questionOrgInfo;
					if($linkID !== null)
					{
						$chatItem["type"] = 'response';
					}
					
					$stmt = $conn->prepare("SELECT * FROM `chat` WHERE `data` = ?");
					$encodedChatItem = json_encode($chatItem);
					$stmt->bind_param("s", $encodedChatItem);
					$dup = false;
					if ($stmt->execute())  
					{
						$result = $stmt->get_result();   
						while ($data = $result->fetch_assoc()) {
							$dup = true;  
						}						
					}
					if($dup === false)
					{
						$stmt = $conn->prepare("INSERT INTO `chat` (`data`) VALUES(?)");
						$stmt->bind_param("s", $encodedChatItem);
						$dup = false;
						if ($stmt->execute())  
						{
							$result = $stmt->get_result();   
						}
					}
					else
					{
						$response["stack"] = "ALL READY HAVE SENT CHAT MESSAGE";						
						$response["msg"] = "ALL READY HAVE SENT CHAT MESSAGE";
						$response["error"] = true;
					}					
					$response["chat"] = $chatItem;
				}
				else if($apiPoint === "createMessage" && isset($_GET["questionID"]) && isset($_GET["variableID"]) && is_numeric($_GET["variableID"]) && isset($_GET['content']))
				{
					$linkID = null;
					if(isset($_GET["linkID"]) && is_numeric($_GET["linkID"]))
					{
						$linkID = $_GET["linkID"];
					}
					$messageID = base64_encode($_GET["questionID"] . $_GET['variableID'] . $userID);
					$stmt = $conn->prepare("SELECT JSON_QUERY(`data` , '$.messageChain') as 'messageChain' FROM `chat` WHERE JSON_VALUE(`data` , '$.messageID') = ?");
					$stmt->bind_param("s", $messageID);
					$dup = false;
					$foundMessageChain = false;
					$messageChain = json_decode("[]",true);
					if ($stmt->execute())  
					{
						$result = $stmt->get_result();   
						while ($row = $result->fetch_assoc()) {
							$messageChain = json_decode($row['messageChain'],true);
							$foundMessageChain = true;
							break;
						}
					}
					$chatItem = json_decode("{}",true);
					$chatItem['messageID'] = $messageID;
					$chatItem["isQOwner"] = $isQOwner;
					$chatItem["messageCreator"] = (int)$userID;
					$chatItem["questionID"] = $_GET["questionID"];
					$chatItem["variableID"] = (int)$_GET["variableID"];
					$chatItem["content"] = urldecode($_GET["content"]);
					$chatItem["type"] = "messsage";
					$chatItem["org"] = $questionOrgInfo;
					header('foundMessageChain: ' .$foundMessageChain);
					if($foundMessageChain == true)
					{				
						$chatItem["type"] = 'response';
					}
					array_push($messageChain, $chatItem);
					$chatItem["messageChain"] = $messageChain;
					
					if($foundMessageChain == false)
					{
						$stmt = $conn->prepare("INSERT INTO `chat` (`data`) VALUES(?)");
						$encodedChatItem = json_encode($chatItem);
						$stmt->bind_param("s", $encodedChatItem);
						$dup = false;
						if ($stmt->execute())  
						{
							$result = $stmt->get_result();   
						}				
					}
					else
					{
						$stmt = $conn->prepare("UPDATE `chat` SET `data` = ?");
						$encodedChatItem = json_encode($chatItem);
						$stmt->bind_param("s", $encodedChatItem);
						$dup = false;
						if ($stmt->execute())  
						{
							$result = $stmt->get_result();   
						}				
					}
					$response["chat"] = $chatItem;
				}
				else if($apiPoint === "createMessageStaff" && isset($_GET["questionID"]) && isset($_GET["variableID"]) && is_numeric($_GET["variableID"]) && isset($_GET['content']) && isset($_GET['searchUser']))
				{
					
					$messageID = base64_encode($_GET["questionID"] . $_GET['variableID'] . $_GET['searchUser']);
					$stmt = $conn->prepare("SELECT JSON_QUERY(`data` , '$.messageChain') as 'messageChain' FROM `chat` WHERE JSON_VALUE(`data` , '$.messageID') = ?");
					$stmt->bind_param("s", $messageID);
					$dup = false;
					$foundMessageChain = false;
					$messageChain = json_decode("[]",true);
					if ($stmt->execute())  
					{
						$result = $stmt->get_result();   
						while ($row = $result->fetch_assoc()) {
							$messageChain = json_decode($row['messageChain'],true);
							$foundMessageChain = true;
							break;
						}
					}
					$chatItem = json_decode("{}",true);
					$chatItem['messageID'] = $messageID;
					$chatItem["isQOwner"] = $isQOwner;
					$chatItem["messageCreator"] = (int)$userID;
					$chatItem["questionID"] = $_GET["questionID"];
					$chatItem["variableID"] = (int)$_GET["variableID"];
					$chatItem["content"] = urldecode($_GET["content"]);
					$chatItem["type"] = "messsage";
					$chatItem["org"] = $questionOrgInfo;
					header('foundMessageChain: ' .$foundMessageChain);
					if($foundMessageChain == true)
					{				
						$chatItem["type"] = 'response';
					}
					array_push($messageChain, $chatItem);
					$chatItem["messageChain"] = $messageChain;
					
					if($foundMessageChain == false)
					{
						$stmt = $conn->prepare("INSERT INTO `chat` (`data`) VALUES(?)");
						$encodedChatItem = json_encode($chatItem);
						$stmt->bind_param("s", $encodedChatItem);
						$dup = false;
						if ($stmt->execute())  
						{
							$result = $stmt->get_result();   
						}				
					}
					else
					{
						$stmt = $conn->prepare("UPDATE `chat` SET `data` = ?");
						$encodedChatItem = json_encode($chatItem);
						$stmt->bind_param("s", $encodedChatItem);
						$dup = false;
						if ($stmt->execute())  
						{
							$result = $stmt->get_result();   
						}				
					}
					$response["chat"] = $chatItem;
				}
				/*
					REMOVE API's
				*/
				else if($apiPoint === "removeQuestion" && isset($URLParams[0]))
				{
					$questionID = $URLParams[0];
					$questionOrg = null;
					$getQuestionInfo = $conn->prepare("SELECT * FROM `questions` WHERE `questionID` = ?");
					$getQuestionInfo->bind_param("s", $questionID);
					if ($getQuestionInfo->execute())  
					{
						$questionTopic = null;
						$result = $getQuestionInfo->get_result(); 
						if($result->num_rows == 1) {   
							while ($data = $result->fetch_assoc()) {
								$questionTopic = $data['topic'];
							}
						}
						if($questionTopic !== null)
						{
							$questionSubject = null;
							$getSubjectSQL = $conn->prepare("SELECT * FROM `topic` WHERE `id` = ?");
							$getSubjectSQL->bind_param("s", $questionTopic);
							$getSubjectSQL->execute();
							$result = $getSubjectSQL->get_result(); 
							if($result->num_rows == 1) {   
								while ($data = $result->fetch_assoc()) {
									$questionSubject = $data['subject'];
								}
							}
							if($questionSubject !== null)
							{
								$getORGSQL = $conn->prepare("SELECT * FROM `subject` WHERE `id` = ?");
								$getORGSQL->bind_param("s", $questionSubject);
								$getORGSQL->execute();
								$result = $getORGSQL->get_result(); 
								if($result->num_rows == 1) {   
									while ($data = $result->fetch_assoc()) {
										$questionOrg = $data['org'];
									}
								}
							}
							$response["questionOrg"] = $questionOrg;
							$response["questionTopic"] = $questionTopic;
							$response["questionSubject"] = $questionSubject;
						}
						else
						{
							$response["error"] = true;
						}
					}
					if($questionOrg !== null)
					{
						if(inDomainAny($questionID) && (hasRole("educator",$questionOrg) || hasRole("orgAdmin",$questionOrg) || hasRole("admin",$questionOrg)))
						{						
							$stmt = $conn->prepare("DELETE FROM `questions` WHERE `questionID` = ?");
							$stmt->bind_param("s", $questionID);
							if ($stmt->execute())  
							{
								$response["msg"] = "done";
							}
							
						}
						else
						{
							$response["error"] = true;
							$response["msg"] = "Dont have permission to do this";
						}
					}
					else
					{
						$response["error"] = true;
						$response["msg"] = "Cant find question";
					}
				}
				//ADMIN API's
				else if($isAdmin && $apiPoint === "getUsersStats")
				{
					if(isset($URLParams[0]))
					{
						$checkID = (int)$URLParams[0];
						$sql = "SELECT `isStaff`, `isAdmin`,JSON_QUERY(org,'$.org') as 'org' FROM `users`";
						$result = $conn->query($sql);
						$userTypes = json_decode("{}",true);
						$userTypes["admins"] = 0;
						$userTypes["staff"] = 0;
						$userTypes["students"] = 0;
						while ($row = $result -> fetch_assoc())
						{
							try
							{
								$IDS = json_decode($row["org"],true);
								$response["aa"] = $IDS;
								$response["adad"] = in_array($checkID, $IDS);
								if(in_array($checkID, $IDS))
								{
									if($row["isAdmin"] == 1)
									{
										$userTypes['admins']++;
									}
									else if($row["isStaff"] == 1)
									{
										$userTypes['staff']++;
									}
									else
									{
										$userTypes['students']++;
									}
								}
							}
							catch (\Throwable $e)
							{}
						}

						$stmt = $conn->prepare("SELECT COUNT(q.isPublic) AS 'total', `isPublic` FROM `questions` q, `subject` s, `topic` t WHERE s.org=? AND t.subject = s.id AND q.topic = t.id GROUP BY `isPublic`;");
						$stmt->bind_param("s", $checkID);
						$dup = false;
						$results = json_decode("{}",true);
						$response["results"]["public"] = 0;
						$response["results"]["private"] = 0;
						if ($stmt->execute())  
						{
							$result = $stmt->get_result(); 
							while ($row = $result->fetch_assoc()) {
								if($row["isPublic"] == 0)
								{
									$response["results"]["private"] += $row["total"];
								}
								if($row["isPublic"] == 1)
								{
									$response["results"]["public"] += $row["total"];
								}
							}
						}
						//$response["results"]["totalQuestions"] = (int)$row['total'];
						$response["results"]["users"] = $userTypes;
					}
					else
					{
						$sql = "SELECT `isStaff`, `isAdmin` FROM `users`";
						$result = $conn->query($sql);
						$userTypes = json_decode("{}",true);
						$userTypes["admins"] = 0;
						$userTypes["staff"] = 0;
						$userTypes["students"] = 0;
						while ($row = $result -> fetch_assoc())
						{
							if($row["isAdmin"] == 1)
							{
								$userTypes['admins']++;
							}
							else if($row["isStaff"] == 1)
							{
								$userTypes['staff']++;
							}
							else
							{
								$userTypes['students']++;
							}
						}

						$sql = "SELECT COUNT(*) AS 'total', `isPublic`	FROM `questions` GROUP BY `isPublic`;";
						$result = $conn->query($sql);
						$results = json_decode("{}",true);
						$response["results"]["public"] = 0;
						$response["results"]["private"] = 0;
						//$response["results"]["totalQuestions"] = (int)$row['total'];
						while ($row = $result -> fetch_assoc())
						{
							if($row["isPublic"] == 0)
							{
								$response["results"]["private"] += $row["total"];
							}
							if($row["isPublic"] == 1)
							{
								$response["results"]["public"] += $row["total"];
							}
						}
						$response["results"]["users"] = $userTypes;
					}
					/*else
					{
						$response["msg"] = "API POINT NOT FOUND";
						http_response_code(404);
					}*/
				}
				else if($isAdmin && $apiPoint === "getUserInfo" && isset($URLParams[0]))
				{
					$response["user"] = json_decode("{}",true);
					$response["user"]["email"] = $URLParams[0];
					$sql = "SELECT *,JSON_QUERY(org,'$.org') as 'org' FROM `users` WHERE `email` = '" . $URLParams[0] . "'";
					$response["user"]["sql"] = $sql;
					$result = $conn->query($sql);
					$f = false;
					while ($row = $result -> fetch_assoc())
					{
						$response["user"]["isStaff"] = $row["isStaff"];
						if($response["user"]["isStaff"] == 1)
						{
							$response["user"]["isStaff"] = true;
						}
						else
						{
							$response["user"]["isStaff"] = false;
						}
						$response["user"]["isAdmin"] = $row["isAdmin"];
						if($response["user"]["isAdmin"] == 1)
						{
							$response["user"]["isAdmin"] = true;
						}
						else
						{
							$response["user"]["isAdmin"] = false;
						}
						$response["user"]["firstName"] = $row["firstName"];
						$response["user"]["lastName"] = $row["lastName"];
						$response["user"]["org"] = json_decode($row["org"],true);
						$f = true;
						$response["user"]["communications"] = [];
						$sql = "SELECT * FROM `communication` WHERE `owner` = '" . $row["id"] . "'";
						$response["user"]["sql"] = $sql;
						$result2 = $conn->query($sql);
						while ($row2 = $result2 -> fetch_assoc())
						{
							$rr = json_decode("{}",true);
							$rr["id"] = $row2["id"];
							$rr["type"] = $row2["type"];							
							$rr["content"] = $row2["content"];
							array_push($response["user"]["communications"], $rr);
						}
					}
					if(!$f)
					{						
						$response["error"] = "User not found";
						http_response_code(404);
						//exit();
					}
				}
				else if($isAdmin && $apiPoint === "getUserInfoID" && isset($URLParams[0]) && is_numeric($URLParams[0]))				{
					$response["user"] = json_decode("{}",true);
					$response["user"]["id"] = $URLParams[0];
					$sql = "SELECT *,JSON_QUERY(org,'$.org') as 'org' FROM `users` WHERE `id` = '" . $URLParams[0] . "'";
					$response["user"]["sql"] = $sql;
					$result = $conn->query($sql);
					$f = false;
					while ($row = $result -> fetch_assoc())
					{
						$response["user"]["isStaff"] = $row["isStaff"];
						if($response["user"]["isStaff"] == 1)
						{
							$response["user"]["isStaff"] = true;
						}
						else
						{
							$response["user"]["isStaff"] = false;
						}
						$response["user"]["isAdmin"] = $row["isAdmin"];
						if($response["user"]["isAdmin"] == 1)
						{
							$response["user"]["isAdmin"] = true;
						}
						else
						{
							$response["user"]["isAdmin"] = false;
						}
						$response["user"]["firstName"] = $row["firstName"];
						$response["user"]["lastName"] = $row["lastName"];
						$response["user"]["org"] = json_decode($row["org"],true);

						
						$f = true;
						$response["user"]["communications"] = [];
						$sql = "SELECT * FROM `communication` WHERE `owner` = '" . $URLParams[0] . "'";
						$response["user"]["sql"] = $sql;
						$result2 = $conn->query($sql);
						while ($row2 = $result2 -> fetch_assoc())
						{
							$rr = json_decode("{}",true);
							$rr["id"] = $row2["id"];
							$rr["type"] = $row2["type"];							
							$rr["content"] = $row2["content"];
							array_push($response["user"]["communications"], $rr);
						}
					}
					if(!$f)
					{						
						$response["error"] = "User not found";
						http_response_code(404);
						//exit();
					}
				}
				else if($isAdmin && $apiPoint === "getOrg" && isset($URLParams[0]))	{
					$response["user"] = json_decode("{}",true);
					$response["user"]["id"] = $URLParams[0];
					$sql = "SELECT * FROM `org` WHERE `name` LIKE '%" . urldecode ($URLParams[0]) . "%'";
					$response["user"]["sql"] = $sql;
					$response["orgs"] = [];
					$result = $conn->query($sql);
					while ($row = $result -> fetch_assoc())
					{
						$t = json_decode("{}",true);
						$t["name"] = $row["name"];
						$t["id"] = $row["id"];
						array_push($response["orgs"], $t);
					}
					
				}
				else if($isAdmin && $apiPoint === "setAdmin" && isset($URLParams[0]))	{
					//$data = json_decode($_GET["data"],true);
					$response["API"] = "setAdmin";
					$targetUser = $URLParams[0];
					$org = -1;
					$perm = json_decode('{"-1":{"org":-1,"roles":["admin"],"domain":[{"type":"target","target":"*"}]}}',true);
					$dupCheck = "SELECT * FROM `perms` WHERE `userID` = '$targetUser'";
					$result = $conn->query($dupCheck);
					$perms = null;
					$dup = false;
					$id = -1;
					while ($row = $result -> fetch_assoc())
					{
						$dup = true;
						$id = $row["id"];
						$perms = json_decode($row["perm"],true);
						$perms[$org] = $perm[$org];
						break;
					}
					if(!$dup)
					{
						$sql = "INSERT INTO `perms`(`userID`, `perm`) VALUES ('$targetUser','".json_encode($perm)."')";
					}
					else
					{
						$sql = "UPDATE `perms` SET `perm`='".json_encode($perms)."' WHERE `id` = '$id'";
					}
					$response["sql"] = $sql;
					$result = $conn->query($sql);
				}
				else if($isAdmin && $apiPoint === "getLoginInfo")
				{
					$getLoginInfo = $conn->prepare("SELECT * FROM `login_status`");
					$getLoginInfo->execute();
					$info = json_decode("{}",true);
					$result = $getLoginInfo->get_result(); 
					while ($row = $result->fetch_assoc()) {
						$r = json_decode("{}",true);
						$r["data"] = json_decode($row["data"]);
						$r["date"] = $row["date"];
						array_push($info, $r);
					}
					$response["info"] = $info;
					//echo (json_encode($info));
					/*
					if(isset($URLParams[0]))
					{
					}
					else
					{
					}*/

				}
				else if($isAdmin && $apiPoint === "addUniversity" && isset($URLParams[0]))
				{
					$uniName = urldecode($URLParams[0]);
					$stmt = $conn->prepare("SELECT * FROM `org` WHERE `name` = ?");
					$stmt->bind_param("s", $uniName);
					$dup = false;
					if ($stmt->execute())  
					{
						$result = $stmt->get_result(); 
						while ($row = $result->fetch_assoc()) {
							array_push($response["stack"],"Already have org");
							$dup = true;
						}						
					}
					if($dup === false)
					{
						
						$stmt3 = $conn->prepare("INSERT INTO `org`(`name`, `type`,`state`,`postCode`,`suburb`) VALUES (?,'uni','',-1,'Some where')");
						$stmt3->bind_param("s", $uniName);
						$stmt3->execute();
						$stmt3->get_result();
						array_push($response["stack"],"Add new org");
					}
					
				}
				/*
				else if($isAdmin && $apiPoint === "resetPassword" && isset($URLParams[0])
				{
					$stmt = $conn->prepare("UPDATE `users` SET `passwd`='a' WHERE `email` = ?");
					$stmt->bind_param("s", $URLParams[0]);
					if ($stmt->execute())  
					{
					}
				}*/
			}
			else
			{				
				$response["msg"] = "API POINT NOT FOUND";
			}
		}
		else
		{
			if($isDev)
			{
				header("Location: /dev./login.php");
			}
			else
			{					
				header("Location: /main/login.php");
			}
			exit();
		}
		header("Content-Type: text/json");
		//$response["text"] = mb_convert_encoding($response["text"], "UTF-8", "Windows-1252");
		//$response["text"] = base64_encode($response["text"]);
		//echo str_replace("<br>","\n",$response["text"]);
		//var_dump($a);
		echo json_encode($response);
	}
	else
	{
		if($apiPoint === "getNewQuestion" && (isset($_GET["questionID"]) || isset($_GET['sessionID']))){
			$questionID = null;
			
			$randomActive = true;
			
			if(isset($_GET["randomlock"]))
			{
				$randomActive = false;
				$rA = false;
			}
			
			$instanceId = null;
			if(isset($_GET["questionID"]))
			{
				$questionID = $_GET["questionID"];
			}
		
			/*CREATE TABLE `datatrain`.`sessions` (`id` INT NOT NULL AUTO_INCREMENT , `sessionID` VARCHAR(32) NULL DEFAULT NULL , `data` JSON NOT NULL DEFAULT '{}' , PRIMARY KEY (`id`)) ENGINE = InnoDB; */
			//$response["validUntil"] = strtotime(gmdate('r', time() + 3 * 60 * 60));
			if(isset($_GET['sessionID']))
			{
				$sessionIndex = 0;
				if(isset($_GET['sessionIndex']))
				{
					$sessionIndex = $_GET['sessionIndex'];
				}
				array_push($response['stack'],'found session id');
				$sessionID = $_GET['sessionID'];
				$stmt = $conn->prepare("SELECT * FROM `sessions` WHERE JSON_VALUE(`data`,'$.sessionID') = ?");
				$stmt->bind_param("s", $sessionID);
				$found = false;
				if ($stmt->execute())  
				{
					$result = $stmt->get_result(); 
					while ($row = $result->fetch_assoc()) {
						if(json_decode($row['data'],true)["validUntil"] >= strtotime(gmdate('r', time())))
						{
							$sessionStats = json_decode($row['data'],true);
							$questionIDs = $sessionStats['questionIDs'];
							$found = true;
							if(isset($sessionStats["instanceID"]))
							{
								$instanceId = $sessionStats["instanceID"];
							}
							if(isset($sessionStats["useBaseValues"]))
							{
								$randomActive = !$sessionStats["useBaseValues"];
							}
							if(isset($userID) && $sessionStats["creatorID"] == $userID)
							{
								$sessionOwner = true;
							}
						}
					}									
				}
				if($found)
				{
					$presentationMode = true;
					$response['sessionOwner'] = $sessionOwner;
				}
				else
				{
					$response["msg"] = 'sessionID not found or expired';
					$response['sessionID'] = $sessionID;
					echo json_encode($response);
					exit();
				} 
				array_push($response['stack'],'got question ids: '.$questionIDs[$sessionIndex]);
				$questionID = $questionIDs[$sessionIndex];
			}
			if(str_contains($_SERVER['HTTP_REFERER'], "presentationMode=true") && !isset($_GET['sessionID'])){
				$response["msg"] = 'sessionID not given';
				echo json_encode($response);
				exit();
			}
			if(1 == 1)
			{
				if($instanceId == null)
				{
					$instanceId = $_GET["instanceId"];
				}
				array_push($response['stack'],'got instanceId: '. $instanceId);
				if(isset($instanceId))
				{
					srand($instanceId);
					header("instanceID: " . $instanceId);
				}
				header("random: " . $randomActive);
				//echo $randomActive;
				if($questionID != null)
				{
					$questionData = json_decode("{}",true);
					$translationTable = json_decode("{}",true);
					$answerData = json_decode("{}",true);
					$tax = rand(1,99);
					$sql = "SELECT * FROM `questions` WHERE `questionID` = '$questionID'";
					$result = $conn->query($sql);
					
					$response["sql"] = $sql;
					//echo $sql;
					$found = false;
					$question = "";
					$questionID = "";
					$answer = "";
					$response["raw"] = "TEMP";
					while ($row = $result -> fetch_assoc())
					{
						$found = true;
						$question = urldecode(base64_decode(utf8_decode(urldecode($row["question"]))));
						$question = mb_convert_encoding($question, 'UTF-8', 'UTF-8');
						$translationTable = urldecode(base64_decode(utf8_decode(urldecode($row["translationTable"]))));
						$response["raw"] = $question;
						$response["translationTable"] = $translationTable;
						$questionData["raw"] = $question;
						$response["title"] = $row["name"];

						$answer = urldecode(base64_decode(utf8_decode(urldecode($row["answer"]))));
						$answer = mb_convert_encoding($answer, 'UTF-8', 'UTF-8');
						$response["answerraw"] = $answer;
						$questionID = $row["questionID"];
						break;
					}
					$sql = "SELECT * FROM `questions` WHERE `questionSubID` = '$questionID'";
					$result = $conn->query($sql);
					while ($row = $result -> fetch_assoc())
					{
						$questionR = urldecode(base64_decode(utf8_decode(urldecode($row["question"]))));
						$question = $question . mb_convert_encoding($questionR, 'UTF-8', 'UTF-8');
						$response["raw"] = $question;
						$response["title"] = $row["name"];

						$answerR = urldecode(base64_decode(utf8_decode(urldecode($row["answer"]))));
						$answer = $answer . mb_convert_encoding($answerR, 'UTF-8', 'UTF-8');
						$response["answerraw"] = $answer;
						$questionID = $row["questionID"];
						break;
					}
					
					//var_dump($allItemsValue);
					if($found === true)
					{
						$translationTable = json_decode($translationTable,true);
						
						foreach ($translationTable as $key => $value) {
							addItemToArray($value[1], $questionData);
						}
						$t = $question;
						//echo strpos($t,"%c[0,0]");
						//echo "<br>";
						$t = str_replace("\n","<br>", $t);
						$response["text222"] = $answer;
						$response["answer"] = $t;
						$questionData["text"] = $t . "|||||" . $answer;
						$answerData["text"] = $answer;
						//echo json_encode($response);
						//$allItems = explode("%n", $questionData["text"]);
						
						//echo $questionData["text"];
						try{
							$questionData = parseText($questionData);
							$questionData = secondParseTextLayer($questionData);
						}
						catch (\Throwable $e)
						{
							echo $e;
						}
						//echo $questionData["text"];
						//var_dump(explode("|||||",$questionData["text"]));
						$answerData["text"] = explode("|||||",$questionData["text"])[1];
						$questionData["text"] = explode("|||||",$questionData["text"])[0];
						//$response["AAAA"] = $questionData["aaaa"];
						$response["ALL"] = $allItemsValue;
						$scripts = [];
						if($presentationMode)
						{
							//$questionData["text"] = $questionData["text"] . "<script src='js/presentation.js'></script>";
							//array_push($scripts,'js/presentation.js');
						}
						if($sessionOwner)
						{
							//$questionData["text"] = $questionData["text"] . "<script src='js/staff.js'></script>";
							array_push($scripts,'js/presentation.js');
						}
						$aa = base64_encode($questionData["text"]);
							
						$b = json_decode("{}",true);
						$b["text"] = $aa;
						$b["raw"] = base64_encode($t);
						$response["text"] = $questionData["text"];
						$response["answer"] = $answerData["text"];
						$response["loadScripts"] = $scripts;
						//$response["ADAD"] = $questionData["ADAD"];//$questionData["text"];
						echo json_encode($response);
						exit();
					}
				}
			}
		}
		else if($apiPoint === "checkValue"){
			if(isset($_GET["answer"]) && (isset($_GET["questionID"]) || isset($_GET['sessionID'])) && isset($_GET["elementId"]))
			{
				header("StartedAPIPoint: $apiPoint");
				checkValue($_GET["elementId"]);
				/*
				$sessionID = null;
				$response["sessionID"] = $sessionID;
				if(isset($_GET['sessionID']))
				{
					$sessionID = $_GET['sessionID'];
				}
				$elementId = $_GET["elementId"];
				$instanceId = $_GET["instanceId"];
				if(isset($instanceId))
				{
					srand($instanceId);
				}
				$randomActive = true;
				if(isset($_GET["randomlock"]))
				{
					$randomActive = false;
				}
				if(isset($_GET["questionID"]) || isset($_GET['sessionID']))
				{
					if(isset($_GET["questionID"]))
					{
						$questionID = $_GET["questionID"];
					}
					if (isset($_GET['sessionID']))
					{
						$sessionIndex = $_GET["sessionIndex"];
						$found = true;
						$sessionID = $_GET['sessionID'];
						$questionData = json_decode("{}",true);
						$answerData = json_decode("{}",true);
						$stmt = $conn->prepare("SELECT * FROM `sessions` WHERE JSON_VALUE(`data`,'$.sessionID') = ?");
						$stmt->bind_param("s", $sessionID);
						$found = false;
						$makeNew = false;
						$results = [];
						$questionIDs = [];
						if ($stmt->execute())  
						{
							$result = $stmt->get_result(); 
							while ($row = $result->fetch_assoc()) {
								$sessionStats = json_decode($row['data'],true);
								$questionIDs = $sessionStats['questionIDs'];
								$found = true;
								if(isset($sessionStats["instanceID"]))
								{
									$instanceId = $sessionStats["instanceID"];
								}
								if(isset($sessionStats["useBaseValues"]))
								{
									$randomActive = !$sessionStats["useBaseValues"];
								}
								if($sessionStats["creatorID"] == $userID)
								{
									$sessionOwner = true;
								}
							}
						}
						$questionID = $questionIDs[$sessionIndex];
					}

					srand($instanceId);
					$found = true;
					
					srand($instanceId);
					$questionData = json_decode("{}",true);
					$answerData = json_decode("{}",true);
					$tax = rand(1,99);
					$sql = "SELECT * FROM `questions` WHERE `questionID` = '$questionID'";
					$result = $conn->query($sql);
					//$response["sql"] = $sql;
					//echo $sql;
					$found = false;
					$question = "";
					$questionID = "";
					$answer = "";
					//$response["raw"] = "TEMP";
					while ($row = $result -> fetch_assoc())
					{
						$found = true;
						$question = urldecode(base64_decode(utf8_decode(urldecode($row["question"]))));
						$question = mb_convert_encoding($question, 'UTF-8', 'UTF-8');
						$translationTable = urldecode(base64_decode(utf8_decode(urldecode($row["translationTable"]))));
						//$response["raw"] = $question;
						$response["title"] = $row["name"];

						$answer = urldecode(base64_decode(utf8_decode(urldecode($row["answer"]))));
						$answer = mb_convert_encoding($answer, 'UTF-8', 'UTF-8');
						//$response["answerraw"] = $answer;
						$questionID = $row["questionID"];
						break;
					}
					$sql = "SELECT * FROM `questions` WHERE `questionSubID` = '$questionID'";
					$result = $conn->query($sql);
					while ($row = $result -> fetch_assoc())
					{
						$questionR = urldecode(base64_decode(utf8_decode(urldecode($row["question"]))));
						$question = $question . mb_convert_encoding($questionR, 'UTF-8', 'UTF-8');
						//$response["raw"] = $question;
						$response["title"] = $row["name"];

						$answerR = urldecode(base64_decode(utf8_decode(urldecode($row["answer"]))));
						$answer = $answer . mb_convert_encoding($answerR, 'UTF-8', 'UTF-8');
						//$response["answerraw"] = $answer;
						$questionID = $row["questionID"];
						break;
					}
					$translationTable = json_decode($translationTable,true);
					foreach ($translationTable as $key => $value) {
						addItemToArray($value[1], $questionData);
					}

					if($found === true)
					{
						$t = $question;
						//echo strpos($t,"%c[0,0]");
						//var_dump(explode("%",$t));
						//echo "<br>";
						$t = str_replace("\n","<br>", $t);
						//$response["text222"] = $answer;
						//$response["answer"] = $t;
						$questionData["text"] = $t . "|||||" . $answer;
						$answerData["text"] = $answer;
						//echo json_encode($response);
						//$allItems = explode("%n", $questionData["text"]);
						
						//echo $questionData["text"];
						try{
							$questionData = parseText($questionData);
							//var_dump($allItemsValue);
							$questionData = secondParseTextLayer($questionData);
							//var_dump($allItemsValue);
						}
						catch (\Throwable $e)
						{
							echo $e;
						}
						//$response["AAA"] = $allItemsValue;
					}
				}
				$correct = 0;
				$total = 0;
				$answer = base64_decode($_GET["answer"]);
				//echo $answer;
				//var_dump($allItemsValue);
				$answer = json_decode($answer,true);

				$rAnswer = json_decode("{}",true);
				$rAnswerT = json_decode("{}",true);
				//var_dump($answer);
				foreach($answer as $key => $value) 
				{
					//echo $key;
					//echo $answer[$key] . " " . var_dump($allItemsValue[$key]) . " " .$key . "\n";
					$rAnswerT[$key] = $allItemsValue[$key][0];
					if(str_starts_with($answer[$key], "=") && json_decode($allItemsValue[$key][1],true)["type"] != "answerText")
					{
						$cleanedEquation = str_replace("=","",$answer[$key]);
						array_push($response['stack'], $cleanedEquation);
						$out = safeEval($cleanedEquation);
						if($out === "NOT VALID EQUATION")
						{
						}
						else
						{
							$answer[$key] = safeEval($cleanedEquation);
						}
						array_push($response['stack'], $out);
					}
					//var_dump($allItemsValue[$key][0]);
					//echo str_replace(",","",$answer[$key][1]) . str_replace(",","",$allItemsValue[$key][0]);
					if(str_replace(",","",$answer[$key]) == str_replace(",","",$allItemsValue[$key][0]))
					{
						$rAnswer[$key] = true;
						$correct++;
					}
					else
					{							
						$rAnswer[$key] = false;
					}
					$total ++;
				}
				$t = json_decode("{}",true);
				$response["rAnswer"] = $rAnswer;
				$response["rAnswerT"] = $rAnswerT;
				$response["providedAnswer"] = $answer;
				//$response["realAwnsers"] = $allItemsValue;
				//$response["corArect"] = $answer;
				$response["correct"] = $correct;
				$response["total"] = $total;
				
				$t["id"] = $questionID;
				$t["title"] = $response["title"];

				$t["rAnswer"] = $rAnswer;
				$t["rAnswerT"] = $rAnswerT;
				$t["providedAnswer"] = $answer;
				//$response["realAwnsers"] = $allItemsValue;
				//$response["corArect"] = $answer;
				$t["correct"] = $correct;
				$t["total"] = $total;
				//echo "\n";
				if($total > 0)
				{
					//$sql = "INSERT INTO `question_stats`(`questionID`, `userID`, `resultData`,`created_At`) VALUES ('$questionID','$userID','".json_encode($t)."','".time()."')";
					//$response["sql2"] = $sql;
					//echo json_encode($t);
					//echo "\n";
					//$result = $conn->query($sql);
				}
				else
				{
					http_response_code(202);
					$response["msg"] = "No awnser needed";
				}*/
			}
		}
		else if($apiPoint === "checkValues"){
			if(isset($_GET["answer"]) && (isset($_GET["questionID"]) || isset($_GET['sessionID'])))
			{
				checkValues();
			}
		}
		else
		{
			header("apiPoint: $apiPoint");
			header("Version: " . $_version);
			
			echo json_encode($response);
			if($isDev)
			{
				header("NOTSET: TRUE");
				header("Location: /dev./login.php");
			}
			else
			{					
				header("Location: /main/login.php");
			}
			exit();
		}
	}
	
	
}
catch (\Throwable $e)
{
	http_response_code(500);
	echo $e;
}


function checkValue($elementId){
	global $response;
	global $conn;
	global $allItemsValue;
	global $randomActive;
	global $userID;
	$sessionOwner = false;
	if($userID == null)
	{
		$userID = -1;
	}
	
	header("US: $userID");
	$sessionID = null;
	$response["sessionID"] = $sessionID;
	if(isset($_GET['sessionID']))
	{
		$sessionID = $_GET['sessionID'];
	}
	$elementId = $_GET["elementId"];
	$instanceId = $_GET["instanceId"];
	if(isset($instanceId))
	{
		srand($instanceId);
	}
	$randomActive = true;
	if(isset($_GET["randomlock"]))
	{
		$randomActive = false;
	}
	if(isset($_GET["questionID"]) || isset($_GET['sessionID']))
	{
		if(isset($_GET["questionID"]))
		{
			$questionID = $_GET["questionID"];
		}
		if (isset($_GET['sessionID']))
		{
			$sessionIndex = $_GET["sessionIndex"];
			$found = true;
			$sessionID = $_GET['sessionID'];
			$questionData = json_decode("{}",true);
			$answerData = json_decode("{}",true);
			$stmt = $conn->prepare("SELECT * FROM `sessions` WHERE JSON_VALUE(`data`,'$.sessionID') = ?");
			$stmt->bind_param("s", $sessionID);
			$found = false;
			$makeNew = false;
			$results = [];
			$questionIDs = [];
			if ($stmt->execute())  
			{
				$result = $stmt->get_result(); 
				while ($row = $result->fetch_assoc()) {
					$sessionStats = json_decode($row['data'],true);
					$questionIDs = $sessionStats['questionIDs'];
					$found = true;
					if(isset($sessionStats["instanceID"]))
					{
						$instanceId = $sessionStats["instanceID"];
					}
					if(isset($sessionStats["useBaseValues"]))
					{
						$randomActive = !$sessionStats["useBaseValues"];
					}
					if($sessionStats["creatorID"] == $userID)
					{
						$sessionOwner = true;
					}
				}
			}
			$questionID = $questionIDs[$sessionIndex];
		}
		
		$response["instanceId"] = $instanceId;
		$response["randomActive"] = $randomActive;
		$response["sessionOwner"] = $sessionOwner;
		srand($instanceId);
		$found = true;
		
		srand($instanceId);
		$questionData = json_decode("{}",true);
		$answerData = json_decode("{}",true);
		$tax = rand(1,99);
		$sql = "SELECT * FROM `questions` WHERE `questionID` = '$questionID'";
		$result = $conn->query($sql);
		//$response["sql"] = $sql;
		//echo $sql;
		$found = false;
		$question = "";
		$questionID = "";
		$answer = "";
		//$response["raw"] = "TEMP";
		while ($row = $result -> fetch_assoc())
		{
			$found = true;
			$question = urldecode(base64_decode(utf8_decode(urldecode($row["question"]))));
			$question = mb_convert_encoding($question, 'UTF-8', 'UTF-8');
			$translationTable = urldecode(base64_decode(utf8_decode(urldecode($row["translationTable"]))));
			//$response["raw"] = $question;
			$response["title"] = $row["name"];

			$answer = urldecode(base64_decode(utf8_decode(urldecode($row["answer"]))));
			$answer = mb_convert_encoding($answer, 'UTF-8', 'UTF-8');
			//$response["answerraw"] = $answer;
			$questionID = $row["questionID"];
			break;
		}
		$sql = "SELECT * FROM `questions` WHERE `questionSubID` = '$questionID'";
		$result = $conn->query($sql);
		while ($row = $result -> fetch_assoc())
		{
			$questionR = urldecode(base64_decode(utf8_decode(urldecode($row["question"]))));
			$question = $question . mb_convert_encoding($questionR, 'UTF-8', 'UTF-8');
			//$response["raw"] = $question;
			$response["title"] = $row["name"];

			$answerR = urldecode(base64_decode(utf8_decode(urldecode($row["answer"]))));
			$answer = $answer . mb_convert_encoding($answerR, 'UTF-8', 'UTF-8');
			//$response["answerraw"] = $answer;
			$questionID = $row["questionID"];
			break;
		}
		$translationTable = json_decode($translationTable,true);
		foreach ($translationTable as $key => $value) {
			addItemToArray($value[1], $questionData);
		}

		if($found === true)
		{
			$t = $question;
			//echo strpos($t,"%c[0,0]");
			//var_dump(explode("%",$t));
			//echo "<br>";
			$t = str_replace("\n","<br>", $t);
			//$response["text222"] = $answer;
			//$response["answer"] = $t;
			$questionData["text"] = $t . "|||||" . $answer;
			$answerData["text"] = $answer;
			//echo json_encode($response);
			//$allItems = explode("%n", $questionData["text"]);
			
			//echo $questionData["text"];
			try{
				$questionData = parseText($questionData);
				//var_dump($allItemsValue);
				$questionData = secondParseTextLayer($questionData);
				//var_dump($allItemsValue);
			}
			catch (\Throwable $e)
			{
				echo $e;
			}
			//$response["AAA"] = $allItemsValue;
		}
	}
	$correct = 0;
	$total = 0;
	$answer = base64_decode($_GET["answer"]);
	//echo $answer;
	//var_dump($allItemsValue);
	$answer = json_decode($answer,true);

	$rAnswer = json_decode("{}",true);
	$rAnswerT = json_decode("{}",true);
	//var_dump($answer);
	foreach($answer as $key => $value) 
	{
		//echo $key;
		//echo $answer[$key] . " " . var_dump($allItemsValue[$key]) . " " .$key . "\n";
		$rAnswerT[$key] = $allItemsValue[$key][0];
		if(str_starts_with($answer[$key], "=") && json_decode($allItemsValue[$key][1],true)["type"] != "answerText")
		{
			$cleanedEquation = str_replace("=","",$answer[$key]);
			array_push($response['stack'], $cleanedEquation);
			$out = safeEval($cleanedEquation);
			if($out === "NOT VALID EQUATION")
			{
			}
			else
			{
				$answer[$key] = safeEval($cleanedEquation);
			}
			array_push($response['stack'], $out);
		}
		//var_dump($allItemsValue[$key][0]);
		//echo str_replace(",","",$answer[$key][1]) . str_replace(",","",$allItemsValue[$key][0]);
		if(str_replace(",","",$answer[$key]) == str_replace(",","",$allItemsValue[$key][0]))
		{
			$rAnswer[$key] = true;
			$correct++;
		}
		else
		{							
			$rAnswer[$key] = false;
		}
		$total ++;
	}
	$t = json_decode("{}",true);
	$response["rAnswer"] = $rAnswer;
	$response["rAnswerT"] = $rAnswerT;
	$response["providedAnswer"] = $answer;
	//$response["realAwnsers"] = $allItemsValue;
	//$response["corArect"] = $answer;
	$response["correct"] = $correct;
	$response["total"] = $total;
	
	$t["id"] = $questionID;
	$t["title"] = $response["title"];

	$t["rAnswer"] = $rAnswer;
	$t["rAnswerT"] = $rAnswerT;
	$t["providedAnswer"] = $answer;
	//$response["realAwnsers"] = $allItemsValue;
	//$response["corArect"] = $answer;
	$t["correct"] = $correct;
	$t["total"] = $total;
	//echo "\n";
	if($total > 0)
	{
		//$sql = "INSERT INTO `question_stats`(`questionID`, `userID`, `resultData`,`created_At`) VALUES ('$questionID','$userID','".json_encode($t)."','".time()."')";
		//$response["sql2"] = $sql;
		//echo json_encode($t);
		//echo "\n";
		//$result = $conn->query($sql);
	}
	else
	{
		http_response_code(202);
		$response["msg"] = "No awnser needed";
	}
	echo json_encode($response);
	exit();
}


function checkValues(){
	global $response;
	global $conn;
	global $allItemsValue;
	global $userID;
	global $randomActive;
	if($userID == null)
	{
		$userID = -1;
	}
	header("US: $userID");
	$sessionID = null;
	$sessionUserID = null;

	if(isset($_GET['sessionID']))
	{
		$sessionID = $_GET['sessionID'];
		if(!isset($_COOKIE["sessionUserID"]))
		{
			$response["error"] = "Can't find session user id cookie. Please ensure that cookies are enabled";
			echo json_encode($response);
			exit();
		}
		$sessionUserID = $_COOKIE["sessionUserID"];
	}
	$sessionIndex = 0;
	if(isset($_GET['sessionIndex']))
	{
		$sessionIndex = $_GET['sessionIndex'];
	}
	$sessionResetCount = 0;
	$instanceId = $_GET["instanceId"];
	if(isset($instanceId))
	{
		srand($instanceId);
	}
	$randomActive = true;
	if(isset($_GET["randomlock"]))
	{
		//echo "FOUND RANDOM";
		$randomActive = false;
	}
	$found = false;
	if(isset($_GET["questionID"]) || isset($_GET['sessionID']))
	{
		if(isset($_GET["questionID"]))
		{
			$questionID = $_GET["questionID"];
		}
		srand($instanceId);
		if (isset($_GET['sessionID']))
		{
			$found = true;
			$sessionID = $_GET['sessionID'];
			$questionData = json_decode("{}",true);
			$answerData = json_decode("{}",true);
			$stmt = $conn->prepare("SELECT * FROM `sessions` WHERE JSON_VALUE(`data`,'$.sessionID') = ?");
			$stmt->bind_param("s", $sessionID);
			$found = false;
			$makeNew = false;
			$results = [];
			$questionIDs = [];
			if ($stmt->execute())  
			{
				$result = $stmt->get_result(); 
				while ($row = $result->fetch_assoc()) {
					$sessionStats = json_decode($row['data'],true);
					$questionIDs = $sessionStats['questionIDs'];
					$found = true;
					if(isset($sessionStats["instanceID"]))
					{
						$instanceId = $sessionStats["instanceID"];
					}
					if(isset($sessionStats["useBaseValues"]))
					{
						$randomActive = !$sessionStats["useBaseValues"];
					}
					if($sessionStats["creatorID"] == $userID)
					{
						$sessionOwner = true;
					}
					if(isset($sessionStats["sessionResetCount"]))
					{
						$sessionResetCount = $sessionStats["sessionResetCount"];
					}
				}
			}
			$questionID = $questionIDs[$sessionIndex];
		}

		$found = true;
		
		srand($instanceId);
		$questionData = json_decode("{}",true);
		$answerData = json_decode("{}",true);
		$tax = rand(1,99);
		$sql = "SELECT * FROM `questions` WHERE `questionID` = '$questionID'";
		$result = $conn->query($sql);
		//$response["sql"] = $sql;
		//echo $sql;
		$found = false;
		$question = "";
		$questionID = "";
		$answer = "";
		//$response["raw"] = "TEMP";
		while ($row = $result -> fetch_assoc())
		{
			$found = true;
			$question = urldecode(base64_decode(utf8_decode(urldecode($row["question"]))));
			$question = mb_convert_encoding($question, 'UTF-8', 'UTF-8');
			$translationTable = urldecode(base64_decode(utf8_decode(urldecode($row["translationTable"]))));
			//$response["raw"] = $question;
			$response["title"] = $row["name"];

			$answer = urldecode(base64_decode(utf8_decode(urldecode($row["answer"]))));
			$answer = mb_convert_encoding($answer, 'UTF-8', 'UTF-8');
			//$response["answerraw"] = $answer;
			$questionID = $row["questionID"];
			break;
		}
		$sql = "SELECT * FROM `questions` WHERE `questionSubID` = '$questionID'";
		$result = $conn->query($sql);
		while ($row = $result -> fetch_assoc())
		{
			$questionR = urldecode(base64_decode(utf8_decode(urldecode($row["question"]))));
			$question = $question . mb_convert_encoding($questionR, 'UTF-8', 'UTF-8');
			//$response["raw"] = $question;
			$response["title"] = $row["name"];

			$answerR = urldecode(base64_decode(utf8_decode(urldecode($row["answer"]))));
			$answer = $answer . mb_convert_encoding($answerR, 'UTF-8', 'UTF-8');
			//$response["answerraw"] = $answer;
			$questionID = $row["questionID"];
			break;
		}
		$translationTable = json_decode($translationTable,true);
		foreach ($translationTable as $key => $value) {
			addItemToArray($value[1], $questionData);
		}

		if($found === true)
		{
			$t = $question;
			//echo strpos($t,"%c[0,0]");
			//var_dump(explode("%",$t));
			//echo "<br>";
			$t = str_replace("\n","<br>", $t);
			//$response["text222"] = $answer;
			//$response["answer"] = $t;
			$questionData["text"] = $t . "|||||" . $answer;
			$answerData["text"] = $answer;
			//echo json_encode($response);
			//$allItems = explode("%n", $questionData["text"]);
			
			//echo $questionData["text"];
			try{
				$questionData = parseText($questionData);
				//var_dump($allItemsValue);
				$questionData = secondParseTextLayer($questionData);
				//var_dump($allItemsValue);
			}
			catch (\Throwable $e)
			{
				echo $e;
			}
			//$response["AAA"] = $allItemsValue;
		}
	}
	if($found)
	{
		$correct = 0;
		$total = 0;
		$answer = base64_decode($_GET["answer"]);
		//echo $answer;var_dump($allItemsValue[$key])
		$answer = json_decode($answer,true);
		$rAnswer = json_decode("{}",true);
		$rAnswerT = json_decode("{}",true);
		//var_dump($allItemsValue);
		foreach($answer as $key => $value) 
		{
			//var_dump($allItemsValue[$key]);
			//echo $answer[$key][1] . " " . var_dump($allItemsValue[$key]) . " " .$key . "\n";
			$rAnswerT[$key] = $allItemsValue[$key][0];
			if(str_starts_with($answer[$key], "=") && json_decode($allItemsValue[$key][1],true)["type"] != "answerText")
			{
				$cleanedEquation = str_replace("=","",$answer[$key]);
				array_push($response['stack'], $cleanedEquation);
				$out = safeEval($cleanedEquation);
				if($out === "NOT VALID EQUATION")
				{
				}
				else
				{
					$answer[$key] = safeEval($cleanedEquation);
				}
				array_push($response['stack'], $out);
			}
			//var_dump($allItemsValue[$key][0]);
			//echo str_replace(",","",$answer[$key][1]) . str_replace(",","",$allItemsValue[$key][0]);
			if(str_replace(",","",$answer[$key]) == str_replace(",","",$allItemsValue[$key][0]))
			{
				$rAnswer[$key] = true;
				$correct++;
			}
			else
			{							
				$rAnswer[$key] = false;
			}
			$total ++;
		}
		$t = json_decode("{}",true);
		$response["rAnswer"] = $rAnswer;
		$response["rAnswerT"] = $rAnswerT;
		$response["providedAnswer"] = $answer;
		$response["sessionResetCount"] = $sessionResetCount;
		//$response["realAwnsers"] = $allItemsValue;
		//$response["corArect"] = $answer;
		$response["correct"] = $correct;
		$response["total"] = $total;
		
		$t["id"] = $questionID;
		$t["title"] = $response["title"];

		$t["rAnswer"] = $rAnswer;
		$t["rAnswerT"] = $rAnswerT;
		$t["providedAnswer"] = $answer;
		$t["sessionID"] = $sessionID;
		$t["sessionIndex"] = $sessionIndex;
		$t["sessionResetCount"] = $sessionResetCount;
		$t["sessionUserID"] = $sessionUserID;
		//$response["realAwnsers"] = $allItemsValue;
		//$response["corArect"] = $answer;
		$t["correct"] = $correct;
		$t["total"] = $total;
		//echo "\n";
		if($total > 0)
		{
			if($sessionID != null){
				$stmt = $conn->prepare("SELECT * FROM `question_stats` WHERE `sessionID` = ? AND JSON_VALUE(`resultData`, '$.sessionResetCount') = ? AND JSON_VALUE(`resultData`, '$.sessionUserID') = ?");
				$stmt->bind_param("sss", $sessionID, $sessionResetCount, $sessionUserID);
				$found = false;
				$makeNew = false;
				$shortCode = null;
				if ($stmt->execute()) {
					$result = $stmt->get_result(); 
					while ($row = $result->fetch_assoc()) {
						$stmtUpdate = $conn->prepare("UPDATE `question_stats` SET `resultData`= ? WHERE `id` = ?");
						$encodedT = json_encode($t);
						$stmtUpdate->bind_param("ss", $encodedT, $row["id"]);
						$stmtUpdate->execute();
						echo json_encode($response);
						exit();
						break;
					}
				}
				$sql = "INSERT INTO `question_stats`(`questionID`, `userID`, `resultData`,`sessionID`,`created_At`) VALUES ('$questionID','$userID','".json_encode($t)."','$sessionID','".time()."')";
				$response["sql2"] = $sql;
				$result = $conn->query($sql);
			}
			else
			{		
				$sql = "INSERT INTO `question_stats`(`questionID`, `userID`, `resultData`,`sessionID`,`created_At`) VALUES ('$questionID','$userID','".json_encode($t)."','$sessionID','".time()."')";
				$response["sql2"] = $sql;
				$result = $conn->query($sql);
			}
		}
		else
		{
			http_response_code(202);
			$response["msg"] = "No awnser needed";
		}
	}
	else
	{
		http_response_code(404);
		$response["msg"] = "No session or question id given";
	}
	echo json_encode($response);
	exit();
}

$nameToIdDict = json_decode("{}",true);
$allDocuments = json_decode("{}",true);
function addItemToArray($item, $questionData){
	global $allItemsValue;
	global $randomActive;
	global $allDocuments;
	global $nameToIdDict;
	global $response;
	$c2 = $item;
	$ss = explode("}",$c2)[0] . "}";
	$c2 = $ss;
	$ss = "{". explode("{",$ss)[1];
	$startText = "";
	$jsonData = json_decode($ss,true);
	header("ATA: " . $randomActive);
	header("Content-Type2: text/json");
	$aa = json_decode($ss,true);
	if(!isset($aa["type"]))
	{
		//echo $ss;
	}
	else
	{
		//echo $aa["type"];
		if(isset($aa["varName"]) && !empty($aa["varName"]))
		{
			array_push($response['stack'], "added ".$aa["varName"]);
			$nameToIdDict[$aa["varName"]] = $jsonData["currentID"];
		}
		if($aa["type"] == "answer" || $aa["type"] == "equation")
		{			
			if($aa["values"] != "" || $aa["values"] != [] || $aa["values"] != [""])
			{
				array_push($response['stack'],"DROP DOWN");
				$allItemsValue[$jsonData["currentID"]] = [0,$ss, "%".$startText];
				//$questionData["text"] = str_replace('%'.$ss,"<a>afawd</a>",$questionData["text"]);
			}
			else
			{
				$allEqu[$jsonData["currentID"]] = [$jsonData["equation"],$ss,"%".$startText];
				$allItemsValue[$jsonData["currentID"]] = [0,$ss, "%".$startText];
			}
		}
		elseif($aa["type"] == "time")
		{
			$allItemsValue[$jsonData["currentID"]] = [0,$ss, "%".$startText];
		}
		else if($aa["type"] == "subDocument" || $aa["type"] == "subdocument" || $aa["type"] == "document")
		{
			$allItemsValue[$jsonData["currentID"]] = [0,$ss, "%".$startText];
			$allDocuments[$jsonData["currentID"]] = [$ss, $c2];
			//var_dump($allDocuments);
		}
		else
		{
			$allItemsValue[$aa["currentID"]] = [$aa["baseText"],$aa];
			
			if(isset($aa["r"]) && $aa["r"] == true && $randomActive == true)
			{
				header("IRt:" .$aa["rType"]);
				if(isset($aa["rType"]) )
				{
					if($aa["rType"] == "range")
					{
						try
						{
							header("rtype:".$aa["rType"]);
							if(is_numeric($aa["values"][0]) && is_numeric($aa["values"][1]))
							{
								$rV = rand((int)($aa["values"][0]),(int)($aa["values"][1]));
								//$questionData["text"] = str_replace('%'.$c2,number_format($rV),$questionData["text"]);
								$allItemsValue[$aa["currentID"]] = [$rV,$ss];
							}
							else
							{
								$rV = array_rand($aa["values"]);
								//$questionData["text"] = str_replace('%'.$c2,number_format($aa["values"][$rV]),$questionData["text"]);
								$allItemsValue[$aa["currentID"]] = [$rV,$ss];
							}
						}
						catch(Exception  $e)
						{
							//echo $e;
							try{
								$rV = array_rand($aa["values"]);
								//$questionData["text"] = str_replace('%'.$c2,number_format($aa["values"][$rV]),$questionData["text"]);
								$allItemsValue[$aa["currentID"]] = [$rV,$ss];
							}
							catch(Exception  $e)
							{
								$questionData["text"] = str_replace('%'.$c2,"ERROR IN RANDOM",$questionData["text"]);
							}
						}
					}
					elseif($aa["rType"] == "list")
					{
						$rV = array_rand($aa["values"]);
						header("I:" .$rV);
						//$questionData["text"] = str_replace('%'.$c2,$aa["values"][$rV],$questionData["text"]);
						$allItemsValue[$aa["currentID"]] = [$rV,$ss];
					}		
					else
					{
						$rV = array_rand($aa["values"]);
						//$questionData["text"] = str_replace('%'.$c2,number_format($aa["values"][$rV]),$questionData["text"]);
						$allItemsValue[$aa["currentID"]] = [$rV,$ss];
					}					
				}
				else
				{								
					$allItemsValue[$aa["currentID"]] = [$aa["baseText"],$ss];
					/*
					if($aa["type"] == "number")
					{
						$questionData["text"] = str_replace('%'.$c2,number_format((float)$aa["baseText"]),$questionData["text"]);
					}
					else
					{
						$questionData["text"] = str_replace('%'.$c2,number_format($aa["baseText"]),$questionData["text"]);
					}*/
					//str_replace("%var".$c, $allItemsValue[$values[1]], $questionData["text"])." ";
				}
			}
			else
			{
				$allItemsValue[$aa["currentID"]] = [$aa["baseText"],$ss];
				/*
				if($aa["type"] == "number")
				{
					$questionData["text"] = str_replace('%'.$c2,number_format((float)$aa["baseText"]),$questionData["text"]);
				}
				else
				{
					$questionData["text"] = str_replace('%'.$c2,$aa["baseText"],$questionData["text"]);
				}*/
				//str_replace("%var".$c, $allItemsValue[$values[1]], $questionData["text"])." ";
			}
		}
	}
}

function valueToKey($dict, $value)
{
	foreach ($dict as $i)
	{
		if($dict[$i] == $value)
		{
			return $i;
		}
	}
}

//Added 26/07/2024 3:30
function calcEqu($eq){
	global $response;
	/*echo $eq . "<br>";
	$p = 0;
	if(preg_match('/(\d+)(?:\s*)([\+\-\*\/])(?:\s*)(\d+)/', $eq, $matches) !== FALSE){
		$operator = $matches[2];
	
		switch($operator){
			case '+':
				$p = $matches[1] + $matches[3];
				break;
			case '-':
				$p = $matches[1] - $matches[3];
				break;
			case '*':
				$p = $matches[1] * $matches[3];
				break;
			case '/':
				$p = $matches[1] / $matches[3];
				break;
			case '^':
				$p = $matches[1] ^ $matches[3];
				break;	
		}
	
		echo $p. "<br>";
	}*/
	//echo $eq."<br><br>";
	try{
		$o = safeEval($eq);//eval("return " .strtolower($eq).";");
		array_push($response['stack'],"OUT: " . $o);
	}
	catch (\Throwable $e)
	{
		array_push($response['stack'],"ERROR: " . $e);
		$o = "0";
	}

	return $o;
}

//Added 26/07/2024 3:30
function getJSON($id){
	global $allItemsValue;
	//header("A: ".json_encode($allItemsValue));
	foreach($allItemsValue as $key => $val) 
	{		
		//var_dump($allItemsValue[$key][1]);
		$jsonData = json_decode($allItemsValue[$key][1],true);
		if($jsonData["currentID"] == $id)
		{
			return $key;
		}
	}
	return -1;
}

//Added 26/07/2024 3:30
function getEquValue($idCurrent, $lastEq = null, $stack = null, $allIds = []){
	global $allItemsValue;
	global $debug;
	global $response;
	$ss2 = explode("}",$allItemsValue[$idCurrent][1])[0] . "}";
	$c2 = $ss2;
	header("ss: ". $ss2);
	$ss = "{". explode("{",$ss2)[1]; 
	$jsonData = json_decode($ss,true);
	$e = base64_decode($jsonData["equation"]);
	//echo $e;
	if(str_contains($e,":"))
	{
		$ifItems = explode(":", $e);
		$varItems = explode(" ", $ifItems);
		//var_dump($varItems);
		foreach($varItems as $item)
		{
			if(str_contains($item,"$"))
			{
				$sC = str_replace("$","",$item);
				$id = getJSON($sC);
				if($id != $idCurrent)
				{
					//echo $id;
					if($id != $sC || $id == -1)
					{
						//echo "SHIFT VALUE FOUND " . $sC . " " . $id;
						//var_dump($allItemsValue);
					}

					$eT = json_decode($allItemsValue[$id][1],true);
					if($eT["type"] == "equation" || $eT["type"] == "answer")
					{
						$eq = str_replace($item, getEquValue($eT["currentID"],$eq,$stack)[0], $eq);
					}
					else
					{
						$eq = str_replace($item, $allItemsValue[$id][0], $eq);
					}
				}
			}			
		}
		$eq = str_replace("$","",$eq);
		//$eq = str_replace(",","",$eq);
		//$equationResult = calcEqu($eq);
		//echo $eq;
		//echo eval($equationResult);
	}
	//echo "AAA<br>";
	$e = str_replace("(","( ", $e);
	$e = str_replace(")"," )", $e);
	$e2 = explode(" ", $e);
	$eq = $e;
	$stackItem = [];
	if($debug)
	{
		array_push($stack, "FULL EQ: ". $e);
		array_push($stack, "FULL EQ PARMS: ");
		array_push($stack, $e2);
	}
	//echo eval("1 > 2 ? 1 : 2");
	//var_dump($e2);
	foreach($e2 as $s)
	{
		if(str_contains($s,"$"))
		{
			if(strtolower($s) == "round(")
			{}
			else
			{
				$sC = str_replace("$","",$s);
				
				$id = getJSON($sC);
				//echo $id;
				if($id != $sC || $id == -1)
				{
					//echo "SHIFT VALUE FOUND " . $sC . " " . $id;
					//var_dump($allItemsValue);
				}
				if($idCurrent != $id)
				{
					if($debug)
					{
						array_push($stack, $sC);
					}
					if(is_numeric($sC))
					{
						$eT = json_decode($allItemsValue[$sC][1],true);
						if($debug)
						{
							array_push($stack, $eT['currentID'] . " : " . $allItemsValue[$sC][0]);
						}
						if($eT["type"] == "equation" || $eT["type"] == "answer")
						{
							if(in_array($eT['currentID'],$allIds))
							{
								$eq = str_replace($s, 'RECURSIVE' , $eq);
							}
							else
							{
								array_push($allIds, $eT['currentID']);
								$eq = str_replace($s, getEquValue($eT["currentID"],$eq,$stack, $allIds)[0], $eq);
							}
						}
						else
						{
							$eq = str_replace($s, $allItemsValue[$sC][0], $eq);
						}
					}
				}
			}
		}
		
	}
	$eq = str_replace("$","",$eq);
	if($debug)
	{
		array_push($stack, "COMPLETED EQ: " . $eq);
	}
	//$eq = str_replace(",","",$eq);
	$rr = calcEqu($eq);
	//var_dump($eq);
	//var_dump($rr);
	//echo $rr."<br>";
	if($debug)
	{
		//array_push($stack, $rr);
		array_push($response["stack"], $stack);
	}
	return [$rr,$eq];
}

//Added 26/07/2024 3:30
function solveEqu($questionData){
	global $debug;
	global $response;
	global $allItemsValue;
	global $allEqu;
	global $presentationMode;
	global $sessionOwner;

	
	if($debug)
	{
		array_push($response["stack"], $allEqu);
	}

	$rawEquation = "";
	if($allEqu != null && sizeof($allEqu) > 0)
	{
		foreach($allEqu as $key => $val) {
			$placeholder = "";
			$equation = "";
			
			$ss = explode("}",$allEqu[$key][1])[0] . "}";
			$c2 = $ss;
			$ss = "{". explode("{",$ss)[1]; 
			$jsonData = json_decode($ss,true);
			$stack = [];
			if($debug)
			{
				array_push($stack, "SOLVE EQU: " . $jsonData["currentID"]);
			}
			$output = getEquValue($jsonData["currentID"], "",$stack);
			if($debug)
			{
				array_push($response["stack"], "LOOKING FOR "."%var".$allEqu[$key][1]);
			}
			$html = "<div>";
			$allItemsValue[$jsonData["currentID"]][0] = $output[0];
			$allItemsValue[$jsonData["currentID"]][2] = $output[1];
			if($jsonData["type"] == "equation")
			{
				$html .= '<span> ' . $output[0] . '</span><span class="helpQuestionInfo" data-hover="'."Equation: ".$jsonData["rawEquation"]. " Filled Equation: ".$output[1].'">?</span>';
			}
			else
			{
				$html .= '<input class="answer" style="background-color: yellow;" onfocusout="checkValue('.$jsonData["currentID"].')" onchange="inputChanged('.$jsonData["currentID"].')" placeholder="'.$placeholder.'" x="'.$output[0].'" id=' . $jsonData["currentID"] . '> </input><span class="helpQuestionInfo" data-hover="'."Equation: ".$jsonData["rawEquation"]. " Filled Equation: ".$output[1].'">?</span>' ;
			}
			if($presentationMode && $sessionOwner && $jsonData["type"] != "equation")
			{
				if($sessionOwner && $jsonData["type"] != "equation")
				{
					$html .= "<span><button onmouseover=openStatsForVar('" . $jsonData['currentID'] . "') onclick=openStatsForVarChart('" . $jsonData['currentID'] . "') class='btn statsBox helpQuestionInfo ".$jsonData['currentID']."-res"."' data-hover='aa'> + </button></span>";
				}
			}
			else
			{
				$html .= '<span><button onclick=openChatMenu("' . $jsonData["currentID"] . '") class="btn"> * </button></span>';
			}
			$html .= "</div>";
			$questionData["text"] = str_replace("%var".$allEqu[$key][1], $html, $questionData["text"])." ";
		}
	}			
	
	addToStack(explode("|||||",$questionData["text"])[0]);
	$questionData = documentManager($questionData);					
	return $questionData;					
}

function getSubDocument($id, $ci, $documentsText){
	global $debug;
	global $response;
	global $allItemsValue;
	global $allEqu;
	global $presentationMode;
	global $sessionOwner;
	global $allDocuments;
	global $nameToIdDict;

	$itemInfo = json_decode($allItemsValue[$ci][1],true);
	array_push($response['stack'],$itemInfo);
	$placeholder = "";
	if($itemInfo["type"] == "answer")
	{
		$documentsText[$id] = $documentsText[$id] .  '<td><input class="answer" style="background-color: yellow;" onchange="inputChanged('.$itemInfo["currentID"].')" placeholder="'.$placeholder.'" x="'.$allItemsValue[$ci][0].'" id=' . $itemInfo["currentID"] . '> </input><span class="helpQuestionInfo" data-hover="'."Equation: ".$itemInfo["rawEquation"]. " Filled Equation: ".$allItemsValue[$ci][2].'">?</span></td>';
	}
	else if($itemInfo["type"] == "answerText")
	{
		$documentsText[$id] = $documentsText[$id] .  '<td><input class="answer" style="background-color: yellow;" onchange="inputChanged('.$itemInfo["currentID"].')" placeholder="'.$placeholder.'" x="'.$allItemsValue[$ci][0].'" id=' . $itemInfo["currentID"] . '> </input><span class="helpQuestionInfo" data-hover="">?</span></td>';
	}
	else
	{
		$documentsText[$id] = $documentsText[$id] .  "<td>". $allItemsValue[$ci][0]. "</td>";
	}
	return $documentsText;
}


function documentManager($questionData){
	global $debug;
	global $response;
	global $allItemsValue;
	global $allEqu;
	global $presentationMode;
	global $sessionOwner;
	global $allDocuments;
	global $nameToIdDict;
	$documentsText = json_decode("{}",true);

	addToStack(explode("|||||",$questionData["text"])[0]);
	if($allDocuments !== null && sizeof($allDocuments) > 0)
	{
		array_push($response['stack'],$nameToIdDict);
		foreach($allDocuments as $key => $val) {
			$jsonItem = json_decode($allDocuments[$key][0],true);
			
			addToStack($jsonItem["type"]);
			addToStack($jsonItem);
			$id = $jsonItem['currentID'];
			if(isset($jsonItem["linkID"]))
			{
				//$id = $jsonItem["linkID"];
			//	$questionData["text"] = str_replace($allDocuments[$key][1],"",$questionData["text"]);
			}
			if(!isset($documentsText[$id]))
			{
				$documentsText[$id][0] = $allDocuments[$key][1];
				$documentsText[$id][1] = "<table id = $id class='table table-hover table-bordered 10 self-table-answer'><tbody>";
			}
			if($jsonItem["type"] === "subDocument")
			{				
				$items = explode(",",$jsonItem["baseText"] . ",");
				$documentsText[$id][1] .= "<tr>";
				foreach($items as $item)
				{	
					$item = str_replace(" ","",$item);
					if($debug)
					{
						array_push($response['stack'],$item);
					}
					if(str_starts_with($item,"$"))
					{						
						$ci = $nameToIdDict[$item];
						if($debug)
						{
							array_push($response['stack'],"CI: ". $ci);
							array_push($response['stack'],$allItemsValue[$ci]);
						}
						array_push($response['stack'],$allItemsValue[$ci]);
						
						$itemInfo = json_decode($allItemsValue[$ci][1],true);
						array_push($response['stack'],$itemInfo);
						$placeholder = "";
						if($itemInfo["type"] == "answer")
						{
							$documentsText[$id][1] .= '<td><input class="answer" style="width:100%;background-color: yellow;" onchange="inputChanged('.$itemInfo["currentID"].')" placeholder="'.$placeholder.'" x="'.$allItemsValue[$ci][0].'" id=' . $itemInfo["currentID"] . '></td>';
						}
						else if($itemInfo["type"] == "answerText")
						{
							$documentsText[$id][1] .= '<td><input class="answer" style="width:100%;background-color: yellow;" onchange="inputChanged('.$itemInfo["currentID"].')" placeholder="'.$placeholder.'" x="'.$allItemsValue[$ci][0].'" id=' . $itemInfo["currentID"] . '> </input></td>';
						}
						else
						{
							$documentsText[$id][1] .= "<td>". $allItemsValue[$ci][0]. "</td>";
						}
					}
					else if($item !== "")
					{
						$documentsText[$id][1] .= "<td>". $item. "</td>";
					}
				}
				$documentsText[$id][1] .= "</tr>";
				array_push($response['stack'],$allDocuments[$key][1]);
			}
			else if($jsonItem['type'] == "document")
			{
				addToStack($documentsText[$id]);
				addToStack($jsonItem["baseText"]);
				$a = base64_decode($jsonItem["baseText"]);
				//$a = substr($a,1,strlen($a)- 2);
				//echo $a;
				$tableInfo = json_decode($a,true);
				//var_dump($a);
				//echo(base64_decode($jsonItem["baseText"]));
				//echo(json_decode(base64_decode($jsonItem["baseText"]),true)['cols']);
				//var_dump($tableInfo['sectionList']);
				$rows = $tableInfo["rows"];
				$cols = $tableInfo["cols"];
				$maxSubCellRows = $tableInfo["maxSubCellRows"];
				$sectionNames = [];
				$sectionInfo = json_decode("{}",true);
				$sectionNamesOptions = "<select>";
				foreach($tableInfo['sectionList'] as $item)
				{
					array_push($sectionNames,$item['name']);
					foreach($item['sectioninfo'] as $key => $val)
					{
						$x = explode(":",$val);
						//var_dump($x);
						//var_dump($allItemsValue[$x[1]]);
						//var_dump();
						if($x[0] == "set")
						{
							$item['sectioninfo'][$key] = "set:" . json_encode(json_decode($x[1],true));
						}
						else
						{
							$item['sectioninfo'][$key] = json_encode(json_decode($allItemsValue[$x[1]][1],true)["values"]);
						}
						
					}
					$sectionInfo[$item['name']] = $item['sectioninfo'];
					$sectionNamesOptions .= "<option onclick=checkValue('".$jsonItem["currentID"]."')>". trim($item['name'], " \n\r\t\v\x00"). "</option>";
				}
				$sectionNamesOptions .= "</select>";
				$documentsText[$id][1] = "<table id = '$id' maxSubCellRows = '$maxSubCellRows' colsAmount ='".$cols."' sectionNames='".json_encode($sectionNames) . "' class='table table-hover table-bordered 10 self-table-answer' sectionInfo='".json_encode($sectionInfo)."'><tbody>";

				
				$documentsText[$id][1] .= "<tr section=''><td colspan=".$cols." style='text-align:center'>$sectionNamesOptions</td></tr>";
				if(!isset($documentsText[$id]))
				{
					$documentsText[$id][1] = "<table class='table table-hover table-bordered 10 self-table-answer'><tbody>";
				}
			}
			else if($jsonItem["type"] === "document2")
			{
				$items = explode(",",$jsonItem["baseText"] . ",");
				$documentsText[$id] .= "<tr>";
				if(!isset($documentsText[$id]))
				{
					$documentsText[$id] = [];
					array_push($documentsText[$id],$allDocuments[$key]);
					array_push($documentsText[$id],"<table class='table table-hover table-bordered 10'><tbody>");
				}
				foreach($items as $item)
				{	
					$item = str_replace(" ","",$item);
					if($debug)
					{
						array_push($response['stack'],"ITEM: ".$item);
					}
					if(str_contains($item,";"))
					{
						$subSubItems = explode(";",$item);
						foreach($subSubItems as $subSubItem)
						{
							if(str_starts_with($subSubItem,"$"))
							{				
								$ci = $nameToIdDict[$subSubItem];
								$documentsText = getSubDocument($id, $ci,$documentsText);
								$documentsText[$id][1] = "";
								if(isset($documentsText[$ci]))
								{
									array_push($response['stack'],$documentsText[$ci]);
								}	
							}
						}
					}
					else if(str_starts_with($item,"$"))
					{				
						$ci = $nameToIdDict[$item];
						$documentsText = getSubDocument($id, $ci,$documentsText);
						$documentsText[$id] = "";
						if(isset($documentsText[$ci]))
						{
							array_push($response['stack'],$documentsText[$ci]);
						}						
					}
					else
					{
						$documentsText[$id] = $documentsText[$id] . "<td>". $item. "</td>";
					}
				}
			}
			addToStack($documentsText[$id]);
			addToStack("-------------");
		}	

		foreach($allDocuments as $key => $val) {
			$jsonItem = json_decode($allDocuments[$key][0],true);
			$id = $jsonItem['currentID'];
			if($jsonItem["type"] == "document2")
			{
				array_push($response['stack'],$jsonItem);
				$rawItems = str_contains($jsonItem["baseText"],"|");
				if(str_contains($jsonItem["baseText"],"|"))
				{
					$documentsText[$id][1] = "";	
					$subDocs = explode("|",$jsonItem["baseText"] . "|");
					foreach($subDocs as $subDoc)
					{	
						$documentsText[$id] .= "<tr>";
						$items = explode(",",$subDoc);
						foreach($items as $item)
						{	
							$item = str_replace(" ","",$item);
							if($debug)
							{
								array_push($response['stack'],"FINAL REPLACE: " .$item);
							}
							if(str_contains($item,";"))
							{
								$subSubItems = explode(";",$item);
								$documentsText[$id] .= "<td>";
								foreach($subSubItems as $subSubItem)
								{
									if(str_starts_with($subSubItem,"$"))
									{						
										$ci = $nameToIdDict[$subSubItem];
										$itemInfo = json_decode($allItemsValue[$ci][1],true);
										array_push($response['stack'],$itemInfo);
										$placeholder = "";
										if($itemInfo["type"] == "answer")
										{
											$documentsText[$id] = $documentsText[$id] .  '<input class="answer" style="width:100%;background-color: yellow;" onchange="inputChanged('.$itemInfo["currentID"].')" placeholder="'.$placeholder.'" x="'.$allItemsValue[$ci][0].'" id=' . $itemInfo["currentID"] . '><br>';
										}
										else if($itemInfo["type"] == "answerText")
										{
											$documentsText[$id] = $documentsText[$id] .  '<input class="answer" style="width:100%;background-color: yellow;" onchange="inputChanged('.$itemInfo["currentID"].')" placeholder="'.$placeholder.'" x="'.$allItemsValue[$ci][0].'" id=' . $itemInfo["currentID"] . '> </input><br>';
										}
										else
										{
											$documentsText[$id] = $documentsText[$id] .  $allItemsValue[$ci][0]. "<br>";
										}
									}
									else
									{
										$documentsText[$id] .= $subSubItem. "<br>";
									}
								}
								
								$documentsText[$id] .= "</td>";
							}
							else
							{
								if(str_starts_with($item,"$"))
								{						
									$ci = $nameToIdDict[$item];
									$itemInfo = json_decode($allItemsValue[$ci][1],true);
									array_push($response['stack'],$itemInfo);
									$placeholder = "";
									if($itemInfo["type"] == "answer")
									{
										$documentsText[$id][1] = $documentsText[$id][1] .  '<td><input class="answer" style="width:100%;background-color: yellow;" onchange="inputChanged('.$itemInfo["currentID"].')" placeholder="'.$placeholder.'" x="'.$allItemsValue[$ci][0].'" id=' . $itemInfo["currentID"] . '></td>';
									}
									else if($itemInfo["type"] == "answerText")
									{
										$documentsText[$id][1] = $documentsText[$id][1] .  '<td><input class="answer" style="width:100%;background-color: yellow;" onchange="inputChanged('.$itemInfo["currentID"].')" placeholder="'.$placeholder.'" x="'.$allItemsValue[$ci][0].'" id=' . $itemInfo["currentID"] . '> </input></td>';
									}
									else
									{
										$documentsText[$id][1] = $documentsText[$id][1] .  "<td>". $allItemsValue[$ci][0]. "</td>";
									}
								}
								else
								{
									$documentsText[$id][1] .= "<td>". $item. "</td>";
								}
							}
						}
						$documentsText[$id][1] .= "</tr>";
					}
					array_push($response['stack'],$documentsText[$id]);
					$documentsText[$id] = substr($documentsText[$id], 0, strlen($documentsText[$id]) - 14);
					
					array_push($response['stack'],$documentsText[$id]);
				}
				else
				{
					$items = explode(",",$jsonItem["baseText"] . ",");
					foreach($items as $item)
					{	
						$item = str_replace(" ","",$item);
						if($debug)
						{
							array_push($response['stack'],"FINAL REPLACE: " .$item);
						}
						if(str_starts_with($item,"$"))
						{						
							$ci = $nameToIdDict[$item];
							if($debug)
							{
								array_push($response['stack'],"ci: " .$ci);
								array_push($response['stack'],$documentsText[$ci]);
							}
							if(isset($documentsText[$ci]))
							{
								$documentsText[$id][1] .= $documentsText[$ci];
								//$documentsText[$id][1] = str_replace("<table class='table table-hover table-bordered 10'><tbody>", "", $documentsText[$id]);
								array_push($response['stack'],"ID FINAL: " . $id);
								array_push($response['stack'],$documentsText[$id]);
							}
						}
					}
				}
				$documentsText[$id][1] = "<table class='table table-hover table-bordered 10'><tbody>" . $documentsText[$id];
			}
		}

		foreach($documentsText as $key => $val) {
			$documentsText[$key][1] .= "</tbody></table><button class='btn' onclick=addRow($key)>Add Row</button><button class='btn' onclick=addSectionRow($key)>Add Section</button>";
		}
		addToStack($documentsText);
		addToStack(explode("|||||",$questionData["text"])[0]);
		foreach($documentsText as $key => $val) {
			//array_push($response['stack'],$key, $allDocuments[$key][1]);
			addToStack("DOCUMENT TEXT KEY: " . $key);
			addToStack($documentsText[$key]);
			//var_dump($documentsText);
			$questionData["text"] = str_replace($documentsText[$key][0],$documentsText[$key][1],$questionData["text"]);
		}
	}		
	return $questionData;					
}

$allEqu = json_decode("{}",true);
function secondParseTextLayer($questionData){
	global $allEqu;
	global $companies;
	global $tax;
	global $allItemsValue;
	$allItemsLine = explode("<br>", $questionData["text"]);
	foreach($allItemsLine as $l)
	{
		$allItems = explode("%",$l);
		//var_dump($allItems);
		//echo $l;
		foreach ($allItems as $i)
		{
			try
			{
				$c = $i;
				
				if(substr($c,0,4) == "sum{" || substr($c,0,2) == "s{")
				{								
					$c = explode("}",$c)[0] . "}";
					$startText = "";
					//echo $c;
					if(substr($c,0,4) == "sum{")
					{
						$startText = substr($c,0,3);
						$c = substr($c,3);
					}
					else
					{
						$startText = substr($c,0,1);
						$c = substr($c,1);
					}
					
					$cleanC = str_replace("sum{","",$c);
					$cleanC = str_replace("s{","",$cleanC);
					$jsonData = json_decode($cleanC,true);
					$values = $jsonData["values"];
					$data = 0;
					$response["sum"] = $values;
					$response["size"] = sizeof($values);
					if(sizeof($values) > 0 && $values[0] != "")
					{
						for($dc = 0; $dc < sizeof($values); $dc++)
						{
							if(isset($allItemsValue[$values[$dc]]) && $values[$dc] != "")
							{
								$response["a"] = $allItemsValue[$values[$dc]][0];
								$data += $allItemsValue[$values[$dc]][0];
							}
						}
					}
					$response["data"] = $data;
					$allItemsValue[$jsonData["currentID"]] = [$data,$cleanC];
					$questionData["text"] = str_replace("%".$startText.$c, $allItemsValue[$jsonData["currentID"]], $questionData["text"])." ";
				}
				else if(substr($c,0,7) == "answer2{" || substr($c,0,2) == "a{")
				{									
					$c = explode("}",$c)[0] . "}";
					$startText = "";
					if(substr($c,0,7) == "answer{")
					{
						$startText = substr($c,0,6);
						$c = substr($c,6);
					}
					else
					{
						$startText = substr($c,0,1);
						$c = substr($c,1);
					}
					$cleanC = str_replace("answer{","",$c);
					$cleanC = str_replace("a{","",$cleanC);
					//$cleanC = "{" . $cleanC;
					$jsonData = json_decode($cleanC,true);
					$valueEquation = 0;
					$fV = false;
					$placeholder = "";
					$equation = "";
					//echo $cleanC;
					if($jsonData != null && array_key_exists("equation",$jsonData))
					{
						if(array_key_exists($jsonData["currentID"], $allItemsValue))
						{							
							//$allItemsValue[$jsonData["currentID"]] = $valueEquation;
							/*if(array_key_exists("placeholder",$jsonData))
							{
								$placeholder = $jsonData["placeholder"];
							}*/
							//25/07/2024 Added
							//$html = '<input class="answer" style="background-color: yellow;" onchange="inputChanged('.$jsonData["currentID"].')" placeholder="'.$placeholder.'" x="'.$valueEquation.'" id=' . $jsonData["currentID"] . '> </input><span class="helpQuestionInfo" data-hover="'."EQUATION: " . $placeholder.'">?</span>';
							//$questionData["text"] = str_replace("%".$startText.$c, $html, $questionData["text"])." ";
						}
						else
						{
							$end = "";
							if($jsonData["equation"] !== null || $jsonData["equation"] !== "")
							{
								$equation = base64_decode($jsonData["equation"]);
								//$equation = str_replace(" ","",$equation);
								$equation = str_replace("+"," + ", $equation);
								$equation = str_replace("-"," - ", $equation);
								$equation = str_replace("/"," / ", $equation);
								$equation = str_replace("*"," * ", $equation);
								$equation = str_replace("^"," ^ ", $equation);
								$equation = str_replace(",","", $equation);
								$rawEquation = $jsonData["rawEquation"];
								$end = substr($equation, strlen($equation) - 1);
								$allEqu[$jsonData["currentID"]] = [$jsonData["equation"],$c,"%".$startText];
								$allItemsValue[$jsonData["currentID"]] = [0,$cleanC, "%".$startText];//$valueEquation;
								
								//echo "AA".$equation."\n\n\n<br><br>";
								//echo $equation;
								//echo "\n\n\n<br><br>";
								//echo $equation."\n\n\n<br><br>".$jsonData["currentID"] ;
								$e = explode(" ", $equation);
								//var_dump($e);
								//var_dump($allItemsValue);
								foreach($e as $key) {
									//echo $key;
									try{
										$key = str_replace("(","",$key);
										$key = str_replace(")","",$key);
										$key = str_replace(",","",$key);
										if(substr($key,0,1) == "$")
										{
											$key = str_replace("$","",$key);
											//echo $key."<br>";
											//echo $equation. "<br>".$key . "<br><br>";
											$fV = true;
											if(array_key_exists($key,$allItemsValue))
											{
												//$equation = str_replace("$". $key,$allItemsValue[$key] ,$equation);
											}
										}
									}
									catch(\Throwable $e)
									{
										echo $e;
									}

								}
							}
							else
							{
								$allEqu[$jsonData["currentID"]] = ["",$c,"%".$startText];
								$allItemsValue[$jsonData["currentID"]] = [0,$cleanC, "%".$startText];//$valueEquation;
								
							}
							/*
							if($equation !== "()" && $equation !== "")
							{
								//$equation = $equation . $end;
								$equation = str_replace(",","",$equation);
								if(str_contains($equation,"$"))
								{
								}
								else
								{
								//echo $equation . "\n<br>";
								//echo "(".$equation.")". "\n<br>";;
								//eval('$valueEquation = (((35000 - 27000) + (90000 - 75000) + (60000 - 50000)) * .7);');
								//echo "valueEquation = (".$equation.");" . "<br><br>";
								eval('$valueEquation = ('.$equation.');');
								}
								//$valueEquation = (((35000 - 27000) + (90000 - 75000) + (60000 - 50000)) * .7);
								//eval('$valueEquation = ('.$equation.');');
								//echo $valueEquation . "<br>";
							}
							*/
						}
						//if($fV == true)
						//{
							//echo $jsonData["currentID"] . "\n<br>";
							
						//}
						///else
						//{
						//	$allItemsValue[$jsonData["currentID"]] = 0;
						//}
						if(array_key_exists("placeholder",$jsonData))
						{
							$placeholder = $jsonData["placeholder"];
						}
						//var_dump($allItemsValue);
						//25/07/2024 Added
					//	$html = '<input class="answer" style="background-color: yellow;" onchange="inputChanged('.$jsonData["currentID"].')" placeholder="'.$placeholder.'" x="'.$valueEquation.'" id=' . $jsonData["currentID"] . '> </input><span class="helpQuestionInfo" data-hover="'."Equation: ".$rawEquation. " Filled Equation: ".$equation.'">?</span>';
						//$questionData["text"] = str_replace("%".$startText.$c, $html, $questionData["text"])." ";
					}
					else if($jsonData != null)
					{
						//$html = '<input type = "hidden" class="answer" id=' . $jsonData["currentID"] . '> </input>';
						//$questionData["text"] = str_replace("%".$startText.$c, $html, $questionData["text"])." ";
					}
				}
				else if(substr($c,0,9) == "equation2{" || substr($c,0,2) == "e{")
				{									
					$c = explode("}",$c)[0] . "}";
					//echo $c;
					$startText = "";
					//echo $c;
					if(substr($c,0,9) == "equation{")
					{
						$startText = substr($c,0,8);
						$c = substr($c,8);
					}
					else if(substr($c,0,2) == "e{")
					{
						$startText = substr($c,0,1);
						$c = substr($c,1);
					}
					$cleanC = str_replace("equation{","",$c);
					$cleanC = str_replace("e{","",$cleanC);
					//echo $cleanC;
					try{	
						$jsonData = json_decode($cleanC,true);
						//var_dump($jsonData);
					}catch(Exception  $e)
					{
						echo $e;
					}
					if($jsonData["currentID"] == 32)
					{
						echo "32 FOUNDER " . var_dump($jsonData). "<br>";
					}
					$valueEquation = 0;
					$fV = false;
					$placeholder = "";
					$equation = "";
					if($jsonData != null && array_key_exists("equation",$jsonData))
					{
						if(array_key_exists($jsonData["currentID"], $allItemsValue))
						{							
							if(array_key_exists("placeholder",$jsonData))
							{
								$placeholder = $jsonData["placeholder"];
							}
							$questionData["text"] = str_replace("%".$startText.$c, $html, $questionData["text"])." ";
						}
						else
						{
							$end = "";
							$equation = base64_decode($jsonData["equation"]);
							//echo $equation;
							//$equation = str_replace(" ","",$equation);
							//echo "AA".$equation."\n\n\n<br><br>";
							$equation = str_replace("+"," + ", $equation);
							$equation = str_replace("-"," - ", $equation);

							$equation = str_replace("*"," * ", $equation);
							$equation = str_replace("/"," / ", $equation);
							
							$equation = str_replace("^"," ^ ", $equation);
							$equation = str_replace(",","", $equation);
							$end = substr($equation, strlen($equation) - 1);
							//echo $equation;
							//echo $equation;
							//echo "\n\n\n<br><br>";
							$e = explode(" ", $equation);
							//echo "<br>";
							//var_dump($allItemsValue);
							//echo "<br>";
							foreach($e as $key) {
								//echo $key;
								try{								
									$key = str_replace("(","",$key);
									$key = str_replace(")","",$key);
									$key = str_replace(",","",$key);
									//echo $key."<br>" . $allItemsValue[$key]."<br>";
									if(substr($key,0,1) == "$")
									{
										$key = str_replace("$","",$key);
										//echo $equation. "a<br>".$key . "<br><br>";
										$fV = true;
										$equation = str_replace("$". $key,$allItemsValue[$key], $equation);
										$equation = str_replace("," ,"", $equation);
										//$equation = "";
									}
								}catch(\Throwable $e)
								{
									$equation = "ERROR";
									//echo $e;
								}
							}
							//echo $equation;
							if($equation !== "()" && $equation !== "" && $equation != "ERROR")
							{
								
								//$equation = $equation . $end;
								//echo $equation . "\n<br>";
								//echo "(".$equation.")". "\n<br>";;
								//eval('$valueEquation = (((35000 - 27000) + (90000 - 75000) + (60000 - 50000)) * .7);');
								//echo "valueEquation = (".$equation.");" . "<br><br>";
								eval('try{		$valueEquation = ('.$equation.');} catch(\Throwable $e) {$valueEquation = "ERROR2";}');
								//$valueEquation = (((35000 - 27000) + (90000 - 75000) + (60000 - 50000)) * .7);
								//eval('$valueEquation = ('.$equation.');');
								//echo $valueEquation . "<br>";
							}
							else
							{
								$valueEquation = "ERROR";
							}
						}
						//if($fV == true)
						//{
							//echo $valueEquation;
							$allItemsValue[$jsonData["currentID"]] = [$valueEquation,$cleanC, "%".$startText.$c];
						//}
						if(array_key_exists("placeholder",$jsonData))
						{
							$placeholder = $jsonData["placeholder"];
						}
						$html = $allItemsValue[$jsonData["currentID"]][0];
						//echo $html;
						//echo "<br>" . $html . "<br>" . $c . "<br>";
						$questionData["text"] = str_replace("%".$startText.$c, $html, $questionData["text"])." ";
					}
				}		
				else if(str_contains($c, "{") && str_contains($c, "}"))
				{
					$c2 = $c;
					$ss = $c;
					//echo $c2;
					//var_dump(explode("{",$c2));
					$raw = explode("}",$c2)[0] . "}";
					$raw = "{". explode("{",$raw)[1];
					//$c2 = $ss;
					//echo $ss;
					//$raw = "{". explode("{",$ss)[1];
					//echo $raw;
					//echo $c2;
					$startText = "";
					//$cleanC = str_replace("var","",$c2);
					//echo $ss."aaaaaaaaaa\n<br>\n<br>\n<br>";
					//echo "ABBB";
					//echo 
					//echo '%'.$c2.$ss;
					$jsonData = json_decode($raw,true);
					
					//$jsonData = json_decode('{"type":"equation","values":"","baseText":"111,850","currentID":10,"equation":"JDQgKyAkNSArICQ2ICsgJDggKyAkOSArIDM=","rawEquation":"$rentExpenses + $wagesExpenses + $otherOperatingExpenses + $intrestExpenses + $depreciation + 3"}',true);
					//echo($aa["type"] . "<br>");
					//var_dump($jsonData);
					if(isset($jsonData["type"]) && $jsonData["type"] == "answer")
					{
						$c2 = $c;
						//echo $c2;
						//var_dump(explode("{",$c2));
						$ss = explode("}",$c2)[0] . "}";
						$c2 = $ss;
						$ss = "{". explode("{",$ss)[1];
						$startText = "";
						/*if(substr($c,0,7) == "answer{")
						{	
							$startText = substr($c,0,6);
							$c = substr($c,6);
						}
						else
						{
							$startText = substr($c,0,1);
							$c = substr($c,1);
						}*/
						$startText = explode("{",$c)[0];
						//$cleanC = str_replace("answer{","",$c);
						//$cleanC = str_replace("a{","",$cleanC);
						$cleanC = $ss;
						//$cleanC = "{" . $cleanC;
						$jsonData = json_decode($cleanC,true);
						$valueEquation = 0;
						$fV = false;
						$placeholder = "";
						$equation = "";
						//echo $cleanC;
						if($jsonData != null && array_key_exists("equation",$jsonData))
						{
							if(array_key_exists($jsonData["currentID"], $allItemsValue) && 1 ==2)
							{							
								//$allItemsValue[$jsonData["currentID"]] = $valueEquation;
								/*if(array_key_exists("placeholder",$jsonData))
								{
									$placeholder = $jsonData["placeholder"];
								}*/
								//25/07/2024 Added
								//$html = '<input class="answer" style="background-color: yellow;" onchange="inputChanged('.$jsonData["currentID"].')" placeholder="'.$placeholder.'" x="'.$valueEquation.'" id=' . $jsonData["currentID"] . '> </input><span class="helpQuestionInfo" data-hover="'."EQUATION: " . $placeholder.'">?</span>';
								//$questionData["text"] = str_replace("%".$startText.$c, $html, $questionData["text"])." ";
							}
							else
							{
								$end = "";
								$equation = base64_decode($jsonData["equation"]);
								//$equation = str_replace(" ","",$equation);
								$equation = str_replace("+"," + ", $equation);
								$equation = str_replace("-"," - ", $equation);
								$equation = str_replace("/"," / ", $equation);
								$equation = str_replace("*"," * ", $equation);
								$equation = str_replace("^"," ^ ", $equation);
								$equation = str_replace(",","", $equation);
								$rawEquation = $jsonData["rawEquation"];
								$end = substr($equation, strlen($equation) - 1);
								$allEqu[$jsonData["currentID"]] = [$jsonData["equation"],$cleanC,"%".$startText];
								$allItemsValue[$jsonData["currentID"]] = [0,$cleanC, "%".$startText];

								$e = explode(" ", $equation);
								//var_dump($e);
								//var_dump($allItemsValue);
								foreach($e as $key) {
									//echo $key;
									try{
										$key = str_replace("(","",$key);
										$key = str_replace(")","",$key);
										$key = str_replace(",","",$key);
										if(substr($key,0,1) == "$")
										{
											$key = str_replace("$","",$key);
											//echo $key."<br>";
											//echo $equation. "<br>".$key . "<br><br>";
											$fV = true;
											if(array_key_exists($key,$allItemsValue))
											{
												//$equation = str_replace("$". $key,$allItemsValue[$key] ,$equation);
											}
										}
									}
									catch(\Throwable $e)
									{
										echo $e;
									}

								}
							}
							if(array_key_exists("placeholder",$jsonData))
							{
								$placeholder = $jsonData["placeholder"];
							}							
							else if($jsonData != null)
							{
								//$html = '<input type = "hidden" class="answer" id=' . $jsonData["currentID"] . '> </input>';
								//$questionData["text"] = str_replace("%".$startText.$c, $html, $questionData["text"])." ";
							}
						}
					}
					else if(isset($jsonData["type"]) && $jsonData["type"] == "equation")
					{
						$c2 = $c;
						//echo $c2;
						//var_dump(explode("{",$c2));
						$ss = explode("}",$c2)[0] . "}";
						$c2 = $ss;
						$ss = "{". explode("{",$ss)[1];
						$startText = "";
						/*if(substr($c,0,7) == "answer{")
						{	
							$startText = substr($c,0,6);
							$c = substr($c,6);
						}
						else
						{
							$startText = substr($c,0,1);
							$c = substr($c,1);
						}*/
						$startText = explode("{",$c)[0];
						//$cleanC = str_replace("answer{","",$c);
						//$cleanC = str_replace("a{","",$cleanC);
						$cleanC = $ss;
						//$cleanC = "{" . $cleanC;
						$jsonData = json_decode($cleanC,true);
						$valueEquation = 0;
						$fV = false;
						$placeholder = "";
						$equation = "";
						//echo $cleanC;
						if($jsonData != null && array_key_exists("equation",$jsonData))
						{
							if(array_key_exists($jsonData["currentID"], $allItemsValue) && 1 == 2)
							{							
								//$allItemsValue[$jsonData["currentID"]] = $valueEquation;
								/*if(array_key_exists("placeholder",$jsonData))
								{
									$placeholder = $jsonData["placeholder"];
								}*/
								//25/07/2024 Added
								//$html = '<input class="answer" style="background-color: yellow;" onchange="inputChanged('.$jsonData["currentID"].')" placeholder="'.$placeholder.'" x="'.$valueEquation.'" id=' . $jsonData["currentID"] . '> </input><span class="helpQuestionInfo" data-hover="'."EQUATION: " . $placeholder.'">?</span>';
								//$questionData["text"] = str_replace("%".$startText.$c, $html, $questionData["text"])." ";
							}
							else
							{
								$end = "";
								$equation = base64_decode($jsonData["equation"]);
								//$equation = str_replace(" ","",$equation);
								$equation = str_replace("+"," + ", $equation);
								$equation = str_replace("-"," - ", $equation);
								$equation = str_replace("/"," / ", $equation);
								$equation = str_replace("*"," * ", $equation);
								$equation = str_replace("^"," ^ ", $equation);
								$equation = str_replace(",","", $equation);
								$rawEquation = $jsonData["rawEquation"];
								$end = substr($equation, strlen($equation) - 1);
								$allEqu[$jsonData["currentID"]] = [$jsonData["equation"],$cleanC,"%".$startText];
								$allItemsValue[$jsonData["currentID"]] = [0,$cleanC, "%".$startText];

								$e = explode(" ", $equation);
								//var_dump($e);
								//var_dump($allItemsValue);
								foreach($e as $key) {
									//echo $key;
									try{
										$key = str_replace("(","",$key);
										$key = str_replace(")","",$key);
										$key = str_replace(",","",$key);
										if(substr($key,0,1) == "$")
										{
											$key = str_replace("$","",$key);
											//echo $key."<br>";
											//echo $equation. "<br>".$key . "<br><br>";
											$fV = true;
											if(array_key_exists($key,$allItemsValue))
											{
												//$equation = str_replace("$". $key,$allItemsValue[$key] ,$equation);
											}
										}
									}
									catch(\Throwable $e)
									{
										echo $e;
									}

								}
							}
							if(array_key_exists("placeholder",$jsonData))
							{
								$placeholder = $jsonData["placeholder"];
							}							
							else if($jsonData != null)
							{
								//$html = '<input type = "hidden" class="equation" id=' . $jsonData["currentID"] . '> </input>';
								//$questionData["text"] = str_replace("%".$startText.$c, $html, $questionData["text"])." ";
								
							}
						}
					}
					else if(isset($jsonData["type"]) && $jsonData["type"] == "equation2")
					{
						$c2 = $c;
						$ss = explode("}",$c2)[0] . "}";
						$c2 = $ss;
						$ss = "{". explode("{",$ss)[1];
						$startText = "";

						$cleanC = $ss;

						$valueEquation = 0;
						$fV = false;
						$placeholder = "";
						$equation = "";
						if($jsonData != null && array_key_exists("equation",$jsonData))
						{
							//echo "EE";
							if(array_key_exists($jsonData["currentID"], $allItemsValue))
							{							
								if(array_key_exists("placeholder",$jsonData))
								{
									$placeholder = $jsonData["placeholder"];
								}
								$questionData["text"] = str_replace("%".$startText.$c, $allItemsValue[$jsonData["currentID"]][0], $questionData["text"])." ";
							}
							else
							{
								$end = "";
								$equation = base64_decode($jsonData["equation"]);

								$equation = str_replace("+"," + ", $equation);
								$equation = str_replace("-"," - ", $equation);

								$equation = str_replace("*"," * ", $equation);
								$equation = str_replace("/"," / ", $equation);
								
								$equation = str_replace("^"," ^ ", $equation);
								$equation = str_replace(",","", $equation);
								$end = substr($equation, strlen($equation) - 1);
								
								$e = explode(" ", $equation);
								//var_dump($allItemsValue);
								foreach($e as $key) {
									//echo $key;
									try{								
										$key = str_replace("(","",$key);
										$key = str_replace(")","",$key);
										$key = str_replace(",","",$key);
										//echo $key."<br>";
										if(substr($key,0,1) == "$")
										{
											$key = str_replace("$","",$key);
											//echo $equation. "a<br>".$key . "<br><br>";
											$fV = true;
											$equation = str_replace("$". $key,$allItemsValue[$key][0], $equation);
											$equation = str_replace("," ,"", $equation);
											//$equation = "";
										}
									}catch(\Throwable $e)
									{
										$equation = "ERROR-SECOND-PARSE";
										//echo $e;
									}
								}
								//echo $equation;
								if($equation !== "()" && $equation !== "" && $equation !== null && $equation != "ERROR")
								{
									
									//$equation = $equation . $end;
									//echo $equation . "\n<br>";
									//echo "(".$equation.")". "\n<br>";;
									//eval('$valueEquation = (((35000 - 27000) + (90000 - 75000) + (60000 - 50000)) * .7);');
									//echo "valueEquation = (".$equation.");" . "<br><br>";
									eval('try{		$valueEquation = '.$equation.';} catch(\Throwable $e) {$valueEquation = "ERROR2";}');
									//$valueEquation = (((35000 - 27000) + (90000 - 75000) + (60000 - 50000)) * .7);
									//eval('$valueEquation = ('.$equation.');');
									//echo $valueEquation . "<br>";
								}
								else
								{
									$valueEquation = "ERROR";
								}
							}
							//if($fV == true)
							//{
								$allItemsValue[$jsonData["currentID"]] = [$valueEquation,$cleanC];
							//}
							if(array_key_exists("placeholder",$jsonData))
							{
								$placeholder = $jsonData["placeholder"];
							}
							$html = $allItemsValue[$jsonData["currentID"]][0];
							//$allEqu[$jsonData["currentID"]] = [$jsonData["equation"],$valueEquation];
							//echo $allItemsValue[$jsonData["currentID"]][0] . "<br>";
							//echo trim("%".$startText.$c);
							$questionData["text"] = str_replace(trim("%".$startText.$c), trim($allItemsValue[$jsonData["currentID"]][0]), $questionData["text"]);
						}
					}
				}
			}
			catch(\Throwable $e)
			{
				echo $e;
			}
		}
	}
	
	addToStack(explode("|||||",$questionData["text"])[0]);
	$questionData = solveEqu($questionData);
	return $questionData;
}

function addToStack($i)
{
	global $response;
	array_push($response['stack'],$i);
}

function parseText($questionData){
	global $rA;
	global $randomActive;
	header("randomActive: ". $randomActive);
	header("rA: ". $rA);
	global $companies;
	global $response;
	global $tax;
	global $allItemsValue;
	global $sessionOwner;
	global $presentationMode;
	//$questionData["text"] = str_replace("%section%","<section> <hr style='width:50%;text-align:left;margin-left:0'> ",$questionData["text"]);
	//$questionData["text"] = str_replace("%endsection%","</section>",$questionData["text"]);
	$allItemsLine = explode("<br>", $questionData["text"]);
	foreach($allItemsLine as $l)
	{
		$allItems = explode("%",$l);
		//echo $l;
		foreach ($allItems as $i)
		{
			try
			{
				$c = $i;
				if(substr($c,0,8) === "company{" || substr($c,0,2) == "c{")
				{
					$c = explode("}",$c)[0] . "}";
					$startText = "";
					if(substr($c,0,7) == "company{")
					{
						$startText = substr($c,0,7);
						$c = substr($c,7);
					}
					else
					{
						$startText = substr($c,0,1);
						$c = substr($c,1);
					}
					$cleanC = trim($c);
					$aaa = $cleanC;
					$jsonData = json_decode($c,true);
					$ii = rand(0,sizeof($companies) - 1);
					if(inDict($allItemsValue,$companies[$ii]))
					{
						$ii = rand(0,sizeof($companies) - 1);
					}
					$allItemsValue[$jsonData["currentID"]] = [$companies[$ii],$c];
					$questionData["text"] = str_replace("%".$startText.$c, $allItemsValue[$jsonData["currentID"]], $questionData["text"])." ";
				}
				else if(substr($c,0,3) === "var2" || substr($c,0,2) == "var2[")
				{
					$c2 = $c;
					$c2 = explode("}",$c2)[0] . "}";
					$startText = "";
					$cleanC = str_replace("var","",$c2);
					$aa = json_decode($cleanC,true);
					$values = explode(",",$cleanC);	
					$allItemsValue[$aa["currentID"]] = $aa["baseText"];//{"type" : "abb", "values" : [0,0], "baseText" : "da","currentID": 5}
					$questionData["text"] = str_replace('%'.$c2,$aa["baseText"],$questionData["text"]);//str_replace("%var".$c, $allItemsValue[$values[1]], $questionData["text"])." ";
				}
				else if(substr($c,0,3) === "tax22" || substr($c,0,2) == "t22[")
				{
					$c = explode("}",$c)[0] . "}";
					$cleanC = str_replace("tax","",$c);
					$cleanC = str_replace("t","",$cleanC);
					$cleanC = str_replace("[","",$cleanC);
					$cleanC = str_replace("]","",$cleanC);
					$values = explode(",",$cleanC);	
					$allItemsValue[$values[1]] = $tax;
					$questionData["text"] = str_replace("%".$startText.$c, $allItemsValue[$values[1]], $questionData["text"])." ";
				}
				else if(substr($c,0,9) === "operator{" || substr($c,0,2) == "o{")
				{
					$c = explode("}",$c)[0] . "}";
					$startText = "";
					$cleanC = "";
					if(substr($c,0,9) == "operator{")
					{
						$startText = substr($c,0,8);
						$c = substr($c,8);
					}
					else
					{
						$startText = substr($c,0,1);
						$c = substr($c,1);
						$cleanC = str_replace("o","",$cleanC);
					}
					$cleanC = str_replace("operator","",$c);
					$data = json_decode($cleanC,true);
					//var_dump($data);
					if(array_key_exists("r", $data) && $data["r"] == true)
					{
						$ro = array("+","-","*","/");
						$rn = rand(0,sizeof($ro) - 1);
						$questionData["text"] = str_replace("%".$startText.$c,$ro[$rn], $questionData["text"])." ";
						$allItemsValue[$data["currentID"]] = $ro[$rn];
						$questionData["aaaa"] = $ro[$rn];
					}
					else
					{
						$questionData["text"] = str_replace("%".$startText.$c, $data["values"], $questionData["text"])." ";
						$allItemsValue[$data["currentID"]] = $data["values"];
					}
					//echo $data["values"];
				}
				else if(substr($c,0,4) == "sum2{" || substr($c,0,2) == "s2{")
				{								
					$c = explode("}",$c)[0] . "}";
					$startText = "";
					//echo $c;
					if(substr($c,0,4) == "sum{")
					{
						$startText = substr($c,0,3);
						$c = substr($c,3);
					}
					else
					{
						$startText = substr($c,0,1);
						$c = substr($c,1);
					}
					
					$cleanC = str_replace("sum{","",$c);
					$cleanC = str_replace("s{","",$cleanC);
					$jsonData = json_decode($cleanC,true);
					$values = $jsonData["values"];
					$data = 0;
					$response["sum"] = $values;
					$response["size"] = sizeof($values);
					if(sizeof($values) > 0 && $values[0] != "")
					{
						for($dc = 0; $dc < sizeof($values); $dc++)
						{
							if(isset($allItemsValue[$values[$dc]]) && $values[$dc] != "")
							{
								$response["a"] = $allItemsValue[$values[$dc]];
								$data += $allItemsValue[$values[$dc]];
							}
						}
					}
					$response["data"] = $data;
					$allItemsValue[$jsonData["currentID"]] = [$data,$cleanC];
					$questionData["text"] = str_replace("%".$startText.$c, $allItemsValue[$jsonData["currentID"]], $questionData["text"])." ";
				}
				else if(substr($c,0,7) == "answer{" || substr($c,0,2) == "a{")
				{			
					/*						
					$c = explode("}",$c)[0] . "}";
					$startText = "";
					if(substr($c,0,7) == "answer{")
					{
						$startText = substr($c,0,6);
						$c = substr($c,6);
					}
					else
					{
						$startText = substr($c,0,1);
						$c = substr($c,1);
					}
					$cleanC = str_replace("answer{","",$c);
					$cleanC = str_replace("a{","",$cleanC);
					//$cleanC = "{" . $cleanC;
					$jsonData = json_decode($cleanC,true);
					$valueEquation = 0;
					$fV = false;
					$placeholder = "";
					$equation = "";
					//echo $cleanC;
					if($jsonData != null && array_key_exists("equation",$jsonData))
					{
						if(array_key_exists($jsonData["currentID"], $allItemsValue))
						{							
							//$allItemsValue[$jsonData["currentID"]] = $valueEquation;
							if(array_key_exists("placeholder",$jsonData))
							{
								$placeholder = $jsonData["placeholder"];
							}
							$html = '<input class="answer" placeholder="'.$placeholder.'" x="'.$valueEquation.'" id=' . $jsonData["currentID"] . '> </input><span class="helpQuestionInfo" data-hover="'."EQUATION: " . $placeholder.'">?</span>';
							$questionData["text"] = str_replace("%".$startText.$c, $html, $questionData["text"])." ";
						}
						else
						{
							$end = "";
							$equation = base64_decode($jsonData["equation"]);
							//$equation = str_replace(" ","",$equation);
							$equation = str_replace("+"," + ", $equation);
							$equation = str_replace("-"," - ", $equation);
							$equation = str_replace("/"," / ", $equation);
							$equation = str_replace("*"," * ", $equation);
							$equation = str_replace("^"," ^ ", $equation);
							$equation = str_replace(",","", $equation);
							$rawEquation = $jsonData["rawEquation"];
							$end = substr($equation, strlen($equation) - 1);
							//echo "AA".$equation."\n\n\n<br><br>";
							//echo $equation;
							//echo "\n\n\n<br><br>";
							//echo $equation."\n\n\n<br><br>".$jsonData["currentID"] ;
							$e = explode(" ", $equation);
							//var_dump($e);
							//var_dump($allItemsValue);
							foreach($e as $key) {
								//echo $key;
								try{
									$key = str_replace("(","",$key);
									$key = str_replace(")","",$key);
									$key = str_replace(",","",$key);
									if(substr($key,0,1) == "$")
									{
										$key = str_replace("$","",$key);
										//echo $key."<br>";
										//echo $equation. "<br>".$key . "<br><br>";
										$fV = true;
										if(array_key_exists($key,$allItemsValue))
										{
											$equation = str_replace("$". $key,$allItemsValue[$key] ,$equation);
										}
									}
								}
								catch(\Throwable $e)
								{
									echo $e;
								}

							}
							if($equation !== "()" && $equation !== "")
							{
								//$equation = $equation . $end;
								if(str_contains($equation,"$"))
								{
								}
								else
								{
								//echo $equation . "\n<br>";
								//echo "(".$equation.")". "\n<br>";;
								//eval('$valueEquation = (((35000 - 27000) + (90000 - 75000) + (60000 - 50000)) * .7);');
								//echo "valueEquation = (".$equation.");" . "<br><br>";
								eval('$valueEquation = ('.$equation.');');
								}
								//$valueEquation = (((35000 - 27000) + (90000 - 75000) + (60000 - 50000)) * .7);
								//eval('$valueEquation = ('.$equation.');');
								//echo $valueEquation . "<br>";
							}
						}
						//if($fV == true)
						//{
							$allItemsValue[$jsonData["currentID"]] = $valueEquation;
						//}
						///else
						//{
						//	$allItemsValue[$jsonData["currentID"]] = 0;
						//}
						if(array_key_exists("placeholder",$jsonData))
						{
							$placeholder = $jsonData["placeholder"];
						}
						//var_dump($allItemsValue);
						$html = '<input class="answer" placeholder="'.$placeholder.'" x="'.$valueEquation.'" id=' . $jsonData["currentID"] . '> </input><span class="helpQuestionInfo" data-hover="'."Equation: ".$rawEquation. " Filled Equation: ".$equation.'">?</span>';
						$questionData["text"] = str_replace("%".$startText.$c, $html, $questionData["text"])." ";
					}
					else if($jsonData != null)
					{
						$html = '<input type = "hidden" class="answer" id=' . $jsonData["currentID"] . '> </input>';
						$questionData["text"] = str_replace("%".$startText.$c, $html, $questionData["text"])." ";
					}
					*/
				}
				else if(substr($c,0,7) == "number2{" || substr($c,0,2) == "n2{")
				{
					//echo $c;
					//echo $c;
					$c = explode("}",$c)[0] . "}";
					$startText = "";
					//echo $c;
					if(substr($c,0,7) == "number{")
					{
						$startText = substr($c,0,5);
						$c = substr($c,6);
					}
					else
					{
						$startText = substr($c,0,1);
						$c = substr($c,1);
					}
					
					$cleanC = str_replace("number{","",$c);
					$cleanC = str_replace("n{","",$cleanC);
					//echo "CC: " .$cleanC;
					//echo $cleanC;
					$jsonData = json_decode($cleanC,true);
					//echo $jsonData;
					//var_dump($jsonData);
					$values = $jsonData["values"];
					//echo $values;
					//echo sizeof($values);
					if(sizeof($values) == 1)
					{
						//$allItemsValue[$values[0]]
						$questionData["text"] = str_replace("%".$c, $allItemsValue[$values[0]], $questionData["text"])." ";
					}
					else
					{
						try
						{
							if(array_key_exists($jsonData["currentID"], $allItemsValue))
							{	
								$questionData["ADAD"][$jsonData["currentID"]] = $jsonData["currentID"] . " ". $allItemsValue[$jsonData["currentID"]][0];
								$questionData["text"] = str_replace("%".$startText.$c, $allItemsValue[$jsonData["currentID"]][0], $questionData["text"])." ";
								$response["N"] = $allItemsValue[$jsonData["currentID"]][0];
							}
							else
							{
								$start = (int)($values[0]);
								$end = (int)($values[1]);
								if(array_key_exists("linkedID", $jsonData))
								{
									$start += (int)($allItemsValue[$jsonData["linkedID"][0]][0]);
									$end += (int)($allItemsValue[$jsonData["linkedID"][0]][0]);										
								}
								$allItemsValue[$jsonData["currentID"]] = [rand($start,$end),$cleanC];
								$questionData["ADAD"][$jsonData["currentID"]] = $jsonData["currentID"] . " ". $allItemsValue[$jsonData["currentID"]][0];
								//echo "%".$startText.$c;
								$questionData["text"] = str_replace("%".$startText.$c, $allItemsValue[$jsonData["currentID"]], $questionData["text"])." ";
								$response["N"] = $allItemsValue[$jsonData["currentID"]][0];
							}
						}
						catch(\Throwable $e)
						{
							var_dump($allItemsValue);
							echo "\n\n\n<br>";
							echo $startText.$c;
							echo "\n\n\n<br>";
							echo $jsonData["currentID"];
							echo "\n\n\n<br>";
							echo $allItemsValue[$jsonData["currentID"]];
							echo "\n\n\n<br>";
							echo $e;
						}
				
					}
				}
				else if(substr($c,0,7) == "equation{" || substr($c,0,2) == "e{")
				{							
					/*		
					$c = explode("}",$c)[0] . "}";
					$startText = "";
					//echo $c;
					if(substr($c,0,7) == "sum{")
					{
						$startText = substr($c,0,6);
						$c = substr($c,6);
					}
					else
					{
						$startText = substr($c,0,1);
						$c = substr($c,1);
					}
					$cleanC = str_replace("answer{","",$c);
					$cleanC = str_replace("a{","",$cleanC);
					$jsonData = json_decode($cleanC,true);
					$valueEquation = 0;
					$fV = false;
					$placeholder = "";
					$equation = "";
					if($jsonData != null && array_key_exists("equation",$jsonData))
					{
						if(array_key_exists($jsonData["currentID"], $allItemsValue))
						{							
							if(array_key_exists("placeholder",$jsonData))
							{
								$placeholder = $jsonData["placeholder"];
							}
							$questionData["text"] = str_replace("%".$startText.$c, $html, $questionData["text"])." ";
						}
						else
						{
							$end = "";
							$equation = base64_decode($jsonData["equation"]);
							//echo $equation;
							//$equation = str_replace(" ","",$equation);
							//echo "AA".$equation."\n\n\n<br><br>";
							$equation = str_replace("+"," + ", $equation);
							$equation = str_replace("-"," - ", $equation);

							$equation = str_replace("*"," * ", $equation);
							$equation = str_replace("/"," / ", $equation);
							
							$equation = str_replace("^"," ^ ", $equation);
							$equation = str_replace(",","", $equation);
							$end = substr($equation, strlen($equation) - 1);
							//echo $equation;
							//echo "\n\n\n<br><br>";
							$e = explode(" ", $equation);
							//var_dump($allItemsValue);
							foreach($e as $key) {
								//echo $key;
								try{								
									$key = str_replace("(","",$key);
									$key = str_replace(")","",$key);
									$key = str_replace(",","",$key);
									//echo $key."<br>";
									if(substr($key,0,1) == "$")
									{
										$key = str_replace("$","",$key);
										//echo $equation. "a<br>".$key . "<br><br>";
										$fV = true;
										$equation = str_replace("$". $key,$allItemsValue[$key], $equation);
										$equation = str_replace("," ,"", $equation);
										//$equation = "";
									}
								}catch(Exception  $e)
								{
									$equation = "ERROR";
									//echo $e;
								}
							}
							if($equation !== "()" && $equation !== "")
							{
								
								//$equation = $equation . $end;
								//echo $equation . "\n<br>";
								//echo "(".$equation.")". "\n<br>";;
								//eval('$valueEquation = (((35000 - 27000) + (90000 - 75000) + (60000 - 50000)) * .7);');
								//echo "valueEquation = (".$equation.");" . "<br><br>";
								eval('try{		$valueEquation = ('.$equation.');} catch(\Throwable $e) {$valueEquation = "ERROR2";}');
								//$valueEquation = (((35000 - 27000) + (90000 - 75000) + (60000 - 50000)) * .7);
								//eval('$valueEquation = ('.$equation.');');
								//echo $valueEquation . "<br>";
							}
						}
						//if($fV == true)
						//{
							$allItemsValue[$jsonData["currentID"]] = $valueEquation;
						//}
						if(array_key_exists("placeholder",$jsonData))
						{
							$placeholder = $jsonData["placeholder"];
						}
						$html = $allItemsValue[$jsonData["currentID"]][0];
						//echo $html;
						$questionData["text"] = str_replace("%".$startText.$c, $html, $questionData["text"])." ";
					}*/
				}
				else if(str_contains($c, "{") && str_contains($c, "}"))
				{
					$c2 = $c;
					//echo $c2;
					//var_dump(explode("{",$c2));
					$ss = explode("}",$c2)[0] . "}";
					$c2 = $ss;
					$ss = "{". explode("{",$ss)[1];
					//echo $c2;
					$startText = "";

					header("Content-Type2: text/json");
					header("randomActive: ". $randomActive);
					//echo $c2;
					$aa = json_decode($ss,true);//json_decode('{"type" : "", "values" : [0,0], "baseText" : "5,685","currentID": 12}',true);
					if($aa !== null)
					{
						$plusHTMLItem = "<span><button onclick=openStatsForVar('" . $aa['currentID'] . "') class='btn statsBox helpQuestionInfo 10-res' onmouseover=openStatsForVar('" . $aa['currentID'] . "')> + </button></span>";
						if($aa["type"] == "equation" || $aa["type"] == "answer")
						{
							addToStack($aa);
							if($aa["values"] != "" && $aa["values"] != [] && $aa["values"] != [""])
							{
								$html = '<select class="answer" id="'.$aa["currentID"].'" x=""';
								if(isset($aa["animationID"]) && isset($aa["animationType"]))
								{
									$html .= 'animationID="'.$aa["animationID"].' "animationType="'.$aa["animationType"].'"';
								}
								$html .= ">";
								foreach($aa["values"] as $value)
								{
									if(trim($value) != trim($aa['baseText']))
									{
										$html = $html . "<option onclick=checkValue('".$aa["currentID"]."')>" . $value . "</option>";
									}
								}
								$html .= "<option class = 'correct' onclick=checkValue('".$aa["currentID"]."')>" . $aa['baseText'] . "</option>";
								$html .=  "</select>";
								$allItemsValue[$aa["currentID"]] = [$aa['baseText'], $ss];
								if($presentationMode && $sessionOwner)
								{
									$html .= $plusHTMLItem;//"<span><button onclick=openStatsForVar('" . $aa['currentID'] . "') class='btn'> + </button></span>";
								}
								else
								{
									$html .= '<span class="helpQuestionInfo" data-hover="Correct Answer: '.$aa['baseText'].'">?</span>';
								}
								$questionData["text"] = str_replace('%'.$c2,$html,$questionData["text"]);
							}
							
						}
						else if($aa["type"] == "answerText")
						{
							$t = $aa["baseText"];
							$html = '<select class="answer" id="'.$aa["currentID"].'" style="background-color: yellow;" onchange="inputChanged('.$aa["currentID"].')"';
							if(isset($aa["animationID"]) && isset($aa["animationType"]))
							{
								$html .= 'animationID="'.$aa["animationID"].' "animationType="'.$aa["animationType"].'"';
							}
							if($aa["values"] != "" && $aa["values"] != [] && $aa["values"] != [""])
							{
								$html .= 'x="">';
								$html .= "<option disabled selected value></option>"; 
								if(!in_array($aa['baseText'], $aa["values"]))
								{
									array_push($aa["values"], $aa['baseText']);
								}
								
								sort($aa["values"]);
								foreach($aa["values"] as $value)
								{
									if(trim($value) != trim($aa['baseText']))
									{
										$html .= "<option onclick=checkValue('".$aa["currentID"]."')>" . $value . "</option>";
									}
									else
									{
										$html .= "<option class = 'correct' onclick=checkValue('".$aa["currentID"]."')>" . $aa['baseText'] . "</option>";
									}
								}
								//$html .= "<option class = 'correct' onclick=checkValue('".$aa["currentID"]."')>" . $aa['baseText'] . "</option>";
								$html .= "</select>";
							}						
							else
							{
								$html .= 'x="'.$aa['baseText'].'>';
								//$html = '<input class="answer" style="background-color: yellow;" onchange="inputChanged('.$aa["currentID"].')" placeholder="" x="'.$aa['baseText'].'" id="'.$aa["currentID"].'">';
							}
							if($presentationMode && $sessionOwner)
							{
								$html .= '<span class="helpQuestionInfo" data-hover="Correct Answer: '.$aa['baseText'].'">?</span>';
								$html .= $plusHTMLItem;
							}
							else
							{
								$html .= '<span class="helpQuestionInfo" data-hover="Correct Answer: '.$aa['baseText'].'">?</span><span><button onclick=openChatMenu("' . $aa["currentID"] . '") class="btn"> * </button></span>';
							}
							
							$questionData["text"] = str_replace('%'.$c2,$html,$questionData["text"]);
						}
						elseif($aa["type"] == "time")
						{
							$equationSplit = explode(" ",$aa["baseText"]);
							array_push($response["stack"],$equationSplit);
							$equationCleaned = "";
							foreach ($equationSplit as $section)
							{
								if($section == "now()")
								{
									$equationCleaned .= date('d/m/Y h:i:s a', time()) . " ";
								}
								elseif($section == "day()")
								{
									$equationCleaned .= date('d', time()) . " ";
								}
								elseif($section == "month()")
								{
									$equationCleaned .= date('m', time()) . " ";
								}
								elseif($section == "year()")
								{
									$equationCleaned .= date('Y', time()) . " ";
								}
								else
								{
									$equationCleaned .= $section . " ";
								}
							}
							$text = "<span ";
							if(isset($aa["animationID"]) && isset($aa["animationType"]))
							{
								$text .= 'class="animation" animationID="'.$aa["animationID"].'"animationType="'.$aa["animationType"].'"';
							}
							$text .= ">";
							$text .= $equationCleaned . " </span>";
							$questionData["text"] = str_replace('%'.$c2,$text,$questionData["text"]);
						}
						else if($aa["type"] == "subDocument")
						{
						}
						else if($aa["type"] == "document")
						{
						}
						else
						{
							$items = $allItemsValue[$aa["currentID"]];
							$items[1] = json_decode($items[1],true);
							if(isset($aa["r"]) && $aa["r"] == true)
							{
								if(isset($aa["rType"]) )
								{
									$rV = $items[0];
									if($aa["rType"] == "range")
									{
										try
										{
											header("rtype:".$aa["rType"]);
											$text = "<span ";
											if(isset($aa["animationID"]) && isset($aa["animationType"]))
											{
												$text .= ' class="animation" animationID="'.$aa["animationID"].'"animationType="'.$aa["animationType"].'"';
											}
											$text .= ">";
											if(is_numeric($aa["values"][0]) && is_numeric($aa["values"][1]))
											{
												//$rV = rand((int)($aa["values"][0]),(int)($aa["values"][1]));
												
												$text .= number_format((float)str_replace(",","",$rV)) . "</span>";
												$questionData["text"] = str_replace('%'.$c2,$text,$questionData["text"]);
												//$allItemsValue[$aa["currentID"]] = [$rV,$ss];
											}
											else
											{
												//$rV = array_rand($aa["values"]);
												$text .= $rV . "</span>";
												$questionData["text"] = str_replace('%'.$c2,$text,$questionData["text"]);
												
												//$allItemsValue[$aa["currentID"]] = [$rV,$ss];
											}
										}
										catch(Exception  $e)
										{
											$response["error"] = (string)$e;
											try{
												$rV = array_rand($aa["values"]);
												$text = "<span ";
												if(isset($aa["animationID"]) && isset($aa["animationType"]))
												{
													$text .= ' class="animation" animationID="'.$aa["animationID"].'"animationType="'.$aa["animationType"].'"';
												}
												$text .= ">" . number_format($aa["values"][$rV]) . "</span>";
												$questionData["text"] = str_replace('%'.$c2,$text,$questionData["text"]);
												//$allItemsValue[$aa["currentID"]] = [$rV,$ss];
											}
											catch(Exception  $e)
											{
												$questionData["text"] = str_replace('%'.$c2,"ERROR IN RANDOM");
											}
										}
									}
									else
									{
										$text = "<span ";
										if(isset($aa["animationID"]) && isset($aa["animationType"]))
										{
											$text .= ' class="animation" animationID="'.$aa["animationID"].'"animationType="'.$aa["animationType"].'"';
										}
										$text .= ">" . number_format($aa["values"][$rV]) . "</span>";
										$questionData["text"] = str_replace('%'.$c2,$text,$questionData["text"]);
									}
								}
							}
							else
							{
								$text = "<span ";
								if(isset($aa["animationID"]) && isset($aa["animationType"]))
								{
									$text .= ' class="animation" animationID="'.$aa["animationID"].'"animationType="'.$aa["animationType"].'"';
								}
								$text .= ">";
								if($aa["type"] == "number")
								{
									$text .= number_format((float)str_replace(",","",$items[1]["baseText"]));
									$questionData["text"] = str_replace('%'.$c2,$text,$questionData["text"]);
									//$allItemsValue[$aa["currentID"]] = [number_format((float)str_replace(",","",$items[1]["baseText"])),$ss];
								}
								else
								{
									
									$text .= $items[1]["baseText"];
									$questionData["text"] = str_replace('%'.$c2,$text,$questionData["text"]);
									//$allItemsValue[$aa["currentID"]] = [$items[1]["baseText"],$ss];
								}
							}
						}
					}
					
				}
			}
			catch(\Throwable $e)
			{
				echo $e;
			}
		}
		
	}
	header("allItemsValue: " . json_encode($allItemsValue));
	return $questionData;
}

function random_strings($length_of_string)
{
    $str_result = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    return substr(str_shuffle($str_result), 0, $length_of_string);
}



?>