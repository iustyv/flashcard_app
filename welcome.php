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
    <title></title>
</head>
<body>
    <table>
        <?php
        while ($row = mysqli_fetch_array($result))
        {
            echo '<tr>';

            echo '<td>'.$row['deck_name'].'</td>';
            echo '<td><a href="review.php?deckId='.$row['deck_id'].'">Review</a></td>';

            echo '</tr>';
        }
        ?>
    </table>
    <a href="manageDecks.php">Manage decks</a>
    <a href="welcome.php?logOut=1">Log out</a>
</body>
</html>