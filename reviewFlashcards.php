 <?php
session_start();

if ( ! isset( $_SESSION['revision'] ) || $_SESSION['rev_count'] == $_SESSION['num_cards'] ) { //pierwsza powtórka lub kolejne powtórki zapomnianych fiszek

	require_once 'connect.php';
	$connection = new mysqli( $host, $dbUsername, $dbPassword, $dbName );

	$result = $connection->query( "SELECT id, front, back, fluency_level FROM flashcards WHERE next_revision <= CURRENT_DATE AND completion = false ORDER BY last_updated;" );
	$_SESSION['num_cards'] = $result->num_rows;

	if ( $_SESSION['num_cards'] ) { //pobranie wyniku zapytania, jeżeli są jakieś fiszki do powtórki
		$_SESSION['revision'] = $result->fetch_all( MYSQLI_ASSOC );
		$_SESSION['rev_count'] = 0; //iterator do fiszek
	}

	$result->free_result();
	$connection->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<title>Review flashcards</title>
	<style>
		body {
				height: 100vh;
				display: flex;
				flex-direction: column;
				align-items: center;
				justify-content: center;
				font-size: larger;
				font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
			}
	</style>
</head>

<body>
	<?php
	if ( $_SESSION['num_cards'] == 0 ) {
		echo "<p>There are no more flashcards to review. Come back tomorrow!</p>";
		session_unset();
	}
	else {
		echo '<p>'.$_SESSION['revision'][ $_SESSION['rev_count'] ]['front'].'</p>';
		echo '<button type="button" id="button" onclick="showBack()">Reveal back</button>';
		echo '<p id="back" style="display:none;">'.$_SESSION['revision'][ $_SESSION['rev_count'] ]['back'].'</p>';

		ECHO <<< HTML
		<form action="review_yes.php" method="GET">
			<input type="submit" value="&#10004;"> <!--przycisk "pamiętam"-->
			<input type="submit" value="&#10006;" formaction="review_no.php"> <!--przycisk "nie pamiętam"-->
		</form>
		<script>
			function showBack() {
				document.getElementById("back").style.display = "block";
				document.getElementById("button").style.display = "none";
			}
		</script>
		HTML;

	}
	?>
	


</body>

</html>