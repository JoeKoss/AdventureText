<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

$parentScenario = null;
$currScenario = null;
$savedEnemy = null;
$logEntry = null;
$enemyHP = null;
$level = null; 
$dbConnection = null;

///////////////////////////////////////////////////////////////////////////////////////////////////

function newGame(){
	clearAdventureLog();
	insertNewAdventureLogRow(1);
	updateLogEntry(1);
	updatePlayerStats(null,null,null);
	updateLevel(1);
	updateGlobals();
	getNextScenario(1);
	updateGlobals();
}

function gameOver($nextScenario){
	if(getPlayerStats()[1] >= 1)
		updateAdventureLog($nextScenario[8]);
	else
		updateAdventureLog($nextScenario[9]);
}

function mySQLConnection(){
	$user = 'RemovedForRepository';
	$pass = 'RemovedForRepository';
	if($GLOBALS["dbConnection"] == null)
			$GLOBALS["dbConnection"] = new PDO('mysql:host=RemovedForRepository; dbname=adventuretextdb', $user, $pass);
}

function updateLogEntry($logEntry){
	$updateQuery = 'UPDATE savedVariables SET logEntry = '.$logEntry;

	try{
		$dbQuery = $GLOBALS["dbConnection"] -> query($updateQuery);
	}
	catch(PDOException $e){
		print_r($e);
		die();
	}
}

function updateLevel($level){
	$updateQuery = 'UPDATE savedVariables SET currLevel = '.$level;

	try{
		$dbQuery = $GLOBALS["dbConnection"] -> query($updateQuery);
	}
	catch(PDOException $e){
		print_r($e);
		die();
	}
}

function updateEnemyHP($eHP){
	$updateQuery = 'UPDATE savedVariables SET currEnemyHP = '.$eHP;

	try{
		$dbQuery = $GLOBALS["dbConnection"] -> query($updateQuery);
	}
	catch(PDOException $e){
		print_r($e);
		die();
	}
}

function updateGlobals(){
	$savedScenarios = getSavedScenarios();
	$GLOBALS["parentScenario"] = $savedScenarios[0];;
	$GLOBALS["currScenario"] = $savedScenarios[1];
	if($savedScenarios[0][6] != null)
		$GLOBALS["savedEnemy"] = getDataByID($savedScenarios[0][6], 'enemydb', 'enemyID');

	$savedVariables = getSavedVariables();
	$GLOBALS['level'] = $savedVariables[0];
	$GLOBALS["enemyHP"] = $savedVariables[1];
	$GLOBALS["logEntry"] = $savedVariables[2];
}

function getSavedScenarios(){
	$dataQuery = 'SELECT * FROM savedScenarios';

	try{
		$dbQuery = $GLOBALS["dbConnection"] -> query($dataQuery);
 
		//Returns an array containing the row, i.e. [0][row data]. To get enemy name: print($enemyData[0][1]); 
		$data = $dbQuery -> fetchAll();

		return($data);
	}
	catch(PDOException $e){
		print($e);
		die();
	}
}

function getSavedVariables(){
	$dataQuery = 'SELECT * FROM savedVariables';

	try{
		$dbQuery = $GLOBALS["dbConnection"] -> query($dataQuery);
 
		//Returns an array containing the row, i.e. [0][row data]. To get enemy name: print($enemyData[0][1]); 
		$data = $dbQuery -> fetchAll();

		return($data[0]);
	}
	catch(PDOException $e){
		print($e);
		die();
	}
}

function updateSavedScenarios($sData, $idToUpdate){
	//due to the return of the query, we need to divide the size in half to update the indices.
	for($i=0; $i < (sizeof($sData)/2); $i++){
		if($sData[$i] == null){
			$sData[$i] = 'null';
		}
	}

	$updateQuery = 'UPDATE savedScenarios SET scenarioLevel='.$sData[1].',scenarioText=\''.$sData[2].'\',option1=\''.$sData[3].'\',option2=\''.$sData[4].'\',option3=\''.$sData[5];
	$updateQuery .='\',enemyID='.$sData[6].',lootBool='.$sData[7].',outcome1=\''.$sData[8].'\',outcome2=\''.$sData[9].'\',enemyAmbush='.$sData[10].',autoSuccess='.$sData[11];
	$updateQuery .= ',hasEnemyContext='.$sData[12].',scenarioDC='.$sData[13];

	if($idToUpdate == 1)
		$updateQuery .=' WHERE scenarioID = 1';
	else if($idToUpdate == 2)
		$updateQuery .=' WHERE scenarioID = 2';

	try{
		$dbQuery = $GLOBALS["dbConnection"] -> query($updateQuery);
	}
	catch(PDOException $e){
		print_r($e);
		die();
	}
}

function updateSavedVariables($level, $enemyHP, $newGame){
	if($newGame == true)
		$updateQuery = 'UPDATE savedVariables SET currLevel = 1, currEnemyHP = null';
	else{
		if($level != null)
			$updateQuery = 'UPDATE savedVariables SET currLevel = currLevel + '.$level;
		else
			$updateQuery = 'UPDATE savedVariables SET currEnemyHP = currEnemyHP + '.$enemyHP;
	}

	try{
		$dbQuery = $GLOBALS["dbConnection"] -> query($updateQuery);
	}
	catch(PDOException $e){
		print_r($e);
		die();
	}
}

function getDataByID($dataID, $table, $col){
	//Need to append the enemyID to the query string with .=
	$dataQuery = 'SELECT * FROM '.$table.' WHERE '.$col.' = '.$dataID;

	try{
		$dbQuery = $GLOBALS["dbConnection"] -> query($dataQuery);
 
		//Returns an array containing the row, i.e. [0][row data]. To get enemy name: print($enemyData[0][1]); 
		$data = $dbQuery -> fetchAll();

		return($data[0]);
	}
	catch(PDOException $e){
		print($e);
		die();
	}
}

//Gets the next scenario when given the level of the scenario.
function getNextScenario($scenarioLevel){
	if($scenarioLevel < 9){
		$dataQuery = "SELECT * FROM scenariodb WHERE scenarioLevel = ".$scenarioLevel;

		try{
			$dbQuery = $GLOBALS["dbConnection"] -> query($dataQuery);
	 
			//Returns an array containing the row, i.e. [0][row data]. To get enemy name: print($enemyData[0][1]); 
			$data = $dbQuery -> fetchAll();

			//if there is a tie, randomly choose
			if(sizeof($data) > 1)
				$nextScenario = $data[rand(0,sizeof($data)-1)];
			else
				$nextScenario = $data[0];

			if($scenarioLevel == 6 || $scenarioLevel == 7){
				updateSavedScenarios($nextScenario, 1);
				updateSavedScenarios(getDataByID(1, 'scenariodb', 'scenarioID'), 2);
			}
			else
				updateSavedScenarios($nextScenario, 3);

			if($nextScenario[6] != null)
				updateEnemyHP(getDataByID($nextScenario[6], 'enemydb', 'enemyID')[2]);

			if($scenarioLevel == 8)
				gameOver($nextScenario);

			updateAdventureLog($nextScenario[2]);
		}
		catch(PDOException $e){
			print($e);
			die();
		}
	}
}

function parseText($txt){
	$newTxt = $txt;
	if($GLOBALS["savedEnemy"] != null){
		$newTxt = str_replace("#enemy",$GLOBALS["savedEnemy"][1],$newTxt);
		$newTxt = str_replace("#eDMG",$GLOBALS["savedEnemy"][4],$newTxt);
	}
	$newTxt = str_replace("#pDMG", getPlayerStats()[3],$newTxt);
	$newTxt = str_replace("#sDMG", floor(getPlayerStats()[0] / 4),$newTxt);
	$newTxt = str_replace("'", "\'", $newTxt);

	return $newTxt;
}

function parseLootText($txt, $loot){
	$newTxt = $txt;
	$newTxt = str_replace("#loot",$loot[1],$newTxt);
	$newTxt = str_replace("#statName",$loot[4],$newTxt);
	$newTxt = str_replace("#statVal", $loot[3],$newTxt);

	return $newTxt;
}

function insertNewAdventureLogRow($logEntry){
	$updateQuery = 'INSERT INTO adventurelog VALUES('.$logEntry.',\'\')';

	try{
		$dbQuery = $GLOBALS["dbConnection"] -> query($updateQuery);
	}
	catch(PDOException $e){
		print_r($e);
		die();
	}
}

function updateAdventureLog($data){
	$parsedText = parseText($data);

	if($GLOBALS["logEntry"] == null)
		$updateQuery = 'UPDATE adventurelog SET logText = concat(logText, \''.$parsedText.'<br><br>\') WHERE logEntry = 1';
	else
		$updateQuery = 'UPDATE adventurelog SET logText = concat(logText, \''.$parsedText.'<br><br>\') WHERE logEntry ='.$GLOBALS["logEntry"];

	try{
		$dbQuery = $GLOBALS["dbConnection"] -> query($updateQuery);
	}
	catch(PDOException $e){
		print_r($e);
		die();
	}
}

function clearAdventureLog(){
	$updateQuery = 'TRUNCATE TABLE adventurelog';

	try{
		$dbQuery = $GLOBALS["dbConnection"] -> query($updateQuery);
	}
	catch(PDOException $e){
		print_r($e);
		die();
	}
}

function getAdventureLog(){
	$updateQuery = 'SELECT * FROM adventurelog';

	try{
		$dbQuery = $GLOBALS["dbConnection"] -> query($updateQuery);

		$data = $dbQuery -> fetchAll();
		return $data;
	}
	catch(PDOException $e){
		print_r($e);
		die();
	}
}

function updatePlayerStats($statName, $val, $drankPotion){
	if($statName == null && $val == null && $drankPotion == null)
		$updateQuery = 'UPDATE playerstatsdb SET playerMaxHP = 10, playerCurrHP = 10, playerAC = 10, playerDMG = 1, numPotions = 1';
	else{
		if($drankPotion == false){
			$updateQuery = 'UPDATE playerstatsdb SET '.$statName.' = '.$statName.' + '.$val;
			if($statName == "playerMaxHP")
				updatePlayerStats("playerCurrHP", $val, false);
		}
		else
			$updateQuery = 'UPDATE playerstatsdb SET playerCurrHP = playerMaxHP, numPotions = numPotions - 1';
	}
	
	try{
		$dbQuery = $GLOBALS["dbConnection"] -> query($updateQuery);
	}
	catch(PDOException $e){
		print_r($e);
		die();
	}
}

function getPlayerStats(){
	$dataQuery = 'SELECT * FROM playerstatsdb';

	try{
		$dbQuery = $GLOBALS["dbConnection"] -> query($dataQuery);
 
		//Returns an array containing the row, i.e. [0][row data]. To get enemy name: print($enemyData[0][1]); 
		$data = $dbQuery -> fetchAll();

		return($data[0]);
	}
	catch(PDOException $e){
		print($e);
		die();
	}
}

function randomizeLoot(){
	$dataQuery = 'SELECT * FROM lootdb';

	try{
		$dbQuery = $GLOBALS["dbConnection"] -> query($dataQuery);
 
		//Returns an array containing the row, i.e. [0][row data]. To get enemy name: print($enemyData[0][1]); 
		$data = $dbQuery -> fetchAll();

		return($data[rand(0,sizeof($data)-1)]);
	}
	catch(PDOException $e){
		print($e);
		die();
	}
}

function getLoot(){
	$randomLoot = randomizeLoot();
	updatePlayerStats($randomLoot[2], $randomLoot[3], false);
	$lootText = parseLootText(getAction("Loot", true)[3], $randomLoot);
	updateAdventureLog($lootText);
}

function updateTimer(){
	$t = date("s");

	if($t >= 0 && $t < 20)
		$targetTime = 20;
	else if($t >= 20 && $t < 40)
		$targetTime = 40;
	else
		$targetTime = 0;

	$updateQuery = 'UPDATE timerdb SET targetTime = ';
	$updateQuery .= $targetTime;

	try{
		$dbQuery = $GLOBALS["dbConnection"] -> query($updateQuery);
	}
	catch(PDOException $e){
		print($e);
		die();
	}
}

function getVotingTime(){
	$dataQuery = 'SELECT * FROM timerdb WHERE timeID = 1';

	try{
		$dbQuery = $GLOBALS["dbConnection"] -> query($dataQuery);
 
		//Returns an array containing the row, i.e. [0][row data]. To get enemy name: print($enemyData[0][1]); 
		$data = $dbQuery -> fetchAll();

		return($data[0][1]);
	}
	catch(PDOException $e){
		print($e);
		die();
	}
}

function timerNeedsUpdate(){
	$currTime = date("s");
	$targetTime = getVotingTime();

	if($currTime == $targetTime)
		return true;
	else if($currTime > $targetTime){
		if(((60 - $currTime) + $targetTime) > 20)
			return true;
		else
			return false;
	}
	else if(($targetTime - $currTime) > 20)
		return true;
	else
		return false;
}

function updateVotes($vote){
	if($vote == null)
		$updateQuery = 'UPDATE votetallydb SET numVotes = 0';
	else
		$updateQuery = 'UPDATE votetallydb SET numVotes = numVotes + 1 WHERE optionID = '.$vote;

	try{
		$dbQuery = $GLOBALS["dbConnection"] -> query($updateQuery);
	}
	catch(PDOException $e){
		print($e);
		die();
	}
}

function getWinningVote(){
	$dataQuery = 'SELECT * FROM votetallydb WHERE numVotes = (SELECT Max(numVotes) FROM votetallydb)';

	try{
		$dbQuery = $GLOBALS["dbConnection"] -> query($dataQuery);
 
		//Returns an array containing the row, i.e. [0][row data]. To get enemy name: print($enemyData[0][1]); 
		$data = $dbQuery -> fetchAll();

		//if there is a tie, randomly choose
		if(sizeof($data) > 1)
			$winningVote = $data[rand(0,sizeof($data)-1)];
		else
			$winningVote = $data[0];

		if($winningVote[1] == 0)
			return null;
		else{
			updateVotes(null);
			return $winningVote[0];
		}
	}
	catch(PDOException $e){
		print($e);
		die();
	}
}

function parseVote($action){
	if($action === "Sneak")
		sneak();
	else if($action === "Ambush"){
		enterCombat(false);
		combat(true, false, true);
	}
	else if($action === "Attack"){
		enterCombat(false);
		combat(true, true, false);
	}
	else if($action === "Drink a potion"){
		updatePlayerStats(null,0, true);
		updateAdventureLog(getAction("Drink a potion", true)[3]);
		enterCombat(false);
		combat(false, true, false);
	}
	else if($action === "Look inside" || $action === "Help them" || $action === "Continue forward" || $action === "Sneak past"){
		if($GLOBALS["parentScenario"][11] == true) //checks if a scenario is supposed to auto-succed
			autoSucceed();
		else if($GLOBALS["parentScenario"][6] == null) //checks if a scenario has an enemyID. if not, it is a non-combat scenario
			nonCombat();
		else if($GLOBALS["parentScenario"][12] == true) //checks if a scenario with combat has any needed context first, stored in outcome1
			enterCombat(true);
		else if($GLOBALS["parentScenario"][10] == true){ //checks if there is an enemy ambush
			enterCombat(false);
			combat(false, true, true);
		}
	}
	else if($action === "Flee")
		flee(true);
	else if($action === "Turn back")
		flee(false);
	else if($action === 'Start a new adventure')
		newGame();
}

//Boolean to determine if attack roll and if player or enemy.
function getDiceRoll($attackRoll, $attackMod){
	if($attackRoll == true)
		return rand(1,20) + $attackMod;
	else
		return rand(1,20);
}

function getAction($aName, $bool){
	if($bool == "")
		$bool = 0;

	$dataQuery = "SELECT * FROM actiondb WHERE actionName = '".$aName."' AND successBool = ".$bool;

	try{
		$dbQuery = $GLOBALS["dbConnection"] -> query($dataQuery);
 
		//Returns an array containing the row, i.e. [0][row data]. To get enemy name: print($enemyData[0][1]); 
		$data = $dbQuery -> fetchAll();

		return($data[0]);
	}
	catch(PDOException $e){
		print($e);
		die();
	}
}

//If successful, nextScenario(), otherwise move to combat().
function sneak(){
	//if diceRoll > enemy perception, you sneak successfully
	if(getDiceRoll(false, 0) > $GLOBALS["savedEnemy"][5]){
		updateAdventureLog(getAction("Sneak", true)[3]);
		updateAdventureLog($GLOBALS["parentScenario"][8]);
		updateLevel($GLOBALS["level"] + 1);
		getNextScenario($GLOBALS["level"] + 1);
		updateGlobals();
	}
	else{
		updateAdventureLog(getAction("Sneak", false)[3]);
		enterCombat(false);
	}
}

//calls nextScenario(), with minus one level. if level is already one, reroll level 1.
function flee($inCombat){
	if ($inCombat == false){
		updateAdventureLog(getAction("Turn back", true)[3]);
		if($GLOBALS["level"] > 1){
			updateLevel($GLOBALS["level"] - 1);
			getNextScenario($GLOBALS["level"] - 1);
		}
		else
			getNextScenario($GLOBALS["level"]);
		updateGlobals();
	}
	else{
		if(getDiceRoll(false, 0) >= $GLOBALS["savedEnemy"][6]){
			updateAdventureLog(getAction("Flee", true)[3]);
			if($GLOBALS["level"] > 1){
				updateLevel($GLOBALS["level"] - 1);
				getNextScenario($GLOBALS["level"] - 1);
			}
			else
				getNextScenario($GLOBALS["level"]);
			updateGlobals();
		}
		else{
			updateAdventureLog(getAction("Flee", false)[3]);
			enterCombat(false);
			combat(false, true, false);
		}
	}
}

//Takes a boolean checking whether the combat has additional context
function enterCombat($hasContext){
	if($hasContext == true)
		updateAdventureLog($GLOBALS["parentScenario"][8]); //adds context for the combat before initiating combat
	updateSavedScenarios(getDataByID(1, 'scenariodb', 'scenarioID'), 2);
	updateGlobals();
}

//Keeps going until either the player or enemy is dead, or until the player chooses to flee(). Moves on to nextScenario() when enemy dies, resetGame() when player dies.
function combat($playerAttack, $enemyAttack, $isAmbush){
	if($playerAttack == true && $isAmbush == true){
		updateAdventureLog(getAction("Ambush", true)[3]); //player ambushes the enemy
		updateEnemyHP($GLOBALS["enemyHP"] - getPlayerStats()[3]);
		updateGlobals();
	}
	else if($playerAttack == true){
		if(getDiceRoll(true, getPlayerStats()[3]) >= $GLOBALS["savedEnemy"][3]){
			updateAdventureLog(getAction("Attack", true)[3]); //player hits
			updateEnemyHP($GLOBALS["enemyHP"] - getPlayerStats()[3]);
			updateGlobals();
		}
		else
			updateAdventureLog(getAction("Miss", true)[3]); //player misses
	}

	if($GLOBALS["enemyHP"] <= 0){
		updateAdventureLog($GLOBALS["currScenario"][8]);
		updateAdventureLog($GLOBALS["parentScenario"][9]);
		if($GLOBALS["parentScenario"][7] == true)
			getLoot();
		updateLevel($GLOBALS["level"] + 1);
		getNextScenario($GLOBALS["level"] + 1);
		updateGlobals();
	}
	else{
		if($enemyAttack == true && $isAmbush == true){
			updateAdventureLog(getAction("Ambush", false)[3]); //enemy ambushes the player
			updatePlayerStats("playerCurrHP", -1 * $GLOBALS["savedEnemy"][4], false);
		}
		else if($enemyAttack == true){
			if(getDiceRoll(true, $GLOBALS["savedEnemy"][4]) >= getPlayerStats()[2]){
				updateAdventureLog(getAction("Attack", false)[3]); //enemy hits
				updatePlayerStats("playerCurrHP", -1 * $GLOBALS["savedEnemy"][4], false);
			}
			else
				updateAdventureLog(getAction("Miss", false)[3]); //enemy misses
		}
	}

	//Check if player dies
	if(getPlayerStats()[1] <= 0){
		updateAdventureLog($GLOBALS["currScenario"][9]);
		updateLevel(8);
		getNextScenario(8);
		updateGlobals();
	}
}

function autoSucceed(){
	updateAdventureLog($GLOBALS["parentScenario"][8]);
	if($GLOBALS["parentScenario"][7] == true)
		getLoot();
	updateLevel($GLOBALS["level"] + 1);
	getNextScenario($GLOBALS["level"] + 1);
	updateGlobals();
}

function nonCombat(){
	//Check if player succeeds
	if(getDiceRoll(false, 0) >= $GLOBALS["parentScenario"][13]){
		updateAdventureLog($GLOBALS["parentScenario"][8]);
		if($GLOBALS["parentScenario"][7] == true)
			getLoot();
		updateLevel($GLOBALS["level"] + 1);
		getNextScenario($GLOBALS["level"] + 1);
		updateGlobals();
	}
	else{
		updateAdventureLog($GLOBALS["parentScenario"][9]);
		updatePlayerStats("playerCurrHP", -1 * floor(getPlayerStats()[0] / 4), false);

		//Check if player dies
		if(getPlayerStats()[1] <= 0){
			updateLevel(8);
			getNextScenario(8);
			updateGlobals();
		}
		else{
			if($GLOBALS["parentScenario"][7] == true)
				getLoot();
			updateLevel($GLOBALS["level"] + 1);
			getNextScenario($GLOBALS["level"] + 1);
			updateGlobals();
		}
	}
}

function parseData($sData, $time, $textBox, $playerStats, $level, $currentTime){
	$arrVals = array();
	$arrVals['scenario'] = array($sData[0],$sData[1],$sData[2],$sData[3],$sData[4],$sData[5],$sData[6],$sData[7], $sData[8], $sData[9], $sData[10], $sData[11], $sData[12], $sData[13]);
	$arrVals['time'] = (int)$time;
	$arrVals['adventureLog'] = $textBox;
	$arrVals['playerStats'] = $playerStats;
	$arrVals['level'] = $level;
	$arrVals['currTime'] = $currentTime;

	return $arrVals;
}

////////////////////////////////////////////////////////////////////////////////////////////

if($_SERVER['REQUEST_METHOD'] === 'POST'){
	mySQLConnection();
	$data = json_decode(file_get_contents('php://input'), true);
	$voteOption = $data['vote'];
	updateVotes($voteOption);
}

if($_SERVER['REQUEST_METHOD'] === 'GET'){
	mySQLConnection();
	updateGlobals();

	if($GLOBALS["level"] == null){
		newGame();
	}

	if(timerNeedsUpdate()){
		updateTimer();
		$winningVote = getWinningVote();
		if($winningVote != null){
			updateLogEntry($GLOBALS["logEntry"] + 1);
			updateGlobals();
			insertNewAdventureLogRow($GLOBALS["logEntry"]);
			parseVote($GLOBALS["currScenario"][2 + $winningVote]);
		}
	}

	$votingTime = getVotingTime();
	$currentTime = date("s");
	$textBox = getAdventureLog();
	$playerStats = getPlayerStats();
	$dataArr = parseData($GLOBALS["currScenario"], $votingTime, $textBox, $playerStats, $GLOBALS["level"], $currentTime);

	$dataJSON = json_encode($dataArr);

	echo($dataJSON);
}

?>