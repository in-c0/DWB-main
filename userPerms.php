<?php


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
		/*
		for ($d = 0; $d < sizeof($permsTemp[$key]["domain"]); $d++)
		{
			try {
				$data = $permsTemp[$key]["domain"][$d];
				if($data["type"] == "group")
				{
					$aaa = $aaa . $data["target"] . " ";
					array_push($groupsToSearch,[$data["target"], $key]);

					$sql = "SELECT * FROM `groups` WHERE JSON_VALUE(perm,'$.name') = '".$data["target"]."' AND `orgID` = '".$key."'";
					$result = $conn->query($sql);
					while ($row = $result -> fetch_assoc())
					{
						$a = json_decode($row["perm"],true);
						if(!isset($groups[$key]))
						{
							$groups[$key] = [];
						}
						if(!isset($permsTemp2[$key]["domain"]))
						{
							$permsTemp2[$key] = json_decode("{}",true);
							$permsTemp2[$key]["orgID"] = $key;
							$permsTemp2[$key]["domain"] = [];
						}
						array_push($groups[$key], $a);
						for ($ci = 0; $ci < sizeof($a["domain"]); $ci ++)
						{
							$a["domain"][$ci]["subType"] = "group::" . $a["name"];
							if($a["domain"][$ci]["target"] == "*")
							{
								$permsTemp2[$key]["globalAccess"] = true;
							}
							array_push($permsTemp2[$key]["domain"], $a["domain"][$ci]);
						}
						$permsTemp[$key]["domain"][$d] = $a;
					}
				}
				else
				{
					if(!isset($permsTemp2[$key]["domain"]))
					{
						$permsTemp2[$key] = json_decode("{}",true);
						$permsTemp2[$key]["orgID"] = $key;
						$permsTemp2[$key]["domain"] = [];
					}
					if($permsTemp[$key]["domain"][$d]["target"] == "*")
					{
						$permsTemp[$key]["globalAccess"] = true;
					}
					array_push($permsTemp2[$key]["domain"], $data);
				}
			} catch (\Throwable $th) {
				echo $th;
			}
		}*/
	}
	header("groups: " . $aaa);
}
//echo"\n\n";
//echo json_encode($permsTemp)."\n\n";
//echo json_encode($permsTemp2)."\n\n";
$perms = $permsTemp;
header("perms: " . json_encode($perms));
?>