const B_Button = 66;

// Allows the search of URL variables 
const urlParams = new URLSearchParams(window.location.search);

/*Create a message box
Inputs 
	- content: The text content
	- type: What type of error it is
Outputs
	- none
side effects 
	- Create a message box
*/
function messageCreate(content, type)
{
	var HTMLBlockConent = `
	<div class="d-flex justify-content-center align-items-center w-100" id="toastMessage">
	<div class="toast show">
	<div class="toast-header">
	<strong class="me-auto">` + type + `</strong>
	<button type="button" class="btn-close" data-bs-dismiss="toast">X</button>
	</div>
	<div class="toast-body">
	<p>`+ content.toUpperCase() + `</p>
	</div>
	</div></div>`;
	var headers = document.createElement("div");
	headers.setAttribute("class", "toast show");
	headers.setAttribute("role", "alert");
	
	var warningType = document.createElement("div");
	headers.setAttribute("class", "toast-header");
	warningType.innerHTML = '<strong class="me-auto"> WARNING </strong>';
	headers.appendChild(warningType);

	warningType = document.createElement("div");
	warningType.innerHTML = HTMLBlockConent;
	headers.appendChild(warningType);
	headers.innerHTML = HTMLBlockConent;
	

	document.getElementById("freefloat").appendChild(headers);
}


// Closes Toast message
function closeBugToast() {
	document.getElementById("toastMessageBug").outerHTML = "";
}








/*Create a bug pop up
Inputs 
	- none
Outputs
	- none
side effects 
	- Create a bug pop up
*/
function bugPopUp()
{
	try {		
		document.getElementById("toastMessageBug").outerHTML = "";
	} catch (error) {
		
	}
	//remove into HMTL file
	var c = `
	<div class="d-flex justify-content-center align-items-center w-100" id="toastMessageBug">
	<div class="toast show">
	<div class="toast-header">
	<strong class="me-auto">Bug Report</strong>
	<button type="button" class="btn-close" data-bs-dismiss="toast" onclick="closeBugToast()">X</button>
	</div>
	<div class="toast-body">
	<p><input placeholder='Title' id="title"> </input></p>
	<p><textarea placeholder='Body' id="body">Write body here</textarea></p>		
	<span> Write the severity <br>
		1 Good to know <br>
		2 No very important <br>
		3 Important <br>
		4 Critical <br>
		</span><br>
	<input id="severity" type="number" max="4" min="1" value = "1" step="1"/>	<br><br>
	<button onclick=reportBugs() class="btn btn-outline-primary my-2 my-sm-0"> Submit Bug </button>
	</div>
	</div></div>`;
	var headers = document.createElement("div");
	headers.setAttribute("class", "toast show");
	headers.setAttribute("role", "alert");
	
	var cc = document.createElement("div");
	headers.setAttribute("class", "toast-header");
	cc.innerHTML = '<strong class="me-auto"> WARNING </strong>';
	headers.appendChild(cc);

	cc = document.createElement("div");
	cc.innerHTML = c;
	headers.appendChild(cc);
	headers.innerHTML = c;
	

	document.getElementById("freefloat").appendChild(headers);
}

function closeItem(item)
{
	item.outerHTML = "";
}



function createPrompt(callback, text, placeholder="", passItems)
{
	try {		
		document.getElementById("promptMessage").outerHTML = "";
	} catch (error) {
		
	}
	//remove into HMTL file
	var c = `
	<div class="d-flex justify-content-center align-items-center w-100" id="promptMessage">
	<div class="toast show">
	<div class="toast-header">
	<strong class="me-auto">Prompt Message</strong>
	<button type="button" class="btn-close" data-bs-dismiss="toast" onclick="closeItem(this)">X</button>
	</div>
	<div class="toast-body">
	<p>`+text+`</p>
	<input placeholder=`+placeholder+`> </input>
	<button class='btn' onclick=`+callback+`(this,`+JSON.stringify(passItems)+`)> Send </button>
	</div>
	</div></div>`;
	var headers = document.createElement("div");
	headers.setAttribute("class", "toast show");
	headers.setAttribute("role", "alert");
	
	var cc = document.createElement("div");
	headers.setAttribute("class", "toast-header");
	cc.innerHTML = '<strong class="me-auto"> WARNING </strong>';
	headers.appendChild(cc);

	cc = document.createElement("div");
	cc.innerHTML = c;
	headers.appendChild(cc);
	headers.innerHTML = c;
	

	document.getElementById("freefloat").appendChild(headers);
}



// Windows event listener 

window.addEventListener('keydown', keyPressHandler);

///

/*Send a bug report
Inputs 
	- none
Outputs
	- none
side effects 
	- Send a bug report
*/
function reportBugs()
{
	if (document.getElementById("title").value != "" && document.getElementById("body").value != " Write the problem here")
	{
		var xhttp = new XMLHttpRequest();
		xhttp.open("POST", "api.php/reportBug/?title=" + document.getElementById("title").value + "&body=" + 
			document.getElementById("body").value + "&severity=" + document.getElementById("severity").value , true);	
		xhttp.setRequestHeader('Content-type', 'text/json;charset=UTF-8');
		xhttp.send();
		document.getElementById("toastMessageBug").outerHTML = "";
	}
	else
	{
		alert("Please fill out all options");
	}
}

function keyPressHandler(e) {
	var evtobj = window.event ? window.event : e;

	if (evtobj.ctrlKey && evtobj.altKey && evtobj.keyCode == B_Button) {
		bugPopUp();
	}
	if(evtobj.keyCode == 81 && evtobj.ctrlKey)
	{
		showQRCode();
	}
}

//https://stackoverflow.com/questions/847185/convert-a-unix-timestamp-to-time-in-javascript
function timeConverter(UNIX_timestamp){
	var a = new Date(UNIX_timestamp * 1000);
	var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
	var year = a.getFullYear();
	var month = months[a.getMonth()];
	var date = a.getDate();
	var hour = a.getHours();
	var min = a.getMinutes();
	var sec = a.getSeconds();
	var time = date + ' ' + month + ' ' + year + ' ' + hour + ':' + min + ':' + sec ;
	return time;
}




function dynamicallyLoadScript(url) {
    var script = document.createElement("script"); 	
	script.crossOrigin = "anonymous";
    script.src = url;  
    document.head.appendChild(script); 
}

// Scripts
dynamicallyLoadScript("https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js");
dynamicallyLoadScript("js/jquery-3.3.1.slim.min.js");

function dynamicallyLoadLink(url) {
    var link = document.createElement("link"); 	
	link.rel = "stylesheet";
    link.href = url;  
    document.head.appendChild(link); 
}

// CSS
dynamicallyLoadLink("https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css");

// fix 
// then make special characters in file and then make it do a loop
function removeSpecialChars(text)
{
	return text.replaceAll("Ã","").replaceAll("â","").replaceAll("","").replaceAll("â","");
}

// might be place somewhere else like in a php database
let exceptions = ['random.pulbic.uni.no.edu'];

function emailVerifier(email) 
{
    // simple snytaxical email check
    const hasCorrectEnding = /@[a-zA-Z]+.[a-zA-Z]+[a-zA-Z.]*/gm;
    const numberOfAts = /@/gm;
    if (!hasCorrectEnding.test(email) || email.match(numberOfAts).length != 1) {
        return 0;
    }
    let dupString = (" " + email).slice(1);
    const emailProvider = dupString.split('@')[1];
    const userName = dupString.split('@')[0];    
    if (userName.length == 0) {
        return 0;
    }
    if (!emailProvider.includes('edu') && !exceptions.includes(emailProvider)) {
        return 0;
    }
    return 1;
}



//dynamicallyLoadScript("js/callback.js");

