var errorText = "If you're seeing this, something has gone wrong!";

function refreshTextBox(){
	fetch("http://adventuretext.quest:80/server/").then((res)=>res.json()).then(data =>{
		// eslint-disable-next-line
		if(data.length == 1){
			document.getElementById("textLog").innerHTML = "";
			document.getElementById("newText").innerHTML = data.adventureLog[0][1];
		}
		else{
			var logText = "";
			for(let i=0; i<data.adventureLog.length - 1; i++)
				logText += data.adventureLog[i][1];
			document.getElementById("textLog").innerHTML = logText;
			document.getElementById("newText").innerHTML = data.adventureLog[data.adventureLog.length - 1][1];
		}
		document.getElementById("newText").scrollIntoView(false);
	});
}

function TextBox(){
	refreshTextBox();
	return(
		<div className="TextBox">
			<p id="textLog">{errorText}</p>
			<p id="newText"></p>
		</div>
	);
}

export default TextBox;