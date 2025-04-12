var textBackgroundColor = "yellow";//powderblue
var textBackgroundColorMulti = "green";
var answer = "";
var editing = false;
var tax = 30;
var tClean = "";
var jsonRaw = {};
var allItemsValue = {};
var translationTable = {};
var companyAmount = 0;
var currentID = 0;
var firstColor = null;
var multiColor = null;
var inDevMenu = false;


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
				var xValuesTotal = [];
				var yValuesTotal = [];
				var names = [];
				//console.log(xhttp.responseText);
				for(var i in jsonData["results"])
				{
					if(xValuesTotal.includes(jsonData["results"][i]["userID"]))
					{
						yValuesTotal[xValuesTotal.indexOf(jsonData["results"][i]["userID"])]++;
					}
					else
					{
						names.push(jsonData["results"][i]["name"]);
						xValuesTotal.push(jsonData["results"][i]["userID"]);
						yValuesTotal.push(1);
					}
				}
				console.log(names);
				console.log(xValuesTotal, yValuesTotal);
				var cols = ["#0074D9", "#FF4136", "#2ECC40", "#FF851B", "#7FDBFF", "#B10DC9", "#FFDC00", "#001f3f", "#39CCCC", "#01FF70", "#85144b", "#F012BE", "#3D9970", "#111111", "#AAAAAA"];
				//var jsonRaw = JSON.parse(xhttp2.responseText);
				//var barColors = ["red", "green","blue","orange","brown"];
				new Chart("total", {
					type: "pie",
					data: {
						labels: names,
						datasets: [{
						data: yValuesTotal,
            			backgroundColor: cols
						}]
					},
					options: {
						title: {
						display: true,
						text: "Total attempts made by user"
						}
					}
				});

				var xValuesTotal = [];
				var yValues = [];
				var yValuesTotal = [];
				var yValuesTotalScore = [];
				var names = [];
				var mostCommonWrong = {};
				//console.log(xhttp.responseText);
				for(var i in jsonData["results"])
				{
					//console.log(jsonData["results"][i]["data"]["rAnswer"]);
					for(v in jsonData["results"][i]["data"]["rAnswer"])
					{
						//console.log(v,jsonData["results"][i]["data"]["rAnswer"][v]);
						if(jsonData["results"][i]["data"]["rAnswer"][v] == false)
						{
							if(v in mostCommonWrong)
							{
								mostCommonWrong[v]++;
							}
							else
							{
								mostCommonWrong[v] = 1;
							}
						}
					}

					if(xValuesTotal.includes(jsonData["results"][i]["userID"]))
					{
						yValuesTotal[xValuesTotal.indexOf(jsonData["results"][i]["userID"])]++;
						yValuesTotal[xValuesTotal.indexOf(jsonData["results"][i]["userID"])] += jsonData["results"][i]["data"]["total"];
						yValuesTotalScore[xValuesTotal.indexOf(jsonData["results"][i]["userID"])] += jsonData["results"][i]["data"]["correct"];
					}
					else
					{
						names.push(jsonData["results"][i]["name"]);
						xValuesTotal.push(jsonData["results"][i]["userID"]);
						yValuesTotal.push(jsonData["results"][i]["data"]["total"]);
						yValuesTotalScore.push(jsonData["results"][i]["data"]["correct"]);
					}
				}
				console.log(names);
				for (var c = 0; c < yValuesTotal.length; c++)
				{
					if(yValuesTotalScore[c] > 0)
					{
						yValues.push(yValuesTotalScore[c] / yValuesTotal[c]);
					}
					else
					{
						yValues.push(0);
					}
				}
				console.log(yValues);



				new Chart("average", {
					type: "bar",
					data: {
						labels: names,
						datasets: [{
						data: yValues,
						backgroundColor: cols
						}]
					},
					options: {
						title: {
						display: true,
						text: "Average score pre user"
						}
					}
					});



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
						backgroundColor: cols,
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
						backgroundColor: cols,
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

var firstPullDevData = false;

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

function loadNewQuestion(questionID, instanceId2)
{
	if(urlParams.get('instanceId') != null)
	{
		instanceId2 = urlParams.get('instanceId');
	}
	adder = "";
	if(urlParams.get('randomlock') != null)
	{
		adder = "&randomlock=true";
	}
	console.log("INSTANCE ID",instanceId2);
	console.log("adder",adder);
	var xhttp = new XMLHttpRequest();
	xhttp.open("GET", "api.php/getNewQuestion/?questionID="+questionID + "&instanceId=" + instanceId2 + adder, true);				
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
				console.log("TITLE: " + "Digital Workbook - " + Jtitle);
				document.title = "Digital Workbook - " + Jtitle;
				document.getElementById("main").innerHTML = tClean;
				document.getElementById("answer").innerHTML = answer;
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