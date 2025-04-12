/*
Inputs
	- Variable ID
*/
function openStatsForVar(id)
{
	adder = "";
	if(urlParams.get('sessionID') != null)
	{
		sessionID = urlParams.get('sessionID');
	}

	var xhttp = new XMLHttpRequest();
	xhttp.open("GET", "api.php/getSessionStats/"+sessionID + "/?variableID=" + id, true);				
	xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xhttp.send("");
	xhttp.onreadystatechange = function() 
	{ 
		if (xhttp.readyState == 4 && xhttp.status == 200)
		{
			if(xhttp.responseText.length > 0)
			{
				var jsonRaw = JSON.parse(xhttp.responseText);
				var total = jsonRaw["total"];
				var offset = 0;
				if("" in jsonRaw["rawResults"])
				{
					offset = Number(jsonRaw["rawResults"][""]["count"]);
				}
				console.log(offset);
				console.log(jsonRaw);
				Array.from(document.getElementsByClassName(id + "-res")).forEach(element => {
					if("correct" in jsonRaw["results"])
					{
						console.log(jsonRaw["results"]);
						if(jsonRaw["results"]["total"] - offset <= 0)
						{
							element.setAttribute("data-hover", "X/" + jsonRaw["results"]["correct"] + "/" + total);
						}
						else
						{
							element.setAttribute("data-hover", jsonRaw["results"]["total"] - offset + "/" + jsonRaw["results"]["correct"]  + "/" + total);
						}
					}
					else
					{
						element.setAttribute("data-hover", "X/0/" + total);
					}
				});
			}
		}
	}
}

chart = null;

autoUpdateChartData = false;
currentQuestionSection = null;

function autoUpdateWindow()
{
	autoUpdateChartData = !autoUpdateChartData;
}

setInterval(function() {
	if(autoUpdateChartData == true && currentQuestionSection != null)
	{
		openStatsForVarChart(currentQuestionSection);
	}
  }, 5000); 

function makeWindow()
{
	var chartWindow = document.createElement("div");
	chartWindow.innerHTML = `<div class="window-top">
			<span id="chatTitle"> Showing the top answers submitted </span>
		</div>
		<div>
			<input onclick=autoUpdateWindow() type="checkbox" > Auto Update </input>
			<button type="button" class="btn" onclick=closeWindow()> Close </button>
		</div>
		<div style="height:auto">	
			<canvas id="chart">
			</canvas>

		</div>`;
	chartWindow.setAttribute("hidden","");
	chartWindow.setAttribute("id","chartWindow");
	chartWindow.setAttribute("class","window");
	chartWindow.setAttribute("style","width:60%; height:60%; left:20%; top: 10%");
	document.body.appendChild(chartWindow);
	makeDraggable(document.querySelector('#chartWindow'));
	var ctx = document.getElementById('chart').getContext('2d');
	chart = new Chart(ctx, {
		type: 'bar',  
		data: {
			labels: [''],									
			datasets: [{
				label: 'Answers',
				data: [0],
				backgroundColor: colours
			}]
		},
		options: {
			responsive: true,  
			scales: {
				y: {
					beginAtZero: true
				}
			},
		}
	});
}
makeWindow();


/*Close window
Inputs 
	- none
Outputs
	- none
side effects 
	- Close window
*/
function closeWindow()
{
	autoUpdateChartData = false;
	document.getElementById('chartWindow').setAttribute("hidden","");
}
/*
Inputs
	- Variable ID
*/
function openStatsForVarChart(id)
{
	adder = "";
	if(urlParams.get('sessionID') != null)
	{
		sessionID = urlParams.get('sessionID');
	}

	var xhttp = new XMLHttpRequest();
	xhttp.open("GET", "api.php/getSessionStats/"+sessionID + "/?variableID=" + id, true);				
	xhttp.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
	xhttp.send("");
	xhttp.onreadystatechange = function() 
	{ 
		if (xhttp.readyState == 4 && xhttp.status == 200)
		{
			if(xhttp.responseText.length > 0 )
			{
				var jsonRaw = JSON.parse(xhttp.responseText);
				yItems = [];
				xItems = [];
				xItems.push('CORRECT AWNSER: ' + jsonRaw['correctResults']);
				yItems.push(0);
				for (const [key, value] of Object.entries(jsonRaw['rawResults'])) {
					console.log(jsonRaw['rawResults'][key])
					if(xItems.includes('CORRECT AWNSER: ' + jsonRaw['rawResults'][key]['value']))
					{
						yItems[xItems.indexOf('CORRECT AWNSER: ' + jsonRaw['rawResults'][key]['value'])] += jsonRaw['rawResults'][key]['count'];
					}
					else
					{
						if(jsonRaw['rawResults'][key]['value'] != "")
						{
							xItems.push(jsonRaw['rawResults'][key]['value']);
						}
						else
						{
							xItems.push("NO AWNSER GIVEN");
						}
						yItems.push(jsonRaw['rawResults'][key]['count']);
					}
				}
				if(yItems.length > 0)
				{				
					currentQuestionSection = id;	
					document.getElementById('chartWindow').removeAttribute("hidden");
					console.log(yItems, xItems);
					chart.data.datasets[0].data = yItems;
					chart.data.labels = xItems;
					console.log(Math.max(...yItems));
					//chart.options.scales.y.ticks.max = Math.max(...yItems) + 2;
					chart.update();
				}
				
			}
		}
	}
}



types = {};
function makeDraggable (element) {
    // Make an element draggable (or if it has a .window-top class, drag based on the .window-top element)
    let currentPosX = 0, currentPosY = 0, previousPosX = 0, previousPosY = 0;

		// If there is a window-top classed element, attach to that element instead of full window
    if (element.querySelector('.window-top')) {
        // If present, the window-top element is where you move the parent element from
        element.querySelector('.window-top').onmousedown = dragMouseDown;
    } 
    else {
        // Otherwise, move the element itself
        element.onmousedown = dragMouseDown;
    }

    function dragMouseDown (e) {
        // Prevent any default action on this element (you can remove if you need this element to perform its default action)
        e.preventDefault();
        // Get the mouse cursor position and set the initial previous positions to begin
        previousPosX = e.clientX;
        previousPosY = e.clientY;
        // When the mouse is let go, call the closing event
        document.onmouseup = closeDragElement;
        // call a function whenever the cursor moves
        document.onmousemove = elementDrag;
    }

    function elementDrag (e) {
        // Prevent any default action on this element (you can remove if you need this element to perform its default action)
        e.preventDefault();
        // Calculate the new cursor position by using the previous x and y positions of the mouse
        currentPosX = previousPosX - e.clientX;
        currentPosY = previousPosY - e.clientY;
        // Replace the previous positions with the new x and y positions of the mouse
        previousPosX = e.clientX;
        previousPosY = e.clientY;
        // Set the element's new position
        element.style.top = (element.offsetTop - currentPosY) + 'px';
        element.style.left = (element.offsetLeft - currentPosX) + 'px';
    }

    function closeDragElement () {
        // Stop moving when mouse button is released and release events
        document.onmouseup = null;
        document.onmousemove = null;
    }
}


//Close the window on click of a red button
document.addEventListener('click', e => {
	if (e.target.closest('.round.red')) {
		e.target.closest('.window').remove();
	}
});