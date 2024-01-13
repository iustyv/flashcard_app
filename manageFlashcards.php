<?php
session_start();
include('autoryzacja.php');
$conn=mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die('Connection error: '.mysqli_connect_error());

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
    $temp=mysqli_query($conn, "SELECT * FROM decks WHERE user_id='".$_SESSION['user_id']."' AND deck_id='".$_GET['manage']."';");
    if(!mysqli_num_rows($temp))
    {
        header('Location: manageDecks.php');
        exit();
    }
    else 
        $_SESSION['deck_id']=$_GET['manage'];
    mysqli_free_result($temp);    
}

if(isset($_GET['add']) && $_GET['add']=='c' && isset($_POST['front']))
{
    $queryError=0;
    //transakcja
    mysqli_query($conn, "BEGIN;");
    mysqli_query($conn, "INSERT INTO flashcards_active(front, back, next_revision, last_updated, user_id, deck_id) VALUES ('".$_POST['front']."', '".$_POST['back']."', CURRENT_DATE, CURRENT_TIMESTAMP, '".$_SESSION['user_id']."', '".$_SESSION['deck_id']."');");
        if(mysqli_affected_rows($conn)!=1) $queryError++;
    mysqli_query($conn, "UPDATE decks SET flashcard_count=flashcard_count+1 WHERE deck_id='".$_SESSION['deck_id']."';");
        if(mysqli_affected_rows($conn)!=1) $queryError++;
    if($queryError) //wiadomość o niepowodzeniu
        mysqli_query($conn, "ROLLBACK;");
    else
        mysqli_query($conn, "COMMIT;");

    $_GET['add']='a';
}

//ogarnąć, gdzie powinno to być względem transakcji
$deck_result=mysqli_query($conn, "SELECT * FROM decks WHERE deck_id='".$_SESSION['deck_id']."'");
$deck_info=mysqli_fetch_array($deck_result);

if(isset($_GET['delete']) && $_GET['delete']=='c')
{
    foreach($_POST['flash'] as $flash){
        $queryError= 0;
        mysqli_query($conn,"BEGIN;");
        mysqli_query($conn,"DELETE FROM flashcards_active WHERE flashcard_id='".$flash."';");
        if(mysqli_affected_rows($conn)!=1) $queryError++;
        mysqli_query($conn,"UPDATE decks SET flashcard_count=flashcard_count-1 WHERE deck_id='".$_SESSION['deck_id']."';");
            if(mysqli_affected_rows($conn)!=1) $queryError++;
        if($queryError)
            mysqli_query($conn, "ROLLBACK;");
        else
            mysqli_query($conn, "COMMIT;");
    }
}

if(isset($_GET['edit']) && $_GET['edit']=='c' && isset($_POST['flashcard_id']))//nie wiem, czy sprawdzić flashcard_id czy front
{
    mysqli_query($conn, "UPDATE flashcards_active SET front='".$_POST['front']."', back='".$_POST['back']."' WHERE flashcard_id='".$_POST['flashcard_id']."';");
    $_GET['edit']='a';
}

if(isset($_GET['search']) && $_GET['search']=='c')
    $flashcards_result=mysqli_query($conn, "SELECT * FROM flashcards_active WHERE deck_id='".$_SESSION['deck_id']."' AND (front REGEXP '".$_POST['query']."' OR back REGEXP '".$_POST['query']."');");
else
    $flashcards_result=mysqli_query($conn, "SELECT * FROM flashcards_active WHERE deck_id='".$_SESSION['deck_id']."';"); //nazwa tabeli zalezna od opcji

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
        tbody, tr {width:700px;}
        tbody {height:400px;}

        ::-webkit-scrollbar {width: 10px;}
        ::-webkit-scrollbar-thumb {background: rgb(49, 48, 57);}
        ::-webkit-scrollbar-thumb:hover {background: rgb(95, 71, 235);}
        
    </style>
    <title></title>
</head>
<body>
<aside>
    <h1 class="flashAside"><?php echo $deck_info['deck_name']?></h1>
    <?php
    if(isset($_GET['add']) && $_GET['add']=='a')
        echo '<a href="manageFlashcards.php?add=e" class="flashAside"><button>Manage flashcards</button></a>';
    else 
    {
        echo '<a href="manageFlashcards.php?add=a" class="flashAside"><button>New flashcard</button></a>';
        if(!isset($_GET['add']) || $_GET['add']=='e')
        {
            echo '<div class="flashAside">';
            echo '<a href="manageFlashcards.php?edit=a"><button>Edit</button></a>';
            echo '<a href="manageFlashcards.php?delete=a"><button>Delete</button></a>';
            echo '</div>';
        }
    } 
    ?>
    <nav style="position: relative; top: 340px; left: 0px;">
        <a href="welcome.php">Review flashcards</a>
        <a href="manageDecks.php">Manage decks</a>
        <a href="settings.php">Settings</a>        
        <a href="welcome.php?logOut=1">Log out</a>
    </nav>
</aside>
<main>
    <?php
    if(!isset($_GET['add']) || $_GET['add']=='e')
    {
        echo '<div id="search">';
        echo '<form action="manageFlashcards.php?search=c" method="POST">';
        echo '<input type="text" name="query" required>';
        echo '<input type="submit" value="Search">';
        echo '</form>';        
        echo '<a href="manageFlashcards.php"><button>See all</button></a>';
        echo '</div>';
    }

    if(isset($_GET['add']) && $_GET['add']=='a')
    {
        ECHO<<<HTML
        <h2>Add new flashcard</h2>
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
        <input type="reset" value="Clear">
        </form>
        HTML;
    }
    else if(isset($_GET['delete']) && $_GET['delete']=='a')
    {
        echo '<form method="POST" action="manageFlashcards.php?delete=c">';
        echo '<table class="flashTable">';
        $i=0;
        while($row=mysqli_fetch_array($flashcards_result))
        {
            echo '<tr>';
            echo '<td style="width:fit-content; padding-right:5px; border-right:none;"><input type="checkbox" id="flash'.$i.'" name="flash[]" value="'.$row['flashcard_id'].'"></td>';
            echo '<td><label for="flash'.$i.'">'.$row['front'].'</label></td>';
            echo '<td><label for="flash'.$i.'">'.$row['back'].'</label></td>';
            echo '</tr>';
            $i++;
        }
        echo '</table>';
        echo '<input type="submit" value="Delete">';
        echo '<input type="reset" value="Cancel">';
        echo '</form>';
    }
    else if(isset($_GET['edit']) && is_numeric($_GET['edit']))
    {            
        $temp=mysqli_query($conn, "SELECT * FROM flashcards_active WHERE user_id='".$_SESSION['user_id']."' AND flashcard_id='".$_GET['edit']."';");
        if($row=mysqli_fetch_array($temp))
        {
            echo '<form method="POST" action="manageFlashcards.php?edit=c" id="editFlash">';
            echo '<input type="hidden" name="flashcard_id" value="'.$_GET['edit'].'">';
            echo '<div class="formDiv">';
            echo '<label for id="front">Front</label>';
            echo '<textarea id="front" name="front" rows="8" cols="75" required>'.$row['front'].'</textarea></div>';
            echo '<div class="formDiv">';
            echo '<label for id="back">Back</label>';
            echo '<textarea id="back" name="back" rows="8" cols="75">'.$row['back'].'</textarea></div>';
            echo '<input type="submit" value="Edit">';
            echo '<input type="submit" value="Cancel" formaction="manageFlashcards.php?edit=e"></form>';
        }
            mysqli_free_result($temp);
    }
    else 
    {
        echo '<table class="flashTable">';
        while($row=mysqli_fetch_array($flashcards_result))
        {
            echo '<tr>';
            echo '<td><a href="manageFlashcards.php?edit='.$row['flashcard_id'].'">'.$row['front'].'</a></td>';
            echo '<td><a href="manageFlashcards.php?edit='.$row['flashcard_id'].'">'.$row['back'].'</a></td>';
            echo '</tr>';
        }
        echo '</table>';
    }
    ?>
</main>
</body>
</html>
