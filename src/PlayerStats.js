var hp_counter = "❤️ HP: "
var armor_counter = "🛡️ Armor: "
var damage_counter = "⚔️ Damage: "
var potion_counter = "🧪 Potions: "
var errorText = "Error";

function refreshPlayerStats(){
	fetch("http://adventuretext.quest:80/server/").then((res)=>res.json()).then(data =>{
		document.getElementById("hpCounter").innerHTML = hp_counter + data.playerStats[1];
		document.getElementById("armorCounter").innerHTML = armor_counter + data.playerStats[2];
		document.getElementById("dmgCounter").innerHTML = damage_counter + data.playerStats[3];
		document.getElementById("potionCounter").innerHTML = potion_counter + data.playerStats[4];
	});
}

function PlayerStats(){
	refreshPlayerStats();
	return(
		<div className='PlayerStats'>
			<ul>
				<li id="hpCounter">{errorText}</li>
				<li id="armorCounter">{errorText}</li>
				<li id="dmgCounter">{errorText}</li>
				<li id="potionCounter">{errorText}</li>
			</ul>
		</div>
	);
}

export default PlayerStats;