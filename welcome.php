<?php
session_start();

if (!isset($_SESSION['user_id'])) 
{
    header('Location: index.php');
    exit();
}

if(isset($_GET['logOut']) && $_GET['logOut']){
    session_unset();
    header('Location: index.php');
    exit();
}
      
include('autoryzacja.php');
$conn=mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die('Connection error: '.mysqli_connect_error());

$result=mysqli_query($conn, "SELECT * FROM decks WHERE user_id='".$_SESSION['user_id']."';");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <title></title>
</head>
<body>
<nav>
    <a href="welcome.php">Review flashcards</a>
    <a href="manageDecks.php">Manage decks</a>
    <a href="settings.php">Settings</a>
    <a href="welcome.php?logOut=1">Log out</a>  
</nav>
<main>
    <table class="decksTable">
        <?php
        while ($row = mysqli_fetch_array($result))
        {
            echo '<tr>';

            echo '<td>'.$row['deck_name'].'</td>';
            echo '<td><a href="reviewFlashcards.php?review='.$row['deck_id'].'">Review</a></td>';

            echo '</tr>';
        }
        ?>
    </table>
</main>
</body>
</html>