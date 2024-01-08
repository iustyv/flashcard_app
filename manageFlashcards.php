<?php

session_start();
if(!isset($_SESSION['user_id']))
{
    header('Location: index.php');
    exit();
}

//test2
//test3
//łącze z bazą danych
//sprawdzić czy w został przekazany deck_id w GET w pliku manageDecks.php

if(isset($_GET['add']) && $_GET['add']=='e') unset($_GET['add']);

if(isset($_GET['add']) && $_GET['add']=='c' && isset($_POST['front']))
{
    //dodać if, jeżeli uzytkownik nie wpisał nic w polu back
    mysqli_query($conn, "INSERT INTO flashcards(front, back, user_id, deck_id) VALUES ('".$_POST['front']."', '".$_POST['back']."', '".$_SESSION['user_id']."', '".."');"); //przekazać deck_id
    $_GET['add']='a';
}

$result=mysqli_query($conn, "SELECT * FROM flashcards WHERE deck_id='".."';") //przekazać deck_id;

?>
<!DOCTYPE html>
<html lang="en">
<head>
</head>
<body>
<aside>
    <button><a href="manageDecks.php">Go back</a></button>
    <h1><?php echo $_GET['deck_id']?></h1>
    <button><a href="manageFlashcards.php?add=a">New flashcard</a></button>
</aside>
<main>
    <?php 
    if(isset($_GET['add']) && $_GET['add']=='a')
    {
        ECHO<<<HTML
        <h1>Add new flashcard</h1>
        <form method="POST" action="manageFlashcards.php?add=c">
        <div class="formDiv">
            <label for="front">Front</label>
            <textarea id="front" name="front" required><!--dodać kolumny i wiersze-->
        </div>
        <div class="formDiv">
            <label for="back">
            <textarea id="back" name="back">
        </div>
        <input type="submit" value="Add">
        <input type="submit" value="Cancel" formaction="manageFlashcards.php?add=e">
        </form>
        HTML;
    }
    else 
    {
        echo '<form method="POST" action="manageFlashcards?delete=c.php">';
        echo '<table>'
        $i=0;
        while($row=mysqli_fetch_array($result))
        {
            echo '<tr>'
            echo '<td><input type=checkbox name="flash'.$i.'" id="flash'.$i.'" value="'$row['flashcard_id']'"></td>';
            echo '<label for="flash'.$i.'">';
            echo '<td>'.$row['front'].'</td>';
            echo '<td>'.$row['back'].'</td>';
            echo '</tr>';
            $i++;
        }
        echo '</table>';
        echo '<input type="submit" value="Delete">';
        echo '<input type="reset" value="Cancel">';
        echo '</form>';
    }
    ?>
</main>
</body>
</html>
