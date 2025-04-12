var textBackgroundColor = "yellow";//powderblue
var textBackgroundColorMulti = "green";
var answer = "";

// used by questions.php
var editing = false;
var tClean = "";
var jsonRaw = {};
var translationTable = {};
var currentID = 0;
var firstColor = null;
var multiColor = null;
var inDevMenu = false;
var firstPullDevData = false;
const colours = [ "#2ECC40","#0074D9", "#FF4136", "#FF851B", "#7FDBFF", "#B10DC9", "#FFDC00", "#001f3f", "#39CCCC", 
	"#01FF70", "#85144b", "#F012BE", "#3D9970", "#111111", "#AAAAAA"];


function openProfile()
{
	location.href = "profile.php"; 
}

async function loadQuestionResults()
{
	var xhttp = new XMLHttpRequest();
	xhttp.open("GET", "api.php/getQuestionResults/?questionID="+questionID, true);				
	xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xhttp.send("");
	xhttp.onreadystatechange = function() 
	{ 
		if (xhttp.readyState == 4 && xhttp.status == 200)
		{
			if(xhttp.responseText.length > 0 )
			{
				var jsonData = JSON.parse(xhttp.responseText);
				var usersAttempted = [];
				var timesAttempted = [];
				var namesTimeBased = [];
				//console.log(xhttp.responseText);
				for(var submission in jsonData["results"])
				{
					if(usersAttempted.includes(jsonData["results"][submission]["userID"]))
					{
						timesAttempted[usersAttempted.indexOf(jsonData["results"][submission]["userID"])]++;
					} else {
						namesTimeBased.push(jsonData["results"][submission]["name"]);
						usersAttempted.push(jsonData["results"][submission]["userID"]);
						timesAttempted.push(1);
					}
				}
				console.log(usersAttempted, timesAttempted);
				
				//var jsonRaw = JSON.parse(xhttp2.responseText);
				//var barColors = ["red", "green","blue","orange","brown"];
				/*
				new Chart("total", {
					type: "pie",
					data: {
						labels: namesTimeBased,
						datasets: [{
						data: yValuesTotal,
            			backgroundColor: colours
						}]
					},
					options: {
						title: {
						display: true,
						text: "Total attempts made by user"
						}
					}
				});*/

				usersAttempted = [];
				var userRatioCorrect = [];
				var userNumQuestions = [];
				var userNumCorrect = [];
				var namesAlphabetical = [];
				var mostCommonWrong = {};
				//console.log(xhttp.responseText);
				for(var submission in jsonData["results"])
				{
					//console.log(jsonData["results"][submission]["data"]["rAnswer"]);
					for(var answer in jsonData["results"][submission]["data"]["rAnswer"])
					{
						//console.log(v,jsonData["results"][submission]["data"]["rAnswer"][answer]);
						if(jsonData["results"][submission]["data"]["rAnswer"][answer] == false)
						{
							if (answer in mostCommonWrong)
							{
								mostCommonWrong[answer]++;
							} else {
								mostCommonWrong[answer] = 1;
							}
						}
					}

					if(usersAttempted.includes(jsonData["results"][submission]["userID"]))
					{
						userNumQuestions[xValuesTotal.indexOf(jsonData["results"][submission]["userID"])]++;
						userNumQuestions[xValuesTotal.indexOf(jsonData["results"][submission]["userID"])] += jsonData["results"][submission]["data"]["total"];
						userNumCorrect[xValuesTotal.indexOf(jsonData["results"][submission]["userID"])] += jsonData["results"][submission]["data"]["correct"];
					}
					else
					{
						namesAlphabetical.push(jsonData["results"][submission]["name"]);
						usersAttempted.push(jsonData["results"][submission]["userID"]);
						userNumQuestions.push(jsonData["results"][submission]["data"]["total"]);
						userNumCorrect.push(jsonData["results"][submission]["data"]["correct"]);
					}
				}

				for (var c = 0; c < yValuesTotal.length; c++)
				{
					if(userNumCorrect[c] > 0)
					{
						userRatioCorrect.push(userNumCorrect[c] / yValuesTotal[c]);
					}
					else
					{
						userRatioCorrect.push(0);
					}
				}
				console.log(userRatioCorrect);

				console.log(yValuesTotal);
				var ctx = document.getElementById('total').getContext('2d');
				var myBarChart = new Chart(ctx, {
					type: 'pie',  // Specify the type of chart: 'bar' for a bar chart
					data: {
						labels: namesTimeBased,
						datasets: [{
							data: yValuesTotal,
            				backgroundColor: colours
						}]
					},
					options: {
						responsive: true,  // Makes the chart responsive to window resizing
						scales: {
							y: {
								beginAtZero: true  // Ensure that the y-axis starts at 0
							}
						},
						title: {
							display: true,
							text: "Average score pre question"
						}
					}
				});

				var ctx = document.getElementById('average');
				var myBarChart = new Chart(ctx, {
					type: 'bar',  // Specify the type of chart: 'bar' for a bar chart
					data: {
						labels: namesAlphabetical,
						datasets: [{
							data: yValues,
            				backgroundColor: colours
						}]
					},
					options: {
						responsive: true,  // Makes the chart responsive to window resizing
						
					}
				});
/*
				new Chart("average", {
					type: "bar",
					data: {
						labels: namesAlphabetical,
						datasets: [{
						data: yValues,
						backgroundColor: colours
						}]
					},
					options: {
						title: {
						display: true,
						text: "Average score pre user"
						}
					}
					});
*/


				var xValuesTotal = [];
				var yValues = [];
				var yValuesTotal = [];
				var yValuesTotalScore = [];
				var names = {};
				//console.log(xhttp.responseText);
				for(var i in jsonData["results"])
				{
					if(xValuesTotal.includes(jsonData["results"][i]["userID"]))
					{						
						names[jsonData["results"][i]["name"]][0] += jsonData["results"][i]["data"]["correct"] / jsonData["results"][i]["data"]["total"] * 100;
						
						names[jsonData["results"][i]["name"]][1]++;
					}
					else
					{
						
						xValuesTotal.push(jsonData["results"][i]["userID"]);
						yValuesTotal.push(jsonData["results"][i]["data"]["total"]);
						if(jsonData["results"][i]["data"]["correct"] > 0)
						{
							names[jsonData["results"][i]["name"]] = [];
							names[jsonData["results"][i]["name"]][0] = jsonData["results"][i]["data"]["correct"] / jsonData["results"][i]["data"]["total"] * 100;
							names[jsonData["results"][i]["name"]][1] = 1;
							yValuesTotalScore.push(jsonData["results"][i]["data"]["correct"] / jsonData["results"][i]["data"]["total"] * 100);
						}
						else
						{
							names[jsonData["results"][i]["name"]] = [];
							names[jsonData["results"][i]["name"]][0] = jsonData["results"][i]["data"]["correct"];
							names[jsonData["results"][i]["name"]][1] = 1;
							yValuesTotalScore.push(0);
						}
					}
				}

				

				yValuesTotalScore.sort();
				var endData = [yValuesTotalScore[0], yValuesTotalScore[yValuesTotalScore.length -1]];
				var xEndData = ["",""];
				var items = [];
				
				for (i in names)
				{
					items.push(names[i][0] / names[i][1]);
				}
				items.sort();
				console.log(items);

				for (i in names)
				{
					if(names[i][0] / names[i][1] == items[0])
					{
						endData[0] = items[0];		
						xEndData[0] = i;			
					}
					else if(names[i][0] / names[i][1] == items[items.length - 1])
					{
						endData[1] = items[items.length - 1];			
						xEndData[1] = i;		
					}
				}



				console.log(xEndData,endData);
				new Chart("maxmin", {
					type: "bar",
					data: {
						labels: xEndData,
						datasets: [{
						backgroundColor: colours,
						data: endData
						}]
					},
					options: {
						title: {
						display: true,
						text: "Min and max score"
						},
						scales: {
							y: {
								suggestedMin: 0,
								suggestedMax: 100
							}
						}
					}
				});

				
				endData = [];
				xEndData = [];
				for (i in mostCommonWrong)
				{
					endData.push(mostCommonWrong[i]);
				}
				endData.sort();
				for (i in mostCommonWrong)
				{
					for (e in endData)
					{
						if(mostCommonWrong[i] == endData[e])
						{
							xEndData.push(translationTable[i][0]);
							break;
						}
					}
				}
				console.log(mostCommonWrong);
				new Chart("mostWrong", {
					type: "bar",
					data: {
						labels: xEndData,
						datasets: [{
						labels: xEndData,
						backgroundColor: colours,
						data: endData
						}]
					},
					options: {
						title: {
						labels: xEndData,
						display: true,
						text: "Most mistakes at"
						},
						scales: {
							y: {
								suggestedMin: 0,
								suggestedMax: 100
							}
						}
					}
				});


			}
		}
	}
}



function showDev(typeC)
{

	console.log(typeC);
	if(typeC == 0)
	{					
		document.getElementById("allHolder").removeAttribute("hidden");	
		//document.getElementById("lower2").removeAttribute("hidden");						
		document.getElementById("devHolder").setAttribute("hidden", true);
		//devView.innerHTML = "DEV VIEW";
	}
	else
	{
		if(!firstPullDevData)
		{
			loadQuestionResults();	
			firstPullDevData = true;	
		}
		document.getElementById("allHolder").setAttribute("hidden", true);		
	//	document.getElementById("lower2").setAttribute("hidden", true);				
		document.getElementById("devHolder").removeAttribute("hidden");
		document.getElementById("topicNameHolder").innerHTML = "Topic: " + topicName;
		document.getElementById("questionStats").innerHTML = "<br>Total variables: " + Object.keys(translationTable).length + "<br>Break down of each type<br> " + getTranslationTableTypesCount();
		//devView.innerHTML = "QUESTION VIEW";
	}
	setNameItems();
	inDevMenu = !inDevMenu;
}

readColorSettings();
function readColorSettings()
{
	firstColor = localStorage.getItem("firstColor");
	multiColor = localStorage.getItem("multiColor");

	if(firstColor != null)
	{
		textBackgroundColor = firstColor;
	}

	if(multiColor != null)
	{
		textBackgroundColorMulti = multiColor;
	}

}


function goSettings()
{
	window.location.href = "settings.php"; 
}

function loadInfo(jsonInfo)
{
	//jsonInfo = atob(jsonInfo);
	console.log(JSON.parse(jsonInfo))
	jsonInfo = JSON.parse(jsonInfo);
	allText = atob(jsonInfo["text"]);
	answerText = atob(jsonInfo["answer"]);
	questionID = atob(jsonInfo["questionID"]);
	translationTable = JSON.parse(atob(jsonInfo["translationTable"]));

	//console.log(allText, answerText, questionID, translationTable);
	
	allText = decodeURI(allText);
	answerText = decodeURI(answerText);



	console.log(allText, answerText, questionID, translationTable);
	console.log(translationTable);
	document.getElementById("main").innerHTML = allText;
	tableCreate();
}

var instanceId = randomIntFromInterval(10000,100000);

function showDevOptions()
{

}
function dumpInfoVis()
{
	dumpInfo();
}

function dumpInfo()
{		
	var allText = document.getElementById("main").innerHTML;
	var answerText = document.getElementById("answer").innerHTML;
	console.log("HTML");
	console.log(allText);
	console.log(answerText);
	for (var key in translationTable) 
	{
		Array.from(document.getElementsByClassName(btoa(key))).forEach(element => {
			element.outerHTML = translationTable[key][TT_JSONVALUE];
		});
	}
	//allText = allText.replaceAll("<br>","\n");
	allText = allText.trim();
	allText = encodeURI(allText);

	answerText= answerText.trim();
	answerText = encodeURI(answerText);

	var d = {"text":btoa(allText), "questionID" : questionID, "answer" : btoa(answerText), "translationTable" : btoa(JSON.stringify(translationTable))};
	//console.log("text=" + btoa(allText) + "\n\n\nquestionID=" + questionID +"\n\n\ntranslationTable=" + btoa(JSON.stringify(translationTable)));
	console.log(btoa(JSON.stringify(d)));
	try {		
		document.getElementById("toastMessageBug").outerHTML = "";
	} catch (error) {
		
	}
	var c = `
	<div class="d-flex justify-content-center align-items-center w-100" id="toastMessageBug">
	<div class="toast show">
	<div class="toast-header">
	<strong class="me-auto">Question Info (Copy this to share the question)</strong>
	<button type="button" class="btn-close" data-bs-dismiss="toast" onclick="closeBugToast()">X</button>
	</div>
	<div class="toast-body">	
	<span> `+btoa(JSON.stringify(d))+`
	</div>
	</div></div>`;
	var h = document.createElement("div");
	h.setAttribute("class", "toast show");
	h.setAttribute("role", "alert");
	
	var cc = document.createElement("div");
	h.setAttribute("class", "toast-header");
	cc.innerHTML = '<strong class="me-auto"> WARNING </strong>';
	h.appendChild(cc);

	cc = document.createElement("div");
	cc.innerHTML = c;
	h.appendChild(cc);
	h.innerHTML = c;
	

	document.getElementById("freefloat").appendChild(h);
}



function goHome()
{
	window.location.href = "index.php"; 
}

function setAnswer()
{
	answer = document.getElementById("answer").innerHTML;
}

function itemInDict(data, valueToFind)
{
	
}

function randomIntFromInterval(min, max) { // min and max included 
	return Math.floor(Math.random() * (max - min + 1) + min)
}

function inDict(data,valueToFind)
{
	for (var key in data) 
	{
		if(data[key][0] == valueToFind)
		{
			return true;
		}
	}
	return false;
}


function toggleRandom()
{
	if(urlParams.get('randomlock') != null)
	{
		document.location.href = window.location.search.replaceAll("&randomlock","");
	}
	else
	{
		document.location.href += "&randomlock"
	}
}
var shortCode = null;
function getShortCode(sessionID)
{
	var xhttp = new XMLHttpRequest();
	xhttp.open("GET", "api.php/getShortCode/"+sessionID, true);			
	xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xhttp.send("");
	xhttp.onreadystatechange = function() 
	{ 
		if (xhttp.readyState == 4 && xhttp.status == 200)
		{
			if(xhttp.responseText.length > 0 )
			{
				jsonRaw = JSON.parse(xhttp.responseText);
				shortCode = jsonRaw['shortCode'];
				console.log(shortCode);
				if(shortCode != null)
				{
					document.getElementById("shortCode").innerHTML = "Join in bslcs.com/session/ | use code " + shortCode;
				}
				else
				{
					createMessage("session has expired","WARNING");
				}
			}
		}
	}
}
sessionIndex = 0;
function loadNewQuestion(questionID, instanceId2, usingSession = false)
{
	console.log("loadNewQuestion");
	if(urlParams.get('instanceId') != null)
	{
		instanceId2 = urlParams.get('instanceId');
	}
	adder = "";
	if(urlParams.get('randomlock') != null)
	{
		adder = "&randomlock=true";
	}

	if(urlParams.get('sessionIndex') != null)
	{
		adder = "&sessionIndex=" + urlParams.get("sessionIndex");
		sessionIndex = Number(urlParams.get("sessionIndex"));
	}
	else
	{
		sessionIndex = 0;
	}
	

	console.log(urlParams);
	console.log("INSTANCE ID",instanceId2);
	console.log("adder",adder);
	var xhttp = new XMLHttpRequest();
	if(usingSession == false)
	{
		xhttp.open("GET", "api.php/getNewQuestion/?questionID="+questionID + "&instanceId=" + instanceId2 + adder, true);		
	}
	else
	{
		xhttp.open("GET", "api.php/getNewQuestion/?sessionID="+questionID + "&instanceId=" + instanceId2 + adder, true);	
		console.log("REMOVING TOGGLE");
		document.getElementById("toggleRandom").setAttribute("hidden","");	
	}		
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
				tClean = jsonRaw["text"];
				answer = jsonRaw["answer"];
				var Jtitle = jsonRaw["title"];
				if(Jtitle == "")
				{
					Jtitle = "No Name Given";
				}
				if("sessionID" in jsonRaw && urlParams.get('sessionID') == null)
				{
					document.location.href += '&sessionID=' + jsonRaw['sessionID'];
				}
				console.log("TITLE: " + "Digital Workbook - " + Jtitle);
				document.title = "Digital Workbook - " + Jtitle;
				document.getElementById("main").innerHTML = tClean;
				document.getElementById("answer").innerHTML = answer;
				if (urlParams.get('learningMode') != null)
				{
					Array.from(document.getElementsByClassName('answer')).forEach(element => {
						console.log(element);
						element.setAttribute("onfocusout","checkValue('" + element.id + "')");
					});
				}
				if(urlParams.get("presentationMode") != null || urlParams.get("sessionID") != null)
				{
					document.getElementById("left").setAttribute("hidden","");
					document.getElementById("right").setAttribute("style","width: 100%; overflow-x: hidden;");
					Array.from(document.getElementsByClassName('answer')).forEach(element => {
						console.log(element);
						element.setAttribute("onfocusout","checkValue('" + element.id + "')");
					});
				}
				if(urlParams.get('sessionID') != null)
				{
					getShortCode(urlParams.get('sessionID'));
				}
				if("loadScripts" in jsonRaw)
				{
					jsonRaw['loadScripts'].forEach(element => {
						dynamicallyLoadScript(element)
					});
				}
				animationFinder();
				Array.from(animation.current).forEach(element =>{					
					console.log(element);
				});

				if (urlParams.get('learningMode') != null)
				{
					Array.from(document.getElementsByClassName('answer')).forEach(element => {
						console.log(element);
						element.setAttribute("onfocusout","checkValue('" + element.id + "')");
					});
				}
				
				

				setTableStyle();
				Array.from(document.getElementsByClassName('onlyStaff')).forEach(element => {
					element.remove();
				});

				//helpHoverFix();
			}
		}
		else if(xhttp.readyState == 4 && xhttp.status == 202)
		{
			jsonRaw = JSON.parse(xhttp.responseText);
			location.replace(jsonRaw["url"]);
		}
	}
}
var learningMode = true;


function darkMode()
{
	
}




function dumpData()
{

}

function sleep(millis)
{
	var date = new Date();
	var curDate = null;
	do { curDate = new Date(); }
	while(curDate-date < millis);
}



function helpHoverFix()
{
	document.querySelectorAll('.helpQuestionInfo').forEach(box => {
	let tooltip;

	box.addEventListener('mouseenter', function () {
		console.log("ENTERED");
		// Create tooltip element dynamically
		tooltip = document.createElement('div');
		tooltip.className = 'tooltip';
		tooltip.textContent = box.getAttribute('data-hover');
		document.body.appendChild(tooltip);

		// Measure text size
		tooltip.style.position = 'absolute';
		tooltip.style.visibility = 'hidden';
		tooltip.style.whiteSpace = 'nowrap';
		document.body.appendChild(tooltip);

		// Limit width and allow wrapping if necessary
		let maxWidth = 300;
		let textWidth = tooltip.offsetWidth;
		tooltip.style.width = textWidth > maxWidth ? maxWidth + 'px' : textWidth + 'px';
		tooltip.style.whiteSpace = textWidth > maxWidth ? 'normal' : 'nowrap';

		// Position tooltip
		let rect = box.getBoundingClientRect();
		tooltip.style.left = rect.left + window.scrollX + rect.width / 2 - tooltip.offsetWidth / 2 + 'px';
		tooltip.style.top = rect.bottom + window.scrollY + 5 + 'px';

		// Adjust position if it's overflowing
		adjustTooltipPosition(tooltip);

		// Make it visible
		tooltip.style.visibility = 'visible';
		tooltip.style.opacity = '1';
	});

	box.addEventListener('mouseleave', function () {
		if (tooltip) {
			tooltip.remove();
		}
	});
});
}
// Function to check if tooltip is overflowing and adjust position
function adjustTooltipPosition(tooltip) {
	let tooltipRect = tooltip.getBoundingClientRect();
	let viewportWidth = window.innerWidth;
	let viewportHeight = window.innerHeight;

	// Adjust horizontally if needed
	if (tooltipRect.left < 0) {
		tooltip.style.left = '5px'; // Shift right
	} else if (tooltipRect.right > viewportWidth) {
		tooltip.style.left = viewportWidth - tooltipRect.width - 5 + 'px'; // Shift left
	}

	// Adjust vertically if needed
	if (tooltipRect.bottom > viewportHeight) {
		tooltip.style.top = parseFloat(tooltip.style.top) - tooltipRect.height - 10 + 'px'; // Move above element
	}
}


