import refreshTextBox from './TextBox.js';
import refreshPlayerStats from './PlayerStats.js';

var timeRemaining;
var timer;
var currentTime;

//Timer countdown.
function voteCountdown(voteTime){
	if(currentTime > voteTime)
		timeRemaining = voteTime + (60-currentTime);
	else
		timeRemaining = voteTime - currentTime;

	// eslint-disable-next-line
	if(timeRemaining <= 0 || timeRemaining > 20){
		clearInterval(timer);
		document.getElementById("TimerText").innerHTML = "Voting finished! Next round will begin shortly!";
		timeRemaining = null;
		timer = null;
		initializeData();
	}
	else{
		// eslint-disable-next-line
		if (timeRemaining == 1){
			document.getElementById("TimerText").innerHTML = timeRemaining + " second remaining to vote!";
		}
		else{
			document.getElementById("TimerText").innerHTML = timeRemaining + " seconds remaining to vote!";
		}
		currentTime += 1;
	}
}

function initializeData(){
	// eslint-disable-next-line
	if(timeRemaining == null){
		fetch("http://adventuretext.quest:80/server/").then((res)=>res.json()).then(data =>{
			var voteTime = parseInt(data.time);
			currentTime = parseInt(data.currTime);

			enableButtons();

			// eslint-disable-next-line
			if(data.scenario[3] != "null")
				document.getElementById("btn1").innerHTML = data.scenario[3];
			else{
				document.getElementById("btn1").innerHTML = "";
				selectiveDisableButton("btn1");
				document.getElementById("btn1").style.background = "#212121";
				document.getElementById("btn1").style.color = "#727272";
			}

			// eslint-disable-next-line
			if(data.scenario[4] != "null"){
				document.getElementById("btn2").innerHTML = data.scenario[4];
				// eslint-disable-next-line
				if(data.playerStats[4] == 0 && data.scenario[4] == "Drink a potion"){
					selectiveDisableButton("btn2");
					document.getElementById("btn2").style.background = "#212121";
					document.getElementById("btn2").style.color = "#727272";
				}
			}
			else{
				document.getElementById("btn2").innerHTML = "";
				selectiveDisableButton("btn2");
				document.getElementById("btn2").style.background = "#212121";
				document.getElementById("btn2").style.color = "#727272";
			}

			// eslint-disable-next-line
			if(data.scenario[5] != "null"){
				document.getElementById("btn3").innerHTML = data.scenario[5];
				if(data.level >= 5){
					selectiveDisableButton("btn3");
					document.getElementById("btn3").style.background = "#212121";
					document.getElementById("btn3").style.color = "#727272";
				}
			}
			else{
				document.getElementById("btn3").innerHTML = "";
				selectiveDisableButton("btn3");
				document.getElementById("btn3").style.background = "#212121";
				document.getElementById("btn3").style.color = "#727272";
			}

			refreshTextBox();
			refreshPlayerStats();
			initializeTimer(voteTime);
		});
	}
}

function initializeTimer(voteTime){
	if(timer == null){
		timer = setInterval(function(){voteCountdown(voteTime)}, 1000);
	}
}

function selectiveDisableButton(btnId){
	document.getElementById(btnId).disabled = true;
}

//Disables buttons after a vote so that a user can't vote twice (unless they refresh the page).
function disableButtons(){
	document.getElementById("btn1").disabled = true;
	document.getElementById("btn1").style.background = "#212121";
	document.getElementById("btn1").style.color = "#727272";
	document.getElementById("btn2").disabled = true;
	document.getElementById("btn2").style.background = "#212121";
	document.getElementById("btn2").style.color = "#727272";
	document.getElementById("btn3").disabled = true;
	document.getElementById("btn3").style.background = "#212121";
	document.getElementById("btn3").style.color = "#727272";
}

//Enables buttons when voting countdown is finished and resets the background colors.
function enableButtons(){
	document.getElementById("btn1").disabled = false;
	document.getElementById("btn1").style.background = "#4E4E50";
	document.getElementById("btn1").style.color = "white";
	document.getElementById("btn2").disabled = false;
	document.getElementById("btn2").style.background = "#4E4E50";
	document.getElementById("btn2").style.color = "white";
	document.getElementById("btn3").disabled = false;
	document.getElementById("btn3").style.background = "#4E4E50";
	document.getElementById("btn3").style.color = "white";
}

//Locks voting and sends the choice to the server.
function sendChoice(c, cID){
		disableButtons();
		document.getElementById(cID).style.background = "#a4dac6";
		document.getElementById(cID).style.color = "black";

		//Send data to server here
		var xhr = new XMLHttpRequest();
		xhr.open("POST", "http://adventuretext.quest:80/server/", true);
		xhr.send(JSON.stringify({
			vote: c
		}));
}

function VoteOptions(){
	initializeData();
	return(
		<div className="VoteCountdown">
			<p id="TimerText">Vote countdown will begin shortly!</p>
			<div className="VoteOptions">
				<ol>
					<li><button id="btn1" value={"1"} onClick={c => sendChoice(c.target.value, c.target.id)}></button></li>
					<li><button id="btn2" value ={"2"} onClick={c => sendChoice(c.target.value, c.target.id)}></button></li>
					<li><button id="btn3" value ={"3"} onClick={c => sendChoice(c.target.value, c.target.id)}></button></li>
				</ol>
			</div>
		</div>
	);
}

export default VoteOptions;