//7/08/2024 new file
// add file describtion and what is should do and progress in other functions

const answerCorrectColour = 'lime';
const answerIncorrectColour = 'red';


//25/07/2024 Added
// change name to be more describtion
function inputChanged(id)
{
	console.log(id);
	if (document.getElementById(id).value != "" != null && document.getElementById(id).value != "")
	{
		document.getElementById(id).style.backgroundColor = '';
	} else {
		document.getElementById(id).style.backgroundColor = 'yellow';
	}

	if(document.getElementById(id).value.startsWith == "=")
	{

	}
}

function saveQuickCode()
{
	navigator.clipboard.writeText("https://bslcs.com/session/?code=" + shortCode);
}


var isQRVisable = false;
function showQRCode()
{
	try {
		document.getElementById("chartWindow").outerHTML = "";
	} catch (error) {
		
	}
	if(isQRVisable == true)
	{
		
	}
	else
	{
		var chartWindow = document.createElement("div");
		chartWindow.innerHTML = `<div class="window-top">
				<span id="chatTitle"> QR code | `+shortCode+` </span>
			</div>
			<div id="qr">
			</div>`;
		chartWindow.setAttribute("id","chartWindow");
		chartWindow.setAttribute("class","window");
		chartWindow.setAttribute("style","width:auto; height:auto; left:40%; top: 30%");
		document.body.appendChild(chartWindow);
		new QRCode(document.getElementById("qr"), "https://bslcs.com/session/?code=" + shortCode); 
	}
	isQRVisable = !isQRVisable;

}

var playAnimations = true;
var currentAnimationID = 0;
var animation; 

function animationFinder()
{
	animation = new Animation();
	animation.find();	
	//animation.next();
	console.log(animation.current);
	Array.from(animation.items).forEach(element => {		
		var type = element.getAttribute("animationtype");
		console.log(type);
		if(animation.amimationTypes[type] == "hide")
		{
			element.setAttribute("hidden","");
		}
		else
		{
			element.removeAttribute("hidden");
		}
		console.log(animation.amimationTypes[type])
	});
}

function animationNext()
{
	console.log("ANIMATION NEXT");
	Array.from(animation.current).forEach(element => {		
		var type = element.getAttribute("animationtype");
		console.log(type);
		if(animation.amimationTypes[type] == "hide")
		{
			element.removeAttribute("hidden");
		}
		else
		{
			element.setAttribute("hidden","");
		}
		console.log(animation.amimationTypes[type])
	});
	animation.next;
}


function nextQuestion()
{
	if(window.location.href.includes("sessionIndex=" + sessionIndex))
	{
		window.location.href = window.location.href.replaceAll("sessionIndex=" + sessionIndex,"sessionIndex="+(sessionIndex + 1)); 
	}
	else
	{
		window.location.href += "&sessionIndex=" + (sessionIndex + 1); 
	}
}

function previousQuestion()
{
	if(sessionIndex - 1 >= 0)
	{
		if(window.location.href.includes("sessionIndex=" + sessionIndex))
		{
			window.location.href = window.location.href.replaceAll("sessionIndex=" + sessionIndex,"sessionIndex="+(sessionIndex - 1)); 
		}
		else
		{
			window.location.href += "&sessionIndex=" + (sessionIndex - 1); 
		}
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
function addRow(id)
{
	var table = document.getElementById(id);
	var cols = table.childNodes[0].childNodes[0].cells.length;
	console.log(table.rows.length);
	var rowLength = table.rows.length;
	var row = table.insertRow(table.rows.length);
	row.setAttribute("rowIndex",table.rows.length);
	var sectionInfo = JSON.parse(document.getElementById(id).getAttribute("sectioninfo"));
	for (var x = 0; x < table.getAttribute("colsamount") ; x++)
	{
		var rowInstance = row.insertCell(x);
		var buttonAdder = document.createElement("button");
		buttonAdder.innerHTML = "+";
		buttonAdder.setAttribute("class","btn");
		
		console.log(sectionInfo)
		console.log("col", x);
		console.log(rowLength);
		var s = getNearestSection(id, rowLength);
		console.log(sectionInfo[s.textContent]);
		console.log(s);
		var sectionItems = sectionInfo[s.textContent];
		if(x in sectionItems)
		{
			console.log(sectionItems[x]);
			if(sectionItems[x].includes("set:"))
			{
				var span = document.createElement("span");
				var item = JSON.parse(sectionItems[x].replace("set:",""))[rowLength];
				if(item != null)
				{
					span.innerHTML = JSON.parse(sectionItems[x].replace("set:",""))[rowLength];
					rowInstance.appendChild(span);
				}
				else
				{
					break;
				}
			}
			else
			{				
				buttonAdder.setAttribute("onclick","addInput(" + id + ",[" + rowLength + "," + x + "])");
				rowInstance.appendChild(buttonAdder);
			}
		}
		else
		{
			
			buttonAdder.setAttribute("onclick","addInput(" + id + ",[" + rowLength + "," + x + "])");
			rowInstance.appendChild(buttonAdder);
		}
	}
}


function getNearestSection(tableId, rowIndex)
{
	var table = document.getElementById(tableId);
	var rowLength = table.rows.length;
	var currentSection = null;
	for (row = 0; row < rowLength; row++) {
		console.log(table.rows.item(row), row, rowIndex);
		if (table.rows.item(row).hasAttribute('section')) {
			currentSection = table.rows.item(row);
		} 
		if(row == rowIndex)
		{
			return currentSection;
		}
	}
}

/* Add a input text field in a cell
Inputs 
	- id of the table
	- post: cell cords (x,y)
Outputs
	- none
side effects 
	- Add a input text field in a cell
*/
function addInput(id, pos)
{
	console.log(id, pos);
	var s = getNearestSection(id, pos[0]);
	var sectionInfo = JSON.parse(document.getElementById(id).getAttribute("sectioninfo"));
	console.log(s.textContent);
	console.log(sectionInfo)
	var sectionItems = sectionInfo[s.textContent];
	console.log("sectionItems",sectionItems);
	var table = document.getElementById(id);
	var cols = table.childNodes[0].childNodes[0].cells.length;
	
	console.log(pos);
	console.log(pos[0],pos[1]);
	console.log(table.childNodes[0].childNodes[pos[0]].cells[pos[1]]);
	console.log(table.rows.length);
	console.log(table.childNodes[0].childNodes[pos[0]].cells[pos[1]].childNodes.length, Number(table.getAttribute("maxSubCellRows")),table.childNodes[0].childNodes[pos[0]].cells[pos[1]].childNodes.length < Number(table.getAttribute("maxSubCellRows")) + 1);
	if(table.childNodes[0].childNodes[pos[0]].cells[pos[1]].childNodes.length < Number(table.getAttribute("maxSubCellRows")) + 1)
	{
		var i = document.createElement("input");
		if(pos[1] in sectionItems)
		{
			if(sectionItems[pos[1]].includes("set:"))
			{

			}
			else
			{
				var items = JSON.parse(sectionItems[pos[1]]);
				console.log("TYPE",typeof (items));
				if(typeof (items) == "object")
				{
					console.log(items);
					i = document.createElement("select");
					items.forEach(element => {
						let o = document.createElement("option");
						o.innerHTML = element;
						i.appendChild(o);
					});
				}
			}
		}
		table.childNodes[0].childNodes[pos[0]].cells[pos[1]].appendChild(i);
		if(table.childNodes[0].childNodes[pos[0]].cells[pos[1]].childNodes.length >= Number(table.getAttribute("maxSubCellRows")) + 1)
		{
			table.childNodes[0].childNodes[pos[0]].cells[pos[1]].childNodes[0].outerHTML = "";
		}
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
function addSectionRow(id)
{
	var table = document.getElementById(id);
	var cols = table.childNodes[0].childNodes[0].cells.length;
	console.log(table.rows.length);
	var row = table.insertRow(table.rows.length);
	row.setAttribute("rowIndex",table.rows.length);
	row.setAttribute("section","");
	var selectText = "<select>";
	for(var i = 0; i < JSON.parse(table.getAttribute("sectionnames")).length; i++)
	{
		selectText += "<option>" + JSON.parse(table.getAttribute("sectionnames"))[i] + "</option>";
	}
	selectText += "</select>";


	for (var x = 0; x < 1 ; x++)
	{
		var rowInstance = row.insertCell(x);
		rowInstance.setAttribute("colspan",table.getAttribute("colsamount"));
		rowInstance.setAttribute("style", "text-align:center");
		rowInstance.innerHTML = selectText;
	}
}


/*
Checks the value of a element
Inputs
	- id: id of element
Outputs 
	- None
*/
function checkValue(id)
{
	if(sessionID == null || sessionID == -1)
	{
		var awnser = {};
		awnser[id] = document.getElementById(id).value;
		console.log(awnser[id]);
		if(awnser[id] != "")
		{
			var adder = "";
			if (urlParams.get('instanceId') != null)
			{
				instanceId = urlParams.get('instanceId');
			}
			if (urlParams.get('randomlock') != null)
			{
				adder = "&randomlock"
			}

			var xhttp = new XMLHttpRequest();
			if(sessionID == -1)
			{
				xhttp.open("GET", "api.php/checkValue/?questionID=" + questionID + "&elementId="+id+"&instanceId=" + instanceId + "&answer=" + 
					btoa(JSON.stringify(awnser)) + adder, true);		
			}	
			else
			{
				xhttp.open("GET", "api.php/checkValue/?sessionID=" + sessionID + "&elementId="+id+ "&instanceId=" + instanceId + "&answer=" + 
					btoa(JSON.stringify(awnser)) + "&sessionIndex=" + sessionIndex + adder, true);	
			}	
			xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
			xhttp.send("");
			xhttp.onreadystatechange = function()
			{ 
				if (xhttp.readyState == 4 && xhttp.status == 200)
				{
					console.log(xhttp.responseText);
					if(xhttp.responseText.length > 0 )
					{
						jsonRaw = JSON.parse(xhttp.responseText);
						console.log(jsonRaw["rAnswer"]);
						for (var key in jsonRaw["rAnswer"]) 
						{
							try {								
								document.getElementById(key + "-" + "text").outerHTML = "";
							} catch (error) {
								
							}
							if (jsonRaw["rAnswer"][key] === true)
							{							
								document.getElementById(key).style.backgroundColor = answerCorrectColour;
								var cSpan = document.createElement("span");
								console.log("KEY", key);
								if(document.getElementById(key).value.startsWith("="))
								{
									console.log("FOUND = IN VALUE");
									cSpan.setAttribute("id",key + "-" + "text");
									cSpan.setAttribute("style","display: block; margin-top: 10px;background-color: yellow");
									cSpan.innerHTML = jsonRaw["rAnswerT"][key];
									document.getElementById(key).parentElement.appendChild(cSpan);		
								}											
							} else {
								document.getElementById(key).style.backgroundColor = answerIncorrectColour;				
							}					
							console.log(document.getElementById(key));
							console.log(jsonRaw["rAnswer"][key]);
						}			
					}
				}
				else if (xhttp.readyState == 4 && xhttp.status == 202)
				{
					messageCreate("Info", "This question doesn't require an awnser");
				}
			}
		}
	}
	else
	{
		checkValues();
	}
}


//Change 26/07/2024 3:45pm
function checkValues()
{

	var answer = document.getElementsByClassName("answer");
	var values = {};
	var adder = "";
	if (urlParams.get('instanceId') != null)
	{
		instanceId = urlParams.get('instanceId');
	}
	if (urlParams.get('randomlock') != null)
	{
		adder = "&randomlock"
	}

	Array.from(document.getElementsByClassName('answer')).forEach(element => {
		// check attritube x
		if(element.hasAttribute("x"))
		{
			values[element.getAttribute("id")] = element.value;
			console.log(values[element.getAttribute("id")], element.getAttribute("id"));
		}
	});

	console.log(JSON.stringify(values));
	if(sessionID == -1)
	{
		console.log("api.php/checkValues/?questionID=" + questionID + "&instanceId=" + instanceId + "&answer=" + 
			btoa(JSON.stringify(values)) + adder);
		var xhttp = new XMLHttpRequest();
		xhttp.open("GET", "api.php/checkValues/?questionID=" + questionID + "&instanceId=" + instanceId + "&answer=" + 
			btoa(JSON.stringify(values)) + adder, true);			
	}
	else
	{
		console.log("api.php/checkValues/?sessionID=" + sessionID + "&instanceId=" + instanceId + "&answer=" + 
			btoa(JSON.stringify(values)) + adder);
		var xhttp = new XMLHttpRequest();
		xhttp.open("GET", "api.php/checkValues/?sessionID=" + sessionID + "&instanceId=" + instanceId + "&answer=" + 
			btoa(JSON.stringify(values)) + "&sessionIndex=" + sessionIndex + adder, true);	
	}	
	xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xhttp.send("");
	xhttp.onreadystatechange = function()
	{ 
		if (xhttp.readyState == 4 && xhttp.status == 200)
		{
			console.log(xhttp.responseText);
			if(xhttp.responseText.length > 0 )
			{
				jsonRaw = JSON.parse(xhttp.responseText);
				for (var key in jsonRaw["rAnswer"]) 
				{
					if(jsonRaw["providedAnswer"][key] != "")
					{
						try {								
							document.getElementById(key + "-" + "text").outerHTML = "";
						} catch (error) {
							
						}
						console.log(key, jsonRaw["rAnswer"][key], jsonRaw["providedAnswer"][key]);
						if (jsonRaw["rAnswer"][key] === true)
						{							
							var cSpan = document.createElement("span");
							document.getElementById(key).style.backgroundColor = answerCorrectColour;
							if(document.getElementById(key).value.startsWith("="))
							{
								console.log("FOUND = IN VALUE");
								cSpan.setAttribute("id",key + "-" + "text");
								cSpan.setAttribute("style","display: block; margin-top: 10px;background-color: yellow");
								cSpan.innerHTML = jsonRaw["rAnswerT"][key];
								document.getElementById(key).parentElement.appendChild(cSpan);		
							}	
													
						} else {
							document.getElementById(key).style.backgroundColor = answerIncorrectColour;				
						}	
					}				
					console.log(document.getElementById(key));
					console.log(jsonRaw["rAnswer"][key]);
				}		
			}
		}
		else if (xhttp.readyState == 4 && xhttp.status == 202)
		{
			messageCreate("Info", "This question doesn't require an awnser");
		}
	}
}

sectionInfo = '<div class="sectionDiv" contenteditable="false"> <hr class="section">  </div>';
var allSections = {};

var showSlides = false;
function toggleSplitView()
{
	showSlides = !showSlides;
	if(showSlides)
	{
		document.getElementById("slideOrAll").innerText = "Document View";
		document.getElementById("slideOrAll").setAttribute("class","btn btn-primary");
		splitSection();
	}	
	else
	{		
		document.getElementById("slideOrAll").setAttribute("class","btn btn-secondary");
		document.getElementById("slideOrAll").innerText = "Slide View";
		hideSlideView();
	}
}

function hideSlideView()
{
	document.getElementById("main").removeAttribute("hidden","");
	document.getElementById("answer").removeAttribute("hidden","");

	Array.from(document.getElementsByClassName("slides")).forEach(element => {
		element.setAttribute("hidden","");
	});
	
}

function splitSection()
{
	var mainText = document.getElementById("main").innerHTML;
	var answerText = document.getElementById("answer").innerHTML;

	var sectionsRaw = mainText.split(sectionInfo);
	var sections = [];
	var sections2 = [];

	sectionsRaw.forEach(element => {
		if(element.startsWith("</div>"))
		{
			element = element.substring(6);
		}
		sections.push(element);
	});

	var sectionsRaw2 = answerText.split(sectionInfo);
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
	document.getElementById("main").setAttribute("hidden","");
	document.getElementById("answer").setAttribute("hidden","");

	Array.from(document.getElementsByClassName("slides")).forEach(element => {
		element.removeAttribute("hidden");
		element.setAttribute("style","height:50%");
	});
	reloadSlides();
}

var slideIndexMain = 0;
var slideIndexAnswer = 0;
function nextSlideMain()
{
	if(slideIndexMain < allSections["main"].length - 1)
	{
		slideIndexMain ++;
	}
	reloadSlides();
}

function nextSlideAnswer()
{	
	if(slideIndexAnswer < allSections["answer"].length - 1)
	{
		slideIndexAnswer ++;
	}
	reloadSlides();
}

function previousSlideAnswer()
{	
	if(slideIndexAnswer - 1 >= 0)
	{
		slideIndexAnswer --;
	}
	reloadSlides();
}

function previousSlideMain()
{	
	if(slideIndexMain - 1 >= 0)
	{
		slideIndexMain --;
	}
	reloadSlides();
}

function reloadSlides()
{
	document.getElementById("mainSlidesContent").innerHTML = allSections["main"][slideIndexMain];
	document.getElementById("mainSlidesOptions").innerHTML = "<button class='btn btn-light' onclick=previousSlideMain()> Previous Slide </button>";
	document.getElementById("mainSlidesOptions").innerHTML += "<button class='btn btn-light' onclick=nextSlideMain()> Next Slide </button>";
	
	document.getElementById("answerSlidesContent").innerHTML = allSections["answer"][slideIndexAnswer];
	document.getElementById("answerSlidesOptions").innerHTML = "<button class='btn btn-light' onclick=previousSlideAnswer()> Previous Slide </button>";
	document.getElementById("answerSlidesOptions").innerHTML += "<button class='btn btn-light' onclick=nextSlideAnswer()> Next Slide </button>";
}



/*
interface table {
	id : number
	sectionList : sections
}

interface section {
	name : string;
	rows : 
}
*/


//Change 26/07/2024 3:45pm
function checkSelfTables()
{
	var tableList = []; 
	Array.from(document.getElementsByClassName("self-table-answer")).forEach(element => {
		var rowLength = element.rows.length;
		tableList.push({id : element.id, sectionList : []});
		currentTable = tableList.find((table) => table.id == element.id);
		table = currentTable
		var sections = [];
		for (row = 0; row < rowLength; row++) {
			var cells = element.rows.item(row).cells;
			if (element.rows.item(row).hasAttribute('section')) {
				table.sectionList.push({name: cells.item(0).innerHTML, rows : []});
				
			} else {
				cellVals = [];
				for (i = 0; i < cells.length; i++) {
					cellVals.push(cells.item(i).innerHTML);
				}
				table.sectionList.at(table.sectionList.length - 1).rows.push(cellVals);
			}
		}
	});

	return tableList;
}


