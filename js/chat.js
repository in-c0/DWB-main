
var varID = -1;

var chatQuestionID;
var chatvarID;
var chatSearchUser = null;
var chatNoTitle = true;
var chatMenuOpened = false;

/*Send a message
Inputs 
	- content: The message content
	- varID: the variable id that the message is sent from
Outputs
	- none
side effects 
	- Send a message
*/
function sendMessage(content, varID = null)
{
	var chatMessage = document.getElementById('chatMessage').value;
	if(chatMessage != "")
	{
		if(chatQuestionID == null && questionID != null)
		{
			chatQuestionID = questionID;
		}
		var chatXML = new XMLHttpRequest(); 
		var url = "api.php/createMessage/?questionID=" + questionID + "&variableID=" + chatvarID + "&content=" + chatMessage;
		chatXML.open("GET", url, true); 
	
		chatXML.onreadystatechange = function() {
			if(chatXML.readyState == 4 && chatXML.status == 200) {
				reloadChatMenu();
			}
		};
		chatXML.send();
		reloadChatMenu();
		document.getElementById("chatMessage").value = "";
	}
}

/*Send a message
Inputs 
	- id: The message input field id
	- varID: the variable id that the message is sent from
	- user: the user id that the message is being responsed to
Outputs
	- none
side effects 
	- Send a message
*/
function send(id, q, varID,user)
{
	var msg = document.getElementById(id).value;
	if(msg != "")
	{
		var chatXML = new XMLHttpRequest(); 
		var url = "api.php/createMessageStaff/?questionID=" + chatQuestionID + "&variableID=" + varID + "&content=" + msg + "&searchUser=" + user;
		chatXML.open("GET", url, true); 
	
		chatXML.onreadystatechange = function() {
			if(chatXML.readyState == 4 && chatXML.status == 200) {
				reloadChatMenu();
			}
		};
		chatXML.send();
		reloadChatMenu();
		document.getElementById(id).value = "";
	}
}


function reloadChatMenuLoop()
{
	reloadChatMenu();
}

setInterval(function() {
	if(chatMenuOpened == true)
	{
		reloadChatMenu();
	}
  }, 5000); 

/*Reload a Chat Menu
Inputs 
	- none
Outputs
	- none
side effects 
	- Reload a Chat Menu
*/
function reloadChatMenu()
{
	if(chatMenuOpened)
	{
		console.log("RELOADING CHAT");
		var chatXML = new XMLHttpRequest(); 
		if(chatQuestionID == null && questionID != null)
		{
			chatQuestionID = questionID;
		}
		var url = "api.php/getChat/?questionID=" + chatQuestionID + "&variableID=" + chatvarID;
		if(chatSearchUser != null)
		{
			
			url = "api.php/getChat/?questionID=" + chatQuestionID + "&variableID=" + chatvarID + "&searchUser=" + chatSearchUser;
		}
		chatXML.open("GET", url, true); 

		chatXML.onreadystatechange = function() {
			if(chatXML.readyState == 4 && chatXML.status == 200) {
				var lastItem = null;
				document.getElementById("chatTranscript").innerHTML = "";
				if(chatNoTitle == false)
				{
					document.getElementById("chatTitle").innerHTML = "Chat Log (" + chatvarID + ")";
				}
				JSON.parse(chatXML.responseText)["chats"].forEach(element => {

					var divTextHolder = document.createElement('div');
					divTextHolder.setAttribute("class","p-3 ms-3");
					if(JSON.parse(chatXML.responseText)['userID'] != element['messageCreator'])
					{					
						var divHolder = document.createElement('div');
						divHolder.setAttribute("class","d-flex flex-row justify-content-start mb-4");
						divTextHolder.setAttribute("style","border-radius: 15px; background-color: rgba(181, 227, 243, 0.2);");
					}
					else
					{
						var divHolder = document.createElement('div');
						divTextHolder.setAttribute("style","border-radius: 15px; background-color: rgba(57, 237, 57, 0.2);");
						divHolder.setAttribute("class","d-flex flex-row justify-content-end mb-4");
					}
				
					var header = document.createElement('span');
					header.innerHTML = element['messageCreator'];
					
					var text = document.createElement('p');
					text.innerHTML = element['content'];

					divTextHolder.appendChild(header);
					divTextHolder.appendChild(text);
					divHolder.appendChild(divTextHolder);
					lastItem = divHolder;
					document.getElementById("chatTranscript").appendChild(divHolder);
				});			
				if(lastItem != null)
				{
					lastItem.scrollIntoView();
				}
			}
		};
		chatXML.send();
	}
}

/*Close chat menu
Inputs 
	- none
Outputs
	- none
side effects 
	- Close chat menu
*/
function closeChatMenu()
{
	chatMenuOpened = false;
	document.getElementById('chatMenu').setAttribute("hidden","");
}

/*Opens chat menu
Inputs 
	- varID2: The varid that was used to call it
Outputs
	- none
side effects 
	- Opens chat menu
*/
function openChatMenu(varID2)
{
	chatvarID = varID2;
	chatMenuOpened = true;
	varID = varID2;
	console.log("OPENING CHAT MENU", chatvarID);
	document.getElementById("chatMenu").removeAttribute("hidden");
	reloadChatMenu();
}