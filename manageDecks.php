<?php
session_start();
if(!isset($_SESSION['user_id']))
{
    header('Location: index.php');
    exit();
}

include('autoryzacja.php');
$conn=mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die('Connection error: '.mysqli_connect_error());

if(isset($_GET['add']) && $_GET['add']=='c' && isset($_POST['deck_name']))
    mysqli_query($conn, "INSERT INTO decks(deck_name, user_id) VALUES ('".$_POST['deck_name']."', '".$_SESSION['user_id']."');");

if(isset($_GET['rename']) && $_GET['rename']=='c' && isset($_POST['deck_name']))
    mysqli_query($conn, "UPDATE decks SET deck_name='".$_POST['deck_name']."' WHERE deck_id='".$_POST['deck_id']."';");

if(isset($_GET['delete']) && $_GET['delete']=='c')
    mysqli_query($conn, "DELETE FROM decks WHERE deck_id='".$_POST['deck_id']."';");

//dodać transakcję do usunięcia wszystkich fiszek z decku i wiadomość ostrzegającą

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
                echo '<td>'.$row['deck_name'].'</td>';
                echo '<td><a href="manageDecks.php?rename='.$row['deck_id'].'">Rename</a></td>';
            }

            if(isset($_GET['delete']) && $_GET['delete']==$row['deck_id'])
            {
                echo '<form action="manageDecks.php?delete=c" method="POST">';
                echo '<input type="hidden" name="deck_id" value="'.$_GET['delete'].'">';
                echo '<td><input type="submit" value="Confirm">';
                echo '<input type="submit" value="Cancel" formaction="manageDecks.php?delete=e">';
                echo '</td></form>';
            }
            else 
            	echo '<td><a href="manageDecks.php?delete='.$row['deck_id'].'">Delete</a></td>';

            echo '</tr>';
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
    <a href="welcome.php">Go back</a>
</body>
</html>