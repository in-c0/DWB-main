var totalTime = 0;
var totalTimeOnApp = 0;
var allWindowChanges = [];


document.addEventListener('visibilitychange', function logData() {
	var time = new Date().getTime();
	if (document.visibilityState === 'hidden') {		
		allWindowChanges.push(["hidden",time]);
	}
	else 
	{			
		allWindowChanges.push(["show",time]);
	}
});


function getTotalTimeOnSite()
{
	var totalTimeT = 0;
	var totalTimeOnAppT = 0;
	var lastTime = 0;
	var lastType = "";
	allWindowChanges.forEach(element => {
		if(lastTime == 0)
		{
			lastTime = element[1];
			lastType = element[0];
		}
		totalTimeT += element[1] - lastTime;
		if(element[0] == "show" && lastType != element[0])
		{
			totalTimeOnAppT += element[1] - lastTime;
		}

		lastTime = element[1];
		
	});
	totalTimeT += new Date().getTime() - lastTime;
	totalTimeOnAppT += new Date().getTime() - lastTime;
	totalTime = totalTimeT;
	totalTimeOnApp = totalTimeOnAppT;
	return [totalTime, totalTimeOnApp];
}