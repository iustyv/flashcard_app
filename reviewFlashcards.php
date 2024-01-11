 <?php
session_start();

if(!isset($_SESSION['user_id']))
{
	header('Location: index.php');
	exit();
}

include('autoryzacja.php');
$conn=mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die('Connection error: '.mysqli_connect_error());

if(!isset($_GET['review']) && !isset($_SESSION['deck_id'])) 
{
    header('Location: welcome.php');
    exit();
}
else if(!isset($_SESSION['deck_id'])) 
{
    $temp=mysqli_query($conn, "SELECT * FROM decks WHERE user_id='".$_SESSION['user_id']."' AND deck_id='".$_GET['review']."';");
    if(!mysqli_num_rows($temp))
    {
        header('Location: welcome.php');
        exit();
    }
    else 
        $_SESSION['deck_id']=$_GET['review'];
    mysqli_free_result($temp);    
}

if(!isset($_SESSION['revision']) || (isset($_SESSION['rev_count'],$_SESSION['num_cards']) && $_SESSION['rev_count']==$_SESSION['num_cards'])) //pierwsza powtórka lub kolejne powtórki zapomnianych fiszek
{ 
	$result=mysqli_query($conn, "SELECT flashcard_id, front, back, fluency_level FROM flashcards_active WHERE user_id='".$_SESSION['user_id']."' AND deck_id='".$_SESSION['deck_id']."' AND next_revision<=CURRENT_DATE AND completion=false ORDER BY last_updated;"); //przekazać deck_id
	$_SESSION['num_cards']=mysqli_num_rows($result);

	if ($_SESSION['num_cards']) //pobranie wyniku zapytania, jeżeli są jakieś fiszki do powtórki
	{ 
		$_SESSION['revision']=mysqli_fetch_all($result, MYSQLI_ASSOC);
		$_SESSION['rev_count']=0; //iterator do fiszek
	}

	mysqli_free_result($result);
}
else if(isset($_SESSION['revision'],$_SESSION['rev_count'],$_SESSION['num_cards']))
{
	if(isset($_GET['review']) && !is_numeric($_GET['review'])) 
	{
		$current_level=$_SESSION['revision'][$_SESSION['rev_count']]['fluency_level'];
		$current_id = $_SESSION['revision'][$_SESSION['rev_count']]['flashcard_id'];

		if($_GET['review']=='y')
		{
			switch($current_level)
			{ //ustalenie daty kolejnej powtórki (interwał)
				case 0:
					$days = 1;
					break;
				case 1:
					$days = 2;
					break;
				case 2:
					$days = 5;
					break;
				case 3:
					$days = 10;
					break;
				case 4:
					$days = 23;
					break;
				case 5:
					$days = 50;
					break;	
			}

			if($current_level!=6)
				mysqli_query($conn, "UPDATE flashcards_active SET next_revision=ADDDATE(CURRENT_DATE, $days), fluency_level=fluency_level+1, last_updated=CURRENT_TIMESTAMP WHERE flashcard_id='".$current_id."';");
			else
				mysqli_query($conn, "UPDATE flashcards_active SET completion=true, last_updated=CURRENT_TIMEMSTAMP WHERE flashcard_id='".$current_id."';");
		}
		else if($_GET['review']=='n')
		{
			mysqli_query($conn, "UPDATE flashcards_active SET next_revision=CURRENT_DATE, fluency_level=0, last_updated=CURRENT_TIMESTAMP WHERE flashcard_id='".$current_id."';");
		}
		$_SESSION['rev_count']++;

		if($_SESSION['rev_count']==$_SESSION['num_cards'])
		{
			header('Location: reviewFlashcards.php');
			exit();
		}
	}
}
	
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title></title>
	<style>
	</style>
</head>

<body>
	<?php
	if ($_SESSION['num_cards']==0) {
		echo '<p>There are no more flashcards to review. Come back tomorrow!</p>';
		echo '<a href="welcome.php"><button>Go back</button></a>';
		unset($_SESSION['num_cards'], $_SESSION['revision'], $_SESSION['rev_count']);
	}
	else {
		echo '<p>'.$_SESSION['revision'][$_SESSION['rev_count']]['front'].'</p>';
		echo '<button type="button" id="button" onclick="showBack()">Reveal back</button>';
		echo '<p id="back" style="display:none;">'.$_SESSION['revision'][$_SESSION['rev_count']]['back'].'</p>'; //wyobraź sobie jako tablicę dwuwymiarową wewnątrz wiersza tablicy zmiennych sesyjnych

		ECHO <<< HTML
		<form action="reviewFlashcards.php?review=y" method="POST">
			<input type="submit" value="&#10004;"> <!--przycisk "pamiętam"-->
			<input type="submit" value="&#10006;" formaction="reviewFlashcards.php?review=n"> <!--przycisk "nie pamiętam"-->
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