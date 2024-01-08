<?php

session_start();
if(!isset($_SESSION['user_id']))
{
    header('Location: index.php');
    exit();
}
if(!isset($_GET['manage']) && !isset($_SESSION['deck_id'])) 
{
    header('Location: manageDecks.php');
    exit();
}
else if(!isset($_SESSION['deck_id'])) 
{
    $temp=mysqli_query($conn, "SELECT * FROM decks WHERE user_id='".$_SESSION['user_id']."';");
    $access=false;
    while($row = mysqli_fetch_array($temp)) 
    {
        if($row['deck_id']==$_GET['manage'])
        {
            $access=true;
            $_SESSION['deck_id']=$_GET['manage'];
            break;
        }
    }
    if(!$access)
    {
        header('Location: manageDecks.php');
        exit();
    }
    mysqli_free_result($temp);
}
    

include('autoryzacja.php');
$conn=mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die('Connection error: '.mysqli_connect_error());

if(isset($_GET['add']) && $_GET['add']=='e') unset($_GET['add']); //czy to na pewno potrzebne

if(isset($_GET['add']) && $_GET['add']=='c' && isset($_POST['front']))
{
    $queryError=0;
    //transakcja
    mysqli_query($conn, "BEGIN;");
    mysqli_query($conn, "INSERT INTO flashcards_active(front, back, user_id, deck_id) VALUES ('".$_POST['front']."', '".$_POST['back']."', '".$_SESSION['user_id']."', '".$_SESSION['deck_id']."');");
    $_GET['add']='a';
        if(mysqli_affected_rows($conn)!=1) $queryError++;
    mysqli_query($conn, "UPDATE decks SET flashcard_count=flashcard_count+1 WHERE deck_id='".$_SESSION['deck_id']."';");
        if(mysqli_affected_rows($conn)!=1) $queryError++;
    if($queryError)
        mysqli_query($conn, "ROLLBACK;");
    else
        mysqli_query($conn, "COMMIT;");
    //czy do countera powinny się liczyć także fiszki z archiwum?
}

//ogarnąć, gdzie powinno to być względem transakcji
$deck_result=mysqli_query($conn, "SELECT * FROM decks WHERE deck_id='".$_SESSION['deck_id']."'");
$deck_info=mysqli_fetch_array($deck_result);

if(isset($_GET['delete']) && $_GET['delete']=='c')
{
    for($i=0; $i<$deck_info['flashcard_count']; $i++)
    {
        if(isset($_POST['flash'.$i]))
        {
            $queryError= 0;
            mysqli_query($conn,"BEGIN;");
            mysqli_query($conn,"DELETE FROM flashcards WHERE flashcard_id='".$_POST['flash'.$i]."';");
                if(mysqli_affected_rows($conn)!=1) $queryError++;
            mysqli_query($conn,"UPDATE decks SET flashcard_count=flashcard_count-1 WHERE deck_id='".$_SESSION['deck_id']."';");
                if(mysqli_affected_rows($conn)!=1) $queryError++;
            if($queryError)
                mysqli_query($conn, "ROLLBACK;");
            else
                mysqli_query($conn, "COMMIT;");
        }
    }
}

$flashcards_result=mysqli_query($conn, "SELECT * FROM flashcards_active WHERE deck_id='".$_SESSION['deck_id']."';"); //nazwa tabeli zalezna od opcji

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            display: flex;
        }

        .formDiv {
            display: flex;
            flex-direction: column;
        }

        .formDiv input {
            width:200px;
        }
    </style>
</head>
<body>
<aside>
    <button><a href="manageDecks.php">Go back</a></button>
    <h1><?php echo $deck_info['deck_name']?></h1>
    <?php
    if(isset($_GET['add']) && $_GET['add']=='a')
        echo '<button><a href="manageFlashcards.php?add=e">Manage flashcards</a></button>';
    else 
        echo '<button><a href="manageFlashcards.php?add=a">New flashcard</a></button>';
    ?>
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
            <textarea id="front" name="front" rows="8" cols="75" required></textarea>
        </div>
        <div class="formDiv">
            <label for="back">Back</label>
            <textarea id="back" name="back" rows="8" cols="75"></textarea>
        </div>
        <input type="submit" value="Add">
        <input type="submit" value="Cancel" formaction="manageFlashcards.php?add=e">
        </form>
        HTML;
    }
    else 
    {
        echo '<form method="POST" action="manageFlashcards.php?delete=c">';
        echo '<table>';
        $i=0;
        while($row=mysqli_fetch_array($flashcards_result))
        {
            echo '<tr>';
            echo '<td><input type="checkbox" id="flash'.$i.'" name="flash'.$i.'" value="'.$row['flashcard_id'].'"></td>';
            echo '<label for="flash'.$i.'">';
            echo '<td>'.$row['front'].'</td>';
            echo '<td>'.$row['back'].'</td></label>';
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
