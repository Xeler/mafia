<?php
	/*
	 *	I JS: createLobby -- Laver ny lobby.
	 *		  le
	 *		  le
	 *
	 *
	 */
	
	
	
	
	mysql_connect("autistklassen.dk", "web83-general", "General-Pass");
	mysql_select_db("web83-general");
	
	
	
	if(isset($_POST["action"])) {
		$action = $_POST["action"];
		switch($action) {
			case "createLobby":
				
				//Find max ID pa rummet og adder med en for at finde ID til nyt rum
				$lobby_id = mysql_query("SELECT MAX(LOBBY_ID) FROM lobby");
				$lobby_id = mysql_result($lobby_id, 0)+1;
				
				//Lav et ID til spilleren som laver rummet
				$id = md5(microtime());
				
				//Find NR til spilleren
				$player_nr = mysql_query("SELECT MAX(PLAYER_NR) FROM lobby");
				$player_nr = mysql_result($player_nr, 0)+1;
				
				mysql_query("INSERT INTO lobby VALUES ('$lobby_id', '$id', '$player_nr', '1')");
				
				echo "var USER_ID='" . $id . "';";
				
				die("alert('lobby created!');");
				
				
			break;
			
			
			
			case "joinLobby":
				$lobby_id = mysql_real_escape_string($_POST["lobby_id"]);
				
				//Lav et ID til spilleren som joiner rummet
				$id = md5(microtime());
				
				//Find NR til spilleren
				$player_nr = mysql_query("SELECT MAX(PLAYER_NR) FROM lobby");
				$player_nr = mysql_result($player_nr, 0)+1;
				
				mysql_query("INSERT INTO lobby VALUES ('$lobby_id', '$id', '$player_nr', '$0')");
				
				echo "var USER_ID='" . $id . "';";
				
				
				
				
				
			break;
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			
			case "leave":
				$id = mysql_real_escape_string($_POST["id"]);
				mysql_query("DELETE FROM lobby WHERE PLAYER_ID='$id'");
				
				alert("bye!");
				
				
			break;
			
			
			
			
			default:
				die("alert('Error! Unkown action!');");
			break;
		}
		
	}
	
	
	
	
?>