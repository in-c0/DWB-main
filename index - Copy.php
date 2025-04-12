<?php
require("conn.php");
global $conn;
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
		$checkIfTokenIsReal = "SELECT * FROM `login` WHERE `authToken` = '$authToken' AND `email` = '$authEmail'";
		$result = $conn->query($checkIfTokenIsReal);
		while ($row = $result -> fetch_assoc())
		{
			$userID = $row["id"];
			break;
		}     
		if($userID == null)
		{              
			header("Location: /datatrain/login.php");
			exit();
		}    
	}
	else
	{
		header("Location: /datatrain/login.php");
		exit();
	}
}
else
{
	header("Location: /datatrain/login.php");
	exit();
}

?>

<!DOCTYPE html>
<html>
	<div class="split left">
  		<div class="centered" id="left">
			<div id="main" contenteditable="true" onchange="setTClean()"> </div>
			<button onclick=textClean(t)> aaa </button><div class="dropdown">

		</div>
	</div>
	<div class="split right">
  		<div class="centered" id="right">
			<div id="rightSection" contenteditable="true" onchange="setTClean()"> </div>
		</div>
	</div>
<script src="main.js"></script>
<script> 
	
	var tax = 30;
	var tClean = "";
	var jsonRaw = {};
	var allItemsValue = {};
	var translationTable = {};
	var companyAmount = 0;
	var currentID = 0;

	function tableCreate() {
		if(document.getElementById("table"))
		{
			document.getElementById("table").outerHTML = "";
		}
		var colNames = ["name","type","value"];
		var body = document.getElementById("right");

		var tbl = document.createElement("table");
		var tblBody = document.createElement("tbody");
		
		row = document.createElement("tr");

		for (var i = 0; i < colNames.length; i++) {
			var cell = document.createElement("td");
			var cellText = document.createTextNode(colNames[i]);
			cell.appendChild(cellText);
			row.appendChild(cell);
		}
		tblBody.appendChild(row);
		// cells creation
		console.log(Object.keys(translationTable).length);
		for (var key in translationTable) 
		{
			var row = document.createElement("tr");
			console.log(translationTable, key);
			for (var i = 0; i < colNames.length; i++) {
				var cell = document.createElement("td");
				var cellText = document.createElement("p");
				console.log(translationTable[key], key);
				if(i == 0)
				{
					cellText.innerHTML = translationTable[key][0];
				}
				else if(i == 1)
				{
					cellText.innerHTML = translationTable[key][2];
				}
				else if(i == 2)
				{
					if(translationTable[key][2] == "sum")
					{
						cellText.innerHTML = "sum";
					}
					else
					{
						cellText.innerHTML = "random";
					}
				}
				else
				{
					cellText.innerHTML = "<button onclick=select(1)> select </button>";
				}
				cell.appendChild(cellText);
				row.appendChild(cell);
			}

			//row added to end of table body
			tblBody.appendChild(row);
		}

		// append the <tbody> inside the <table>
		tbl.appendChild(tblBody);
		// put <table> in the <body>
		body.appendChild(tbl);
		// tbl border attribute to 
		tbl.setAttribute("border", "2");
		tbl.setAttribute("id","table");
	}
</script>
<style>
textarea {
  font-size: 0.8rem;
  letter-spacing: 1px;
}
#contextMenu {
	position: fixed;
	background:teal;
	color: white;
	cursor: pointer;
	border: 1px black solid
	}

#contextMenu > p {
	padding: 0 1rem;
	margin: 0
	}

#contextMenu > p:hover {
	background: black;
	color: white;
	}

/* Split the screen in half */
.split {
  height: 100%;
  width: 50%;
  position: fixed;
  z-index: 1;
  top: 0;
  overflow-x: hidden;
  padding-top: 20px;
}

/* Control the left side */
.left {
  left: 0;
}

/* Control the right side */
.right {
  right: 0;
}
/*


.dropbtn {
  background-color: #04AA6D;
  color: white;
  padding: 16px;
  font-size: 16px;
  border: none;
}

.dropdown {
  position: relative;
  display: inline-block;
}

.dropdown-content {
  display: none;
  position: absolute;
  background-color: #f1f1f1;
  min-width: 160px;
  box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
  z-index: 1;
}

.dropdown-content a {
  color: black;
  padding: 12px 16px;
  text-decoration: none;
  display: block;
}

.dropdown-content a:hover {background-color: #ddd;}

.dropdown:hover .dropdown-content {display: block;}

.dropdown:hover .dropbtn {background-color: #3e8e41;}


*/


.third-level-menu
{
    position: absolute;
    top: 0;
    right: -150px;
    width: 150px;
    list-style: none;
    padding: 0;
    margin: 0;
    display: none;
}

.third-level-menu > li
{
    height: 30px;
    background: #999999;
}
.third-level-menu > li:hover { background: #CCCCCC; }

.second-level-menu
{
    position: absolute;
    top: 30px;
    left: 0;
    width: 150px;
    list-style: none;
    padding: 0;
    margin: 0;
    display: none;
}

.second-level-menu > li
{
    position: relative;
    margin: 2%;
    height: 30px;
    background: #999999;
}
.second-level-menu > li:hover { background: #CCCCCC; }

.top-level-menu
{
    list-style: none;
    padding: 0;
    margin: 0;
}

.top-level-menu > li
{
    height: 30px;
    width: 150px;
}
.top-level-menu > li:hover { background: #CCCCCC; }

.top-level-menu li:hover > ul
{
    /* On hover, display the next level's menu */
    display: inline;
}


/* Menu Link Styles */

.top-level-menu a /* Apply to all links inside the multi-level menu */
{
    font: bold 14px Arial, Helvetica, sans-serif;
    color: #FFFFFF;
    text-decoration: none;
    padding: 0 0 0 10px;

    /* Make the link cover the entire list item-container */
    display: block;
    line-height: 30px;
}
.top-level-menu a:hover { color: #000000; }
</style>
<?php
	if(isset($_GET["editing"]) && $_GET["editing"] === "true")
	{
	?>
		
		<script>
			document.getElementById("left").innerHTML += `<button onclick="newQuestion()"> New Question </button>
		<button onclick="submit()"> Submit </button>`;
		var questionID = "";
		var selectedText = "";
		<?php
			if(isset($_GET["questionID"]))
			{
			?> 
			questionID = '<?php echo $_GET["questionID"];?>';
			getQuestionRaw(questionID);
			<?php
			}
		?>
		function getQuestionRaw(questionID)
		{
			var xhttp = new XMLHttpRequest();
			xhttp.open("GET", "api.php/getQuestionRaw/?questionID="+questionID , true);				
			xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			xhttp.send("");
			xhttp.onreadystatechange = function() 
			{ 
				if (xhttp.readyState == 4 && xhttp.status == 200)
				{
					if(xhttp.responseText.length > 0 )
					{
						var jsonResponse = JSON.parse(xhttp.responseText);
						tClean = jsonResponse["raw"];
						translationTable = JSON.parse(jsonResponse["translationTable"]);
						currentID = Object.keys(translationTable).length;
						for (var key in translationTable) 
						{
							tClean = tClean.replaceAll(translationTable[key][1],"<span id='customVar' style='background-color:powderblue;'><u>" + translationTable[key][0] + "</u></span>");
						};
						console.log(tClean);
						document.getElementById("main").innerHTML = tClean;
						
						tableCreate();
					}
				}
			}	
		}

		function removeAssigment(id)
		{
			tClean = tClean.replaceAll('<span id="customVar" style="background-color:powderblue;"><u>' + id + '</u></span>',id);
			console.log('<span id="customVar" style="background-color:powderblue;"><u>' + id + '</u></span>');
			document.getElementById("main").innerHTML = tClean;
			removeFromDict(id);
		}

		function removeFromDict(value)
		{
			for (var key in translationTable) 
			{
				console.log(key);
				if(translationTable[key][0] == selectedText)
				{
					delete translationTable[key];
					break;
				}
			}
		}

		oncontextmenu = (e) => {
			setTClean();
			e.preventDefault();
			selectedText = getSelectionText();
			selectedText = selectedText.trim();
			if(selectedText != "")
			{
				if(selectedText.includes('<span id="customVar"'))
				{
					selectedText = selectedText.split("<u>")[1];
					selectedText = selectedText.replace("</u></span>","");
				}
				selectedText = selectedText.trim();
				var tF = false;
				var keyF = "";
				for (var key in translationTable) 
				{
					console.log(key);
					if(translationTable[key][0] == selectedText)
					{
						tF = true;
						keyF = key;
						break;
					}
				}
				selectedText = selectedText.replace("nbsp;","");
				console.log(selectedText);
				var menu = document.createElement("div");
				menu.id = "contextMenu";
				menu.style = `top:${e.pageY-10}px;left:${e.pageX-40}px`;
				var y = e.pageY-10;
				var x = e.pageX-40;
				menu.onmouseleave = () =>  menu.outerHTML = '';
				var xItem = `<ul class="top-level-menu">`;
				if(tF)
				{
					if(translationTable[keyF][2] == "number")
					{
						//menu.innerHTML += `<p onmouseover="" onclick=setValues('number')> Set min and max values</p>`
						//
						xItem += `
						<li>
							
							<p onclick=setValues('number')> Link to number</p>
						<ul class="third-level-menu">
							`;
							var num = getNumbers(keyF);
							for (var i = 0; i < num.length; i ++)
							{
								xItem += "<li> <a href=javascript:linkWithId(" + num[i] + "," + keyF + ")>" + translationTable[num[i]][0] + "</a></li>";
							}
							if(num.length < 6)
							{
								for (var i = 0; i < 6 - num.length; i ++)
								{
									xItem += "<li></li>";
								}
							}
							xItem +=  `
							</ul>
						</li><li><p> Set min and max values</p>
						<ul class="third-level-menu"><li><input id='tempMin' type='number'> min </input></li><li><input id='tempMax' type='number'> min </input></li><li> <button onclick=setNumbers(`+keyF+`)> Set Values </button></li><li></li><li></li></ul>`;
					}
					else if(translationTable[keyF][2] == "sum")
					{
						xItem += `
						<li>
							
							<p> Link to number</p>
						<ul class="third-level-menu">
							`;
							var num = getNumbers(keyF);
							for (var i = 0; i < num.length; i ++)
							{
								xItem += "<li> <a href=javascript:addSumItem(" + num[i] + "," + keyF + ")>" + translationTable[num[i]][0] + "</a></li>";
							}
							if(num.length < 6)
							{
								for (var i = 0; i < 6 - num.length; i ++)
								{
									xItem += "<li></li>";
								}
							}
							xItem += `</ul></li><li><p> Unlink to number</p><ul class="third-level-menu">`;
							var num = getSummedItems(keyF);
							console.log(num);
							for (var i = 0; i < num.length; i ++)
							{
								if(num[i] != "")
								{
									xItem += "<li> <a href=javascript:removeSumItem(" + num[i] + "," + keyF + ")>" + translationTable[num[i]][0] + "</a></li>";
								}
							}
							if(num.length < 6)
							{
								for (var i = 0; i < 6 - num.length; i ++)
								{
									xItem += "<li></li>";
								}
							}
							xItem +=  `</ul></li>`;
					}
					xItem += `<li><p onclick=removeAssigment('`+selectedText+`');>Remove Assigment</p></li>`;
					//,'top:" + y + "px;left:" + x + "px')
					//document.getElementById('contextMenu').outerHTML = '';
					//menu.innerHTML += "";
				}
				else
				{
					console.log("NOT FOUND");
					xItem += "<p onclick=addNewCompany()>Add New Company</p><p onclick=addNewNumber()>Add New Number</p><p onclick=addNewSumNumber()>Add New Sum Number</p><p onclick=addNewSumNumber()>Add Awnser</p>";
				}
				xItem += '</ul>';
				console.log(xItem);
				menu.innerHTML += xItem;
				document.getElementById("left").appendChild(menu);
			}
		}

		function getSummedItems(id)
		{
			var cleanC = translationTable[id][1];
			console.log(cleanC);
			cleanC = cleanC.replaceAll("[","");
			cleanC = cleanC.replaceAll("%s","");
			cleanC = cleanC.replaceAll("]","");
			var sum = cleanC.split(",");
			var valuesT = sum;
			valuesT.splice(valuesT.length - 1, 1); 
			var output = [];
			return valuesT;
		}

		function removeSumItem(targetID, id)
		{
			var cleanC = translationTable[id][1];
			console.log(cleanC);
			cleanC = cleanC.replaceAll("[","");
			cleanC = cleanC.replaceAll("%s","");
			cleanC = cleanC.replaceAll("]","");
			var sum = cleanC.split(",");
			var valuesT = sum;
			console.log(valuesT, sum, targetID, id);
			valuesT.splice(valuesT.length - 1, 1); 
			valuesT.push(targetID);
			console.log(cleanC.split(","));
			var command = "%s[[";
			for (var i = 0; i < valuesT.length; i ++)
			{
				if(valuesT[i] != "" && valuesT[i] != targetID)
				{
					command += valuesT[i] + ",";
				}
			}
			command = command.substring(0,command.length - 1);
			command += "]," + id + "]";
			translationTable[id][1] = command;
			console.log(command);
		}

		function addSumItem(targetID, id)
		{
			var cleanC = translationTable[id][1];
			console.log(cleanC);
			cleanC = cleanC.replaceAll("[","");
			cleanC = cleanC.replaceAll("%s","");
			cleanC = cleanC.replaceAll("]","");
			var sum = cleanC.split(",");
			var valuesT = sum;
			console.log(valuesT, sum, targetID, id);
			valuesT.splice(valuesT.length - 1, 1); 
			valuesT.push(targetID);
			console.log(cleanC.split(","));
			var command = "%s[[";
			for (var i = 0; i < valuesT.length; i ++)
			{
				if(valuesT[i] != "")
				{
					command += valuesT[i] + ",";
				}
			}
			command = command.substring(0,command.length - 1);
			command += "]," + id + "]";
			translationTable[id][1] = command;
			console.log(command);
		}

		function setNumbers(id)
		{
			var min = document.getElementById("tempMin").value;
			var max = document.getElementById("tempMax").value;
			console.log(id, min,max);
			if(min != null && max != null)
			{
				var values = translationTable[id][1].split(",");
				console.log(values);
				translationTable[id][1] = "%n[" + min + "," + max + "," + values[2];
				if(values.length == 4)
				{
					translationTable[id][1] += "," + values[3];
				}
				translationTable[id][1] += "]";
				console.log(translationTable[id][1]);
			}
		}

		function getNumbers(id)
		{
			var ids = [];
			for (var key in translationTable) 
			{
				if(translationTable[key][2] == "number" && key != id)
				{
					ids.push(key);
				}
			}
			return ids;
		}

		function linkWithId(targetID,thisID)
		{
			console.log(translationTable[thisID]);
			var values = translationTable[thisID][1].split(",");
			console.log(values);
			if(values.length != 4)
			{
				var command = translationTable[thisID][1].substring(0,translationTable[thisID][1].length - 1);
				command += "," + targetID +"]";
				translationTable[thisID][1] = command;
				console.log(command);
			}
			console.log(thisID);
		}

		function setValues(type, style = "")
		{
			if(type == "number")
			{
				var menu = document.createElement("div");
				menu.id = "values";
				menu.style = style;
				menu.onmouseleave = () => values.outerHTML = '';
				menu.innerHTML = "<input>Min</input><input>Max</input>";
				document.body.appendChild(menu);
			}
		}

		function newQuestion()
		{
			var xhttp = new XMLHttpRequest();
			xhttp.open("GET", "api.php/newQuestionID/?userID=1", true);				
			xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			xhttp.send("");
			xhttp.onreadystatechange = function() 
			{ 
				if (xhttp.readyState == 4 && xhttp.status == 200)
				{
					if(xhttp.responseText.length > 0 )
					{
						jsonRaw = JSON.parse(xhttp.responseText);
						questionID = jsonRaw["questionID"];
						submit2("?editing=true&questionID=" + questionID);
					}
				}
			}
		}

		function submit2(foward)
		{
			var allText = document.getElementById("main").innerText;
			for (var key in translationTable) 
			{
				console.log(translationTable[key]);
				allText = allText.replaceAll('<span id="customVar" style="background-color:powderblue;"><u>' + translationTable[key][0] + '</u></span>',translationTable[key][1]);
				allText = allText.replaceAll(translationTable[key][0],translationTable[key][1]);
				
			}
			//allText = allText.replaceAll("<br>","\n");
			allText = allText.trim();
			allText = encodeURIComponent(allText);
			console.log(allText);
			var xhttp = new XMLHttpRequest();
			xhttp.open("POST", "api.php/updateQuestion/" , true);				
			xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			xhttp.send("value="+encodeURIComponent(btoa(allText)) + "&questionID=" + questionID +"&translationTable=" + JSON.stringify(translationTable));
			xhttp.onreadystatechange = function() 
			{ 
				if (xhttp.readyState == 4 && xhttp.status == 200)
				{
					if(xhttp.responseText.length > 0 )
					{
						window.location = foward;
					}
				}
			}
		}

		function submit()
		{
			var allText = document.getElementById("main").innerText;
			for (var key in translationTable) 
			{
				console.log(translationTable[key]);
				console.log('<span id="customVar" style="background-color:powderblue;"><u>' + translationTable[key][0] + '</u></span>',translationTable[key][1]);
				allText = allText.replaceAll('<span id="customVar" style="background-color:powderblue;"><u>' + translationTable[key][0] + '</u></span>',translationTable[key][1]);
				allText = allText.replaceAll(translationTable[key][0],translationTable[key][1]);
			}
			console.log(allText);
			//allText = allText.replaceAll("<br>","\n");
			allText = allText.trim();
			//allText = encodeURIComponent(allText);
			var xhttp = new XMLHttpRequest();
			xhttp.open("POST", "api.php/updateQuestion/" , true);				
			xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			xhttp.send("value="+btoa(allText) + "&questionID=" + questionID +"&translationTable=" + JSON.stringify(translationTable));
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

		function addNewNumber()
		{
			var text = selectedText;//getSelectionText();
			text = text.trim();
			if(!inDict(translationTable, text))
			{
				console.log(text);
				translationTable[currentID] = [text,"%n[0,0,"+currentID+"]","number"];
				console.log(currentID,translationTable);
				tClean = tClean.replaceAll(text,"%n[0,0,"+currentID+"]");
				console.log(tClean);
				companyAmount++;
				currentID++;
				for (var key in translationTable) 
				{
					tClean = tClean.replaceAll(translationTable[key][1],"<span id='customVar' style='background-color:powderblue;'><u>" + translationTable[key][0] + "</u></span>");
				};
				document.getElementById("main").innerHTML = tClean;
			}
		}

		function addNewSumNumber()
		{
			var text = selectedText;//getSelectionText();
			text = text.trim();
			if(!inDict(translationTable, text))
			{
				console.log(text);
				translationTable[currentID] = [text,"%s[[],"+currentID+"]","sum"];
				console.log(currentID);
				tClean = tClean.replaceAll(text,"%s[[],"+currentID+"]");
				console.log(tClean);
				companyAmount++;
				currentID++;
				for (var key in translationTable) 
				{
					tClean = tClean.replaceAll(translationTable[key][1],"<span id='customVar' style='background-color:powderblue;'><u>" + translationTable[key][0] + "</u></span>");
				};
				document.getElementById("main").innerHTML = tClean;
			}
		}

		function addNewAwnser()
		{
			var text = selectedText;
			text = text.trim();
			if(!inDict(translationTable, text))
			{
				console.log(text);
				translationTable[currentID] = [text,"%a[[],"+currentID+"]","string"];
				console.log(currentID);
				tClean = tClean.replaceAll(text,"%a["+companyAmount+","+currentID+"]");
				console.log(tClean);
				companyAmount++;
				currentID++;
				for (var key in translationTable) 
				{
					tClean = tClean.replaceAll(translationTable[key][1],"<span id='customVar' style='background-color:powderblue;'><u>" + translationTable[key][0] + "</u></span>");
				};
				document.getElementById("main").innerHTML = tClean;
			}
		}

		function addNewCompany()
		{
			var text = selectedText;//getSelectionText();
			text = text.trim();
			if(!inDict(translationTable, text))
			{
				console.log(text);
				translationTable[currentID] = [text,"%c["+companyAmount+","+currentID+"]","string"];
				console.log(currentID);
				tClean = tClean.replaceAll(text,"%c["+companyAmount+","+currentID+"]");
				console.log(tClean);
				companyAmount++;
				currentID++;
				for (var key in translationTable) 
				{
					tClean = tClean.replaceAll(translationTable[key][1],"<span id='customVar' style='background-color:powderblue;'><u>" + translationTable[key][0] + "</u></span>");
				};
				document.getElementById("main").innerHTML = tClean;
			}
		}

		function getSelectionText() {
    var html = "";
    if (typeof window.getSelection != "undefined") {
        var sel = window.getSelection();
        if (sel.rangeCount) {
            var container = document.createElement("div");
            for (var i = 0, len = sel.rangeCount; i < len; ++i) {
                container.appendChild(sel.getRangeAt(i).cloneContents());
            }
            html = container.innerHTML;
        }
    } else if (typeof document.selection != "undefined") {
        if (document.selection.type == "Text") {
            html = document.selection.createRange().htmlText;
        }
    }
    return html;
}

		function getSelectionText2()
		{
			var txtarea = document.getElementById("main");

			// Obtain the index of the first selected character
			var start = txtarea.selectionStart;

			// Obtain the index of the last selected character
			var finish = txtarea.selectionEnd;

			// Obtain the selected text
			var sel = txtarea.textContent.substring(start, finish);
			return sel;
		}

		function getSelectionText3() {
			var text = "";
			if (window.getSelection) {
				text = window.getSelection().toString();
			} else if (document.selection && document.selection.type != "Control") {
				text = document.selection.createRange().text;
			}
			return text;
		}
		
		<?php
		/*
		if(isset($_GET["questionID"]))
		{
			echo $_GET["questionID"];
			?>
			loadNewQuestion(<?php $_GET["questionID"]; ?>);
			<?php
		}

		*/
	}
	else
	{
		if(isset($_GET["questionID"]))
		{
			?>
			
			<script>
				loadNewQuestion("<?php echo $_GET["questionID"];?>");
			<?php
		}

	}
?>
</script>
<script>
	function setTClean()
	{
		tClean = document.getElementById("main").innerHTML;
		console.log(tClean);
	}

	function randomIntFromInterval(min, max) { // min and max included 
		return Math.floor(Math.random() * (max - min + 1) + min)
	}
	function inDict(data,valueToFind)
	{
		for (var key in data) 
		{
			if(data[key] == valueToFind)
			{
				return true;
			}
		}
		return false;
	}

	function randomIntFromInterval(min, max) { // min and max included 
		return Math.floor(Math.random() * (max - min + 1) + min)
	}
	

	function loadNewQuestion(questionID, instanceId = randomIntFromInterval(10000,100000))
	{
		var xhttp = new XMLHttpRequest();
		xhttp.open("GET", "api.php/getNewQuestion/?questionID="+questionID+"&instanceId="+instanceId, true);				
		xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		xhttp.send("");
		xhttp.onreadystatechange = function() 
		{ 
			if (xhttp.readyState == 4 && xhttp.status == 200)
			{
				if(xhttp.responseText.length > 0 )
				{
					jsonRaw = JSON.parse(xhttp.responseText);
					tClean = jsonRaw["text"];
					document.getElementById("main").innerHTML = tClean;
				}
			}
		}
	}

	function sleep(millis)
	{
		var date = new Date();
		var curDate = null;
		do { curDate = new Date(); }
		while(curDate-date < millis);
	}
</script>

</html>