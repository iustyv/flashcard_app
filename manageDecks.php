<?php
session_start();
if(!isset($_SESSION['user_id']))
{
    header('Location: index.php');
    exit();
}
unset($_SESSION['deck_id']);

include('autoryzacja.php');
$conn=mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die('Connection error: '.mysqli_connect_error());

if(isset($_GET['add']) && $_GET['add']=='c' && isset($_POST['deck_name']))
    mysqli_query($conn, "INSERT INTO decks(deck_name, user_id) VALUES ('".$_POST['deck_name']."', '".$_SESSION['user_id']."');");

if(isset($_GET['rename']) && $_GET['rename']=='c' && isset($_POST['deck_name']))
    mysqli_query($conn, "UPDATE decks SET deck_name='".$_POST['deck_name']."' WHERE deck_id='".$_POST['deck_id']."';");

if(isset($_GET['delete']) && $_GET['delete']=='c') //czy powinna być transkacja
{
    $queryError=0;
    $temp=mysqli_query($conn, "SELECT * FROM decks WHERE deck_id='".$_POST['deck_id']."';");
    $row=mysqli_fetch_array($temp);
    mysqli_query($conn, "BEGIN;");
    mysqli_query($conn, "DELETE FROM flashcards_active WHERE deck_id='".$_POST['deck_id']."';");  
        if(mysqli_affected_rows($conn)!=$row['flashcard_count']) $queryError; 
    mysqli_query($conn, "DELETE FROM decks WHERE deck_id='".$_POST['deck_id']."';");
        if(mysqli_affected_rows($conn)!=1) $queryError++;
    if($queryError) //wiadomość o niepowodzeniu
        mysqli_query($conn, "ROLLBACK;");
    else
        mysqli_query($conn, "COMMIT;");
    mysqli_free_result($temp);
}

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
    <style>
        main {
            height: 100vh;
            width: 87%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding-right: 25%;
        }

        nav {
            display: flex;
            width: 13%;
            flex-direction: column;
            justify-content: flex-end;
            padding: 30px 0px 30px 30px;
        }
        .decksTable tr {
            justify-content: flex-end;
            column-gap: 10px;
        }

    </style>
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

            if(isset($_GET['rename']) && $_GET['rename']==$row['deck_id'])
            {
                echo '<form action="manageDecks.php?rename=c" method="POST">';
                echo '<td><input type="text" name="deck_name" value="'.$row['deck_name'].'" required></td>';
                //parametr value nie załatwia sprawy całkowicie, bo jeżeli użytkownik go usunie, a potem się rozmyśli, nie będzie mógł wcisnąć przycisku cancel
                echo '<input type="hidden" name="deck_id" value="'.$_GET['rename'].'">';
                echo '<td><input type="submit" value="Confirm">';
                echo '<input type="submit" value="Cancel" formaction="manageDecks.php?rename=e">';
                echo '</td></form>';
            }
            else 
            {
                echo '<td><a href="manageFlashcards.php?manage='.$row['deck_id'].'" class="deckName">'.$row['deck_name'].'</a></td>';
                echo '<td><a href="manageDecks.php?rename='.$row['deck_id'].'">Rename</a></td>';
            }

            if(isset($_GET['delete']) && $_GET['delete']==$row['deck_id'])
            {
                echo '<form action="manageDecks.php?delete=c" method="POST">';
                echo '<input type="hidden" name="deck_id" value="'.$_GET['delete'].'">';
                echo '<td><input type="submit" value="Confirm">';
                echo '<input type="submit" value="Cancel" formaction="manageDecks.php?delete=e">';
                echo '</td></form></tr>';
                echo '<tr><td>! All flashcards from the deck will also be deleted.</td></tr>';
            }
            else 
            	echo '<td><a href="manageDecks.php?delete='.$row['deck_id'].'">Delete</a></td></tr>';

        }

        /*
        1. action (a) lub id - użytkownik wybrał akcję do wykonania, id w przypadku działań na konkretnych rekordach
        2. confirm (c) - użytkownik potwierdził zmiany
        3. exit (e) - użytkownik zrezygnował z wprowadzenia zmian*/

        if(isset($_GET['add']) && $_GET['add']=='a')
        {
            ECHO<<<HTML
            
            <tr>
            <form action="manageDecks.php?add=c" method="POST">
            <td><input type="text" name="deck_name" value="Deck name" required>
            <input type="submit" value="Add">
            <input type="submit" value="Cancel" formaction="manageDecks.php?add=e"></td>
            </form>
            </tr>

            HTML;
        }
        else 
            echo '<tr><td><a href="manageDecks.php?add=a">Add new deck</a></td></tr>';

        ?> 
    </table>
</main>
</body>
</html>