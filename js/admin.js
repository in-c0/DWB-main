
function openAdmin()
{
	window.location.href = "admin.php"; 
}

var allOrgs = {};

function getAllOrgs()
{
	var xhttp = new XMLHttpRequest();
	xhttp.open("GET", "api.php/getAllOrgs", true);				
	xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xhttp.send("");
	xhttp.onreadystatechange = function() 
	{ 
		if (xhttp.readyState == 4 && xhttp.status == 200)
		{
			if(xhttp.responseText.length > 0 )
			{			
				/*	
				var jsonData = JSON.parse(xhttp.responseText);
				var h = document.getElementById("allOrgs");
				var d = document.createElement("a");
				d.innerHTML = "ALL";
				d.setAttribute("href","?");
				d.setAttribute("class","list-group-item list-group-item-action");
				h.appendChild(d);
				for(var i =0; i < jsonData["orgs"].length; i++)
				{
					var d = document.createElement("a");
					d.innerHTML = jsonData["orgs"][i]["name"];
					d.setAttribute("href","?org=" + jsonData["orgs"][i]["id"]);
					d.setAttribute("class","list-group-item list-group-item-action");
					h.appendChild(d);
				}
				*/
				var holder = "orgs";
				var j = JSON.parse(xhttp.responseText);
				var item = j["orgs"];
				document.getElementById("datalistOptions").innerHTML = "";
				var dropdown=document.createElement("select");
				var topicData = document.getElementById("datalistOptions");
				topicData.innerHTML = "";
				for(var i=0;i<item.length;i++){
					allOrgs[item[i]["name"]] = item[i]["id"];
					console.log(item[i]["name"]);
					var opt=document.createElement("option");
					opt.text=item[i]["name"];
					opt.value=item[i]["name"];
					opt.setAttribute("onclick","getAllQuestions("+ item[i]["id"] + ")");
					dropdown.options.add(opt);
					topicData.appendChild(opt);
				}
			}
		}
	}
}
userAccountsChart = null;
var cols = ["#0074D9", "#FF4136", "#2ECC40", "#FF851B", "#7FDBFF", "#B10DC9", "#FFDC00", "#001f3f", "#39CCCC", "#01FF70", "#85144b", "#F012BE", "#3D9970", "#111111", "#AAAAAA"];

function createNewUni()
{
	var xhttp = new XMLHttpRequest();
	xhttp.open("GET", "api.php/addUniversity/" + document.getElementById("newUniName").value, true);				
	xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xhttp.send("");
}

function resetPassword()
{
	var xhttp = new XMLHttpRequest();
	xhttp.open("GET", "api.php/resetPassword/" + document.getElementById("userEmailForPassword").value, true);				
	xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xhttp.send("");
}

function loadUserData()
{
	var xhttp = new XMLHttpRequest();
	xhttp.open("GET", "api.php/getUsersStats", true);				
	xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xhttp.send("");
	xhttp.onreadystatechange = function() 
	{ 
		if (xhttp.readyState == 4 && xhttp.status == 200)
		{
			if(xhttp.responseText.length > 0 )
			{
				
				var jsonData = JSON.parse(xhttp.responseText);
				x = [jsonData["results"]["users"]["admins"],jsonData["results"]["users"]["staff"],jsonData["results"]["users"]["students"]];
				y = ["admin","staff","students"];
				
				/*userAccountsChart.data.labels.pop();
				userAccountsChart.data.datasets.forEach((dataset) => {
					dataset.data.pop();
				});
				chart.update();*/

						
				userAccountsChart = new Chart("userAccounts", {
					type: "pie",
					data: {
						labels: y,
						datasets: [{
						data: x,
						backgroundColor: cols
						}]
					},
					options: {
						title: {
						display: true,
						text: "Users distribution"
						}
					}
				});


				y = ["public","private"];
				x = [jsonData["results"]["public"],jsonData["results"]["private"]];

				document.getElementById("questions").innerHTML = "";
				new Chart("questions", {
					type: "pie",
					data: {
						labels: y,
						datasets: [{
						data: x,
						backgroundColor: ["#2ECC40","#FF4136"]
						}]
					},
					options: {
						title: {
						display: true,
						text: "Question distribution"
						}
					}
				});
			}
		}
	}
}


function loadUserDataID(id)
{
	var xhttp = new XMLHttpRequest();
	xhttp.open("GET", "api.php/getUsersStats/" + id, true);				
	xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xhttp.send("");
	xhttp.onreadystatechange = function() 
	{ 
		if (xhttp.readyState == 4 && xhttp.status == 200)
		{
			if(xhttp.responseText.length > 0 )
			{
				
				var jsonData = JSON.parse(xhttp.responseText);
				x = [jsonData["results"]["users"]["admins"],jsonData["results"]["users"]["staff"],jsonData["results"]["users"]["students"]];
				y = ["admin","staff","students"];

				document.getElementById("userAccountsHolder").innerHTML = "";
				var c = document.createElement("canvas");
				c.setAttribute("id","userAccounts");
				document.getElementById("userAccountsHolder").appendChild(c);
				
				
				new Chart("userAccounts", {
					type: "pie",
					data: {
						labels: y,
						datasets: [{
						data: x,
						backgroundColor: cols
						}]
					},
					options: {
						title: {
						display: true,
						text: "Users distribution"
						}
					}
				});

				y = ["public","private"];
				x = [jsonData["results"]["public"],jsonData["results"]["private"]];

				document.getElementById("questions").innerHTML = "";
				new Chart("questions", {
					type: "pie",
					data: {
						labels: y,
						datasets: [{
						data: x,
						backgroundColor: ["#2ECC40","#FF4136"]
						}]
					},
					options: {
						title: {
						display: true,
						text: "Question distribution"
						}
					}
				});
			}
		}
	}
}

function search()
{
	var email = document.getElementById("searchEmail").value;
	var xhttp = new XMLHttpRequest();
	xhttp.open("GET", "api.php/getUserInfo/" + email, true);				
	xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xhttp.send("");
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
}