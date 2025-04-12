<?php
	$questionData = json_decode("{}",true);
	$companies = ["BHP","TLC","WDS"];
	$c0 = $companies[array_rand($companies)];
	$c1 = $companies[array_rand($companies)];
	$tax = rand(1,99);
	$t = "<p> Intra group transactions
QUESTION 1:
On 1 July 2021, %c[0,0] acquired all the share capital of %c[1,-1] for $%n[100,2000,0]. At this date, %c[1,-1]'s equity comprised:

	   Carrying amount
Share capital – 100 000 shares	$100 000
General reserve	    $50 000
Retained earnings	    $36 000


All identifiable assets and liabilities of %c[1,-1] were recorded at fair value as at 1 July 2021 except for the following:
Type &emsp;&emsp;Carrying amount&emsp;&emsp;&emsp;Fair value
Inventory &emsp;&emsp;&emsp;$%n[1000,20000,1] &emsp;&emsp;&emsp;&emsp;&emsp;&emsp; $%n[1000,30000,2,1] 
Land &emsp;&emsp;&emsp;&emsp;&emsp;$%n[600000,200000,3] &emsp;&emsp;&emsp;&emsp;&emsp; $%n[1000,30000,4,3] 
Equipment (cost $100,000)&emsp;&emsp;50 000&emsp;&emsp;60 000

Additional information and intra group transactions

a.	The equipment is expected to have a further 10-year useful life. 
b.	The pre-acquisition inventory was sold by 30 June 2022.  Assume a perpetual inventory system.
c.	On 30 June 2022, the directors of %c[1,-1] transferred $25,000 from their general reserve to retained earnings.
d.	The tax rate is $tax%.
e.	The financial and tax year is 30 June.

Intra group transactions

There are four possible variations for intra group sales of inventory. Assume each of the following transactions is independent of the others and all internal sales between %c[0,0] and %c[1,-1] took place in FY2022:

1.	%c[1,-1] sold inventory to %c[0,0] for $%n[10000,15000,5]. The inventory had cost %c[1,-1] $%n[-6000,-2000,6,5]. The inventory is still held by %c[0,0] at 30 June 2022.

2.	%c[1,-1] sold inventory to %c[0,0] for $%n[10000,15000,7]. The inventory had cost %c[1,-1] $%n[-6000,-2000,8,7]. During the year, %c[0,0] sold $10 000 of the inventory for $20 000. The remaining inventory is still held by %c[0,0] at 30 June 2022.

3.	%c[1,-1] sold inventory to %c[0,0] for $%n[10000,15000,9]. The inventory had cost %c[1,-1] $%n[-6000,-2000,10,9]. The inventory was sold by %c[0,0] for $25 000 prior to 30 June 2022.

4.	%c[1,-1] sold inventory to %c[0,0] for $%n[10000,15000,11]. The inventory had cost %c[1,-1] $%n[-6000,-2000,12,11]. The inventory was sold by %c[0,0] for $25 000 on 1 July 2022.

REQUIRED:
3.	Prepare the consolidation entries for %c[0,0] and %c[1,-1] at 1 July 2021 and 30 June 2022 clearly labelling each step in the following order. No consolidation worksheets are required.
a.	acquisition analysis, 
b.	business combination valuation entries (revaluation on consolidation), 
c.	pre-acquisition entries,
d.	intra group elimination entries.</p>";
	
	$t = str_replace("\n","<br>", $t);
	echo strpos($t,"%c[0,0]");

	echo "<br>";
	$questionData["text"] = $t;
	$questionData["info"] = "company moves %n[1000,5000] to another account";
	$allItems = explode("%n", $questionData["text"]);
	
	$allItems = explode("%", $questionData["text"]);
	
	//var_dump($allItems2);
	
	$allItemsValue = json_decode("{}",true);
	foreach ($allItems as $i)
	{
		$n = explode(" ", $i);
		$c = str_replace(".","",$n[0]);
		if($c[0] === "c")
		{
			$indexOfString = strpos($questionData["text"], $c);
				
			$cleanC = str_replace("n","",$c);
			$cleanC = str_replace("[","",$cleanC);
			$cleanC = str_replace("]","",$cleanC);
			$ii = rand(0,sizeof($companies) - 1);
			//echo $ii."<br>";
			//echo $c;
			//echo $companies[$ii]."<br>"."<br>";
			$questionData["text"] = str_replace("%".$c, $companies[$ii], $questionData["text"]);
			
			//array_splice($companies, 1, 1);	
		}
		else if($c[0] === "n")
		{
			if($c[1] === "[")
			{
				$indexOfString = strpos($questionData["text"], $c);
				
				$cleanC = str_replace("n","",$c);
				$cleanC = str_replace("[","",$cleanC);
				$cleanC = str_replace("]","",$cleanC);
				$values = explode(",",$cleanC);
				$start = $values[0];
				$end = $values[1];
				//var_dump($values);
				//echo "<br>";
				if(sizeof($values) === 4)
				{
					$ci = (int)str_replace(" ","",$values[3]);
					if($ci !== -1)
					{
						$start += $allItemsValue[$ci];
						$end += $allItemsValue[$ci];
					}
				}
				$allItemsValue[$values[2]] = rand($start, $end);
				//var_dump($allItemsValue);
				echo "<br>";
				echo $start."<br>";
				echo $end."<br>";
				//echo substr($questionData["text"],$indexOfString, $indexOfString + strlen($c));
				$questionData["text"] = str_replace("%".$c, $allItemsValue[$values[2]], $questionData["text"]);
			}
		}
	}
	
	$t = str_replace("%c[0,0]",$c0, $t);
	$t = str_replace("%c1",$c1, $t);
/*
	foreach ($allItems as $i)
	{
		$n = explode(" ", $i);
		$c = str_replace(".","",$n[0]);
		if($c[0] === "[")
		{
			$indexOfString = strpos($questionData["text"], $c);
			$cleanC = str_replace("[","",$c);
			$cleanC = str_replace("]","",$cleanC);
			$values = explode(",",$cleanC);
			$start = $values[0];
			$end = $values[1];
			if(sizeof($values) === 4)
			{
				$ci = (int)str_replace(" ","",$values[3]);
				if($ci !== -1)
				{
					$start += $allItemsValue[$ci];
					$end += $allItemsValue[$ci];
				}
			}
			$allItemsValue[$values[2]] = rand($start, $end);
			var_dump($allItemsValue);
			echo "<br>";
			echo $start."<br>";
			echo $end."<br>";
			//echo substr($questionData["text"],$indexOfString, $indexOfString + strlen($c));
			$questionData["text"] = str_replace("%n".$c, $allItemsValue[$values[2]], $questionData["text"]);
		}
	}
		*/
	echo "<br><br><br><br>";

	$questionData["info"] = str_replace("%n",rand(1000,50000),$questionData["info"]);

	//echo $questionData["text"];













	function setCompanies($data)
	{
		global $companies;
		$companies1 = $companies;
		var_dump($companies1);
		echo sizeof($companies1);
		for ($i =0; $i < 100; $i++)
		{
			$checkVal = "%c".$i;
			if(strpos($checkVal,$data) !== null)
			{
				$rc = rand(0,sizeof($companies1));
				echo $rc;
				var_dump($companies1);
				str_replace($checkVal,$companies1[$rc] , $data);
				echo $data;
				$companies1 = array_splice($companies1, $rc, 1);
			}
			else
			{
				break;
			}
		}

	}

	function replaceCompany($data,$key)
	{
		return str_replace($key,$companies[array_rand($companies)],$data);
	}



?>

<html>
<textarea id="main" name="w3review" rows="25" cols="70"></textarea>

<button onclick=textClean(t)> aaa </button>
<button onclick=addNewCompany()> New Company </button>
<script src="main.js"></script>
<script> 
	
	tableCreate();
	function tableCreate() {
	  //body reference 
	  var colNames = ["value","type","a"];
	  var body = document.getElementsByTagName("body")[0];

	  // create elements <table> and a <tbody>
	  var tbl = document.createElement("table");
	  var tblBody = document.createElement("tbody");
	  
		row = document.createElement("tr");

		for (var i = 0; i < colNames.length; i++) {
		  // create element <td> and text node 
		  //Make text node the contents of <td> element
		  // put <td> at end of the table row
		  var cell = document.createElement("td");
		  var cellText = document.createTextNode(colNames[i]);
		  cell.appendChild(cellText);
		  row.appendChild(cell);
		}
		tblBody.appendChild(row);
	  // cells creation
	  for (var j = 0; j <= 2; j++) {
		// table row creation
		var row = document.createElement("tr");

		for (var i = 0; i <colNames.length; i++) {
		  // create element <td> and text node 
		  //Make text node the contents of <td> element
		  // put <td> at end of the table row
		  var cell = document.createElement("td");
		  var cellText = document.createElement("button");
		  cellText.innerHTML = "<button onclick=select(1)> select </button>";
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
	}

	var t2 = `<b>
		Intra group transactions
		QUESTION 1:
		On 1 July 2021, %c[0,0] acquired all the share capital of %company[1,-1] for $%n[100,2000,-2]. 
		At this date, %c[1,-1] 's equity comprised:</b>

		Carrying amount
		Share capital - 100 000 shares	$100 000
		General reserve	    $50 000
		Retained earnings	    $36 000


		All identifiable assets and liabilities of %c[1,-1] were recorded at fair value as at 1 July 2021 except for the following:
		Type		Carrying amount			Fair value
		Inventory		$%number[1000,20000,1] 				 $%number[1000,30000,2,1] 
		Land			$%number[600000,200000,3] 			 $%number[1000,30000,4,3] 
		Equipment (cost $100,000) 		 50 000&emsp;&emsp;60 000 
		%sum[[-2,-2],19] 

		Additional information and intra group transactions

		a.	The equipment is expected to have a further 10-year useful life. 
		b.	The pre-acquisition inventory was sold by 30 June 2022.  Assume a perpetual inventory system.
		c.	On 30 June 2022, the directors of %c[1,-1] transferred $25,000 from their general reserve to retained earnings.
		d.	The tax rate is %t[0,13]%.
		e.	The financial and tax year is 30 June.

		Intra group transactions

		There are four possible variations for intra group sales of inventory. Assume each of the following transactions is independent of the others and all internal sales between %c[0,0] and %c[1,-1] took place in FY2022:

		1.	%c[1,-1] sold inventory to %c[0,0] for $%number[10000,15000,5]. The inventory had cost %c[1,-1] $%number[-2000,-6000,6,5]. The inventory is still held by %c[0,0] at 30 June 2022.

		2.	%c[1,-1] sold inventory to %c[0,0] for $%number[10000,15000,7]. The inventory had cost %c[1,-1] $%number[-6000,-2000,8,7]. During the year, %c[0,0] sold $10 000 of the inventory for $20 000. The remaining inventory is still held by %c[0,0] at 30 June 2022.

		3.	%c[1,-1] sold inventory to %c[0,0] for $%number[10000,15000,9]. The inventory had cost %c[1,-1] $%number[-6000,-2000,10,9]. The inventory was sold by %c[0,0] for $25 000 prior to 30 June 2022.

		4.	%c[1,-1] sold inventory to %c[0,0] for $%number[10000,15000,11]. The inventory had cost %c[1,-1] $%number[-6000,-2000,12,11]. The inventory was sold by %c[0,0] for $25 000 on 1 July 2022.

		REQUIRED:
		3.	Prepare the consolidation entries for %c[0,0] and %c[1,-1] at 1 July 2021 and 30 June 2022 clearly labelling each step in the following order. No consolidation worksheets are required.
		a.	acquisition analysis, 
		b.	business combination valuation entries (revaluation on consolidation), 
		c.	pre-acquisition entries,
		d.	intra group elimination entries`;
	


var tClean = `
Intra group transactions
QUESTION 1:
On 1 July 2021, APA acquired all the share capital of WDS for $321.
At this date, WDS 's equity comprised:

Carrying amount
Share capital - 100 000 shares $100 000
General reserve  $50 000
Retained earnings  $36 000


  	All identifiable assets and liabilities of WDS were recorded at fair value as at 1 July 2021 except for the following:
  	Type  Carrying amount   Fair value
  	Inventory  $18302      $43286
  	Land   $301713     $315713
  	Equipment (cost $100,000)    50 000  60 000
  	642

  	Additional information and intra group transactions

a. The equipment is expected to have a further 10-year useful life.
  	b. The pre-acquisition inventory was sold by 30 June 2022. Assume a perpetual inventory system.
  	c. On 30 June 2022, the directors of WDS transferred $25,000 from their general reserve to retained earnings.
  	d. The tax rate is 30%.
  	e. The financial and tax year is 30 June.

  	Intra group transactions

  	There are four possible variations for intra group sales of inventory. Assume each of the following transactions is independent of the others and all internal sales between APA and WDS took place in FY2022:

  	1. WDS sold inventory to APA for $11478. The inventory had cost WDS $9332. The inventory is still held by APA at 30 June 2022.

  	2. WDS sold inventory to APA for $13885. The inventory had cost WDS $10006. During the year, APA sold $10 000 of the inventory for $20 000. The remaining inventory is still held by APA at 30 June 2022.

  	3. WDS sold inventory to APA for $12565. The inventory had cost WDS $7142. The inventory was sold by APA for $25 000 prior to 30 June 2022.

  	4. WDS sold inventory to APA for $12852. The inventory had cost WDS $8867. The inventory was sold by APA for $25 000 on 1 July 2022.

  	REQUIRED:
  	3. Prepare the consolidation entries for APA and WDS at 1 July 2021 and 30 June 2022 clearly labelling each step in the following order. No consolidation worksheets are required.
  	a. acquisition analysis,
  	b. business combination valuation entries (revaluation on consolidation),
  	c. pre-acquisition entries,
  	d. intra group elimination entries`;

	function randomIntFromInterval(min, max) { // min and max included 
		return Math.floor(Math.random() * (max - min + 1) + min)
	}
	var tax = 30;
	
	var allItemsValue = {};
	var translationTable = {};
	var comapnyAmount = 0;
	var currentID = 0;

	function inDict(data,valueToFind)
	{
		for (var key in data) 
		{
			if(data[key] == valueToFind)
			{
				return true;
			}
			//console.log(key, data[key], valueToFind);
		}
		return false;
	}

	function getSelectionText()
	{
		var txtarea = document.getElementById("main");

    // Obtain the index of the first selected character
    var start = txtarea.selectionStart;

    // Obtain the index of the last selected character
    var finish = txtarea.selectionEnd;

    // Obtain the selected text
    var sel = txtarea.value.substring(start, finish);
	return sel;
	}

	function getSelectionText2() {
		var text = "";
		if (window.getSelection) {
			text = window.getSelection().toString();
		} else if (document.selection && document.selection.type != "Control") {
			text = document.selection.createRange().text;
		}
		return text;
	}
	
	//t = tClean.replaceAll("\n","<br>");
	//t = t.replaceAll("\t","&emsp;");
	document.getElementById("main").innerHTML = tClean;
	//textClean(t);

	function addNewCompany()
	{
		var text = getSelectionText()
		text = text.trim();
		if(!inDict(translationTable, text))
		{
			console.log(text);
			translationTable[currentID] = [text,"%n["+comapnyAmount+","+currentID+"]"];
			console.log(currentID);
			tClean = tClean.replaceAll(text,"%c["+comapnyAmount+","+currentID+"]");
			document.getElementById("main").innerHTML = tClean;
			comapnyAmount++;
			currentID++;
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