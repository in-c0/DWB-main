//7/08/2024 new file
var operators = ["+","-","*","/","^","%"];
const TT_VARIABLENAME = 0;
const TT_JSONVALUE = 1;
const TT_VARIABLETYPE = 2;

const sectionInfo = '<div class="sectionDiv" contenteditable="false"> <hr class="section"> <button onclick="removeSection(this)" class="btn onlyStaff">Remove Section Line</button> </div>';


var aaa = "aaa";

/*Gets the new question name and sends it to the API
Inputs 
	- none
Outputs
	- none
side effects 
	- Changes the question on the backend
*/
function setQuestionName() 
{
	var n = document.getElementById("questionName").value;
	var xhttp = new XMLHttpRequest();
	questionName = n;
	xhttp.open("GET", "api.php/setQuestionName/?questionID="+questionID + "&name=" + n, true);				
	xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xhttp.send("");
	setNameItems();
}

function getSelectionObject() {
    let selection = window.getSelection();
    var startElement = null;
    
    if (!selection.isCollapsed) {
        let range = selection.getRangeAt(0);
    
        startElement = range.startContainer;
		console.log(startElement);
		console.log(startElement.parentElement);
        // If the selection starts in a text node, move to the parent element
        if (startElement.nodeType === Node.TEXT_NODE) {
            startElement = startElement.parentElement;
        }
		console.log(startElement.offsetParent.localName);
		if(startElement && startElement.nodeName !== 'TD' && startElement.offsetParent.localName == "table")
		{			
			console.log(startElement.parentElement);
		}
        // Now traverse upwards to find the closest <td> element
		if(startElement.offsetParent.localName == "td")
		{
			startElement = startElement.offsetParent;
		}
		console.log(startElement);
    }
    
    return startElement;
}

/*Format manager
Inputs 
	- type of change
	- text size
Outputs
	- None
side effects 
	- Changes the format of text to a specific type
*/
function formatManager(type, s = null)
{
	var object = getSelectionObject();
	console.log(object.style['font-weight']);
	if(type == "bold")
	{
		if(object.style['font-weight'] == 'bold')
		{
			object.style.fontWeight = "normal";
		}
		else
		{
			object.style.fontWeight = "bold";
		}
	}
	else if(type == "underline")
	{
		if(object.style['text-decoration-line'] == 'underline')
		{
			object.style.textDecorationLine = '';
		}
		else
		{			
			object.style.textDecorationLine = 'underline';
		}
	}
	else if(type == "italic")
	{
		if(object.style['font-style'] == 'italic')
		{
			object.style.fontStyle = 'normal';
		}
		else
		{
			object.style.fontStyle = 'italic';
		}		
	}
	else if(type == "changeFont")
	{
		object.style.fontSize = s;
	}
	else if(type == "center")
	{
		object.style.textAlign = 'center';			
	}
	else if(type == "left")
	{
		object.style.textAlign = 'left';		
	}
	else if(type == "right")
	{
		object.style.textAlign = 'right';		
	}
}

function changeTableFormat()
{
	var object = getSelectionObject();
	console.log(object);
	if(object.offsetParent.localName == "table")
	{
		var evenRowColor = document.getElementById("evenRowColor").value;
		var oddRowColor = document.getElementById("oddRowColor").value;

		object.offsetParent.setAttribute("even-color",evenRowColor);
		object.offsetParent.setAttribute("odd-color",oddRowColor);
		
		setTableStyle();
		console.log("Change Table Info");
	}
}

function getAnimationOrders()
{
	console.log("ANIMATION ORDERS");
	var items = document.getElementsByClassName("animation");
	console.log(items);
	var maxID = 1;
	Array.from(items).forEach(element => {
		console.log("ORDER",element);
		if(element.id == "customVar")
		{
			console.log(translationTable[nameToID(translationTable,element.className)]);
		}
		if(element.getAttribute("animationID") > maxID)
		{
			console.log(element.getAttribute("animationID"));
			maxID = Number(element.getAttribute("animationID"));
		}
	});

	for(var key in translationTable)
	{
		var jsonInfo = getJSON(key);
		console.log("key", key,jsonInfo);
		if("animationID" in jsonInfo)
		{
			if(jsonInfo["animationID"] > maxID)
			{
				maxID = Number(jsonInfo["animationID"]);
				console.log("maxID", maxID);
			}
		}
	}

	console.log("maxID", maxID);
	var s = "";
	for(var i = 1; i <= (maxID + 1); i++)
	{
		console.log("ADDING", i);
		s += "<option> " + i + "</option>";
	}
	return s;
}

function getAnimationTypes()
{
	var items = ['fade in', 'fade out', 'none'];
	s = "";
	Array.from(items).forEach(element => {
		s += "<option> " + element + "</option>";
	});
	return s;
}

function addAnimation()
{
	console.log("ADDING ANIMATION");
	var object = getSelectionObject();
	object.innerHTML = object.innerHTML.replaceAll("<br>","");
	console.log(object);
	let id = document.getElementById("animationOrders").value;
	let type = document.getElementById("animationTypes").value;
	if(object.id == "customVar")
	{
		changeJSONValue(atob(object.classList[0]), "animationID", id);
		changeJSONValue(atob(object.classList[0]), "animationType", type);
	}
	else
	{
		object.setAttribute("animationID", id);
		object.setAttribute("animationType", type);
		object.classList.add("animation");
	}
	propogateChangeText();
}

function removeAnimation()
{
	var object = getSelectionObject();
	showAnimation = false;
	propogateChangeText();
	if(object.id == "customVar")
	{
		changeJSONValue(atob(object.classList[0]), "animationID", "");
		changeJSONValue(atob(object.classList[0]), "animationType", "");
	}
	else
	{
		object.classList.remove("animation");
		object.removeAttribute("animationID");
	}
	showAnimation = true;
	
	propogateChangeText();
}

function changeBackground()
{
	var col = document.getElementById("backgroundColor").value;
	console.log(col);
	document.body.style.backgroundColor = col;
}



/*Does checks on the var 
Inputs 
	- id (of the new item)
Outputs
	- if it meets all the checks
side effects 
	- Does checks on the var 
*/
function varCheck(id)
{
	var jsonInfo = getJSON(id);
	if(jsonInfo["type"] == "answer" || jsonInfo['type'] == "equation")
	{
		if(jsonInfo['rawEquation'] == "None" || jsonInfo['rawEquation'] == null)
		{
			return 2;
		}
		else if(jsonInfo['rawEquation'] == "")
		{
			return 1;
		}
		else if(jsonInfo['rawEquation'] != "" && jsonInfo['equation'] == "")
		{
			return 2;
		}
		else
		{			
			return 0;
		}
	}
	if(jsonInfo["type"] == "number")
	{
		if(isNaN(jsonInfo["baseText"].replaceAll(",","")))
		{
			return 2;
		}
	}
	return 0;
}


function fixCaptial()
{
	for (var key in translationTable) 
	{
		translationTable[key][TT_VARIABLETYPE] = translationTable[key][TT_VARIABLETYPE].toLowerCase();
		changeJSONValue(key,"type", translationTable[key][TT_VARIABLETYPE].toLowerCase());
	}
}

var debugOn = true;

function inTranslationTable(name)
{
	for(key in translationTable)
	{
		if(translationTable[key][0] == name)
		{
			return true;
		}
	}
	return false;
}

const NOALLOWEDCHARS = ['-','+',"*",'/','\\'];

/*
Handle the import option for files
Inputs 
	- file event handeler
Outputs
	- create the vars in translation table
side effects 
	- Handle the import option for files
*/
var paramaters = {};
function handleFiles(event) {
	var fileReader=new FileReader();
	const fileList = this.files; /* now you can work with the file list */
	console.log(fileList);
	console.log(event);
	console.log(event.target.result);
	console.log(fileList.length);
	paramaters = [];
	fileReader.onload=function(){

		//console.log(fileReader.result);
		var l = 0;
		contents = fileReader.result.replaceAll("\r","\n").replaceAll("\n\n","\n");
		contents.split("\n").forEach(element => {
			if(l != 0)
			{
				var c = 0;
				var item = {};
				var badRow = false;
				element.split(',').forEach(sE => {
					
					if(c == 0)
					{
						var allGood = true;
						item["name"] = "$" + sE.replaceAll('"',"").replaceAll(" ", "_").replaceAll("$","").replaceAll("'","").replaceAll("`","");
						NOALLOWEDCHARS.forEach(element => {
							if(item["name"].includes(element))
							{
								allGood = false;
								badRow = true;
							}
						});
						if(allGood)
						{
							
						}
						else
						{
							messageCreate("Error","Found a non accepted char in the paramter name");
						}
					}
					else if(c == 1 && !badRow)
					{
						item["type"] = sE.replaceAll('"',"").replaceAll("'","").replaceAll("`","").replaceAll(" ", "_");
					}
					else if(c == 2 && !badRow)
					{
						item["value"] = sE.replaceAll('"',"").replaceAll("'","").replaceAll("`","");
					}
					c++;
				});
				console.log(item);
				if(item != {})
				{
					paramaters.push(item);
				}
			}
			l++;
		});
		console.log(paramaters);

		paramaters.forEach(element => {
			if(element.type == undefined)
			{

			}
			else
			{
				console.log(inTranslationTable(element['name']));
				if(!inTranslationTable(element['name']))
				{
					if(element['type'].toLowerCase() == 'number')
					{
						translationTable[currentID] = [element['name'],'%var{"type": "'+element['type']+'", "values" : [0,0], "baseText": "'+element['value'].replaceAll(",","")+'","currentID":'+currentID+',"varName": "'+element['name']+'"}',element['type']];
					}
					else if(element['type'].toLowerCase() == 'answer' || element['type'].toLowerCase() == 'equation')
					{
						translationTable[currentID] = [element['name'],'%var{"type": "'+element['type'].toLowerCase()+'", "values" : "", "baseText": "'+element['value']+'","currentID":'+currentID+', "equation" : "", "rawEquation" : "'+(element['value'])+'","varName": "'+element['name']+'"}',element['type'].toLowerCase()];
					}
					else
					{
						translationTable[currentID] = [element['name'],'%var{"type": "'+element['type']+'", "values" : [0,0], "baseText": "'+element['value']+'","currentID":'+currentID+',"varName": "'+element['name']+'"}',element['type']];
					}
					currentID++;
				}
				else
				{
					/*
					var jsonInfo = getJSON(nameToID(translationTable,element['name']));
					if(element['type'].toLowerCase() == 'number')
					{
						translationTable[jsonInfo['currentID']] = [element['name'],'%var{"type": "'+element['type']+'", "values" : [0,0], "baseText": "'+element['value'].replaceAll(",","")+'","currentID":'+jsonInfo['currentID']+',"varName": "'+element['name']+'"}',element['type']];
					}
					else if(element['type'].toLowerCase() == 'answer' || element['type'].toLowerCase() == 'equation')
					{
						translationTable[jsonInfo['currentID']] = [element['name'],'%var{"type": "'+element['type'].toLowerCase()+'", "values" : "", "baseText": "'+element['value']+'","currentID":'+jsonInfo['currentID']+', "equation" : "", "rawEquation" : "'+(element['value'])+'","varName": "'+element['name']+'"}',element['type'].toLowerCase()];
					}
					else
					{
						translationTable[jsonInfo['currentID']] = [element['name'],'%var{"type": "'+element['type']+'", "values" : [0,0], "baseText": "'+element['value']+'","currentID":'+jsonInfo['currentID']+',"varName": "'+element['name']+'"}',element['type']];
					}*/
				}
			}
		});
		tableCreate();
	}
  
	fileReader.readAsText(this.files[0]);
}


/*
Handle the import option for files into a list or dictionary
Inputs 
	- file event handeler
Outputs
	- create the vars in translation table
side effects 
	- Handle the import option for files
*/

let fileListOutput = null;
function fileToList(event) {
	var fileReader=new FileReader();
	const fileList = this.files; /* now you can work with the file list */
	fileReader.onload=function(){
		//console.log(fileReader.result);
		var type = 0;
		var l = 0;
		contents = fileReader.result.replaceAll("\r","\n").replaceAll("\n\n","\n");
		var list;
		contents.split("\n").forEach(element => {
			if (type == 0) {
				if (element.includes(',')) {
					list = {};
					type = 2;
				} else {
					list = [];
					type = 1;
				}
			} 
			if (type == 1) {
				if(element !== undefined && element !== "" && !list.includes(element))
				{
					console.log(element);
					list.push(element);
				}
			} else if (type == 2) {
				let pair = element.split(',', 2);
				if(pair[0] !== undefined && pair[1] !== undefined && pair[0] !== "" && pair[1] !== "")
				{
					list[pair[0]] = pair[1];
				}
			}
			
		});
		fileListOutput = list;
		document.getElementById("setItemValue").setAttribute("disabled","");
		if(type == 1)
		{
			document.getElementById("typeView").value = "list";
		}
		else
		{
			document.getElementById("typeView").value = "dictonary";
		}
	}
	fileReader.readAsText(this.files[0]);
	
	
}





/*Creates a table and can also be called to reload the table
Inputs 
	- id (of the new item)
Outputs
	- none
side effects 
	- displayes the table 
	- can be called to reload table
*/
function tableCreate(id = null, notPrompt = false) {
	document.getElementById("varName").innerHTML = getAllVarsOption();
	if(id != null)
	{
		document.getElementById("varName").value = translationTable[id][0];
	}
	if(document.getElementById("table"))
	{
		document.getElementById("table").outerHTML = "";
	}
	var colNames = ["Name","","Type","Value",""];
	if(debugOn)
	{		
		var colNames = ["Name","","Type","Value","","ID"];
	}
	var body = document.getElementById("tableHolder");

	var tbl = document.createElement("table");
	tbl.setAttribute("class","table table-hover table-bordered");
	//tbl.setAttribute("class","baseTable");
	var tblBody = document.createElement("tbody");
	
	row = document.createElement("tr");

	for (var i = 0; i < colNames.length; i++) {
		var cell = document.createElement("td");
		var cellText = document.createTextNode(colNames[i]);
		cell.appendChild(cellText);
		
		if(colNames[i] == "Name")
		{
			var exportButton = document.createElement("button");
			exportButton.innerHTML = "Export";
			exportButton.setAttribute("onclick","exportParamaterList()");
			exportButton.setAttribute("class","btn btn-outline-success");
			var space = document.createElement("span");
				
			cell.appendChild(space);
			cell.appendChild(exportButton);

			var importInput = document.createElement("input");
			importInput.innerHTML = "Import";
			//importButton.setAttribute("class","btn btn-outline-success");
			importInput.setAttribute("id","importFile");
			importInput.setAttribute("type","file");
			importInput.addEventListener("change", handleFiles, false);
			var space = document.createElement("span");

			cell.appendChild(space);
			cell.appendChild(importInput);

			
			var importButton = document.createElement("button");
			importButton.innerHTML = "Import";
			importButton.setAttribute("onclick","importFile()");
			importButton.setAttribute("class","btn btn-outline-success");
			var space = document.createElement("span");
				
			cell.appendChild(space);
			cell.appendChild(importButton);

		}
		row.appendChild(cell);
	}
	tblBody.appendChild(row);

	var names = [];
	for (var key in translationTable) 
	{
		if(translationTable[key][TT_VARIABLETYPE] != "table")
		{
			names.push([translationTable[key][TT_VARIABLENAME],key]);
		}
	}
	names.sort((a, b) => a[0].localeCompare(b[0]));

	//console.log(names);

	var failOptions = [];
	names.forEach(name => {
		var row = document.createElement("tr");
		key = name[1];
		//console.log(name);
		//console.log(key);
		

		for (var i = 0; i < colNames.length; i++) {
			if(translationTable[key][TT_VARIABLETYPE] != "table")
			{
				var cell = document.createElement("td");
				var cellText = document.createElement("p");
				if(i == 0)
				{
					cellText.innerHTML = translationTable[key][TT_VARIABLENAME];
				}
				else if(i == 1)
				{
					console.log(varCheck(key));
					var checkResult = varCheck(key);
					if((translationTable[key][TT_VARIABLETYPE] == "answer" || translationTable[key][TT_VARIABLETYPE] == "equation") )
					{
						if(checkResult == 2)
						{
							failOptions.push(["equationPullFromRaw",key]);
						}
						else if(checkResult == 1)
						{
							//failOptions.push(["equationSetToZero",key]);
						}
					}
					if(checkResult == 0)
					{
						cellText.innerHTML = "<img src=pics/pass.png style='height:20px;width:20px' >";
					}
					else if(checkResult == 1)
					{
						cellText.innerHTML = "<img src=pics/warning.png style='height:20px;width:20px' >";
					}
					else
					{						
						cellText.innerHTML = "<img src=pics/fail.png style='height:20px;width:20px' >";
					}
				}
				else if(i == 2)
				{
					cellText.innerHTML = translationTable[key][TT_VARIABLETYPE];
				}
				else if(i == 3)
				{
					if(translationTable[key][TT_VARIABLETYPE] == "sum")
					{
						cellText.innerHTML = "sum";
					}
					else if(translationTable[key][TT_VARIABLETYPE] == "answer")
					{
						var v = getJSON(key);
						if(v["rawEquation"] != "")
						{
							cellText.innerHTML = v["rawEquation"];//translateEquation(atob(v["equation"]));
						}
						else
						{
							cellText.innerHTML = "none"
						}
					}
					else if(translationTable[key][TT_VARIABLETYPE] == "equation")
					{
						var v = getJSON(key);
						if(v["rawEquation"] != "")
						{
							cellText.innerHTML = v["rawEquation"];//translateEquation(atob(v["equation"]));
						}
						else
						{
							cellText.innerHTML = "none"
						}
					}
					else if(translationTable[key][TT_VARIABLETYPE] == "list")
					{
						var v = getJSON(key);
						if(v["values"] != "")
						{
							cellText.innerHTML = "List of " + v["values"].length + " items";
						}
						else
						{
							cellText.innerHTML = "none"
						}
					}
					else
					{
						var v = getJSON(key);
						if(v["r"] == true && (v["rType"] == "range" || v["rType"] == "list"))
						{								
							cellText.innerHTML = v["baseText"] + " (random) [" + v["values"].join(",") + "]";
						}
						else
						{
							if("baseText" in v)
							{									
								cellText.innerHTML = v["baseText"];
							}
						}
					}
				}
				else if(i == 4)
				{
					cellText.innerHTML = key;
				}
				else
				{
					cellText.innerHTML = "<button class='btn btn-light' onclick=remove("+getJSON(key)["currentID"]+")> Remove </button>";
				}
				if(translationTable[key][TT_VARIABLETYPE] != "table")
				{
					cell.appendChild(cellText);
					row.appendChild(cell);
				}
			}
		}
		if(translationTable[key][TT_VARIABLETYPE] != "table")
		{
			tblBody.appendChild(row);
		}
	});
	
	if(failOptions.length > 0 && !notPrompt)
	{
		createPrompt("autoFixVars","Do you wish to try to automaticly fix any issues","(y/n)",failOptions);
		
		tbl.appendChild(tblBody);
		// put <table> in the <body>
		body.appendChild(tbl);
		// tbl border attribute to 
		tbl.setAttribute("border", "2");
		tbl.setAttribute("id","table");
	}
	else
	{		
		// append the <tbody> inside the <table>
		tbl.appendChild(tblBody);
		// put <table> in the <body>
		body.appendChild(tbl);
		// tbl border attribute to 
		tbl.setAttribute("border", "2");
		tbl.setAttribute("id","table");
	}

}

function autoFixVars(callback, passItems)
{
	console.log(callback);
	console.log(callback.parentNode.childNodes);
	console.log(callback.parentNode.childNodes[3])
	if(callback.parentNode.childNodes[3].value.toLowerCase() == "y")
	{
		console.log(passItems);
		passItems.forEach(element => {
			console.log(element);
			if(element[0] == "equationPullFromRaw")
			{
				var jsonInfo = getJSON(element[1]);
				var eq = rawEquationToEquation(jsonInfo["rawEquation"]);
				console.log(eq, element[1]);
				if(eq[1] == 0)
				{
					changeJSONValue(element[1], "equation", btoa(eq[0]));
					console.log(getJSON(element[1])['equation']);
				}
			}
			else if(element[0] == "equationSetToZero")
			{
				changeJSONValue(element[1], "rawEquation", "");
				changeJSONValue(element[1], "equation", btoa(""));
			}
		});
		tableCreate(null,true);
	}
	document.getElementById("promptMessage").outerHTML = "";

}

function applyDefaultTableFormat(table = null)
{
	if(table == null)
	{
		var o = getSelectionObject();

		console.log(o);

		if(o.offsetParent.localName == "table")
		{
			let table = o.offsetParent;
			table.width = "60%";
			table.removeAttribute("style");
			for (let i = 0; i < table.rows.length; i++) { 
				// Loop through rows
				let row = table.rows[i];
				
				row.removeAttribute("style");  
				// Loop through cells in each row
				for (let j = 0; j < row.cells.length; j++) { 
					// Change background color of cell
					row.cells[j].removeAttribute("style");  
					row.cells[j].setAttribute("width", "auto");  
					row.cells[j].setAttribute("height", "50px;");  
					//row.cells[j].setAttribute("colspan",1);  
				}
			}			
		}
	}
	else
	{
		table.width = "60%";
		table.removeAttribute("style");
		for (let i = 0; i < table.rows.length; i++) { 
			// Loop through rows
			let row = table.rows[i];
			
			row.removeAttribute("style");  
			// Loop through cells in each row
			for (let j = 0; j < row.cells.length; j++) { 
				// Change background color of cell
					row.cells[j].removeAttribute("style");  
					row.cells[j].setAttribute("width", "auto");  
					row.cells[j].setAttribute("height", "50px;");  
					//row.cells[j].setAttribute("colspan",1);  
			}
		}
	}
}

function changeTableSize(adder)
{
	var o = getSelectionObject();
	if(o.offsetParent.localName == "table" || o.localName == "table")
	{
		if(o.offsetParent.localName == "table")
		{
			o = o.offsetParent
		}
		
		var width = o.getAttribute("width").replace("%","");
		if(width == "NaN")
		{
			width = 50;
		}
		else
		{
			width = Number(width);
		}
		console.log(width);
		width += (adder * 5);
		if(width > 100)
		{
			width = 100;
		}
		if(width < 5)
		{
			width = 5;
		}
		o.setAttribute("width",width + "%");

		document.getElementById("currentTableWidth").innerHTML = width + "%";
	}
}


function setColspan(size = null)
{	
	if(size == null)
	{
		size = document.getElementById("colSpanSizeSetter").value;
	}

	var o = getSelectionObject();
	if(o.offsetParent.localName == "table" && o.localName == "td")
	{
		o.setAttribute("colspan",size);
	}
}

function changeRowWidth(adder = 1)
{

	var o = getSelectionObject();
	if(o.offsetParent.localName == "table" && o.localName == "td")
	{
		var width = o.getAttribute("width").replace("%","");
		if(width == "NaN" || width == "auto")
		{
			width = 50;
		}
		else
		{
			width = Number(width);
		}
		console.log(width);
		width += (adder * 5);
		if(width > 100)
		{
			width = 100;
		}
		if(width < 5)
		{
			width = "auto";
		}
		if(width == "auto")
		{
			o.setAttribute("width",width );
			document.getElementById("currentRowWidth").innerHTML = width;
		}
		else
		{
			o.setAttribute("width",width + "%");			
			document.getElementById("currentRowWidth").innerHTML = width + "%";
		}
		if(o.hasAttribute("style"))
		{
			o.removeAttribute("style");
		}
		//o.style.width = size + "%";
	}
}


/*Set the names of questionName and questionNameDev to the questionName
Inputs 
	- none
Outputs
	- none
side effects 
	- sets the name of questionName and questionNameDev to the questionName
*/
//24/09/2024 Added
function setNameItems()
{
	document.getElementById("questionName").value = questionName;
	document.getElementById("questionNameDev").value = questionName;
	document.title = "Digital Workbook - " + questionName;
}

function removeNT(text)
{	
	return text.replaceAll("\t","").replaceAll("\n","")
}

//Removes spaces in the front and the end of the text in each array item
function stripArray(array)
{
	var cleanArray = [];
	for(var i = 0; i < array.lenght; i++)
	{
		cleanArray.append(array.trim());
	}
	return cleanArray;
}


function rawEquationToEquation(text)
{
	text = text.replaceAll("+"," + ");
	text = text.replaceAll("-"," - ");
	
	text = text.replaceAll("*"," * ");
	text = text.replaceAll("/"," / ");

	text = text.replaceAll("^"," ^ ");
	text = text.replaceAll("  "," ");
	var tt = text.split(" ");
	for (var i = 0; i < tt.length; i++)
	{
		if(tt[i].includes("$"))
		{
			console.log(tt[i].replaceAll(")","").replaceAll("(",""));
			var nID = nameToID(translationTable,tt[i].replaceAll(")","").replaceAll("(","").replaceAll(",",""));
			console.log(nID);
			if(nID == -1)
			{
				messageCreate("PARAMATER " + tt[i].replaceAll(")","").replaceAll("(","") + ": has not been made as it refrenced a non created paramater in an equation","ERROR");
				errorInEquation = true;	
				return [text,1];
			}
			text = text.replaceAll(tt[i].replaceAll(")","").replaceAll("(",""), "$" + nID);
		}
		else if(operators.includes(tt[i]))
		{

		}
		else if(tt[i] == ":" || tt[i] == "?")
		{

		}
		else if(tt[i] == "(" || tt[i] == ")" || tt[i].toLowerCase() == "round(" || tt[i].toLowerCase() == "," || tt[i] == "|")
		{

		}
		else if(isNaN(tt[i]))
		{			
			console.log("ERROR",tt[i]);	
			messageCreate("EQUATION CONTAINED A REFRENCE TO A ITEM THAT ISN'T A NUMBER: " + tt[i],"ERROR");
			errorInEquation = true;			
			return [text,1];
		}
	}
	return [text,0];

}


/*Gets the input from the quick input bar and checks if its matches the correct format if its type is answer or equation
Inputs 
	- none
Outputs
	- none
side effects 
	- will return an error if invalid format is given 
	- will set the new var to the given value if correct
	- will reload the table to show the updated values
*/
//25/07/2024 Added
function checkQuickBarInput()
{
	console.log("CHECKING QUICK ACCESS BAR INPUT");
	var varName = document.getElementById("varName").value;
	var varValueOptions = document.getElementById("varValueOptions").value;
	var incorrectValues = document.getElementById("values").value;
	var correctValues = document.getElementById("setBaseValue").value;
	var inputData = document.getElementById("quickBar").value;
	console.log("CQI",inputData);
	var id = nameToKey(varName);
	console.log(varName, id);
	var j = getJSON(id);
	var dropDownOption = false;
	if(j['type'] == "number")
	{
		inputData = inputData.replaceAll(",","");
	}
	if(correctValues != "")
	{
		dropDownOption = true;
	}
	var errorInEquation = false;
	console.log(j["type"]);
	const str = ",1234)"; // Example input string

	const regex = /^,\d*\)$/;

	if(j["type"] == "answer" || j["type"] == "equation")
	{
		for (o in operators)
		{
			console.log(operators[o]);
			inputData = inputData.replaceAll(operators[o], " " + operators[o] + " ");
		}
		inputData = inputData.replaceAll("(","( ");
		inputData = inputData.replaceAll(")"," )");
		inputData = inputData.replaceAll(","," , ");
		inputData = inputData.replaceAll("|"," | ");
		inputData = inputData.replaceAll("  "," ");
		console.log(inputData);
		var raw = inputData.split(" ");
		operatorLast = true;
		if(!dropDownOption)
		{
			for (item in raw)
			{
				console.log(raw[item]);
				if(!operatorLast)
				{
					if(operators.includes(raw[item]))
					{
						operatorLast = true;
					}
					else if(raw[item] == "(" || raw[item] == ")" || raw[item].includes("$") || raw[item].toLowerCase() == "round(" || raw[item].toLowerCase() == "," || regex.test(raw[item]))
					{
						
					} 
					else if(raw[item] == "|")
					{
						dropDownOption = true;	
					}
					else if(!isNaN(raw[item]))
					{

					}
					else
					{
						console.log("ERROR IN QUICK EQUATION",raw[item]);
						errorInEquation = true;
					}
				}
				else
				{
					operatorLast = false;
				}
			}
		}
		if(!errorInEquation)
		{
			if(dropDownOption)
			{

			}
			else
			{
				document.getElementById("quickBar").value = inputData;
			}
			
			rawText = inputData;
			text = inputData;
			console.log(text);
			var tt = text.split(" ");
			errorInEquation = false;
			console.log("ITEM IN EQUATION", tt);
			for (var i = 0; i < tt.length; i++)
			{
				if(tt[i].includes("$"))
				{
					console.log(tt[i].replaceAll(")","").replaceAll("(",""));
					var nID = nameToID(translationTable,tt[i].replaceAll(")","").replaceAll("(","").replaceAll(",",""));
					console.log(nID);
					if(nID == -1)
					{
						messageCreate("PARAMATER " + tt[i].replaceAll(")","").replaceAll("(","") + " HAS NOT BEEN MADE","ERROR");
						errorInEquation = true;
						break;
					}
					text = text.replaceAll(tt[i].replaceAll(")","").replaceAll("(",""), "$" + nID);
				}
				else if(operators.includes(tt[i]))
				{

				}
				else if(tt[i] == ":" || tt[i] == "?")
				{

				}
				else if(tt[i] == "(" || tt[i] == ")" || tt[i].toLowerCase() == "round(" || tt[i].toLowerCase() == "," || tt[i] == "|")
				{

				}
				else if(isNaN(tt[i]))
				{				
					
					console.log("ERROR",tt[i]);	
					messageCreate("EQUATION CONTAINED A REFRENCE TO A ITEM THAT ISN'T A NUMBER: " + tt[i],"ERROR");
					errorInEquation = true;
					break;
				}
			}
			console.log(errorInEquation);
			console.log("VAR OPTINS",varValueOptions);
			if(varValueOptions == "Dropdown")
			{
				console.log("SETTING DROPDOWN");
				changeJSONValue(id,"values",removeNT(document.getElementById("values").value.replaceAll("  "," ")).split("|").sort());
				changeJSONValue(id,"baseText",removeNT(document.getElementById("setBaseValue").value.replaceAll("  "," ")));
				console.log(stripArray(removeNT(document.getElementById("values").value.replaceAll("  "," ")).split("|")),stripArray(removeNT(document.getElementById("setBaseValue").value.replaceAll("  "," "))))
				console.log(getJSON(id));
			}		
			else if(!errorInEquation)
			{
				changeJSONValue(id,"rawEquation",rawText);
				text = text.replaceAll(" )",")").replaceAll("( ","(").replaceAll("  ", " ");
				console.log(text);
				changeJSONValue(id,"equation",btoa(text.toLocaleLowerCase()));
				var evalForm = inputData;
				console.log("EVAL FORM RAW",inputData);
				
				evalForm = evalForm.replaceAll(",","");
				console.log("EVAL FORM",evalForm);
				var equationValue = getEquationValue(id);
				//0 = equation output 
				//1 = raw equation
				document.getElementById("formulaEval").innerHTML = equationValue[1] + " = " + equationValue[0];
				propogateChangeText(id);
			}
		}
	}
	else if(j["type"] == "answerText")
	{		
		inputData = inputData.replaceAll("|"," | ");
		inputData = inputData.replaceAll("  "," ");

		if(varValueOptions == "Dropdown")
		{
			console.log("SETTING DROPDOWN");
			console.log(removeNT(document.getElementById("values").value.replaceAll("  "," ")).split("|"), removeNT(document.getElementById("setBaseValue").value));
			changeJSONValue(id,"values",removeNT(document.getElementById("values").value.replaceAll("  "," ")).split("|").sort());
			changeJSONValue(id,"baseText",removeNT(document.getElementById("setBaseValue").value.replaceAll("  "," ")));
			console.log(getJSON(id));
		}
		else
		{
			console.log("SETTING baseText");
			changeJSONValue(id,"baseText",inputData);
			changeJSONValue(id,"values",[]);
			changeJSONValue(id,"rType",'set');
			changeJSONValue(id,"r",false);
			console.log(getJSON(id)["baseText"]);
		}
	}
	else if(j["type"] == "document")
	{
		if(varValueOptions == "Set Value")
		{
			changeJSONValue(id,"baseText",btoa(inputData));
			changeJSONValue(id,"rType",'set');
			changeJSONValue(id,"r",false);
		}	
	}
	else
	{
		if(varValueOptions == "Set Value")
		{
			changeJSONValue(id,"baseText",inputData);
			changeJSONValue(id,"rType",'set');
			changeJSONValue(id,"r",false);
		}	
		else if(varValueOptions == "Random in Range")
		{			
			if(isNaN(document.getElementById("randomQuickBarOptionMin").value.replaceAll(",","")) || isNaN(document.getElementById("randomQuickBarOptionMax").value.replaceAll(",","")))
			{
				messageCreate("Recived a non number in range","ERROR");
			}
			else
			{
				var min = Number(document.getElementById("randomQuickBarOptionMin").value.replaceAll(",",""));
				var max = Number(document.getElementById("randomQuickBarOptionMax").value.replaceAll(",",""));
				//rType
				changeJSONValue(id,"rType",'range');
				changeJSONValue(id,"r",true);
				changeJSONValue(id,"values",[min,max]);
			}
		}	
		else if(varValueOptions == "Random in List")
		{					
			var items = document.getElementById("listQuickBar").value.split("|");
			//rType
			changeJSONValue(id,"rType",'list');
			changeJSONValue(id,"r",true);
			changeJSONValue(id,"values",items);
			
		}	
		propogateChangeText(id);
		document.getElementById("formulaEval").innerHTML = inputData.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
	}
	if(errorInEquation)
	{
		//messageCreate("Quick bar recived a none correct input, if you are writing in an equation check if its correct", "ERROR");
	}
	tableCreate(id);
}

function testMerge()
{
	var table= document.getElementById(9);
	var tableRowNodes = table.rows;
	var yItems = [];
	var gotYItems = false;
	var currentSection = -1;
	var sectionNames = [];
	var newVars = {};
	for(var row = 0; row < tableRowNodes.length; row++)
	{		
		var cells = table.rows.item(row).cells;
		console.log(table.rows.item(row));
		var xAdder = "";
		if(table.rows.item(row).hasAttribute('section'))
		{
			sectionNames.push(table.rows.item(row).childNodes[0].innerText.replaceAll(" ","_"));
			currentSection++;
		}
		else
		{
			console.log(table.rows.item(row).childNodes);
			for(var i in Array.from(table.rows.item(row).childNodes))
			{
				var text = table.rows.item(row).childNodes[i].innerText.replaceAll(" ","_");
				
				if(!gotYItems)
				{
					yItems.push(text);
				}
				else
				{
					if(i == 0)
					{
						xAdder = text;
					}
					else
					{
						var s = table.rows.item(row).childNodes[i].innerHTML.replaceAll("<p>","").replaceAll("<span>","").replaceAll("</p>","").replaceAll("</span>","").split("<br>");
						for (x in s)
						{					
							var textType = null;
							if(isNaN(s[x].replaceAll(",","").replaceAll(".","")))
							{
								textType = "string";
							}
							else
							{
								textType = "number";
							}
							if(sectionNames[currentSection] + "-" + xAdder + "-" + yItems[i] in newVars)
							{
								var a = 0;
								while(sectionNames[currentSection] + "-" + xAdder + "-" + yItems[i] + "-" + a in newVars)
								{
									a++;
								}
								newVars[sectionNames[currentSection] + "-" + xAdder + "-" + yItems[i] + "-" + a] = textType;

							}
							else
							{								
								newVars[sectionNames[currentSection] + "-" + xAdder + "-" + yItems[i]] = textType;
								console.log(sectionNames[currentSection] + "-" + xAdder + "-" + yItems[i],textType,s[x]);
							}
						}
					}
				}
			}
			if(!gotYItems)
			{
				gotYItems = true;
			}
			for(var cell in cells)
			{
				console.log(cells.item(cell).innerText);
			}
		}
		console.log(sectionNames);
		console.log(yItems);
	}
	console.log(newVars);
	console.log(Object.keys(newVars).length);
}

/*
Creates a document type from a table
Inputs 
	- var id
Outputs
	- none
side effects 
	- Creates a document type from a table
*/
function tableToDocumentGen(id, encoded=false)
{
	var t = document.getElementById(id);
	var table = t;
	var rows = t.childNodes[0].childNodes;
	console.log(rows);
	var sizeInfo = rows.length + ":";
	var documentCode = "";
	var cols = 0;

	var sectionList = [];

	var rowLength = table.rows.length;
	var sections = [];
	var maxSubCellRows = 0;
	for (row = 0; row < rowLength; row++) {
		var cells = table.rows.item(row).cells;
		console.log(table.rows.item(row));
		if(table.rows.item(row).childNodes.length > cols)
		{
			cols = table.rows.item(row).childNodes.length;
		}
		if (table.rows.item(row).hasAttribute('section')) {
			var sectionInfo = {};
			if(table.rows.item(row).hasAttribute("sectioninfo"))
			{
				sectionInfo = JSON.parse(table.rows.item(row).getAttribute("sectioninfo"));
			}
			sectionList.push({name: cells.item(0).innerText, sectioninfo : sectionInfo, rows : []});
		} else {
			cellVals = [];
			for (i = 0; i < cells.length; i++) {
				if(cells.item(i).innerText.split("\n").length > maxSubCellRows)
				{
					maxSubCellRows = cells.item(i).innerText.split("\n").length;
				}
				cellVals.push(cells.item(i).innerText.replace("\n","\\n").replaceAll("'",""));
			}
			sectionList.at(sectionList.length - 1).rows.push(cellVals);
		}
		console.log(sectionList);
	}

	console.log(sectionList);
	/*
	for(var i = 0; i < rows.length; i++)
	{
		let row = rows[i];
		let subDocument = "";
		if(row.childNodes.length > cols)
		{

			cols = row.childNodes.length;
		}
		row.childNodes.forEach(cell => {
			console.log(cell.childNodes);
			var added = false;
			var adderText = "";
			cell.childNodes.forEach(element => {
				//console.log(element);
				if(element.id == "customVar" || element.id == "customVar2")
				{
					console.log(element.className);
					console.log("FOUND CUSTOM");
					adderText += getJSON(atob(element.className))["varName"];
					added = true;
				}
				else if(element.nodeName == "#text")
				{
					adderText += element.nodeValue ;
					added = true;
				}
				else if(element.id == "emptySpan")
				{
					console.log("SKIPPED EMPTY SPAN");
				}
				else if(!element.innerHTML.includes('id="customVar'))
				{
					if(element.innerHTML == ";")
					{
						adderText += ";";
					}
					adderText = element.innerHTML;
					added = true;
				}
			});
			if(added)
			{
				subDocument += adderText;
				subDocument += ",";
			}
			console.log(subDocument);
		});
		subDocument = subDocument.substring(0,subDocument.length - 1);
		documentCode += subDocument + "|";
	}*/
	var info = {};
	info["cols"] = cols;
	info["rows"] = rowLength;
	info["maxSubCellRows"] = maxSubCellRows;
	info["sectionList"] = sectionList
	console.log(info);
	if(!encoded)
	{
		return info;
	}
	const json = JSON.stringify(info);
	let jsonString = JSON.stringify(info);
	jsonString = jsonString.trim();
	const base64Encoded = btoa(unescape(encodeURIComponent(jsonString)));
  	return base64Encoded;
}




/*Changes the amount of info shown on the page
Inputs 
	- none
Outputs
	- none
side effects 
	- Changes the amount of info shown on the page
*/
function toggleVerboseView()
{
	showAllInfo = !showAllInfo;
	for (const [key, value] of Object.entries(translationTable)) {
		propogateChangeText(key);
	}
}

/*Propogate any new changes through the text
Inputs 
	- id of a user variable
Outputs
	- none
side effects 
	- will change any text of all the instance of the user variable with the id in the text with the new information 
*/
var showAnimation = false;
var showAllInfo = false;
function propogateChangeText(id = null)
{
	if(id != null)
	{
		Array.from(document.getElementsByClassName(btoa(id))).forEach(element => {
			var animationAdder = "";
			if("animationID" in getJSON(id) && showAnimation) 
			{
				animationAdder += getJSON(id)['animationID'];
			}
			if(showAllInfo == true)
			{
				if(getJSON(id)["type"] == "equation" || getJSON(id)["type"] == "answer")
				{
					element.innerHTML = "(" + translationTable[id][TT_VARIABLENAME] + "," + getJSON(id)["rawEquation"] + "," + translationTable[id][TT_VARIABLETYPE] + ")" + "<sup>" + animationAdder + "</sup>";
				}
				else
				{
					element.innerHTML = "(" + translationTable[id][TT_VARIABLENAME] + "," + getJSON(id)["baseText"] + "," + translationTable[id][TT_VARIABLETYPE] + ")" + "<sup>" + animationAdder + "</sup>";
				}
			}
			else
			{
				if(getJSON(id)["type"] == "equation" || getJSON(id)["type"] == "answer")
				{
					element.innerHTML = "(" + translationTable[id][TT_VARIABLENAME] + ")" + "<sup>" + animationAdder + "</sup>";
				}
				else
				{
					element.innerHTML = "(" + translationTable[id][TT_VARIABLENAME] + ")" + "<sup>" + animationAdder + "</sup>";
				}
			}
		});
	}

	Array.from(document.getElementsByClassName('animation')).forEach(element => {
		if(element != null)
		{
			element.innerHTML = element.innerHTML.replaceAll("<sup>" + element.getAttribute("animationID") + "</sup>","");
			if(showAnimation)
			{			
				if(element != null)
				{
					element.innerHTML += "<sup>" + element.getAttribute("animationID") + "</sup>";
				}
			}
		}
	});
}

/*Remove a item from the table
Inputs 
	- id of the user variable
Outputs
	- none
side effects 
	- will remove the item from the table
	- will remove the item from the text and replace it with the base value
*/
function remove(targetID)
{
	console.log(targetID);
	removeAssigment(targetID);
	delete translationTable[targetID];
	console.log(translationTable);
	tableCreate();
}

function round(number, digits)
{
	if(digits == 0)
	{
		console.log(digits);
		return Math.round(number);
	}
	return Math.round(number * (digits * 10));
	
}

/*Get the value of a given user variable
Inputs 
	- id of the user variable
Outputs
	- [the equation output, the equation]
side effects 
	- none
*/
//26/07/2024 Added 1:00pm
function getEquationValue(id)
{
	var evalForm = atob(getJSON(id)["equation"]);
	evalForm += " ";
	console.log("RAW EQUATION", evalForm);
	for (key in translationTable)
	{	
		evalForm.split(" ").forEach(element => {			
			if(evalForm.includes("$" + key) && "baseText" in getJSON(key))
			{
				console.log(key,getJSON(key)["baseText"]);
				if(getJSON(key)["type"] == "equation" || getJSON(key)["type"] == "answer") 
				{ 
					if(evalForm.includes("|"))
					{
						console.log(evalForm, "|||");
					}
					else
					{
						evalForm = evalForm.replaceAll("$" + key + " ", getEquationValue(key)[0]);
					}
				}
				else
				{
					evalForm = evalForm.replaceAll("$" + key + " ", getJSON(key)["baseText"]);	
				}	
				console.log("evalForm",evalForm);
			}
		});
	}
	console.log("CLEANED EQUATION",evalForm);
	return [eval(evalForm.toLocaleLowerCase()),evalForm];
}

/*Get the value of a given user variable
Inputs 
	- none
Outputs
	- get all the diffrent types in the translation table
side effects 
	- none
*/
function getTranslationTableTypesCount()
{
	var translationTableTypesCount = {};
	var out = "";
	for (y in translationTable){
		if(translationTable[y][2] in translationTableTypesCount)
		{
			translationTableTypesCount[translationTable[y][2]] += 1;
		}
		else
		{
			translationTableTypesCount[translationTable[y][2]] = 1;
		}
		console.log(translationTable[y]);
	} 
	for (y in translationTableTypesCount){
		out += y + " : " + translationTableTypesCount[y] + "<br>";
	}
	return out;
}

var orgName = "";
var questionName = "";
var topicName = "";
var topicID = -1;
var subjectName = "";
var orgId = null;
var public = false;
var org = {};
chatQuestionID = questionID;
/*Gets and loads the the raw data of the question this contains all the data need to change the text in the future
Inputs 
	- question ID
Outputs
	- none
side effects 
	- sets the question title text box and title of the page
	- sets the text 
	- sets the translation table
	- creates the DataTable
*/
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
				try {					
					types["number"] = JSON.parse("{}");
					types["equation"] = JSON.parse("{}");
					types["answer"] = JSON.parse("{}");
					types["answerText"] = JSON.parse("{}");
					types["time"] = JSON.parse("{}");
					types["document"] = JSON.parse("{}");
					types["subDocument"] = JSON.parse("{}");
					types["list"] = JSON.parse("{}");
					types["dictonary"] = JSON.parse("{}");
				} catch (error) {
					types = {};
					types["number"] = JSON.parse("{}");
					types["equation"] = JSON.parse("{}");
					types["answer"] = JSON.parse("{}");
					types["answerText"] = JSON.parse("{}");
					types["time"] = JSON.parse("{}");
					types["document"] = JSON.parse("{}");
					types["subDocument"] = JSON.parse("{}");
					types["list"] = JSON.parse("{}");
					types["dictonary"] = JSON.parse("{}");
				}

				var jsonResponse = JSON.parse(xhttp.responseText);
				tClean = atob(jsonResponse["raw"]);
				console.log(tClean);
				answer = jsonResponse["answer"];
				if(jsonResponse["translationTable"] == "")
				{
					jsonResponse["translationTable"] = "{}";
				}
				subjectName = jsonResponse["subjectName"];
				orgName = jsonResponse["orgName"];
				if("topicName" in jsonResponse)
				{
					topicName = jsonResponse["topicName"];
				}
				topicID = jsonResponse["topicID"];
				
				if("name" in jsonResponse)
				{
					var questionTitle = jsonResponse["name"];
					if(questionTitle == "")
					{
						questionTitle = "No Name Given";
					}
					document.title = "Digital Workbook - " + questionTitle;
				}
				else
				{
					console.log(jsonResponse, "TITLE SEARCH");
				}

				console.log(decodeURIComponent(jsonResponse["translationTable"]));
				translationTable = JSON.parse(decodeURIComponent(jsonResponse["translationTable"]));
				var tempID = 0;
				console.log(translationTable);
				for (var key in translationTable) 
				{
					console.log(getJSON(key)["type"]);
					tempID = key;
					if(getJSON(key)["type"] in types)
					{

					}
					else
					{
						if(translationTable[key][2] != "table")
						{
							var jsonObject = JSON.parse("{}");
							var jsonValue = getJSON(key);
							if("items" in jsonValue)
							{									
								jsonObject["items"] = getJSON(key)["items"];
							}
							if(getJSON(key)["type"] == undefined)
							{
								console.log("TYPE UNDI",getJSON(key));
							}
							types[getJSON(key)["type"]] = jsonObject;
						}
					}
				}
				document.getElementById("main").innerHTML = tClean;
				currentID = Number(tempID) + 1;
				console.log(translationTable, currentID);
				var d = [];
				for (var key in translationTable) 
				{
					tClean = document.getElementById("main").innerHTML;
					var y = document.getElementsByClassName(key);
					d = tClean.split("<br>");
					console.log(translationTable[key][TT_JSONVALUE]);
					if(translationTable[key][TT_VARIABLETYPE] != "table")
					{
						tClean = tClean.replaceAll(translationTable[key][TT_JSONVALUE],"<span class='" + btoa(key) + "' id='customVar' style='background-color:" + textBackgroundColor + "'></span>");
						answer = answer.replaceAll(translationTable[key][TT_JSONVALUE],"<span class='" + btoa(key) + "' id='customVar' style='background-color:" + textBackgroundColor + "'></span>");
					}
					
					var cc = 0;
					/*
					Array.from(document.getElementsByClassName(btoa(key))).forEach(element => {
						console.log("QUESTION LOAD", element);
						var jsonInfo = getJSON(key);
						if(cc > 0)
						{
							if(jsonInfo["type"] == "equation" || jsonInfo["type"] == "answer")
							{
								element.outerHTML = "<span class='"+btoa(key)+"' id='customVar' style='background-color:" + textBackgroundColorMulti + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonInfo["rawEquation"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span>";
							}
							else
							{
								element.outerHTML = "<span class='"+btoa(key)+"' id='customVar' style='background-color:" + textBackgroundColorMulti + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonInfo["baseText"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span>";
							}
						}
						else 
						{
							if(jsonInfo["type"] == "equation" || jsonInfo["type"] == "answer")
							{
								element.outerHTML = "<span class='" + btoa(key) + "' id='customVar' style='background-color:" + textBackgroundColor + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonInfo["rawEquation"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span>";
							}
							else
							{
								element.outerHTML = "<span class='"+btoa(key)+"' id='customVar' style='background-color:" + textBackgroundColor + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonInfo["baseText"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span>";
							}
						}
						cc++;
					});
					*/
				};
				document.getElementById("main").innerHTML = tClean;
				document.getElementById("answer").innerHTML = answer;
				
				if(document.getElementById("main").innerHTML.includes("%var{") || document.getElementById("answer").innerHTML.includes("%var{"))
				{
					for (var key in translationTable) 
					{
						tClean = document.getElementById("main").innerHTML;
						var y = document.getElementsByClassName(key);
						d = tClean.split("<br>");
						console.log(translationTable[key][TT_JSONVALUE]);
						if(translationTable[key][TT_VARIABLETYPE] != "table")
						{
							document.getElementById("main").innerHTML = document.getElementById("main").innerHTML.replaceAll(translationTable[key][TT_JSONVALUE],"<span class='" + btoa(key) + "' id='customVar' style='background-color:" + textBackgroundColor + "'></span>");
							document.getElementById("answer").innerHTML = document.getElementById("answer").innerHTML.replaceAll(translationTable[key][TT_JSONVALUE],"<span class='" + btoa(key) + "' id='customVar' style='background-color:" + textBackgroundColor + "'></span>");
						}
					}							
				}
				
				tClean = document.getElementById("main").innerHTML;
				answer = document.getElementById("answer").innerHTML;
				
				console.log(jsonResponse);
				questionName = jsonResponse["name"];
				document.getElementById("questionName").value = jsonResponse["name"];
				document.getElementById("answer").innerHTML += "<br>";
				document.getElementById("main").innerHTML += "<br>";
				public = jsonResponse["public"];
				
				tableCreate();
				//25/07/2024 Added
				if(Object.keys(translationTable).length > 0)
				{
					for(key in translationTable)
					{				
						//console.log(key);		
						if(translationTable[key][TT_VARIABLETYPE] != "table")
						{		
							showQuickBarVarItem(key);
							break;
						}
						//document.getElementById("varName").value = translationTable[key][TT_VARIABLENAME];
					}
				}

				document.getElementById("answer").innerHTML = answer;
				for (var key in translationTable) 
				{
					propogateChangeText(key);
				}


				var showOrgFile = getCookie("showOrgFileStructure");
				if(showOrgFile != null)
				{
					if(showOrgFile == 1)
					{
						showOrgFileStructure = true;
					}
				}
				console.log("COOKIE OPTION",showOrgFile);

				if(showOrgFileStructure == true)
				{			
					document.getElementById("fileStructure").removeAttribute("hidden");
					document.getElementById("orgName").innerHTML = orgName;
					document.getElementById("topicName").innerHTML = topicName;
					document.getElementById("subjectName").innerHTML = subjectName;
					document.getElementById("questionIDGo").innerHTML = questionName;
				}

				if(public)
				{					
					document.getElementById("publishButton").setAttribute("class","btn btn-outline-success");
					document.getElementById("publishButton").setAttribute("onclick","makeQuestionPrivate()");
					document.getElementById("publishButton").innerHTML = "PUBLIC";
				}
				else
				{					
					document.getElementById("publishButton").setAttribute("class","btn btn-outline-danger");
					document.getElementById("publishButton").setAttribute("onclick","makeQuestionPublic()");
					document.getElementById("publishButton").innerHTML = "PRIVATE";
				}

				Array.from(document.getElementsByClassName('animation')).forEach(element => {
					if(showAnimation)
					{
						element.innerHTML += "<sup>" + element.getAttribute("animationID") + "</sup>";
					}
				});

				for(key in translationTable)
				{			
					var jsonInfo = getJSON(key);
					if("animationID" in jsonInfo)
					{

					}
					
					//element.innerHTML += "<sup>" + element.getAttribute("animationID") + "</sup>";
				}
				Array.from(document.getElementsByClassName("tableholderdiv")).forEach(element => {
					console.log(element,element.childNodes[0].localName);
					let currentNode = element.firstChild;
					while (currentNode) {
						console.log(currentNode.nodeName);
						if (currentNode.nodeName === 'BR') {
							console.log("Removed", currentNode);
							currentNode.remove();
						} else if (currentNode.nodeName === 'TABLE') {
							
							break;
						}
						currentNode = currentNode.nextSibling;
					}



					addRowButton = document.createElement("button");
					addRowButton.setAttribute("onclick","addRow('"+element.getAttribute("id")+"',false)");
					addRowButton.setAttribute("class","btn submitRemove");
					addRowButton.innerHTML = "Add Row";

					addSectionButton = document.createElement("button");
					addSectionButton.setAttribute("onclick","addSection('"+element.getAttribute("id")+"')");
					addSectionButton.setAttribute("class","btn submitRemove");
					addSectionButton.innerHTML = "Add Section";

					removeTableButton = document.createElement("button");
					removeTableButton.setAttribute("onclick","removeTable('"+element.getAttribute("id")+"')");
					removeTableButton.setAttribute("class","btn submitRemove");
					removeTableButton.innerHTML = "Remove Table";

					element.appendChild(addRowButton);
					element.appendChild(addSectionButton);
					element.appendChild(removeTableButton);
					Array.from(element.childNodes).forEach(table => {
						if(table.localName == "table")
						{
							var rowLength = table.rows.length;
							for (row = 0; row < rowLength; row++) {
								table.rows.item(row).setAttribute("row",row);
								var cells = table.rows.item(row).cells;
								//console.log(table.rows.item(row));
								if (table.rows.item(row).hasAttribute('section')) {
								} else {
									console.log(cells.length);
									for (i = 0; i < cells.length; i++) {
										cells.item(i).setAttribute("col",i)
									}
								}
							}
						}
					});
				});
				setTableStyle();
				//checkQuickBarInput();
			}
		}
		else if(xhttp.readyState == 4 && xhttp.status == 404)
		{
			messageCreate("Can't find question", "ERROR");
			window.top.document.title = "DIGITAL WORKBOOK";
		}
	}	
	var xhttp2 = new XMLHttpRequest();
	xhttp2.open("GET", "api.php/me" , true);				
	xhttp2.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xhttp2.send("");
	xhttp2.onreadystatechange = function() 
	{ 
		if (xhttp2.readyState == 4 && xhttp2.status == 200)
		{
			if(xhttp2.responseText.length > 0 )
			{	
				org = JSON.parse(xhttp2.responseText)["org"];
			}
		}
	}


} 

/*Remove a var from the text
Inputs 
	- var id
Outputs
	- none
side effects 
	- remove the reference of the var from the text and replaces it with the base value
*/
function removeAssigment(id)
{
	Array.from(document.getElementsByClassName(btoa(id))).forEach(element => {
		element.outerHTML = getJSON(id)["baseText"];
	});
}

function createList()
{
	var chartWindow = document.createElement("div");
	chartWindow.innerHTML = `<div class="window-top">
		</div>
		<div>
			<input type="file" style="width:30%"> Upload csv file of the list/dictonary </input>
			<button type="button" class="btn" onclick=closeWindow()> Close </button>
		</div>`;
	chartWindow.setAttribute("id","chartWindow");
	chartWindow.setAttribute("class","window");
	chartWindow.setAttribute("style","width:30%; height:30%; left:20%; top: 10%");
	document.body.appendChild(chartWindow);
}




/*Gets all the topics
Inputs 
	- none
Outputs
	- none
side effects 
	- writes all the topics to a dropdown
*/
function getAllTopics()
{
	var xhttp = new XMLHttpRequest();
	xhttp.open("GET", "api.php/getAllItems/", true);				
	xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xhttp.send("");
	xhttp.onreadystatechange = function() 
	{ 
		if (xhttp.readyState == 4 && xhttp.status == 200)
		{
			if(xhttp.responseText.length > 0 )
			{		
				var jsonResponse = JSON.parse(xhttp.responseText);
				var items = jsonResponse["itemsAll"];
				var holder = document.getElementById("topicOptions");
				for (var i = 0; i < items.length; i++)
				{
					var liInstance = document.createElement("li");
					var buttonInstance = document.createElement("button");
					buttonInstance.setAttribute("onclick","setQuestionTopic(" + items[i]["topicID"] + ")");
					buttonInstance.setAttribute("class","btn btn-secondary btn-light");
					console.log("GET ALL TOPIC", "ORG", items[i]["org"]);
					buttonInstance.innerHTML = getOrg(items[i]["org"])["name"] + "\\" + items[i]["subjectName"] + "\\" + items[i]["topicName"];
					liInstance.appendChild(buttonInstance);
					holder.appendChild(liInstance);
				}
			}
		}
	}
}

/*Gets the details of an org with a given id
Inputs 
	- org id
Outputs
	- org element
side effects 
	- none
*/
function getOrg(id)
{
	var foundElement = null;
	org.forEach(element => {
		console.log(element["id"],id, element["id"] == id);
		if(element["id"] == id)
		{
			//Doesn't want to work normally odd
			found = true;
			foundElement = element;
			return element;
		}
	});
	return foundElement;	
}

/*Sets the topic number for the question
Inputs 
	- topic id
Outputs
	- none
side effects 
	- sets the new topic id in the backend
*/
function setQuestionTopic(id)
{
	var xhttp = new XMLHttpRequest();
	xhttp.open("GET", "api.php/setQuestionTopic/?newT=" + id + "&questionID=" + questionID, true);				
	xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xhttp.send("");
}

/*Removes a value from the translation table
Inputs 
	- value
Outputs
	- none
side effects 
	- removes an item from translation table
*/
function removeFromDict(value)
{
	for (var key in translationTable) 
	{
		console.log(key);
		if(translationTable[key][TT_VARIABLENAME] == value)
		{
			delete translationTable[key];
			break;
		}
	}
}

/*Get all the user variables name from the translation table
Inputs 
	- none
Outputs
	- all the names of the vars
side effects 
	- none
*/
function getAllVars()
{
	var names = [];
	for (var key in translationTable) 
	{
		names.push(translationTable[key][TT_VARIABLENAME]);
	}
	return names;
}

/*Makes the current loaded question public
Inputs 
	- none
Outputs
	- none
side effects 
	- Makes the current loaded question public
*/
function makeQuestionPublic()
{
	var xhttp = new XMLHttpRequest();
	xhttp.open("GET", "api.php/makeQuestionPublic/?questionID=" + questionID, true);				
	xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xhttp.send("");
	document.getElementById("publishButton").setAttribute("class","btn btn-outline-success");
	document.getElementById("publishButton").setAttribute("onclick","makeQuestionPrivate()");
	document.getElementById("publishButton").innerHTML = "PUBLIC";
}

/*Makes the current loaded question private
Inputs 
	- none
Outputs
	- none
side effects 
	- Makes the question private
*/
function makeQuestionPrivate()
{
	var xhttp = new XMLHttpRequest();
	xhttp.open("GET", "api.php/makeQuestionPrivate/?questionID=" + questionID, true);				
	xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xhttp.send("");
	document.getElementById("publishButton").setAttribute("class","btn btn-outline-danger");
	document.getElementById("publishButton").setAttribute("onclick","makeQuestionPublic()");
	document.getElementById("publishButton").innerHTML = "PRIVATE";
}

/*Add an item to a specific type in translation table
Inputs 
	- the current type that an item should be added to
	- items to add to the translation table
Outputs
	- none
side effects 
	- add items to a specific type in translation table
*/
function addItemsToType(type, items)
{
	types[type] = '{"items" : ' + items + "}";
	for(var key in translationTable)
	{				
		var jsonObject = getJSON(key);
		if(jsonObject["type"] == type)
		{					
			jsonObject["items"] = items;
			translationTable[key][TT_JSONVALUE] = "%var" + JSON.stringify(jsonObject);
		}
	}
}

/*Add an item to a specific id in translation table
Inputs 
	- key 
	- items to add to the translation table
Outputs
	- none
side effects 
	- add items to a specific id in translation table
*/
function addItemsToJSON(key, items)
{
	var jsonObject = getJSON(key);
	jsonObject["items"] = items;
	translationTable[key][TT_JSONVALUE] = "%var" + JSON.stringify(jsonObject);
}

/*Add an item to a specific id in translation table
Inputs 
	- id of the var
	- value to change
	- data to add
Outputs
	- none
side effects 
	- add items to a specific id in translation table
*/
function addToJSON(key, val, newData)
{
	var jsonObject = getJSON(key);
	jsonObject[val] = newData;
	translationTable[key][TT_JSONVALUE] = "%var" + JSON.stringify(jsonObject);
}

/*Change an item to a specific id in translation table
Inputs 
	- id of the var
	- value to change
	- data to add
Outputs
	- none
side effects 
	- changes the value of an item at a specific id in translation table
*/
function changeJSONValue(key, val, newData)
{
	var jsonObject = getJSON(key);
	jsonObject[val] = newData;
	translationTable[key][TT_JSONVALUE] = "%var" + JSON.stringify(jsonObject);
}

/*Closes the menu
Inputs 
	- none
Outputs
	- none
side effects 
	- closes the menu
*/
function closeMenu()
{
	document.getElementById('contextMenu').outerHTML = '';
}

const stripHTMLTags = str => str.replace(/<\!--.*?-->/g, "");//str.replace(/<[^>]*>/g, ''); //Strips HTML tags

/*Closes the menu
Inputs 
	- event handler
Outputs
	- none
side effects 
	- removes all the xml and other special tag from a paste 
*/
var disablePasteFix = false;
function handlePaste(e) {
		var clipboardData, pastedData;
  
	// Stop data actually being pasted into div
  
	// Get pasted data via clipboard API
	clipboardData = e.clipboardData || window.clipboardData;
	clipboardText = clipboardData.getData('Text');
	var parser = new DOMParser(); 
	clipboardText.split("\n").forEach(element => {
		
	var myXMLstring = parser.parseFromString(element, "application/xml");
	console.log(myXMLstring);
	});
	if(disablePasteFix == false)
	{
		hasTable = clipboardText.includes("<table");
		console.log("HAS TABLE",hasTable);
		if(hasTable == true)
		{
			clipboardText.replaceAll("<table","#table");
		}
		console.log(clipboardText);
		pastedData = clipboardText//stripHTMLTags(clipboardText);
		console.log(pastedData);
		if (window.getSelection) {
			var newNode = document.createElement("span");
			newNode.innerHTML = pastedData;
			newNode.setAttribute("id","pasteID");
			window.getSelection().getRangeAt(0).insertNode(newNode);
		} else {
			document.selection.createRange().pasteHTML(pastedData);
		}
		e.stopPropagation();
		e.preventDefault();
		document.getElementById("pasteID").outerHTML = document.getElementById("pasteID").innerHTML;
	}
}

function getNearestSection(tableId, rowIndex)
{
	var table = document.getElementById(tableId);
	var rowLength = table.rows.length;
	var currentSection = null;
	for (row = 0; row < rowLength; row++) {
		if (table.rows.item(row).hasAttribute('section')) {
			currentSection = table.rows.item(row);
		} 
		console.log(row);
		if(row == rowIndex)
		{
			return currentSection;
		}
	}
	return currentSection;
}

function getNextSection(tableId, rowIndex)
{
	var table = document.getElementById(tableId);
	var rowLength = table.rows.length;
	var currentSection = null;
	var currentSectionId = 0;
	for (row = 0; row < rowLength; row++) {
		if (table.rows.item(row).hasAttribute('section')) {
			currentSection = table.rows.item(row);
			if(row > rowIndex)
			{
				return [currentSectionId, currentSection,row];
			}
			currentSectionId = row;
		} 
	}
	return [currentSectionId, currentSection,-1];
}


function setColType(tableId, pos)
{
	var name = document.getElementById("varItemColNames").value;
	var type = document.getElementById("varItemColTypes").value;
	console.log(name, type);
	console.log(tableId,pos);
	var nearestSection = getNearestSection(tableId, pos[0]);
	console.log("nearestSection",nearestSection);
	if(nearestSection != null)
	{
		var sectionInfo = nearestSection.getAttribute("sectionInfo");
		if(sectionInfo == null)
		{
			sectionInfo = {};
		}
		else
		{
			sectionInfo = JSON.parse(sectionInfo);
		}
		console.log(sectionInfo,pos[1]);
		var id = nameToID(translationTable,name);
		console.log("VAR NAME",id);
		console.log(getJSON(id));
		sectionInfo[pos[1]] = type;
		sectionInfo[pos[1]] = type + ":" + id;
		/*
		if(id != -1 && type == "dropdown")
		{			
			sectionInfo[pos[1]] = type + ":" + id;
			
			console.log(sectionInfo);
		}*/
		console.log(sectionInfo);
		nearestSection.setAttribute("sectionInfo", JSON.stringify(sectionInfo));
	}
}

function showColItems(tableId, pos)
{
	console.log(tableId,pos);
	var nearestSection = getNearestSection(tableId, pos[0]);
	var nextSection = getNextSection(tableId, pos[0]);
	console.log(nextSection);
	console.log("nearestSection",nearestSection);
	if(nearestSection != null)
	{
		var sectionInfo = nearestSection.getAttribute("sectionInfo");
		if(sectionInfo == null)
		{
			sectionInfo = {};
		}
		else
		{
			sectionInfo = JSON.parse(sectionInfo);
		}
		var table = document.getElementById(tableId);
		var rowLength = table.rows.length;
		if(nextSection[2] != -1)
		{
			rowLength = nextSection[2];
		}
		sectionInfo[pos[1]] = [];
		for (row = nextSection[0]; row < rowLength; row++) {
			if (table.rows.item(row).hasAttribute('section')) {
			}
			else
			{
				var cells = table.rows.item(row).cells;
				for (i = 0; i < cells.length; i++) {
					if(i == pos[1])
					{
						sectionInfo[pos[1]].push(cells.item(i).innerText);
					}
				}
			}
		}
		sectionInfo[pos[1]] = "set:" + JSON.stringify(sectionInfo[pos[1]]);
		console.log(sectionInfo,pos[1]);
		
		console.log(sectionInfo);
		nearestSection.setAttribute("sectionInfo", JSON.stringify(sectionInfo));
	}
}



function setItemCol()
{
	var optionsText = "";
	var options = ["number","text","dropdown"];
	options.forEach(element => {
		optionsText += "<option>" + element + "</option>";
	});
	return optionsText;
}

/*Intercept the right click handler and use it to display menu's
Inputs 
	- event handler
Outputs
	- none
side effects 
	- shows the right click menu that allows one to chose options depending on the text selected
*/
oncontextmenu = (e) => {
	setTClean();
	setAnswer();
	e.preventDefault();
	var selectedTextArray = getSelectionText(); //[returns the text, returns the text html object]
	selectedText = selectedTextArray[0];
	var htmlItem = selectedTextArray[1];
	console.log(htmlItem);
	var target = "itemHolder";
	target = "itemHolder";
	if(htmlItem != null && htmlItem != "")
	{
		try
		{
			var HTMLFocusNode = htmlItem.focusNode.parentElement;
			if(HTMLFocusNode != null)
			{
				while(HTMLFocusNode != null || HTMLFocusNode.id != null || HTMLFocusNode.id != "")
				{
					if(HTMLFocusNode.id == "split right")
					{
						console.log("SPLITING");
						target = "answer";
						break;
					}
					HTMLFocusNode = HTMLFocusNode.parentElement;
				}
			}
		}
		catch(e)
		{
			//console.log(e);
		}
	}
	console.log(selectedText.includes("<table"));
	console.log("SELECTED ", selectedText);
	var object = getSelectionObject();
	//New fix
	//selectedText = selectedText.replace("nbsp;","");
	//Diffrent use case for a given text
	if(object !== null && object.offsetParent.localName == "table")
	{
		selectedText = selectedText.trim().replaceAll("<table>","").replaceAll("<tbody>","");
		console.log(selectedText);
		console.log("SELECTED OBJECT", object);
		var rowIndex = object.rowIndex;
		if(object.rowIndex == null)
		{
			rowIndex = object.parentElement.rowIndex;
		}
		console.log("rowIndex", rowIndex);
		var tableId = object.offsetParent.id;
		console.log("tableID" , tableId);
		var menu = document.createElement("div");
		menu.id = "contextMenu";
		menu.style = `top:${e.pageY-10}px;left:${e.pageX-40}px`;
		var y = e.pageY-10;
		var x = e.pageX-40;

		var xItem = `<p onclick=closeMenu()> Close </p><ul class="top-level-menu">`;
		console.log(btoa(selectedText));
		//var iii = selectedText.split("id")[1].split(">")[0].replaceAll('"',"").replace("=","");
		xItem += `<li><p onclick="autoVars('`+tableId+`','`+"a"+`')"> Auto Generate Names </p><ul class="third-level-menu"><li><p> TYPE </p>`;
		for (var key in types) 
		{
			xItem += `<li> <a href=javascript:autoVars('`+tableId+`','`+key+`')>` + key + '</a></li>';
		}
		xItem += `</li></ul></li><li><p> Add Row</p><ul class="third-level-menu"><li><select class="tableRow" id="addRowAmount"></select></li><li><button onclick=addRow('`+tableId+`','false','`+rowIndex+`')> Add Row Above</button><button onclick=addRow('`+tableId+`','false','`+(rowIndex + 1)+`')> Add Row Below</button></li>`;
		for (var i = 0; i < 8; i ++)
		{
			xItem += `<li></li>`;
		}
		if(object.getAttribute("col") != null)
		{
			xItem += `</li></ul></li><li><p> Set Column Items</p><ul class="third-level-menu"><li><select class="varItemColTypes" id="varItemColTypes"></select></li><li><select class="varItems" id="varItemColNames"></select></li><li><button onclick=setColType('`+tableId+`','[`+object.getAttribute("col")+`,`+rowIndex+`]')> Set Colum Type</button></li>`;
			for (var i = 0; i < 8; i ++)
			{
				xItem += `<li></li>`;
			}

			xItem += `</li></ul></li><li><p> Show column text</p><ul class="third-level-menu"><li><button onclick=showColItems('`+tableId+`','[`+object.getAttribute("col")+`,`+rowIndex+`]')> Show column text</button></li>`;
			for (var i = 0; i < 8; i ++)
			{
				xItem += `<li></li>`;
			}
		}
		
		console.log(document.getElementById(tableId).childNodes[0].childNodes.length);
		xItem += `</li></ul></li><li><p> Remove Row</p><ul class="third-level-menu"><li><select class="tableRow" id="removeRowAmount"></select></li><button onclick=removeRow('`+tableId+`')> Remove Row </button>`;

		for (var i = 0; i < 8; i ++)
		{
			xItem += `<li></li>`;
		}
	

		xItem += `</ul><li><p> Link Table Document</p><ul class="third-level-menu">` ;
		docs = getAllDocuments();
		for (var i = 0; i < docs.length; i ++)
		{
			xItem += `<li><a href=javascript:linkTableToDocument('`+tableId+`','`+docs[i]["currentID"]+`')>`+docs[i]["varName"]+` </li>`;
		}
		if(docs.length < 8)
		{
			for (var i = 0; i < 8 - docs.length; i ++)
			{
				xItem += `<li></li>`;
			}
		}
		xItem +=`</ul><li>Remove Table<ul class="third-level-menu">`;
		xItem += `<li><a href=javascript:removeTable('`+tableId+`')> Remove table </a>`;
		for (var i = 0; i < 6 ; i ++)
		{
			xItem += `<li></li>`;
		}		

		xItem += `<ul></ul><ul></ul><ul></ul><ul></ul></li><li></li>`;
		console.log(xItem);
		menu.innerHTML += xItem;
		document.getElementById(target).appendChild(menu);
		document.getElementById("varItemColTypes").innerHTML = setItemCol();
		document.getElementById("varItemColNames").innerHTML = getVars();

		Array.from(document.getElementsByClassName("tableRow")).forEach(element => {
			element.innerHTML = getTableRows(tableId);
		});		
		
	}
	else if(selectedText.includes("<table"))
	{
		selectedText = selectedText.trim().replaceAll("<table>","").replaceAll("<tbody>","");
		console.log(selectedText);
		
		var tableId = selectedText.split("id")[1].split(" ")[0].replaceAll('"',"").replaceAll("=","").split(">")[0];
		console.log("tableID" , tableId);
		var menu = document.createElement("div");
		menu.id = "contextMenu";
		menu.style = `top:${e.pageY-10}px;left:${e.pageX-40}px`;
		var y = e.pageY-10;
		var x = e.pageX-40;

		var xItem = `<p onclick=closeMenu()> Close </p><ul class="top-level-menu">`;
		console.log(btoa(selectedText));
		console.log("ID" + selectedText.split("id")[1].split(">")[0].replaceAll('"',"").replace("=",""));
		var iii = selectedText.split("id")[1].split(">")[0].replaceAll('"',"").replace("=","");
		xItem += `<li><p onclick="autoVars('`+tableId+`','`+"a"+`')"> Auto Generate Names </p><ul class="third-level-menu"><li><p> TYPE </p>`;
		for (var key in types) 
		{
			xItem += `<li> <a href=javascript:autoVars('`+tableId+`','`+key+`')>` + key + '</a></li>';
		}
		xItem += `</li></ul></li><li><p> Add Row</p><ul class="third-level-menu"><li><select class="tableRow" id="addRowAmount"></select></li><li><button onclick=addRow('`+tableId+`')> Add Row </button>`;
		for (var i = 0; i < 8; i ++)
		{
			xItem += `<li></li>`;
		}

		console.log(document.getElementById(tableId).childNodes[0].childNodes.length);
		xItem += `</li></ul></li><li><p> Remove Row</p><ul class="third-level-menu"><li><select class="tableRow" id="removeRowAmount"></select></li><button onclick=removeRow('`+tableId+`')> Add Row </button>`;

		for (var i = 0; i < 8; i ++)
		{
			xItem += `<li></li>`;
		}
	

		xItem += `</ul><li><p> Link Table Document</p><ul class="third-level-menu">` ;
		docs = getAllDocuments();
		for (var i = 0; i < docs.length; i ++)
		{
			xItem += `<li><a href=javascript:linkTableToDocument('`+tableId+`','`+docs[i]["currentID"]+`')>`+docs[i]["varName"]+` </li>`;
		}
		if(docs.length < 8)
		{
			for (var i = 0; i < 8 - docs.length; i ++)
			{
				xItem += `<li></li>`;
			}
		}
		xItem +=`</ul><li>Remove Table<ul class="third-level-menu">`;
		xItem += `<li><a href=javascript:removeTable('`+tableId+`')> Remove table </a>`;
		for (var i = 0; i < 6 ; i ++)
		{
			xItem += `<li></li>`;
		}		

		xItem += `<ul></ul><ul></ul><ul></ul><ul></ul></li><li></li>`;
		console.log(xItem);
		menu.innerHTML += xItem;
		document.getElementById(target).appendChild(menu);
		Array.from(document.getElementsByClassName("tableRow")).forEach(element => {
			element.innerHTML = getTableRows(tableId);
		});		
	}
	else if(selectedText == "$table")
	{
		selectedText = selectedText.trim();
		var menu = document.createElement("div");
		menu.id = "contextMenu";
		menu.style = `top:${e.pageY-10}px;left:${e.pageX-40}px`;
		var y = e.pageY-10;
		var x = e.pageX-40;
		menu.onmouseleave = () =>  menu.outerHTML = '';
		var xItem = `<ul class="top-level-menu">`;
		xItem +=  `<li><p> Add Table</p>
		<ul class="third-level-menu"><li><input id='rows' type='number' placeholder='rows'> min </input></li><li><input id='columns' type='number' placeholder='columns'> min </input></li><li> <p onclick=addTable()> Create table </p></li><li></li><li></li></ul></ul>`;
		menu.innerHTML += xItem;
		document.getElementById(target).appendChild(menu);
	}
	else if(selectedText != "") //This section checks for if the string is contained in the translationTable and then displays unique menu items has been disabled
	{
		selectedText = selectedText.trim();
		console.log(selectedText.includes('<span id="customVar"'));
		if(selectedText.includes('<span id="customVar"'))
		{
			selectedText = selectedText.split("<u>")[1];
			selectedText = selectedText.replace("</u></span>","");
			console.log(selectedText);
		}
		selectedText = selectedText.trim();
		var tempselectedText = selectedText;
		if(!tempselectedText.startsWith("$"))
		{
			tempselectedText = "$" + tempselectedText;
		}
		console.log("SELECTED TEXT", tempselectedText);
		var textFound = false;
		var keyFound = "";
		for (var key in translationTable) 
		{
			console.log(tempselectedText,translationTable[key][TT_VARIABLENAME]); 
			if(translationTable[key][TT_VARIABLENAME] == tempselectedText)
			{
				console.log(key);
				textFound = true;
				keyFound = key;
				break;
			}
		}
		selectedText = selectedText.replace("nbsp;","");
		console.log("FOUND VARIABLE IN TRANSLATION TABLE",selectedText,textFound, keyFound);
		var menu = document.createElement("div");
		menu.id = "contextMenu";
		menu.style = `top:${e.pageY-10}px;left:${e.pageX-40}px`;
		var y = e.pageY-10;
		var x = e.pageX-40;
		menu.onmouseleave = () =>  menu.outerHTML = '';
		//target = 'main';
		var xItem = `<ul class="top-level-menu">`;
		if(textFound)
		{
			console.log("SELECTED TEXT TYPE",translationTable[keyFound][2]);
			if(translationTable[keyFound][2] == "number")
			{
				document.getElementById("varName").value = translationTable[keyFound][0];
				document.getElementById("quickBar").value = getJSON(keyFound)["baseText"];
				//menu.innerHTML += `<p onmouseover="" onclick=setValues('number')> Set min and max values</p>`
				//
				xItem += `
				<li>
					<p> Link to number</p>
				<ul class="third-level-menu">
					`;
					var num = getNumbers(keyFound);
					for (var i = 0; i < num.length; i ++)
					{
						xItem += "<li> <a href=javascript:linkWithId(" + num[i] + "," + keyFound + ")>" + translationTable[num[i]][0] + "</a></li>";
					}
					if(num.length < 8)
					{
						for (var i = 0; i < 8 - num.length; i ++)
						{
							xItem += "<li></li>";
						}
					}
					xItem +=  `
					</ul>
				</li><li><p> Set min and max values</p>
				<ul class="third-level-menu"><li><input id='tempMin' type='number'> min </input></li><li><input id='tempMax' type='number'> min </input></li><li> <button onclick=setNumbers(` + keyFound + `)> Set Values </button></li><li></li><li></li></ul>`;
			}
			else if(translationTable[keyFound][2] == "sum")
			{
				xItem += `
				<li>
					<p> Link to number</p>
				<ul class="third-level-menu">
					`;
					var num = getNumbers(keyFound);
					for (var i = 0; i < num.length; i ++)
					{
						xItem += "<li> <a href=javascript:addSumItem(" + num[i] + "," + keyFound + ")>" + translationTable[num[i]][0] + "</a></li>";
					}
					if(num.length < 6)
					{
						for (var i = 0; i < 6 - num.length; i ++)
						{
							xItem += "<li></li>";
						}
					}
					xItem += `</ul></li><li><p> Unlink to number</p><ul class="third-level-menu">`;
					var num = getSummedItems(keyFound);
					console.log(num);
					for (var i = 0; i < num.length; i ++)
					{
						console.log(num);
						console.log(num[i]);
						xItem += "<li> <a href=javascript:removeSumItem(" + num[i] + "," + keyFound + ")>" + translationTable[num[i]][0] + "</a></li>";
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
			else if(translationTable[keyFound][2] == "operator")
			{
				xItem += `
				<li><ul> Set Operator </ul></li><li><ul onclick=setRandomOperator("` + keyF +`")> Randomize Operator </ul></li><li><ul onclick=unRandomOperator("` + keyF +`") > Unrandomize Operator </ul></li>`;
			}
			else if(translationTable[keyFound][2] == "answer")
			{
				xItem += `
				<li>
					
					<p> Link to number</p>
				<ul class="third-level-menu">
					`;
				var num = getNumbers(keyFound);
				for (var i = 0; i < num.length; i ++)
				{
					xItem += "<li> <a href=javascript:addSumItem(" + num[i] + "," + keyFound + ")>" + translationTable[num[i]][0] + "</a></li>";
				}
				if(num.length < 6)
				{
					for (var i = 0; i < 6 - num.length; i ++)
					{
						xItem += "<li></li>";
					}
				}
				xItem += `</ul></li><li><p> Unlink to number</p><ul class="third-level-menu">`;
				var num = getSummedItems(keyFound);
				console.log(num);
				for (var i = 0; i < num.length; i ++)
				{
					console.log(num);
					console.log(num[i]);
					xItem += "<li> <a href=javascript:removeSumItem(" + num[i] + "," + keyFound + ")>" + translationTable[num[i]] + "</a></li>";
				}
				if(num.length < 6)
				{
					for (var i = 0; i < 6 - num.length; i ++)
					{
						xItem += "<li></li>";
					}
				}
				xItem +=  `</ul></li>`;
				var placeholder = getJSON(keyFound);//JSON.parse(translationTable[keyFound][1].substring(2,translationTable[keyFound][1].length));
				var placeholderValue = "";
				if(placeholder.hasOwnProperty("placeholder"))
				{
					placeholderValue = placeholder["placeholder"];
				}
				xItem += `<li><p>Set Hint Text</p><ul class="third-level-menu"><li><input id="placeholder" placeholder="`+placeholderValue+`" value="`+placeholderValue+`"><button onclick=setAnswerPlaceholderText(`+keyFound + `)> Set Name </button></li>`;
				for (var i = 0; i < 8 - num.length; i ++)
					{
						xItem += "<li></li>";
					}
					
				xItem +=  `</ul></li>`;
				var equation = getJSON(keyFound);//JSON.parse(translationTable[keyFound][1].substring(2,translationTable[keyFound][1].length));
				var equationValue = "";
				if(equation.hasOwnProperty("equation"))
				{
					console.log(equation["equation"]);
					equationValue = atob(equation["equation"]);
					equationValue = translateEquation(equationValue);
				}
				document.getElementById("varName").value = translationTable[keyFound][0];
				document.getElementById("quickBar").value = equationValue;
				xItem += `<li><p>Set Equation</p><ul class="third-level-menu"><li><input id="equation" placeholder="`+equationValue+`" value="`+equationValue+`"><button onclick=setAnswerEquation(`+keyFound + `)> Set Equation </button></li>`;
				for (var i = 0; i < 8 - num.length; i ++)
					{
						xItem += "<li></li>";
					}
					
				xItem +=  `</ul></li>`;
			}			
			else if(translationTable[keyFound][2] == "equation")
			{
				xItem += `
				<li>
					<p> Link to number</p>
				<ul class="third-level-menu">
					`;
				var num = getNumbers(keyFound);
				for (var i = 0; i < num.length; i ++)
				{
					xItem += "<li> <a href=javascript:addSumItem(" + num[i] + "," + keyFound + ")>" + translationTable[num[i]][0] + "</a></li>";
				}
				if(num.length < 6)
				{
					for (var i = 0; i < 6 - num.length; i ++)
					{
						xItem += "<li></li>";
					}
				}
				xItem += `</ul></li><li><p> Unlink to number</p><ul class="third-level-menu">`;
				var num = getSummedItems(keyFound);
				console.log(num);
				for (var i = 0; i < num.length; i ++)
				{
					console.log(num);
					console.log(num[i]);
					xItem += "<li> <a href=javascript:removeSumItem(" + num[i] + "," + keyFound + ")>" + translationTable[num[i]][0] + "</a></li>";
				}
				if(num.length < 6)
				{
					for (var i = 0; i < 6 - num.length; i ++)
					{
						xItem += "<li></li>";
					}
				}
				xItem +=  `</ul></li>`;
				var placeholder = getJSON(keyFound);
				var placeholderValue = "";

				console.log(keyFound);

				if(placeholder.hasOwnProperty("placeholder"))
				{
					placeholderValue = placeholder["placeholder"];
				}
				xItem += `<li><p>Set Hint Text</p><ul class="third-level-menu"><li><input id="placeholder" placeholder="`+placeholderValue+`" value="`+placeholderValue+`"><button onclick=setAnswerPlaceholderText(`+keyFound + `)> Set Name </button></li>`;
				for (var i = 0; i < 8 - num.length; i ++)
					{
						xItem += "<li></li>";
					}
					
				xItem +=  `</ul></li>`;
				var equation = getJSON(keyFound);
				var equationValue = "";
				if(equation.hasOwnProperty("equation"))
				{
					equationValue = atob(equation["equation"]);
					equationValue = translateEquation(equationValue);
				}
				document.getElementById("varName").value = translationTable[keyFound][0];
				document.getElementById("quickBar").value = equationValue;
				xItem += `<li><p>Set Equation</p><ul class="third-level-menu"><li><input id="equation" placeholder="`+equationValue+`" value="`+equationValue+`"><button onclick=setAnswerEquation(`+keyFound + `)> Set Equation </button></li>`;
				for (var i = 0; i < 8 - num.length; i ++)
					{
						xItem += "<li></li>";
					}
					
				xItem +=  `</ul></li>`;				
				//,'top:" + y + "px;left:" + x + "px')
				//document.getElementById('contextMenu').outerHTML = '';
				//menu.innerHTML += "";
			}
			else
			{				
				console.log("OTHER TYPE FOUND SETTING QUICK BAR BASE TEXT");
				document.getElementById("varName").value = translationTable[keyFound][0];
				document.getElementById("quickBar").value = getJSON(keyFound)["baseText"];
			}
			var a = false;
			if("r" in getJSON(keyFound))
			{
				a = getJSON(keyFound)["r"];
			}
			showQuickBarVarItem(keyFound);
			xItem += `<li><p onclick=setRandomOperator('`+keyFound+`');> Toggle Random Operator (`+ a +`)</p>
			<li>
				<p>Set Base Value</p>
				<ul class="third-level-menu">
					<li><input id='baseText' type='text'>  </input></li>
					<li><a onclick=setBaseText('`+keyFound+`');> Submit </a></li>
					`;for (var i = 0; i < 6; i ++)
						{
							xItem += "<li></li>";
						}
					`
				</ul>
				</li>`;
				if("r" in getJSON(keyFound) && getJSON(keyFound)["r"] == true)
				{
					//</ul></li><li><p> Chose Random Option</p><ul class="third-level-menu"><li><a onclick=changeJSONValue('`+keyFound + `',"rType","range")> Range </a></li><li><a onclick=changeJSONValue(`+keyFound + `,"rType","list")> List </p></li>`;
					
					//
					xItem += `</ul></li><li><p> Chose Random Option</p><ul class="third-level-menu"><li><a onclick=changeJSONValue('`+keyFound + `',"rType","range")> Range </a></li><li><a onclick=changeJSONValue(`+keyFound + `,"rType","list")> List </p></li>`;
					for (var i = 0; i < 8; i ++)
					{
						xItem += "<li></li>";
					}
					xItem += `</ul></li>`;

					/*xItem += `<li><p> Remove Value</p><ul class="third-level-menu">`;
					if("values" in getJSON(keyFound))
					{
						//xItem += `<li><select id="typeView">`+getValues(keyFound)+`</select></li>`;
						for (var i = 0; i < getJSON(keyFound)["values"].length; i ++)
						{
							xItem += `<li><a onclick=removeValue('`+keyFound + `','`+i+`')> Remove `+getJSON(keyFound)["values"][i]+`</a></li>`;
						}

						for (var i = 0; i < 8 - getJSON(keyFound)["values"].length; i ++)
						{
							xItem += `<li></li>`;
						}
					}
					xItem += `</li></ul>`;
					*/
					xItem += `<li><p>Remove Value</p><ul class="third-level-menu">`;
					if("values" in getJSON(keyFound))
					{
						//xItem += `<li><select id="typeView">`+getValues(keyFound)+`</select></li>`;
						for (var i = 0; i < getJSON(keyFound)["values"].length; i ++)
						{
							xItem += `<li><a onclick=removeValue('`+keyFound + `','`+i+`')> Remove `+getJSON(keyFound)["values"][i]+`</a></li>`;
						}

						for (var i = 0; i < 8 - getJSON(keyFound)["values"].length; i ++)
						{
							xItem += `<li></li>`;
						}
					}
					
					xItem +=  `</ul></li>`;
					xItem += `<li><p>Add Value</p><ul class="third-level-menu"><li><input id="valueAdder"> </input><a onclick=addValue('`+keyFound + `')> Add Item </a></li>`;
					
					for (var i = 0; i < 8; i ++)
					{
						xItem += `<li></li>`;
					}
					
					xItem +=  `</ul></li>`;		
				}

				//<li><a onclick=changeJSONValue('`+keyFound + `',"rType","range")> Range </a></li><li><a onclick=changeJSONValue(`+keyFound + `,"rType","list")> List </p></li>
				xItem += `</ul></li><li><p> Change Type</p><ul class="third-level-menu">`;
				for(key in types)
				{
					xItem += `<li onclick="changeType('`+keyFound + `','type','`+key+`')">` + key + `</li>`;
				}
				for (var i = 0; i < 12 - Object.keys(types).length; i++)
				{
					xItem += `<li></li>`;
				}
				xItem += `</ul></li>`;
				xItem += `<li><p onclick=removeAssigment('`+keyFound+`');>Remove Assigment</p></li>`;
		}
		else
		{
			console.log("NOT FOUND");
			searchVariable(selectedText);
			xItem += "<p onclick=searchVariable('"+selectedText+"')>Add New Variable</p>";
		}
		xItem += '</ul>';
		console.log(xItem);
		console.log(target);
		menu.innerHTML += xItem;
	}
	else
	{
		console.log(selectedText);
		var menu = document.createElement("div");
		menu.id = "contextMenu";
		menu.style = `top:${e.pageY}px;left:${e.pageX-40}px`;
		var y = e.pageY;
		var x = e.pageX;
		menu.onmouseleave = () =>  menu.outerHTML = '';
		/*
		menu.onmouseleave = () =>  menu.outerHTML = '';
		var xItem = `<ul class="top-level-menu">`;
		menu.innerHTML += `<p onmouseover="" onclick=setValues('number')> Set min and max values</p>		


		<p> Add Var</p><ul class="third-level-menu">`
			
			var vars = getAllVars(keyFound);
			console.log(vars);
			for (var key in translationTable) 
			{
				xItem += "<li> <a href=javascript:addWithIdName(" + translationTable[key][TT_VARIABLENAME]+ ")>" + translationTable[key][TT_VARIABLENAME] + "</a></li>";
			}
			if(vars.length < 6)
			{
				for (var i = 0; i < 6 - vars.length; i ++)
				{
					xItem += "<li></li>";
				}
			}
					
			
			menu.innerHTML +=  `</li>`


		*/
		xItem = `<li><p onclick="findVars()"> Fix Vars </p></li></ul>`;
		console.log(xItem);
		menu.innerHTML += xItem;
		document.getElementById(target).appendChild(menu);
	}
}

function linkTableToDocument(tableId, varID)
{
	changeJSONValue(varID, 'linkID', tableId);
}

/*Inserts a visual var into the text at the text selector
Inputs 
	- var id
Outputs
	- none
side effects 
	- Inserts a visual var into the text at the text selector
*/
function insertVar(id)
{
	insertTextAtCaret("%" + translationTable[id][0] + "%");
	findVars();
}

/*Inserts a table into the text at the text selector
Inputs 
	- none
Outputs
	- none
side effects 
	- Inserts a table into the text at the text selector with the given col and row length from the input fields
*/
function insertTable()
{	
	var colLength = document.getElementById("colTableInsert").value;
	var rowLength = document.getElementById("rowTableInsert").value;
	console.log(colLength,rowLength);
	loadFocus();
	insertTextAtCaret("$table");
	loadFocus();
	addTableData(colLength,rowLength);
}


/*Shows the quick bar
Inputs 
	- none
Outputs
	- none
side effects 
	- Shows the quick bar and depending of the type of var it shows the base value of equation
*/
/*
function showQuickBarVarItemT()
{
	console.log("FIXING QUICK BAR");
	var varName = document.getElementById("varName").value;
	var jsonObject = getJSON(nameToKey(varName));
	if(jsonObject["type"] == "answer" || jsonObject["type"] == "equation")
	{
		if("rawEquation" in jsonObject)
		{
			document.getElementById("quickBar").value = jsonObject["rawEquation"];
			checkQuickBarInput();
		}
	}
	else
	{
		document.getElementById("quickBar").value = jsonObject["baseText"];
		checkQuickBarInput();
	}
}*/

function showQuickBarVarItemReload()
{
	console.log("FIXING QUICK BAR");
	var varName = document.getElementById("varName").value;
	document.getElementById("quickBar").value = "";		
	document.getElementById("randomQuickBarOptionMin").value = 0;		
	document.getElementById("randomQuickBarOptionMax").value = 0;	
	document.getElementById("listQuickBar").value = "";
	document.getElementById("setBaseValue").value = "";
	document.getElementById("values").value = "";
	var jsonObject = getJSON(nameToKey(varName));
	console.log(jsonObject);
	if(jsonObject["type"] == "answer" || jsonObject["type"] == "equation" )
	{
		if("values" in jsonObject && jsonObject["values"] != [] && jsonObject["values"] != "")
		{
			console.log(jsonObject["values"], "AAA");
			document.getElementById("quickBar").setAttribute("hidden","");
			document.getElementById("values").removeAttribute("hidden");
			document.getElementById("setBaseValue").removeAttribute("hidden");
			document.getElementById("values").value = jsonObject["values"].join("|");
			document.getElementById("setBaseValue").value = jsonObject["baseText"];
			if(document.getElementById("values").value != "" || document.getElementById("setBaseValue").value != "")
			{
				//document.getElementById("varValueOptions").selectedIndex = 1;
			}
		}
		else if("rawEquation" in jsonObject)
		{
			document.getElementById("quickBar").value = jsonObject["rawEquation"];
			//checkQuickBarInput();
		}
		else
		{			
			document.getElementById("quickBar").value = jsonObject["baseText"];
		}
	}
	else if(jsonObject["type"] == "answerText")
	{
		if("values" in jsonObject && jsonObject["values"] != [] && jsonObject["values"] != "")
		{
			console.log(jsonObject["values"], "AAA");
			document.getElementById("quickBar").setAttribute("hidden","");
			document.getElementById("values").removeAttribute("hidden");
			document.getElementById("setBaseValue").removeAttribute("hidden");
			document.getElementById("values").value = jsonObject["values"].join("|");
			document.getElementById("setBaseValue").value = jsonObject["baseText"];
			if(document.getElementById("values").value != "" || document.getElementById("setBaseValue").value != "")
			{
				//document.getElementById("varValueOptions").selectedIndex = 1;
			}
		}
		else
		{			
			console.log(jsonObject["baseText"]);
			document.getElementById("quickBar").value = jsonObject["baseText"];
		}
	}
	else if(jsonObject["type"] == "document")
	{
		document.getElementById("quickBar").value = atob(jsonObject["baseText"]);
	}
	else
	{
		document.getElementById("quickBar").value = jsonObject["baseText"];
		document.getElementById("randomQuickBarOptionMin").value = jsonObject["values"][0];
		document.getElementById("randomQuickBarOptionMax").value = jsonObject["values"][1];
		document.getElementById("listQuickBar").value = jsonObject["values"].join("|");
		if(jsonObject["r"] == false)
		{
			document.getElementById("quickBar").value = jsonObject["baseText"];
		}
		else if(jsonObject["rType"] == "range")
		{
			document.getElementById("randomQuickBarOptionMin").value = jsonObject["values"][0];
			document.getElementById("randomQuickBarOptionMax").value = jsonObject["values"][1];
		}
		else if(jsonObject["rType"] == "list")
		{
			document.getElementById("listQuickBar").value = jsonObject["values"].join("|");
		}
		//checkQuickBarInput();
	}
}


/*Shows the quick bar
Inputs 
	- none
Outputs
	- none
side effects 
	- Shows the quick bar and depending of the type of var it shows the base value of equation
*/
//25/07/2024 Added
function showQuickBarVarItem(id)
{
	showQuickBarVarItemReload();
	setVarItemOptions();
	changeVarInputType();
}


/*Sets the value types given an item
Inputs 
	- none
Outputs
	- none
side effects 
	- Sets the value types given an item
*/
//16/01/2025 Added
function setVarItemOptions()
{
	console.log("Set variable option types");
	var varName = document.getElementById("varName").value;
	var options = [];
	var typeOptions = document.getElementById("varValueOptions");
	typeOptions.innerHTML = "";
	var selectedOption = 0;
	var quickBar = document.getElementById("quickBar");
	var random = document.getElementById("randomQuickBarOption");
	Array.from(document.getElementsByClassName("quickBarOptions")).forEach(element => {
		element.setAttribute("hidden","");
	});
	var jsonObject = getJSON(nameToKey(varName));
	console.log("SET VAR ITEM OPTIONS",varName,jsonObject);
	if(jsonObject["type"] == "answer")
	{
		var setValue = document.createElement('option');
		setValue.innerHTML = "Set Equation";
		options.push(setValue);
		var setValue = document.createElement('option');
		setValue.innerHTML = "Dropdown";
		options.push(setValue);

		if("values" in jsonObject && jsonObject["values"] != [] && jsonObject["values"] != "")
		{
			selectedOption = 1;
		}
	}
	else if(jsonObject["type"]  == "answerText")
	{
		var setValue = document.createElement('option');
		setValue.innerHTML = "Set Value";
		options.push(setValue);
		var setValue = document.createElement('option');
		setValue.innerHTML = "Dropdown";
		options.push(setValue);

		if("values" in jsonObject && jsonObject["values"] != [] && jsonObject["values"] != "")
		{
			selectedOption = 1;
		}
	}
	else if(jsonObject["type"] == "equation")
	{
		if("rawEquation" in jsonObject)
		{
			var setValue = document.createElement('option');
			setValue.innerHTML = "Set Equation";
			options.push(setValue);
		}
	}
	else
	{
		var setValue = document.createElement('option');
		setValue.setAttribute("onclick","changeVarInputType()");
		setValue.innerHTML = "Set Value";
		options.push(setValue);
		var randomRange = document.createElement('option');
		randomRange.setAttribute("onclick","changeVarInputType()");
		if(jsonObject["r"] == true && jsonObject["rType"] == "range")
		{
			randomRange.setAttribute("selected","");			
		}
		randomRange.innerHTML = "Random in Range";
		options.push(randomRange);

		var randomList = document.createElement('option');
		randomList.setAttribute("onclick","changeVarInputType()");
		if(jsonObject["r"] == true && jsonObject["rType"] == "list")
		{
			randomList.setAttribute("selected","");			
		}
		randomList.innerHTML = "Random in List";
		options.push(randomList);
	}

	options.forEach(element => {
		typeOptions.appendChild(element)
	});
	typeOptions.selectedIndex = selectedOption;
	return options;
}

/*Changes the input field to have the correct options
Inputs 
	- none
Outputs
	- none
side effects 
	- Changes the input field to have the correct options
*/
//16/01/2025 Added
function changeVarInputType()
{	
	
	showQuickBarVarItemReload();
	var typeOptions = document.getElementById("varValueOptions").value;

	
	var quickBar = document.getElementById("quickBar");
	var listQuickBar = document.getElementById("listQuickBar");
	var random = document.getElementById("randomQuickBarOption");
	var values = document.getElementById("values");
	var setBaseValue = document.getElementById("setBaseValue");


	console.log("CHANGE VAR INPUT TYPE", typeOptions);
	Array.from(document.getElementsByClassName("quickBarOptions")).forEach(element => {
		element.setAttribute("hidden","");
	});
	if(typeOptions == "Set Value" || typeOptions == "Set Equation")
	{
		quickBar.removeAttribute("hidden");
	}
	else if(typeOptions == "Dropdown")
	{
		values.removeAttribute("hidden");		
		setBaseValue.removeAttribute("hidden");
	}
	else if(typeOptions == "Random in Range")
	{
		Array.from(document.getElementsByClassName("randomQuickBarOption")).forEach(element => {
			element.removeAttribute("hidden");
		});
	}
	else if(typeOptions == "Random in List")
	{
		listQuickBar.removeAttribute("hidden");
	}
}


//25/07/2024 Added
function getAllVarsOption()
{
	var varOptions = "";
	var names = [];
	for(key in translationTable)
	{
		if(translationTable[key][TT_VARIABLETYPE] != "table")
		{
			names.push(translationTable[key][TT_VARIABLENAME]);
		}
	}
	names.sort();
	names.forEach(element => {
		varOptions += "<option onclick='showQuickBarVarItem()'> " + element + "</option>";	
	});
	return varOptions;
}

/* Changes the type of a var
Inputs 
	- id of the user variable
	- the current type
	- the new type
Outputs
	- none
side effects 
	- Changes the type of a var
	- Reloads the data table to show changes
*/
function changeType(id,type,newType)
{
	if(newType == "equation" || newType == "answer")
	{		
		addToJSON(id,'equation','');
		addToJSON(id,'rawEquation','');
	}
	changeJSONValue(id,type,newType);
	translationTable[id][TT_VARIABLETYPE] = newType;
	tableCreate();
}

/* Add a row to a table
Inputs 
	- id of the user variable table
Outputs
	- none
side effects 
	- Add a row to a table
*/
function addRow(id, getValue = true, indexSet = null)
{
	var table = document.getElementById(id);
	var rows = table.childNodes[0].childNodes[0].cells.length;
	if(table.hasAttribute("colsamount"))
	{
		rows = table.getAttribute("colsAmount");
	}
	console.log("ROWS",rows);
	if(indexSet == null)
	{
		var index = table.rows.length;
		if(getValue)
		{
			index = document.getElementById("addRowAmount").value;
			if(index == undefined)
			{
				index = table.rows.length;
			}
		}
		
	}
	else
	{
		index = indexSet
	}
	var row = table.insertRow(index);
	for (var x = 0; x < rows ; x++)
	{
		var rowInstance = row.insertCell(x);
		rowInstance.innerHTML = "&nbsp;";
	}
}

/* Add a row to a table
Inputs 
	- id of the user variable table
Outputs
	- none
side effects 
	- Add a row to a table
*/
function addSection(id)
{
	var table = document.getElementById(id);
	var cols = table.childNodes[0].childNodes[0].cells.length;
	console.log(table.rows.length);
	var row = table.insertRow(table.rows.length);
	row.setAttribute("rowIndex",table.rows.length);
	row.setAttribute("section","");
	for (var x = 0; x < 1 ; x++)
	{
		var rowInstance = row.insertCell(x);
		rowInstance.setAttribute("style","text-align:center");
		rowInstance.setAttribute("colspan",cols);
		rowInstance.innerHTML = "&nbsp;";
	}
}


/* Remove a row to a table
Inputs 
	- id of the user variable table
Outputs
	- none
side effects 
	- Removes a row to a table
*/
function removeRow(id)
{
	var rowNumber = document.getElementById("removeRowAmount").value;
	var table = document.getElementById(id);
	var row = table.childNodes[0].childNodes[rowNumber];
	console.log(row);
	table.childNodes[0].removeChild(row);
	Array.from(document.getElementsByClassName("tableRow")).forEach(element => {
		element.innerHTML = getTableRows(tableId);
	});		
}

/* Remove a collumn to a table
Inputs 
	- id of the user variable table
Outputs
	- none
side effects 
	- Removes a row to a table
*/
function removeCol(id,colNumber)
{
	var table = document.getElementById(id);
	var rows = table.childNodes[0].childNodes;
	Array.from(rows).forEach(row => {		
		if(row.childNodes.length - 1 >= colNumber)
		{			
			console.log(row);
			row.removeChild(row.childNodes[colNumber]);
		}
	});
}


/* Gets the rows a table has for a dropdown menu
Inputs 
	- id of the user variable
Outputs
	- none
side effects 
	- Gets the rows a given table has to be used by a dropdown menu
*/
function getTableRows(id)
{
	console.log(id);
	var tableRows = "";
	var table = document.getElementById(id);
	var rows = table.childNodes[0].childNodes.length;
	for (var i = 0; i < rows ; i++)
	{
		tableRows += "<option> " + i + "</option>";
	}
	return tableRows;
}

/* Remove a value from translation table
Inputs 
	- id of the user variable
	- the index to remove item at
Outputs
	- the new values
side effects 
	- Remove a value from translation table
*/
function removeValue(id,index)
{
	var newValue = null;
	if("values" in getJSON(id))
	{				
		newValue = getJSON(id)["values"];
		newValue.splice(index,1);
		changeJSONValue(id,"values",newValue);
	}
	return newValue;
}

/* Add a value from translation table
Inputs 
	- id of the user variable
Outputs
	- the new values
side effects 
	- Add a value from translation table
*/
function addValue(id)
{
	var newValue = null;
	var newValueItem = document.getElementById("valueAdder").value;
	if("values" in getJSON(id))
	{				
		newValue = getJSON(id)["values"];
		newValue.push(newValueItem);
		console.log(newValueItem);
		changeJSONValue(id,"values",newValue);
	}
	return newValue;
}

/* Get the value of a given id from translation table
Inputs 
	- id of the user variable
Outputs
	- the values at a given id
side effects 
	- Get the value of a given id from translation table
*/
function getValues(id)
{
	var objectValues = "";
	if("values" in getJSON(id))
	{				
		for(key in getJSON(id)["values"])
		{
			objectValues += "<option> " + getJSON(id)["values"][key] + "</option>";
		}
	}
	return objectValues;
}

/* Set the base text of a given id
Inputs 
	- id of the user variable
Outputs
	- none
side effects 
	- Set the base text of a given id
*/
function setBaseText(id)
{
	changeJSONValue(id,"baseText",document.getElementById("baseText").value);
	tableCreate();
}

/* Get the status of the random flag of a given id
Inputs 
	- id of the var
Outputs
	- the correct random status of a given id
side effects 
	- none
*/
function getRandomStatus(id)
{
	if("r" in translationTable[id])
	{
		return translationTable[id]["r"];
	}	
	return false;		
}


function removeHTMLChars(str) {
	return str.replace(/<[^>]*>/g, ''); // Removes all HTML tags
}

/* Generates vars automaticly from a table and sets them to be a given type
Inputs 
	- id of the table
	- type of the new var
Outputs
	- none
side effects 
	- Generates vars automaticly from a table and sets them to be a given type
*/
function autoVars(tableId, type)
{
	var splitSelectedText = selectedText.split("<tr>");
	//var dd = null;
	console.log("Table ID " + tableId);
	//var topRowItems = [];
	//var firstColumItems = [];
	var tableRowNodes = document.getElementById(tableId).rows;
	console.log(tableRowNodes.length);
	for(var row = 0; row < tableRowNodes.length; row++)
	{			
				
		for (var col = 0; col < tableRowNodes[row].childNodes.length; col++)
		{
			try {
				if(row > 0 && col > 0)
				{
					var rawText = tableRowNodes[0].childNodes[col].innerHTML.replaceAll("<br>","").replaceAll(" ","").replaceAll(" ","").trim() + tableRowNodes[row].childNodes[0].innerHTML.replaceAll("<br>","").replaceAll(" ","").trim();
					rawText = rawText.replaceAll("_"," ");					
					rawText = rawText.replaceAll(" "," ");
					rawText = removeSpecialChars(rawText);				
					rawText = rawText.replaceAll("<p>","</p>");
					rawText = removeHTMLChars(rawText);
					rawText = rawText.replaceAll("&nbsp;"," ").trim();
					cleanedText = "$" + rawText;
					console.log(cleanedText);
					setItemNameRaw(cleanedText, tableRowNodes[0].childNodes[col].innerHTML.replaceAll("<br>","").replaceAll(" ","").trim() + tableRowNodes[row].childNodes[0].innerHTML.replaceAll("<br>","").replaceAll(" ","").trim(), type);
					var found = false;
					if(cleanedText in itemReference)
					{
						itemReference[cleanedText] = itemReference[cleanedText] + 1;
					}
					else
					{
						found = true;
						itemReference[cleanedText] = 1;
					}
					usableText = tClean.substring(0, selectedIndex[currentSelectedIndex]);

					console.log(usableText,getJSON(nameToKey(cleanedText)));
					var jsonObject = getJSON(nameToKey(cleanedText));
					var completedHTMLString = "";
					var startOfHTMLString = "<span class='"+btoa(nameToKey(cleanedText))+"' id='customVar2' style='background-color:";
					var endOfHTMLString = "'>(" + translationTable[nameToKey(cleanedText)][TT_VARIABLENAME] + "," + jsonObject["baseText"] + "," + translationTable[nameToKey(cleanedText)][TT_VARIABLETYPE] + ")</span><span> </span><span> </span>";

					if(!found)
					{
						completedHTMLString = startOfHTMLString + textBackgroundColorMulti + endOfHTMLString;
					}
					else
					{						
						completedHTMLString = startOfHTMLString + textBackgroundColor + endOfHTMLString;
					}
					tableRowNodes[row].childNodes[col].innerHTML = completedHTMLString;
				}
			} catch (error) {
				console.log(error);
			}
		}
	}

	setTClean();
}

function dumpToCSV()
{
	var endCSV = "";
	for(var key in translationTable)
	{
		
	}	
	return endCSV;		
}		

/* Opens the student view tab
Inputs 
	- none
Outputs
	- none
side effects 
	- opens the student view tab
*/
function showLearningMode()
{
	window.open("question.php?questionID=" + questionID + "&learningMode=true&randomlock",'_blank');
}

/* Opens the test mode tab
Inputs 
	- none
Outputs
	- none
side effects 
	- opens the test view tab
*/
function showTestMode()
{
	window.open("question.php?questionID=" + questionID + "&testMode=true&randomlock",'_blank');
}

/* Opens the presentation mode tab
Inputs 
	- none
Outputs
	- none
side effects 
	- opens the presentation view tab
*/
function showPresentationMode()
{
	var xhttp = new XMLHttpRequest();
	data = '{"data" : ["'+questionID+'"]}';
	x = JSON.parse(data);
	/*xhttp.open("GET", "api.php/makeNewSession/?questionIDs=" + JSON.stringify(x), true);				
	xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xhttp.send("");
	xhttp.onreadystatechange = function() 
	{ 
		if (xhttp.readyState == 4 && xhttp.status == 200)
		{
			if(xhttp.responseText.length > 0 )
			{
				var JSONData = JSON.parse(xhttp.responseText);
				var sessionID = JSONData["sessionID"];
				window.open("question.php?sessionID=" + sessionID + "&shortCode=" + JSONData["shortCode"],'_blank');
			}
		}
	}*/
	//submit();
	window.open("session/sessionCreator.php?questionID=" + questionID,'_blank');	
}



/* Addes a visual variable to the text
Inputs 
	- the selected text area
Outputs
	- none
side effects 
	- Adds a variable to the text with the default type of var
*/
function searchVariable(selectedText)
{
	if(!openNewVarMenu)
	{
		console.log("ADD VAR", selectedText);
		searchItems(selectedText, "var", {"newT":true});
	}
}

/* Removes the table using the selected text and the unique id of the table
Inputs 
	- the table id
Outputs
	- none
side effects 
	- Removes the table using the selected text and the unique id of the table
*/
function removeTable(targetID)
{
	console.log(targetID);
	if(document.getElementById((targetID)).parentElement.hasAttribute("tableHolderDiv"))
	{
		document.getElementById((targetID)).parentElement.outerHTML = "";
	}
	else
	{
		document.getElementById((targetID)).outerHTML = "";
	}
}

/* Gets the raw equation and cleans it up to a correct format
Inputs 
	- raw equation
Outputs
	- clean equation
side effects 
	- Gets the raw equation and cleans it up to a correct format
*/
function translateEquation(text)
{
	console.log(text);
	text = text.replaceAll("$"," $").trim();
	var raw = text;
	text = text.split(" ");
	console.log(text);
	for (var char in text)
	{
		if(text[char].trim() != "" && text[char].includes("$"))
		{
			try {
				var charAdder = "";
				if (text[char].includes("("))
				{
					charAdder += "(";
				}
				else if(text[char].includes(")"))
				{
					charAdder += ")";
				}
				var id = text[char].replace("$","").replaceAll("(","").replaceAll(")","");
				console.log(char);
				console.log(text[char]);
				raw = raw.replaceAll(text[char], ("$" + translationTable[id][0] + charAdder).replaceAll("  "," "));
				console.log(text[char], translationTable[id]);
			} catch (error) {
				console.log(error);
			}
		}
	}
	console.log(raw.trim().replaceAll("  "," "));
	return raw.trim().replaceAll("  "," ").replaceAll("( ", "(").replaceAll(" )", ")");
}

/* Toggles the random operator for a given id
Inputs 
	- id of a user variable
Outputs
	- the new value of the id
side effects 
	- Toggles the random operator for a given id
*/
function setRandomOperator(id)
{
	console.log("OPERATOR RANDOMIZED");
	var startText = translationTable[id][TT_JSONVALUE].substring(0,translationTable[id][TT_JSONVALUE].split("{")[0].length);
	var jsonObject = getJSON(id);
	if(jsonObject["r"] == true)
	{
		jsonObject["r"] = false;
	}
	else
	{			
		jsonObject["r"] = true;
	}
	translationTable[id][TT_JSONVALUE] = startText + JSON.stringify(jsonObject);
	tableCreate();
	return translationTable[id];
}

/* Get the json value for a given id
Inputs 
	- id of a user variable
Outputs
	- JSON of the given key
side effects 
	- none
*/
function getJSON(key)
{
	var jsonString = translationTable[key][TT_JSONVALUE].split("{");
	jsonString = jsonString.splice(1);
	jsonString = "{" + jsonString.join("{");
	return JSON.parse(jsonString);
}

/* Sets the random operator to false
Inputs 
	- id of a user variable
Outputs
	- the new item of translation table
side effects 
	- Sets the random operator to false
*/
function unRandomOperator(id)
{
	console.log("OPERATOR NON-RANDOMIZED");
	var startText = translationTable[id][TT_JSONVALUE].substring(0,translationTable[id][TT_JSONVALUE].split("{")[0].length);
	var jsonObject = getJSON(id);
	jsonObject["r"] = false;
	translationTable[id][TT_JSONVALUE] = startText + JSON.stringify(jsonObject);
	return translationTable[id];
}

/* Gets the id of a given name in a dictonary
Inputs 
	- the dict to search through
	- the name to search for
Outputs
	- the id of the item in the dict
side effects 
	- none
*/
function nameToID(data, name)
{
	for (var key in data) 
	{
		//console.log(data[key]);
		if(data[key][0] == name)
		{
			var splitString = data[key][1].split("{");
			splitString = splitString.splice(1);
			splitString = "{" + splitString.join("{");
			//console.log(splitString);
			return JSON.parse(splitString)["currentID"];
		}
	}
	return -1;
}

/* Add a table at the given text using the input values
Inputs 
	- none
Outputs
	- none
side effects 
	- Add a table at the given text using the input values
*/
function addTable()
{
	/*
	var tbl = document.createElement("table");
	tbl.setAttribute("border", "2");
	tbl.setAttribute("class","table table-hover table-bordered " + currentID);
	tbl.setAttribute("id",currentID);
	var tblBody = document.createElement("tbody");
	var columnLength = document.getElementById("columns").value;
	var rowLength = document.getElementById("rows").value;
	if(columnLength != null && columnLength != "" && rowLength != null && rowLength != "")
	{
		translationTable[currentID] = ['','%table{"values" : ['+rowLength+','+columnLength+'],"currentID": '+currentID+'}',"table"];
		console.log(currentID,translationTable);
		console.log(rowLength, columnLength);
		for (var i = 0; i < rowLength; i++) 
		{
			var row = document.createElement("tr");
			for (var j = 0; j < columnLength; j++) {
				var cell = document.createElement("td");
				cell.innerHTML = "<br>";
				//cell.appendChild(cellText);
				row.appendChild(cell);
			}

			//row added to end of table body
			tblBody.appendChild(row);
		}

		// append the <tbody> inside the <table>
		tbl.appendChild(tblBody);
		console.log(tbl);
		setTClean();
		tClean = tClean.replaceAll("$table",tbl.outerHTML);
		answer = answer.replaceAll("$table",tbl.outerHTML);
		console.log(tClean);
		document.getElementById("main").innerHTML = tClean;
		document.getElementById("answer").innerHTML = answer;
		setTClean();
		currentID++;
	}*/
	var columnLength = document.getElementById("columns").value;
	var rowLength = document.getElementById("rows").value;
	if(columnLength != null && columnLength != "" && rowLength != null && rowLength != "")
	{
		addTableData(columnLength,rowLength);
	}
	else{
		messageCreate("Didn't recive a correct input","ERROR");
	}
}

/* Add a table at the given text using the input values
Inputs 
	- none
Outputs
	- none
side effects 
	- Add a table at the given text using the input values
*/
function addTableData(col,row)
{
	loadFocus();
	var tbl = document.createElement("table");
	var tblDiv = document.createElement("div");
	tblDiv.setAttribute("tableHolderDivID",currentID);
	tblDiv.setAttribute("class","tableHolderDiv");
	tblDiv.appendChild(tbl);
	tbl.setAttribute("border", "2");
	tbl.setAttribute("id",currentID);
	tbl.setAttribute("colsAmount",col);
	tbl.setAttribute("class","table table-hover table-bordered " + currentID);
	var tblBody = document.createElement("tbody");
	var columnLength = col;
	var rowLength = row;
	console.log("INSERTING TABLE", columnLength,rowLength);
	if(columnLength != "" && rowLength != "")
	{
		translationTable[currentID] = ['','%table{"values" : ['+rowLength+','+columnLength+'],"currentID": '+currentID+'}',"table"];
		console.log(currentID,translationTable);
		console.log(rowLength, columnLength);
		for (var i = 0; i < rowLength; i++) 
		{
			var row = document.createElement("tr");
			for (var j = 0; j < columnLength; j++) {
				var cell = document.createElement("td");
				cell.innerHTML = "<br>";
				row.appendChild(cell);
			}

			//row added to end of table body
			tblBody.appendChild(row);
		}
		addRowButton = document.createElement("button");
		addRowButton.setAttribute("onclick","addRow('"+currentID+"',false)");
		addRowButton.setAttribute("class","btn submitRemove");
		addRowButton.innerHTML = "Add Row";

		addSectionButton = document.createElement("button");
		addSectionButton.setAttribute("onclick","addSection('"+currentID+"')");
		addSectionButton.setAttribute("class","btn submitRemove");
		addSectionButton.innerHTML = "Add Section";

		removeTableButton = document.createElement("button");
		removeTableButton.setAttribute("onclick","removeTable('"+currentID+"')");
		removeTableButton.setAttribute("class","btn submitRemove");
		removeTableButton.innerHTML = "Remove Table";

		// append the <tbody> inside the <table>
		tbl.appendChild(tblBody);
		tblDiv.appendChild(addRowButton);
		tblDiv.appendChild(addSectionButton);
		tblDiv.appendChild(removeTableButton);
		//tblDiv.appendChild("<br>");
		console.log(tbl);
		setTClean();
		tClean = tClean.replaceAll("$table","<br>" + tblDiv.outerHTML + "<br>");
		answer = answer.replaceAll("$table","<br>" + tblDiv.outerHTML + "<br>");
		document.getElementById("main").innerHTML = tClean;
		document.getElementById("answer").innerHTML = answer;
		setTClean();
		currentID++;
	}
}

function autoMakeTableNames()
{

}

/* Add a table at the given text using the input values
Inputs 
	- none
Outputs
	- none
side effects 
	- Add a table at the given text using the input values
*/
//These function take care of the menu clicks i.e setting placeholder text, linking items to each other etc
function getSummedItems(thisID)
{
	var value = getJSON(thisID);
	return value["values"];
}

/* Sets the placeholder text for answer item given an user variable id
Inputs 
	- id of the user variable
Outputs
	- none
side effects 
	- Sets the placeholder text for answer item given an user variable id
*/
function setAnswerPlaceholderText(thisID)
{
	console.log("SETTING PLACEHOLDER");
	var text = document.getElementById("placeholder").value;
	var cleanC = translationTable[thisID][1];
	console.log(cleanC);
	var value = getJSON(thisID);
	value["placeholder"] = text;
	console.log(value);
	addToJSON(thisID,placeholder, text);
}

/* Sets the equation for answer item given an user variable id
Inputs 
	- id of the user variable
Outputs
	- none
side effects 
	- Sets the equation for answer item given an user variable id
	- Also reloads the table
*/
function setAnswerEquation(id)
{
	console.log("SETTING EQUATION");
	var text = document.getElementById("equation").value;
	console.log(text);
	text = text.replaceAll("+"," + ");
	text = text.replaceAll("-"," - ");
	
	text = text.replaceAll("*"," * ");
	text = text.replaceAll("/"," / ");

	text = text.replaceAll("^"," ^ ");
	text = text.replaceAll("  "," ");
	rawText = text;
	console.log(text);
	var tt = text.split(" ");
	var error = false;
	for (var iTT = 0; iTT < tt.length; iTT++)
	{
		console.log(tt[iTT]);
		console.log(translationTable);
		if(tt[iTT].includes("$"))
		{
			console.log(tt[iTT].replaceAll(")","").replaceAll("(",""));
			var nID = nameToID(translationTable,tt[iTT].replaceAll(")","").replaceAll("(",""));
			console.log(nID);
			/*if(nID == -1)
			{
				alert("EQUATION CONTAINED A REFRENCE TO A NON CREATED VAR");
				error = true;
				//break;
			}
			else if(translationTable[nID][2] != "number" && translationTable[nID][2] != "operator")
			{
				alert("EQUATION CONTAINED A REFRENCE TO A NON NUMBER OR OPERATOR VAR");
				error = true;
				break;
			}*/
			text = text.replaceAll(tt[iTT].replaceAll(")","").replaceAll("(",""), "$" + nID);
		}
	}
	if(!error)
	{
		text = text.replaceAll(" )",")").replaceAll("( ","(").replaceAll("  ", " ");
		console.log(text);
		var cleanC = translationTable[id][1];
		var value = getJSON(id);
		value["equation"] = btoa(text);
		value["rawEquation"] = rawText;
		console.log(value);
		translationTable[id][1] = translationTable[id][1].substring(0,2) + JSON.stringify(value);
		tableCreate();
	}
}
var openNewVarMenu = false;
var selectedIndex = [];
var itemReference = {};
var currentSelectedIndex = 0;
var currentTextSearch = "";
var currentTextType = "";
var types = {};
var showOrgFileStructure = false;

var me = {};


/* Gets all the diffrent user created variable types
Inputs 
	- none
Outputs
	- All the user created variable types
side effects 
	- Gets all the diffrent user created variable types
*/
function getTypes()
{
	typesText = "";
	for(key in types)
	{
		typesText += "<option> " + key + "</option>";
	}
	typesText += "<option> New Type</option>";
	return typesText;
}

function getVars()
{
	typesText = "";
	for(key in translationTable)
	{
		if(translationTable[key][2] != "table")
		{
			typesText += "<option> " + translationTable[key][0] + "</option>";
		}
	}
	return typesText;
}

/* Will close the add variable item menu
Inputs 
	- none
Outputs
	- none
side effects 
	- Will close the add variable item menu
*/
function closeSelectItems()
{
	var item = document.getElementById("selectedValue");
	if(item != null)
	{
		document.getElementById("selectedValue").outerHTML = currentTextSearch;
	}
	for (const [key, value] of Object.entries(translationTable)) {
		propogateChangeText(key);
	}
	location.href = "#";
	//5/08/2024 Added
	document.getElementById("newVar").setAttribute("hidden","");
	openNewVarMenu = false;
}

/* Will search for all hotkey items %$key% and replace them with the correct visual variable
Inputs 
	- none
Outputs
	- none
side effects 
	- Will search for all hotkey items %$key% and replace them with the correct visual variable
	- Will do this to the awnser text 
	- Will do this to the main text
*/
//Fix 24/7/2024 (Fix color)
function findVars()
{
	setTClean();
	console.log("REPLACING VAR TEXT TO VAR ITEMS");
	var text = tClean;
	var aw = answer;
	for(key in translationTable)
	{
		console.log("LOOKING FOR",translationTable[key], key, nameToKey(translationTable[key]));
		var foundItem = false;
		var name = translationTable[key][TT_VARIABLENAME];
		if(name in itemReference)
		{
			if(itemReference[name] > 0)
			{						
				foundItem = true;
			}
			else
			{
				foundItem = false;
			}
			console.log("ITEM REFRENCE", itemReference[name],name);
			itemReference[name] = itemReference[name] + 1;
		}
		else
		{
			foundItem = false;
			itemReference[name] = 1;
		}
		var jsonObject = "";
		//console.log(nameToKey(name));
		console.log("%" + translationTable[key][TT_VARIABLENAME] + "%");
		if(!foundItem)
		{
			jsonObject = getJSON(key);
			if(translationTable[key][TT_VARIABLETYPE] == "equation")
			{

				text = text.replaceAll("<span>%</span>" + translationTable[key][TT_VARIABLENAME] + "%", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColor + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["rawEquation"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
				aw = aw.replaceAll("<span>%</span>" + translationTable[key][TT_VARIABLENAME] + "%", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColor + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["rawEquation"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
			
				text = text.replaceAll("<span>%</span>" + translationTable[key][TT_VARIABLENAME] + "<span>%</span>", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColor + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["rawEquation"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
				aw = aw.replaceAll("<span>%</span>" + translationTable[key][TT_VARIABLENAME] + "<span>%</span>", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColor + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["rawEquation"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
				
				text = text.replaceAll("%" + translationTable[key][TT_VARIABLENAME] + "<span>%</span>", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColor + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["rawEquation"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
				aw = aw.replaceAll("%" + translationTable[key][TT_VARIABLENAME] + "<span>%</span>", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColor + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["rawEquation"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
			

				text = text.replaceAll("%" + translationTable[key][TT_VARIABLENAME] + "%", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColor + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["rawEquation"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
				aw = aw.replaceAll("%" + translationTable[key][TT_VARIABLENAME] + "%", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColor + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["rawEquation"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
			}
			else
			{
				text = text.replaceAll("<span>%</span>" + translationTable[key][TT_VARIABLENAME] + "%", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColor + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["baseText"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
				aw = aw.replaceAll("<span>%</span>" + translationTable[key][TT_VARIABLENAME] + "%", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColor + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["baseText"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
				
				text = text.replaceAll("<span>%</span>" + translationTable[key][TT_VARIABLENAME] + "<span>%</span>", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColor + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["baseText"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
				aw = aw.replaceAll("<span>%</span>" + translationTable[key][TT_VARIABLENAME] + "<span>%</span>", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColor + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["baseText"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
				
				text = text.replaceAll("%" + translationTable[key][TT_VARIABLENAME] + "<span>%</span>", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColor + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["baseText"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
				aw = aw.replaceAll("%" + translationTable[key][TT_VARIABLENAME] + "<span>%</span>", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColor + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["baseText"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
			
				text = text.replaceAll("%" + translationTable[key][TT_VARIABLENAME] + "%", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColor + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["baseText"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
				aw = aw.replaceAll("%" + translationTable[key][TT_VARIABLENAME] + "%", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColor + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["baseText"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
			}
		}
		else
		{
			jsonObject = getJSON(key);
			if(translationTable[key][TT_VARIABLETYPE] == "equation")
			{
				text = text.replaceAll("<span>%</span>" + translationTable[key][TT_VARIABLENAME] + "%", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColorMulti + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["rawEquation"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
				aw = aw.replaceAll("<span>%</span>" + translationTable[key][TT_VARIABLENAME] + "%", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColorMulti + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["rawEquation"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
				
				text = text.replaceAll("<span>%</span>" + translationTable[key][TT_VARIABLENAME] + "<span>%</span>", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColorMulti + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["rawEquation"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
				aw = aw.replaceAll("<span>%</span>" + translationTable[key][TT_VARIABLENAME] + "<span>%</span>", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColorMulti + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["rawEquation"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
				
				text = text.replaceAll("%" + translationTable[key][TT_VARIABLENAME] + "<span>%</span>", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColorMulti + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["rawEquation"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
				aw = aw.replaceAll("%" + translationTable[key][TT_VARIABLENAME] + "<span>%</span>", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColorMulti + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["rawEquation"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
			
				text = text.replaceAll("%" + translationTable[key][TT_VARIABLENAME] + "%", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColorMulti + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["rawEquation"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
				aw = aw.replaceAll("%" + translationTable[key][TT_VARIABLENAME] + "%", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColorMulti + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["rawEquation"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
			}
			else
			{
				text = text.replaceAll("<span>%</span>" + translationTable[key][TT_VARIABLENAME] + "%", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColorMulti + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["baseText"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
				aw = aw.replaceAll("<span>%</span>" + translationTable[key][TT_VARIABLENAME] + "%", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColorMulti + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["baseText"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
				
				text = text.replaceAll("<span>%</span>" + translationTable[key][TT_VARIABLENAME] + "<span>%</span>", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColorMulti + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["baseText"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
				aw = aw.replaceAll("<span>%</span>" + translationTable[key][TT_VARIABLENAME] + "<span>%</span>", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColorMulti + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["baseText"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
				
				text = text.replaceAll("%" + translationTable[key][TT_VARIABLENAME] + "<span>%</span>", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColorMulti + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["baseText"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
				aw = aw.replaceAll("%" + translationTable[key][TT_VARIABLENAME] + "<span>%</span>", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColorMulti + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["baseText"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
			
				text = text.replaceAll("%" + translationTable[key][TT_VARIABLENAME] + "%", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColorMulti + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["baseText"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
				aw = aw.replaceAll("%" + translationTable[key][TT_VARIABLENAME] + "%", "<span class='"+btoa(key)+"' id='customVar2' style='background-color:" + textBackgroundColorMulti + "'>(" + translationTable[key][TT_VARIABLENAME] + "," + jsonObject["baseText"] + "," + translationTable[key][TT_VARIABLETYPE] + ")</span><span id='emptySpan'> </span><span id='emptySpan'> </span>");
			}
		}
	}
	document.getElementById("main").innerHTML = text;
	document.getElementById("answer").innerHTML = aw;
	setTClean();
	
	findSections();
}


/* Gets all the document items in the translationTable
Inputs 
	- none
Outputs
	- none
side effects 
	-  Gets all the document items in the translationTable
*/
function getAllDocuments()
{
	var documents = [];
	for (const [key, value] of Object.entries(translationTable)) {
		if(getJSON(key)['type'] == 'document')
		{
			documents.push(getJSON(key));
		}
	}
	return documents;
}


/* Will add a temp Selected Value text to the current word that wants to be replaced with the visual variable
Inputs 
	- none
Outputs
	- none
side effects 
	- Will add a temp Selected Value text to the current word that wants to be replaced with the visual variable
*/
function showCurrentItem()
{
	document.getElementById("selectedValue").outerHTML = currentTextSearch;
	document.getElementById(searchSectionSide).innerHTML = document.getElementById(searchSectionSide).innerHTML.substring(0,selectedIndex[currentSelectedIndex]) + "<span id='selectedValue' style='background-color:red'>selected this text</span>" + document.getElementById(searchSectionSide).innerHTML.substring(selectedIndex[currentSelectedIndex] + currentTextSearch.length);
	
	/*
	if(currentSelectedIndex <= selectedIndex.length - 1)
	{
		console.log(currentSelectedIndex, selectedIndex.length - 1);
		uy = tClean.substring(0, selectedIndex[currentSelectedIndex]) + '<span id="selectedValue" style="background-color:red;"> Selected Value</span>' + tClean.substring(selectedIndex[currentSelectedIndex] + currentTextSearch.length, tClean.length);
		location.href = "#selectedTextVar";
		document.getElementById("main").innerHTML = uy;
	}*/

}

/* Creates a new variable, with a given name, basetext and type
Inputs 
	- name: the name of the variable
	- baseText: the base text that will set back if removed
	- type: type of variable
Outputs
	- none
side effects 
	- Creates a new variable, with a given name, basetext and type
	- Reload the table
	- Submit this data to the DB
*/
function setItemNameRaw(name, baseText, type)
{
	name = name.replaceAll(" ","_");
	if(!name.startsWith("$"))
	{
		name = "$" + name;
	}
	console.log(name,type);
	if(!inDict(translationTable,name))
	{
		type2 = type.substring(0,1);
		type2 = "var";
		if(type2 == "o")
		{
			translationTable[currentID] = [name,'%'+type2+'{"type" : "'+type+'", "values" : "+", "baseText" : "'+baseText+'","currentID": '+currentID+'}',type];
		}
		else if(type2 == "e")
		{
			translationTable[currentID] = [name,'%'+type2+'{"type" : "'+type+'", "values" : "", "baseText" : "'+baseText+'","currentID": '+currentID+'}',type];
		}
		else
		{
			translationTable[currentID] = [name,'%'+type2+'{"type" : "'+type+'", "values" : [0,0], "baseText" : "'+baseText+'","currentID": '+currentID+'}',type];
		}
		currentID++;
		tableCreate();
		submit();
	}
	else
	{
		messageCreate("Already have a variable with the same name","INFO");
	}
}

/* Sets the name of a given user variable
Inputs 
	- none
Outputs
	- none
side effects 
	- Sets the name of a given user variable
	- Reload the table
	- Submit this data to the DB
*/
function setItemName()
{
	var name = document.getElementById("variableNameHolder").value;
	document.getElementById("setItemValue").removeAttribute("disabled");
	var baseValue = document.getElementById("setItemValue").value;

	if(name.trim() != "")
	{
		name = name.replaceAll(" ","_");
		if(!name.startsWith("$"))
		{
			name = "$" + name;
		}
		var addBaseValue = false;
		if(baseValue != "")
		{
			addBaseValue = true;
		}

		var typeRaw = document.getElementById("typeView");
		var type = typeRaw.options[typeRaw.selectedIndex].value;
		console.log("CREATE VAR", type);
		console.log(name,type);
		if(!inDict(translationTable,name))
		{
			var type2 = type;
			if(type2 == "o")
			{
				translationTable[currentID] = [name,'%'+type2+'{"type" : "'+type+'", "values" : "+", "baseText" : "'+currentTextSearch+'","currentID": '+currentID+'}',type];
			}
			else if(type2 == "equation" || type2 == "e")
			{
				if(addBaseValue)
				{
					translationTable[currentID] = [name,'%equation{"type" : "'+type+'", "values" : "", "baseText" : "'+baseValue+'","currentID": '+currentID+',"equation":"","rawEquation":""}',type];
				}
				else
				{
					translationTable[currentID] = [name,'%equation{"type" : "'+type+'", "values" : "", "baseText" : "'+currentTextSearch+'","currentID": '+currentID+',"equation":"","rawEquation":""}',type];
				}
			}
			else if(type2 == "answer" || type2 == "a")
			{				
				if(addBaseValue)
				{
					translationTable[currentID] = [name,'%answer{"type" : "'+type+'", "values" : "", "baseText" : "'+baseValue+'","currentID": '+currentID+',"equation":"","rawEquation":""}',type];
				}
				else
				{
					translationTable[currentID] = [name,'%answer{"type" : "'+type+'", "values" : "", "baseText" : "'+currentTextSearch+'","currentID": '+currentID+',"equation":"","rawEquation":""}',type];
				}
			}
			else if(type2 == "answerText" || type2 == "a")
			{				
				if(addBaseValue)
				{
					translationTable[currentID] = [name,'%answer{"type" : "'+type+'", "values" : "", "baseText" : "'+baseValue+'","currentID": '+currentID+'}',type];
				}
				else
				{
					translationTable[currentID] = [name,'%answer{"type" : "'+type+'", "values" : "", "baseText" : "'+currentTextSearch+'","currentID": '+currentID+'}',type];
				}
			}
			else if (type2 == "list" || type2 == "dictonary") {				
				if(fileListOutput === null)
				{
					translationTable[currentID] = [name,'%answer{"type" : "'+type+'", "values" : "", "baseText" : "'+baseValue+'","currentID": '+currentID+'}',type];
				}
				else
				{
					translationTable[currentID] = [name,'%answer{"type" : "'+type+'", "values" : ' + JSON.stringify(fileListOutput) + ' , "baseText" : "'+currentTextSearch+'","currentID": '+currentID+'}',type];
					fileListOutput = null;
				}
			}
			else
			{
				if(addBaseValue)
				{
					translationTable[currentID] = [name,'%'+type2+'{"type" : "'+type+'", "values" : [0,0], "baseText" : "'+baseValue+'","currentID": '+currentID+'}',type];
				}
				else
				{
					translationTable[currentID] = [name,'%'+type2+'{"type" : "'+type+'", "values" : [0,0], "baseText" : "'+currentTextSearch+'","currentID": '+currentID+'}',type];
				}
			}
			currentID++;
			document.getElementById("setItemValue").value = "";
			tableCreate();
		}
		else
		{
			messageCreate("Already have a variable with the same name", "INFO");
		}		
		//submit();
	}
}

/* Creates a new type, with a given name
Inputs 
	- none
Outputs
	- none
side effects 
	- Creates a new type, with a given name
*/
function createType()
{
	var name = document.getElementById("typeName").value;
	if(name in types)
	{
		messageCreate("Already have a type with the same name", "INFO");
	}
	else
	{
		types[name] = JSON.parse("{}");
		document.getElementById("typeView").innerHTML = getTypes();
	}
}

/* Sets the base value of a user variable, this value is used in equations
Inputs 
	- none
Outputs
	- none
side effects 
	- Sets the base value of a user variable, this value is used in equations
	- Reloads the table
*/
function setItemBaseValue()
{
	var name = document.getElementById("variableNameHolder").value;
	if(!name.startsWith("$"))
	{
		name = "$" + name;
	}
	var type = document.getElementById("variableNameHolder").className;
	var baseValue = document.getElementById("setItemBaseValue").value;
	if(inDict(translationTable,name))
	{
		var i = nameToID(translationTable, name);
		type2 = type.substring(0,1);
		if(type == "number" || type == "n")
		{
			translationTable[i] = [name,'%'+type2+'{"baseValue": '+Number(baseValue)+', "baseText" : "'+currentTextSearch+'","values" : ['+Number(baseValue)+','+Number(baseValue)+'],"currentID": '+i+'}',type];
		}
		tableCreate();
	}
}

/* Sets the base value of a user variable, this value is used in equations
Inputs 
	- none
Outputs
	- none
side effects 
	- Sets the base value of a user variable, this value is used in equations
	- Reloads the table
*/
function setItemBaseValueDropDown()
{
	var name = document.getElementById("variableNameHolder").value;
	if(!name.startsWith("$"))
	{
		name = "$" + name;
	}
	var type = document.getElementById("variableNameHolder").className;
	var baseValue = document.getElementById("setItemBaseValueDropDown").value;
	if(inDict(translationTable,name))
	{
		var i = nameToID(translationTable, name);
		type2 = type.substring(0,1);
		if(type == "operator" || type == "o")
		{
			translationTable[i] = [name,'%'+type2+'{"baseText" : "'+currentTextSearch+'", "values": "'+baseValue+'", "currentID": '+i+'}',type];
		}
		tableCreate();
	}
}

/* Finds all instance of the variable text in the text
Inputs 
	- text to search
	- type 
	- extra data JSON
Outputs
	- none
side effects 
	- Finds all instance of the variable text in the text
	- Reloads the table
*/
var searchSectionSide = "main";
function searchItems(textSearch, type, extra)
{
	if(!openNewVarMenu)
	{
		textSearch = textSearch.trim();
		if(textSearch != "")
		{
			selectedIndex = [];
			console.log(extra);
			setTClean();
			var tCleanInstance = tClean;
			//Adding the option it doesnet search div items

			for (var key in translationTable) 
			{
				Array.from(document.getElementsByClassName(btoa(key))).forEach(element => {
					var replaceString = "";
					for (var i = 0; i < translationTable[key][TT_JSONVALUE].length; i++)
					{
						replaceString += "<";
					}
					replaceString = "<";
					element.innerHTML = replaceString;
					tClean = tClean.replaceAll(translationTable[key][TT_JSONVALUE],replaceString);
				});
			}
			console.log("INSTANCE IN MAIN",document.getElementById("main").innerHTML.search(textSearch));
			var mIH = document.getElementById("main").innerHTML;
			
			const sourceStr = document.getElementById('main').innerHTML;
			const searchStr = textSearch;
			selectedIndex = [...sourceStr.matchAll(new RegExp(searchStr, 'gi'))].map(a => a.index);
			console.log(selectedIndex); // [2, 25, 27, 33]
			console.log(extra);
			var selectedItem = "";
			if("selected" in extra)
			{
				selectedItem = extra['selected'];
			}

			if(document.getElementById("main").innerHTML.search(textSearch) && selectedItem != "answer")
			{
				searchSectionSide = 'main';
				document.getElementById('main').innerHTML = document.getElementById('main').innerHTML.substring(0,document.getElementById("main").innerHTML.search(textSearch)) + "<span id='selectedValue' style='background-color:red'>selected this text</span>" + document.getElementById('main').innerHTML.substring(document.getElementById("main").innerHTML.search(textSearch) + textSearch.length);
			}
			else{
				if(document.getElementById("answer").innerHTML.search(textSearch))
				{
					searchSectionSide = 'answer';
					document.getElementById('answer').innerHTML = document.getElementById('answer').innerHTML.substring(0,document.getElementById("answer").innerHTML.search(textSearch)) + "<span id='selectedValue' style='background-color:red'>selected this text</span>" + document.getElementById('answer').innerHTML.substring(document.getElementById("answer").innerHTML.search(textSearch) + textSearch.length);
				}
			}
			document.getElementById("newVar").removeAttribute("hidden");
			document.getElementById("typeView").innerHTML = getTypes();					
			openNewVarMenu = true;
			showCurrentItem();
			currentTextSearch = textSearch;
			/*
			currentTextSearch = textSearch.replace(/<\!--.*?-->/g, "");
			currentTextType = type; 
			console.log(textSearch);
			var tCleanSplit = tClean.split(textSearch);
			var jk = 0;
			if("next" in extra)
			{
				jk = extra["next"];
				currentSelectedIndex += jk;
			}
			console.log(tCleanSplit.length);
			for(var x2 = 0; x2 < tCleanSplit.length; x2++)
			{
				if(tCleanInstance.indexOf(currentTextSearch) > -1)
				{
					selectedIndex.push(tCleanInstance.indexOf(currentTextSearch));
					var tempText = "";
					for(var q = 0; q < currentTextSearch.length; q++)
					{
						tempText = tempText + "#";
					}
					tCleanInstance = tCleanInstance.replace(currentTextSearch, tempText);
				}
				else 
				{
					console.log(currentTextSearch, tCleanInstance.indexOf(currentTextSearch));
				}
			}
			
			console.log(selectedIndex);
			if("newT" in extra && extra["newT"] == true)
			{
				document.getElementById("newVar").removeAttribute("hidden");
				document.getElementById("typeView").innerHTML = getTypes();					
				openNewVarMenu = true;
			}			
			showCurrentItem();*/
		}
	}
}

/* Exports the vars to a csv file
Inputs 
	- none
Outputs
	- none
side effects 
	- Exports the vars to a csv file
*/
function exportParamaterList()
{
	var pList = "Name,Type,Value\n";
	for (const [key, value] of Object.entries(translationTable)) {
		if(value[2] != "table")
		{
			var jInfo = getJSON(key);
			var valueText = jInfo["baseText"];
			if(jInfo["type"] == "equation" || jInfo["type"] == "answer")
			{				
				valueText = jInfo["rawEquation"];
			}
			pList += '"' + value[0] + '","' + value[2] + '","' + valueText + '"\n';
		}
	}
	pList = pList.substring(0,pList.length - 1);
	//https://stackoverflow.com/questions/609530/download-textarea-contents-as-a-file-using-only-javascript-no-server-side
	var textFileAsBlob = new Blob([pList], {type:'text/plain'}); 
	var downloadLink = document.createElement("a");
	downloadLink.download = "paramaters.csv";
	downloadLink.innerHTML = "Download File";
	if (window.webkitURL != null)
	{
		downloadLink.href = window.webkitURL.createObjectURL(textFileAsBlob);
	}
	else
	{
		downloadLink.href = window.URL.createObjectURL(textFileAsBlob);
		downloadLink.onclick = destroyClickedElement;
		downloadLink.style.display = "none";
		document.body.appendChild(downloadLink);
	}
	console.log("DOWNLOADING PARMATERS")
	downloadLink.click()

}

function aat()
{
	
	let text = "";

	if (window.getSelection) {
		text = window.getSelection().toString();
		window.getSelection().focusNode.innerHTML == "aaaa"
		console.log(window.getSelection().focusNode);
	} else if (document.selection && document.selection.type != "Control") {
		text = document.selection.createRange().text;
		console.log(document.selection.createRange());
	}
	
	return text;
	
}



function abb()
{
	
}

/* Opens the paramater window
Inputs 
	- none
Outputs
	- none
side effects 
	- Opens the paramater window
*/
function openParamaterWindow()
{
	document.getElementById("newVar").removeAttribute("hidden");
	document.getElementById("typeView").innerHTML = getTypes();					
	openNewVarMenu = true;
}

function checkNewItem()
{
	var typeSelected = document.getElementById("typeView").value;
	if(typeSelected.toLowerCase() == "new type")
	{
		document.getElementById("typeCreateDiv").removeAttribute("hidden");
	}
	else
	{
		document.getElementById("typeCreateDiv").setAttribute("hidden","");
	}
}

/* Changes the selected text with the visual variable
Inputs 
	- none
Outputs
	- none
side effects 
	- Changes the selected text with the visual variable
	- selects the next instance of the selected text
*/
//Finds the selectedValue item and replaces the text with the formated data
function addSelectedItem()
{
	var name = document.getElementById("variableNameHolder").value;
	if(!name.startsWith("$"))
	{
		name = "$" + name;
	}
	if(inDict(translationTable,name))
	{
		var f = false;
		if(name in itemReference)
		{
			itemReference[name] = itemReference[name] + 1;
		}
		else
		{
			f = true;
			itemReference[name] = 1;
		}
		//uy = tClean.substring(0, selectedIndex[currentSelectedIndex]);

		console.log(nameToKey(name));
		document.getElementById("selectedValue").outerHTML = "<span class='"+btoa(nameToKey(name))+"' id='customVar2' style='background-color:" + textBackgroundColorMulti + "'>(" + translationTable[nameToKey(name)][0] + "," + getJSON(nameToKey(name))["baseText"] + "," + translationTable[nameToKey(name)][2] + ")</span><span> </span><span> </span>";

		/*
		if(currentSelectedIndex + 1 <= selectedIndex.length)
		{
			if(!f)
			{
				var jsonObject = getJSON(nameToKey(name));
				//uy += "<cc class='"+btoa(nameToKey(name))+"'" + 'id="customVar" style="background-color:'+ textBackgroundColorMulti +'">' + translationTable[nameToKey(name)][0] + "</cc><span> </span>" + tClean.substring(selectedIndex[currentSelectedIndex] + currentTextSearch.length, tClean.length);
				//uy += " <span class='"+btoa(nameToKey(name))+"' id='customVar2' style='background-color:" + textBackgroundColorMulti + "'>(" + translationTable[nameToKey(name)][0] + "," + j["baseText"] + "," + translationTable[nameToKey(name)][2] + ")</span><span> </span><span> </span>" + tClean.substring(selectedIndex[currentSelectedIndex] + currentTextSearch.length, tClean.length);
				document.getElementById("selectedValue").outerHTML = "<span class='"+btoa(nameToKey(name))+"' id='customVar2' style='background-color:" + textBackgroundColorMulti + "'>(" + translationTable[nameToKey(name)][0] + "," + jsonObject["baseText"] + "," + translationTable[nameToKey(name)][2] + ")</span><span> </span><span> </span>";
				//document.getElementById("selectedText2").outerHTML = "AAAAA";
			}
			else
			{
				var jsonObject = getJSON(nameToKey(name));
				//uy += "<cc class='"+btoa(nameToKey(name))+"'" + 'id="customVar" style="background-color:' + textBackgroundColor + '">' + translationTable[nameToKey(name)][0] + "</cc><span> </span>" + tClean.substring(selectedIndex[currentSelectedIndex] + currentTextSearch.length, tClean.length);
				//uy += " <span class='"+btoa(nameToKey(name))+"' id='customVar2' style='background-color:" + textBackgroundColor + "'>(" + translationTable[nameToKey(name)][0] + "," + j["baseText"] + "," + translationTable[nameToKey(name)][2] + ")</span><span> </span><span> </span>" + tClean.substring(selectedIndex[currentSelectedIndex] + currentTextSearch.length, tClean.length);
				document.getElementById("selectedValue").outerHTML = "<span class='"+btoa(nameToKey(name))+"' id='customVar2' style='background-color:" + textBackgroundColor + "'>(" + translationTable[nameToKey(name)][0] + "," + jsonObject["baseText"] + "," + translationTable[nameToKey(name)][2] + ")</span><span> </span><span> </span>";
				//document.getElementById("selectedText2").outerHTML = "AAAAA2";
			}
			console.log("Added selected Item");
			//document.getElementById("main").innerHTML = uy;
		}
		else
		{
		}*/
		
		setTClean();
		//uy = tClean.substring(0, selectedIndex[currentSelectedIndex]) + currentTextSearch + tClean.substring(selectedIndex[currentSelectedIndex] + currentTextSearch.length, tClean.length);
		//document.getElementById("main").innerHTML = uy;
		
		//setTClean();
		//nextSelectItem();
		searchItems(currentTextSearch,currentTextType);
		if(currentSelectedIndex + 1 >= selectedIndex.length - 1)
		{
			//var y = document.getElementById("selectItemHolder");
			//y.parentNode.removeChild(y);
			//document.getElementById("newVar").setAttribute("hidden","");
			//closeSelectItems();
		}
		else
		{				
			//nextSelectItem();
		}
	}
}

function nextSelectItem()
{
	if(currentSelectedIndex + 1 < selectedIndex.length)
	{
		currentSelectedIndex++;
		showCurrentItem();
	}
	else 
	{
		if(searchSectionSide == 'main')
		{
			searchSectionSide = 'answer';
			searchItems(currentTextSearch,currentTextType,{"start" : searchSectionSide});
		}
	}
}

function previousSelectItem()
{
	if(currentSelectedIndex - 1 >= 0)
	{
		currentSelectedIndex--;
		showCurrentItem()
	}
}

/* Gets the key of a given variable name
Inputs 
	- name of the variable
Outputs
	- gets the key with the name given
side effects 
	- Gets the key of a given variable name
*/
function nameToKey(name)
{
	for (var key in translationTable) 
	{
		if(translationTable[key][TT_VARIABLENAME] == name)
		{
			return key;
		}
	}
	return -1;
}

function removeSumItem(targetID, thisID)
{
	var cleanC = translationTable[thisID][1];
	console.log(cleanC);
	var value = getJSON(thisID);
	var v = value["values"];
	v.splice(targetID, 1);
	value["values"] = v;
	console.log(value);
	translationTable[thisID][1] = translationTable[thisID][1].substring(0,2) + JSON.stringify(value);
	console.log(translationTable[thisID][1]);
}

function addSumItem(targetID, thisID)
{
	var cleanC = translationTable[thisID][1];
	console.log(cleanC);
	var value = getJSON(thisID);
	var v = value["values"];
	v.push(targetID)
	value["values"] = v;
	console.log(value);
	translationTable[thisID][1] = translationTable[thisID][1].substring(0,2) + JSON.stringify(value);
	console.log(translationTable[thisID][1]);
}

function setNumbers(thisID)
{
	var min = document.getElementById("tempMin").value;
	var max = document.getElementById("tempMax").value;
	var value = getJSON(thisID);
	console.log(thisID, min,max);
	if(min != null && max != null)
	{
		value["values"][0] = min;
		value["values"][1] = max;
		translationTable[thisID][1] = translationTable[thisID][1].substring(0,2) + JSON.stringify(value);
		console.log(translationTable[thisID][1]);
	}
}
var lastEquationValue = "";
function translateID(value)
{
	console.log(targetID);
	console.log(translationTable[targetID][0]);
	//retrun translationTable[targetID][0];
}

function getNumbers(id)
{
	var ids = [];
	for (var key in translationTable) 
	{
		if(translationTable[key][TT_VARIABLETYPE] == "number" && key != id)
		{
			ids.push(key);
		}
	}
	return ids;
}

function linkWithId(targetID,thisID, add)
{
	console.log(translationTable[thisID]);
	var value = translationTable[thisID][1].substring(2,translationTable[thisID][1].length);
	console.log(value);
	var v = []
	command = JSON.parse(value);
	if(add)
	{
		if("linkedID" in command)
		{
			v = command["linkedID"];
		}
		v.push(targetID);
	}
	else
	{
		v.push(targetID);
	}
	command["linkedID"] = v;
	translationTable[thisID][1] = translationTable[thisID][1].substring(0,2) + JSON.stringify(command);
	console.log(command);
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

/* Creates a new question id
Inputs 
	- none
Outputs
	- none
side effects 
	- Creates a new question id
*/
//These function load the data from the backend
function newQuestion()
{
	var xhttp = new XMLHttpRequest();
	xhttp.open("GET", "api.php/newQuestionID/", true);				
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

/* Deletes the question
Inputs 
	- none
Outputs
	- none
side effects 
	- Deletes the question
*/
function removeQuestion()
{
	var r = prompt("Are you sure u want to delete this question? (Y,n)");
	if(r == "Y")
	{
		var xhttp = new XMLHttpRequest();
		xhttp.open("GET", "api.php/removeQuestion/" + questionID, true);				
		xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		xhttp.send("");
		xhttp.onreadystatechange = function() 
		{ 
			if (xhttp.readyState == 4 && xhttp.status == 200)
			{
				if(xhttp.responseText.length > 0 )
				{
					jsonRaw = JSON.parse(xhttp.responseText);
					console.log(jsonRaw);
				}
			}
		}
	}
}


//Create the new question using the new questionID from the newQuestion function and then forward the page to the give uri
function submit2(foward)
{
	closeSelectItems();	
	var allText = removeSpecialChars(document.getElementById("main").innerHTML);
	var answerText = removeSpecialChars(document.getElementById("answer").innerHTML);
	console.log("HTML");
	console.log(allText);
	console.log(answerText);
	for (var key in translationTable) 
	{
		Array.from(document.getElementsByClassName(btoa(key))).forEach(element => {
			element.outerHTML = translationTable[key][TT_JSONVALUE];
		});
		/*console.log(translationTable[key]);
		console.log('<span class="'+key+'" id="customVar" style="background-color:' + textBackgroundColor + ';"><u>' + translationTable[key][TT_VARIABLENAME] + '</u></span>',translationTable[key][TT_JSONVALUE]);
		allText = allText.replaceAll('<span class="'+key+'" id="customVar" style="background-color:' + textBackgroundColor + ';"><u>' + translationTable[key][TT_VARIABLENAME] + '</u></span>',translationTable[key][TT_JSONVALUE]);
		allText = allText.replaceAll(translationTable[key][TT_VARIABLENAME],translationTable[key][TT_JSONVALUE]);

		answerText = answerText.replaceAll('<span class="'+key+'" id="customVar" style="background-color:' + textBackgroundColor + ';"><u>' + translationTable[key][TT_VARIABLENAME] + '</u></span>',translationTable[key][TT_JSONVALUE]);
		answerText = answerText.replaceAll(translationTable[key][TT_VARIABLENAME],translationTable[key][TT_JSONVALUE]);*/
	}
	console.log(allText);
	console.log(answerText);
	//allText = allText.replaceAll("<br>","\n");
	//allText = allText.trim();
	allText = encodeURIComponent(allText);
	//answerText = answerText.trim();
	answerText = encodeURIComponent(answerText);
	var xhttp = new XMLHttpRequest();
	xhttp.open("POST", "api.php/updateQuestion/" , true);				
	xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xhttp.send("value="+btoa(allText) + "&answer=" + btoa(answerText) + "&questionID=" + questionID +"&translationTable=" + btoa(JSON.stringify(translationTable)));
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


function t()
{
	var allText = document.getElementById("main").innerText;
	console.log(allText);
	for (var key in translationTable) 
	{
		console.log(translationTable[key]);
		console.log('<span class="'+key+'" id="customVar" style="background-color:' + textBackgroundColor + ';"><u>' + translationTable[key][TT_VARIABLENAME] + '</u></span>',translationTable[key][TT_JSONVALUE]);
		allText = allText.replaceAll('<span class="'+key+'" id="customVar" style="background-color:' + textBackgroundColor + ';"><u>' + translationTable[key][TT_VARIABLENAME] + '</u></span>',translationTable[key][TT_JSONVALUE]);
		allText = allText.replaceAll(translationTable[key][TT_VARIABLENAME],translationTable[key][TT_JSONVALUE]);
	}
	console.log(allText);
	//allText = allText.replaceAll("<br>","\n");
	allText = allText.trim();
}

function antiWrap()
{
	var q = document.getElementById("main").innerText;
	q.replaceAll('<span class="'+key+'" id="customVar" style="background-color:powderblue;"><u><span class="'+key+'" id="customVar" style="background-color:powderblue;"><u>',"");
	
	document.getElementById("main").innerText = q;
	return q;
}
var autoSubmitB = false;
//Auto Save function, well send the data every 5 min
function foo() {
	// your function code here
	submit();
	setTimeout(foo, 5000);
}




//foo();

function fixTables()
{
	for (var key in translationTable) 
	{
		var jsonInfo = getJSON(key);
		console.log(translationTable[key][2]);
		if(translationTable[key][TT_VARIABLETYPE] == "table")
		{
			console.log(key,jsonInfo);
			try {
				var item = document.getElementById(jsonInfo["currentID"]);
				console.log(parentN.id);
				if(item.parentNode.id != "tableholderdiv")
				{
					console.log("ADDING HOLDER");
					console.log(item);
					let div = document.createElement("div");
					item.outerHTML = "<div class='tableholderdiv'>" + item.outerHTML + "</div>";

					addRowButton = document.createElement("button");
					addRowButton.setAttribute("onclick","addRow('"+element.getAttribute("id")+"',false)");
					addRowButton.setAttribute("class","btn submitRemove");
					addRowButton.innerHTML = "Add Row";

					addSectionButton = document.createElement("button");
					addSectionButton.setAttribute("onclick","addSection('"+element.getAttribute("id")+"')");
					addSectionButton.setAttribute("class","btn submitRemove");
					addSectionButton.innerHTML = "Add Section";

					removeTableButton = document.createElement("button");
					removeTableButton.setAttribute("onclick","removeTable('"+element.getAttribute("id")+"')");
					removeTableButton.setAttribute("class","btn submitRemove");
					removeTableButton.innerHTML = "Remove Table";

					item.parentNode.appendChild(addRowButton);
					item.parentNode.appendChild(addSectionButton);
					item.parentNode.appendChild(removeTableButton);

					//div.appendChild(document.getElementById(jsonInfo["currentID"]));
				}
			} catch (error) {
				console.log("TABLE NO MORE");
			}			
		}
	}

	Array.from(document.getElementsByTagName("table")).forEach(element => {
		if(!element.hasAttribute("id"))
		{
			translationTable[currentID] = ['$','%var{"values":[-1,-1],"currentID":'+currentID+',"varName":"$"}',"table"];
			element.setAttribute("id", currentID);
			element.setAttribute("class", "baseTable table-hover table-bordered " + currentID);
			console.log(element.parentNode);
			currentID++;
			try {
				var item = document.getElementById(jsonInfo["currentID"]);
				console.log(parentN.id);
				if(item.parentNode.id != "tableholderdiv")
				{
					console.log("ADDING HOLDER");
					console.log(item);
					let div = document.createElement("div");
					item.outerHTML = "<div class='tableholderdiv'>" + item.outerHTML + "</div>";
					//div.appendChild(document.getElementById(jsonInfo["currentID"]));
				}
			} catch (error) {
				console.log("TABLE NO MORE");
			}	
			
			applyDefaultTableFormat(element);
		}
	});

	Array.from(document.getElementsByTagName("baseTable")).forEach(element => {
		if(!element.hasAttribute("id"))
		{
			translationTable[currentID] = ['$','%var{"values":[-1,-1],"currentID":'+currentID+',"varName":"$"}',"table"];
			element.setAttribute("id", currentID);
			element.setAttribute("class", "baseTable table-hover table-bordered " + currentID);
			console.log(element.parentNode);
			currentID++;
			try {
				var item = document.getElementById(jsonInfo["currentID"]);
				console.log(parentN.id);
				if(item.parentNode.id != "tableholderdiv")
				{
					console.log("ADDING HOLDER");
					console.log(item);
					let div = document.createElement("div");
					item.outerHTML = "<div class='tableholderdiv'>" + item.outerHTML + "</div>";
					//div.appendChild(document.getElementById(jsonInfo["currentID"]));
				}
			} catch (error) {
				console.log("TABLE NO MORE");
			}	
			
		applyDefaultTableFormat(element);
		}
	});

	

}

function allSections()
{
	const sectionsData = [];

	// Get all sections with class "aa"
	const targetSections = document.querySelectorAll('div.sectionDiv');
	
	targetSections.forEach((targetSection, index) => {
		// Get content before the current target section
		const contentBefore = [];
		let currentNode = targetSection.previousElementSibling;

		while (currentNode) {
			contentBefore.unshift(currentNode.outerHTML); // Prepend HTML content to the array
			currentNode = currentNode.previousElementSibling;
		}

		// Get content after the current target section
		const contentAfter = [];
		let nextNode = targetSection.nextElementSibling;

		while (nextNode) {
			contentAfter.push(nextNode.outerHTML); // Append HTML content to the array
			nextNode = nextNode.nextElementSibling;
		}

		// Join the collected HTML into strings
		const beforeHtml = contentBefore.join('');
		const afterHtml = contentAfter.join('');

		// Store the result in the array
		sectionsData.push({
			sectionIndex: index + 1,   // Optional: section index (1-based)
			before: beforeHtml,
			after: afterHtml
		});
	});

	// Log the array with the results
	console.log(sectionsData);
}


function findSections()
{
	document.getElementById("main").innerHTML = document.getElementById("main").innerHTML.replaceAll("%section", sectionInfo);
	document.getElementById("answer").innerHTML = document.getElementById("answer").innerHTML.replaceAll("%section", sectionInfo);

	var allSections = {};
	var sectionsRaw = document.getElementById("main").innerHTML.split(sectionInfo);
	var sections = [];
	var sections2 = [];

	sectionsRaw.forEach(element => {
		if(element.startsWith("</div>"))
		{
			element = element.substring(6);
		}
		sections.push(element);
	});

	var sectionsRaw2 = document.getElementById("answer").innerHTML.split(sectionInfo);
	sectionsRaw2.forEach(element => {
		if(element.startsWith("</div>"))
		{
			element = element.substring(6);
		}
		sections2.push(element);
	});
	
	allSections["main"] = sections;
	allSections["answer"] = sections2;

	console.log(allSections);
}

function removeSection(item)
{
	console.log(item.parentNode);
	item.parentNode.remove();
}


//Push the new version of the question text, answer and translationTable to the DB 
function submit()
{	
	closeSelectItems();
	var error = false;
	fixTables();
	for (var key in translationTable) 
	{
		console.log(key);
		var jsonInfo = getJSON(key);
		if(translationTable[key][0].startsWith("$") == false)
		{			
			translationTable[key][0] = "$" + translationTable[key][0];
		}

		if(jsonInfo['type'] == "document" && jsonInfo.hasOwnProperty("linkID"))
		{			
			var tableID = jsonInfo['linkID'];
			console.log("BUILDING LINKING FOR",tableID);
			changeJSONValue(jsonInfo['currentID'], "baseText", tableToDocumentGen(tableID,true));
		}

		if(jsonInfo["type"] != "table")
		{
			addToJSON(key,"varName", translationTable[key][0]);
		}

	
		if(jsonInfo["type"] == "answer" || jsonInfo['type'] == "equation")
		{
			if(jsonInfo['rawEquation'] == "None" || jsonInfo['rawEquation'] == null)
			{
				messageCreate("And equation was not set please set", "ERROR");
				break;
			}
		}
	}
	if(!error)
	{
		var allText = removeSpecialChars(document.getElementById("main").innerHTML);

		c = 0
		/*
		allText.split("<br>").forEach(element => {
			if(element == "")
			{
				c++;
			}
			else
			{
				document.getElementById("main").innerHTML = allText.split("<br>").slice(c).join("<br>");
			}
			console.log(element);
		});
*/






		var answerText = removeSpecialChars(document.getElementById("answer").innerHTML);//.replace(/<\!--.*?-->/g, "");
		var cleanAllText = allText.replaceAll("","").replaceAll("","").replaceAll("Â","").replaceAll("Ã","");
		var cleanAnswerText = answerText.replaceAll("","").replaceAll("","").replaceAll("Â","").replaceAll("Ã","");
		Array.from(document.getElementsByClassName("submitRemove")).forEach(element => {
			element.outerHTML = "";
		});
		for (var key in translationTable) 
		{
			if(translationTable[key][TT_VARIABLETYPE] != "table")
			{
				Array.from(document.getElementsByClassName(btoa(key))).forEach(element => {
					element.outerHTML = translationTable[key][TT_JSONVALUE];
				});
			}
		}
		answerText = encodeURIComponent(document.getElementById("answer").innerHTML).replaceAll("","").replaceAll("","").replaceAll("Â","").replaceAll("Ã","");
		var answerTextC = answerText;
		console.log(allText);
		console.log(answerText);
		allText = encodeURIComponent(document.getElementById("main").innerHTML).replaceAll("","").replaceAll("","").replaceAll("Â","").replaceAll("Ã","").replaceAll("%E2%80%8B","");
		document.getElementById("main").innerHTML = cleanAllText;
		document.getElementById("answer").innerHTML = cleanAnswerText;
		answerText = (answerText).replaceAll("%E2%80%8B","");
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
					
				}
			}
		}
	}
}

//Added functions for the menu caller above
function addNewNumber(target)
{
	var id = "main";
	if(target == "right")
	{
		id = "answer";
	}
	var text = selectedText;//getSelectionText();
	text = text.trim();
	/*if(!inDict(translationTable, text))
	{
		console.log(text);
		translationTable[currentID] = [text,'%n{"values" : [0,0],"currentID": '+currentID+'}',"number"];
		console.log(currentID,translationTable);
		tClean = tClean.replaceAll(text,'%n{"values" : [0,0],"currentID": '+currentID+'}');
		console.log(tClean);
		companyAmount++;
		currentID++;
		for (var key in translationTable) 
		{
			tClean = tClean.replaceAll(translationTable[key][TT_JSONVALUE],"<span class='customVar' id='"+key+"' style='background-color:' + textBackgroundColor + ';'><u>" + translationTable[key][TT_VARIABLENAME] + "</u></span>");
		};
		document.getElementById("main").innerHTML = tClean;
	}*/
	console.log("STARTING SEARCH");
	searchItems(text, "number",JSON.parse('{"newT":true}'));
	tableCreate();
}

function addNewSumNumber(target)
{
	var id = "main";
	if(target == "right")
	{
		id = "answer";
	}
	var text = selectedText;//getSelectionText();
	text = text.trim();
	if(!inDict(translationTable, text))
	{
		console.log(text);
		translationTable[currentID] = [text,'%s{"values" : [],"currentID": '+currentID+'}',"sum"];
		console.log(currentID);
		tClean = tClean.replaceAll(text,'%s{"values" : [],"currentID": '+currentID+'}');
		console.log(tClean);
		companyAmount++;
		currentID++;
		for (var key in translationTable) 
		{
			tClean = tClean.replaceAll(translationTable[key][TT_JSONVALUE],"<span class='customVar' id='"+key+"' style='background-color:" + textBackgroundColor + ";'><u>" + translationTable[key][TT_VARIABLENAME] + "</u></span>");
		};
		document.getElementById(id).innerHTML = tClean;
	}
	tableCreate();
}

function addNewOperator(target)
{
	var id = "main";
	if(target == "right")
	{
		id = "answer";
	}
	var text = selectedText;//getSelectionText();
	text = text.trim();
	searchItems(text, "operator",JSON.parse('{"newT":true}'));
	tableCreate();
}

function addNewAnswer(target)
{
	var id = "main";
	if(target == "right")
	{
		id = "answer";
	}
	var text = selectedText;
	text = text.trim();
	searchItems(text, "answer",JSON.parse('{"newT":true}'));
	/*
	if(!inDict(translationTable, text))
	{
		console.log(text);
		translationTable[currentID] = [text,'%a{"values" : [],"currentID": '+currentID+'}',"answer"];
		console.log(currentID);
		tClean = tClean.replaceAll(text,'%a{"values" : [],"currentID": '+currentID+'}');
		console.log(tClean);
		companyAmount++;
		currentID++;
		for (var key in translationTable) 
		{
			tClean = tClean.replaceAll(translationTable[key][TT_JSONVALUE],"<span class='customVar' id='"+key+"' style='background-color:" + textBackgroundColor + ";'><u>" + translationTable[key][TT_VARIABLENAME] + "</u></span>");
		};
		document.getElementById(id).innerHTML = tClean;
	}
	*/
	tableCreate();
}

function addNewEquation(target)
{
	var id = "main";
	if(target == "right")
	{
		id = "answer";
	}
	var text = selectedText;
	text = "$" + text;
	text = text.trim();
	//searchItems(text, "equation",JSON.parse('{"newT":true}'));
	//tableCreate();
	if(!inDict(translationTable, text))
	{
		tClean = document.getElementById("main").innerHTML;
		console.log(text);
		translationTable[currentID] = [text,'%e{"type": "equation", "values" : [],"currentID": '+currentID+'}',"equation"];
		console.log(currentID);
		console.log(translationTable);
		var j = getJSON(currentID);
		tClean = tClean.replaceAll(selectedText.trim(),"<span class='"+btoa(currentID)+"' id='customVar' style='background-color:" + textBackgroundColorMulti + "'>(" + translationTable[currentID][0] + "," + j["baseText"] + "," + translationTable[currentID][2] + ")</span><span> </span><span> </span>");
		document.getElementById("main").innerHTML = tClean;		
		
		//document.getElementById(currentID).outerHTML = "<span class='"+btoa(currentID)+"' id='customVar2' style='background-color:" + textBackgroundColorMulti + "'>(" + translationTable[currentID][0] + "," + j["baseText"] + "," + translationTable[currentID][2] + ")</span><span> </span><span> </span>";

		console.log("Added Equation");
		companyAmount++;
		currentID++;				
		document.getElementById("main").innerHTML = tClean;			
		console.log(tClean);
	}
	tableCreate();
}

function addNewCompany(target)
{
	var id = "main";
	if(target == "right")
	{
		id = "answer";
	}
	var text = selectedText;//getSelectionText();
	text = text.trim();
	/*if(!inDict(translationTable, text))
	{
		console.log(text);
		translationTable[currentID] = [text,'%c{"values" : ['+companyAmount+'],"currentID": '+currentID+'}',"string"];
		console.log(currentID);
		tClean = tClean.replaceAll(text,'%c{"values" : ['+companyAmount+'],"currentID": '+currentID+'}');
		answer = answer.replaceAll(text,'%c{"values" : ['+companyAmount+'],"currentID": '+currentID+'}');
		console.log(tClean);
		companyAmount++;
		currentID++;
		for (var key in translationTable) 
		{
			tClean = tClean.replaceAll(translationTable[key][TT_JSONVALUE],"<span class='customVar' id='"+key+"' style='background-color:" + textBackgroundColor + ";'><u>" + translationTable[key][TT_VARIABLENAME] + "</u></span><div> </div>");
			answer = answer.replaceAll(translationTable[key][TT_JSONVALUE],"<span class='customVar' id='"+key+"' style='background-color:" + textBackgroundColor + ";'><u>" + translationTable[key][TT_VARIABLENAME] + "</u></span><div> </div>");
		};
		console.log(target);
		if(id == "main")
		{
			document.getElementById("main").innerHTML = tClean;
		}
		else
		{
			document.getElementById("answer").innerHTML = answer;
		}
	}*/
	searchItems(text, "company",JSON.parse('{"newT":true}'));
	tableCreate();
}

function getSelectionText() {
	var html = "";
	var sel = "";
	if (typeof window.getSelection != "undefined") {
		sel = window.getSelection();
		console.log(sel);
		if (sel.rangeCount) {
			var container = document.createElement("div");
			console.log("RANGE: " + sel.rangeCount);
			for (var i = 0, len = sel.rangeCount; i < len; ++i) {
				console.log(sel.getRangeAt(i));
				console.log(i);
				container.appendChild(sel.getRangeAt(i).cloneContents());
			}
			html = container.innerHTML;
		}
	} else if (typeof document.selection != "undefined") {
		if (document.selection.type == "Text") {
			html = document.selection.createRange().htmlText;
		}
	}
	return [html, sel];
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

function loadData(text2, out=false)
{
	var d = JSON.parse(atob(text2));
	var text = decodeURI(atob(d["text"]));
	var answer = decodeURI(atob(d["answer"]));
	var translationT = atob(d["translationTable"]);
	console.log(text);
	if(out == false)
	{
		document.getElementById("main").innerHTML = text;
		document.getElementById("answer").innerHTML = answer;
		translationTable = JSON.parse(translationT);
		for (const [key, value] of Object.entries(translationTable)) {
			propogateChangeText(key);
		}
		tableCreate();
	}
	else{
		return d;
	}
}


function setTClean()
{
	document.getElementById("saveButton").setAttribute("class","btn btn-outline-success my-2 my-sm-0");
	tClean = document.getElementById("main").innerHTML;
	
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
