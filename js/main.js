function textClean(t)
{
	console.log("RAW: " + t);
	var questionData = {};
	//t = t.replaceAll("\n","<br>");
	//t = t.replaceAll("\t","&emsp;");
	questionData["text"] = t;
	var allItems = t.split("%");
	var companies = ["TLC","BHP","APA","COM","WDS","WES"];
	for (var ic = 0; ic < allItems.length; ic++)
	{
		var i = allItems[ic];
		var n = i.split(" ");
		var c = n[0].replaceAll(".","");
			
		if(c.substring(0,7) === "company" || c.substring(0,2) == "c[")
		{
			cleanC = c.replaceAll("company","");
			cleanC = cleanC.replaceAll("c[","");
			cleanC = cleanC.replaceAll("[","");
			cleanC = cleanC.replaceAll("]","");
			var values = cleanC.split(",");
			//console.log("v",values);
			var ii = Math.floor(Math.random() * (companies.length - 0) ) + 0;
			if(inDict(allItemsValue,companies[ii]))
			{
				ii = Math.floor(Math.random() * (companies.length - 0) ) + 0;
			}
			allItemsValue[values[1]] = companies[ii];
			questionData["text"] = questionData["text"].replaceAll("%"+c, companies[ii]);
			//array_splice($companies, 1, 1);	
		}
		else if(c.substring(0,3) === "tax" || c.substring(0,2) == "t[")
		{
			cleanC = c.replaceAll("tax","");
			cleanC = cleanC.replaceAll("t","");
			cleanC = cleanC.replaceAll("[","");
			cleanC = cleanC.replaceAll("]","");
			var values = cleanC.split(",");	
			allItemsValue[values[1]] = tax;
			questionData["text"] = questionData["text"].replaceAll("%"+c, tax);
		}
		else if(c.substring(0,3) == "sum" || c.substring(0,2) == "s[")
		{
			cleanC = c.replaceAll("sum","");
			cleanC = cleanC.replaceAll("s[","");
			cleanC = cleanC.replaceAll("[","");
			var sumR = cleanC.split("],");
			var sum = sumR[0].split(",");
			cleanC = cleanC.replaceAll("]","");
			var values = sumR[1].split("]")[0];
			values = values.split(",");
			var data = 0;
			for(var dc = 0; dc < sum.length; dc++)
			{
				data += allItemsValue[sum[dc]];
			}
			allItemsValue[values[0]] = data;
			//console.log("SUM:" ,data,values);
			questionData["text"] = questionData["text"].replaceAll("%"+c, data);
		}
		else if(c.substring(0,6) == "number" || c.substring(0,2) == "n[")
		{
			cleanC = c.replaceAll("number","");
			cleanC = cleanC.replaceAll("n","");
			cleanC = cleanC.replaceAll("[","");
			//cleanC = cleanC.replaceAll("]","");
			cleanC = cleanC.split("]")[0];
			var values = cleanC.split(",");
			if(values.length == 1)
			{
				console.log(values,cleanC);
				questionData["text"] = questionData["text"].replaceAll("%"+c, allItemsValue[values[0]]);
			}
			else
			{
				var start = Number(values[0]);
				var end = Number(values[1]);
				if(values.length > 3)
				{
					start += Number(allItemsValue[values[3]]);
					end += Number(allItemsValue[values[3]]);
					/*console.log("LINKED TO ID: " + values[3]);
					console.log("GOT VALUE " + allItemsValue[values[3]]);
					console.log("TO VALUE " + values[0]);
					console.log("TO VALUE " + values[1]);
					console.log("ADDED " + (Number(values[0]) + Number(allItemsValue[values[3]])));
					*/console.log("ADDED " + (Number(values[1]) + Number(allItemsValue[values[3]])));
				}
				allItemsValue[values[2]] = randomIntFromInterval(start,end)
				var ii = allItemsValue[values[2]];
				questionData["text"] = questionData["text"].replaceAll("%"+c, ii);
			}
		}
	}
	document.getElementById("main").innerHTML = questionData["text"];
}


