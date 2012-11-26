<?php
	/*
	 *	events:	createLobby -- Laver ny lobby.
	 *			joinLobby -- tilslut en lobby. Kraver POST['id'] = bruger ID
	 *			sendRefresh -- sender anmodning om opdatering af tid; sikrer at brugeren forbliver 'online'. Kraver POST['id'] = bruger ID
	 *			getPlayers -- finder en liste over brugerer som er i et rum med spilleren. Kraver POST['id'] = bruger ID && POST['time'] = tid pa hvornar sidste opdatering skete.
	 *
	 */
	
	
	
	
	mysql_connect("autistklassen.dk", "web83-general", "General-Pass");
	mysql_select_db("web83-general");
	
	
	
	if(isset($_POST["action"])) {
		action($_POST["action"]);
	}

	
		
	function action($event) {
	//Tid til brug af tjekning om en bruger er inaktiv eller ej.
	$time = time();
	
	
	
		switch($event) {
			case "createLobby":
				
				//Find max ID pa rummet og adder med en for at finde ID til nyt rum
				$lobby_id = mysql_query("SELECT MAX(LOBBY_ID) FROM lobby");
				$lobby_id = mysql_result($lobby_id, 0)+1;
				
				//Lav et ID til spilleren som laver rummet
				$id = md5(microtime());
				
				//Find NR til spilleren
				$player_nr = mysql_query("SELECT MAX(PLAYER_NR) FROM lobby WHERE LOBBY_ID='$lobby_id'");
				$player_nr = mysql_result($player_nr, 0)+1;
				
				//Lav lobby
				mysql_query("INSERT INTO lobby(LOBBY_ID, PLAYER_ID, PLAYER_NR, IS_ADMIN, LAST_ACTION) VALUES ('$lobby_id', '$id', '$player_nr', '1', '$time')");
				//Lav ny entry i update-table
				mysql_query("INSERT INTO updates(lobby_id) VALUES('" . $lobby_id . "')");
				
				
				echo "USER_ID='" . $id . "';";
				echo "STATUS='IN_LOBBY';";
				echo "LAST_USER_UPDATE_TIME=0;";
				die("alert('lobby created!');");
				
			break;
			
			
			
			case "joinLobby":
				$lobby_id = mysql_real_escape_string($_POST["lobby_id"]);
				
				//Tjek om lobby'en findes; hvis ikke, stop med fejl-meddelse.
				$query = mysql_query("SELECT * FROM lobby WHERE LOBBY_ID='" . $lobby_id . "'");
				if(mysql_num_rows($query)==0) {
					die("alert('Lobby not found!');");
				}
				
				//Slet inaktive brugere i denne lobby.
				deleteInactive($lobby_id);
				
				
				
				
				
				
				//Lav et ID til spilleren som joiner rummet
				$id = md5(microtime());
				
				//Find NR til spilleren
				$player_nr = mysql_query("SELECT MAX(PLAYER_NR) FROM lobby WHERE LOBBY_ID='$lobby_id'");
				$player_nr = mysql_result($player_nr, 0)+1;
				
				//Indsat brugeren ind i 'lobby'.
				mysql_query("INSERT INTO lobby(LOBBY_ID, PLAYER_ID, PLAYER_NR, IS_ADMIN, LAST_ACTION) VALUES ('$lobby_id', '$id', '$player_nr', '0', '$time')");
				
				//Opdater timeren til players i updates
				mysql_query("UPDATE updates SET players='" . time() . "' WHERE lobby_id='" . $lobby_id . "'");
				
				
				
				
				echo "USER_ID='" . $id . "';";
				echo "STATUS='IN_LOBBY';";
				echo "LAST_USER_UPDATE_TIME=0;";
				die("alert('lobby joined!');");
				
				
				
			break;
			
			
			
			case "sendRefresh":
				$id = mysql_real_escape_string($_POST["id"]);
				
				mysql_query("UPDATE lobby SET LAST_ACTION='$time' WHERE PLAYER_ID='$id'");
				
			break;
			
			
			case "getPlayers":
				$id = mysql_real_escape_string($_POST["id"]);
				$last_user_update = $_POST["last_update"];
				
				
				
				
				//Find room ID
				$room = mysql_result(mysql_query("SELECT LOBBY_ID FROM lobby WHERE PLAYER_ID='$id'"), 0);
				
				deleteInactive($room); //Slet alle inaktive brugere; dvs. brugere som ikke sendt en sendRefresh-request i de sidste 10 sekunder.
				
				//Tjek om der er en opdatering under 'players' i updates.
				$lastupdate = mysql_result(mysql_query("SELECT players FROM updates WHERE lobby_id='$room'"), 0);
				
				if($lastupdate<$last_user_update) {
					die();
				}
				
				$query = mysql_query("SELECT * FROM lobby WHERE LOBBY_ID='$room'");
				
				echo "document.getElementById('playerlist').innerHTML = \"";
				while($row = mysql_fetch_array($query)) {
					if($row['PLAYER_ID']==$id) {
						echo "- <span style='color: green;'>Player " . $row['PLAYER_NR'] . "(me)</span><br />";
					} else {
						echo "- <span style='color: black;'>Player " . $row['PLAYER_NR'] . "</span><br />";
					}
				}
				echo "\";"; //slut pa andring i playerlist
				
				echo "LAST_USER_UPDATE_TIME=$time;";
				
			break;
			
			
			
			
			
			
			
			default:
				die("alert('Error! Unkown action!');");
			break;
		}
		
	}
	
	
	
	
	
	
	
	
	function deleteInactive($lobby_id) {
		$timeout_limit = time()-3; //serveren har ikke modtaget de sidste 3 refresh/beskeder, sa vi regner ikke med at han er i sin browser langere.
		
		mysql_query("DELETE FROM lobby WHERE LAST_ACTION<" . $timeout_limit . " AND LOBBY_ID='" . $lobby_id . "'");
		
		
		
		if(mysql_affected_rows()>0) {
			mysql_query("UPDATE updates SET players='" . time() . "' WHERE lobby_id='" . $lobby_id ."'");
		}
	}
	
	
	
?>