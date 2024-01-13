<?php
session_start();

if (!isset($_SESSION['user_id'])) 
{
    header('Location: index.php');
    exit();
}

include('autoryzacja.php');
$conn=mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die('Connection error: '.mysqli_connect_error());

$result=mysqli_query($conn, "SELECT * FROM user_data WHERE user_id='".$_SESSION['user_id']."';");
$row=mysqli_fetch_array($result);

if(isset($_GET['user']) && $_GET['user']=='e') unset($_POST['username']); //powoduje usunięcie wiadomości o istniejącym nicku i przywrócenie defaultowej value
else if(isset($_POST['username']) && $_POST['username']!=$row['username'])
{
    $temp=mysqli_query($conn, "SELECT * FROM user_data WHERE username=CAST('".$_POST['username']."' AS BINARY);");
    if(!mysqli_num_rows($temp))
    {
      mysqli_query($conn, "UPDATE user_data SET username='".$_POST['username']."' WHERE user_id='".$_SESSION['user_id']."';");
      $row['username']=$_POST['username'];
    }
    else 
      $userError='Username already exists.';
    mysqli_free_result($temp);
}   

if(isset($_POST['passwordOld']))
{
    if($_POST['passwordOld']==$row['password'])
    {
        if($_POST['passwordNew']==$_POST['passwordRepeat'])
        {
            mysqli_query($conn, "UPDATE user_data SET password='".$_POST['passwordNew']."' WHERE user_id='".$_SESSION['user_id']."';");
            $row['password']=$_POST['passwordNew'];
        }    
        else 
            $passError='Please check your new passwords one more time.';
    }
    else 
        $passError='The password does not match your current password.';
}

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
            flex-direction: row;
            column-gap: 70px;
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
    <div>
        <form action="settings.php?user=c" method="POST">
            <div class="formDiv">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php if(isset($_POST['username'])) echo $_POST['username']; else if(isset($row['username'])) echo $row['username']?>">
            </div>
            <input type="submit" value="Confirm">
            <input type="submit" value="Cancel" formaction="settings.php?user=e ">
        </form>
        <br>
        <?php
        if(isset($userError)) echo '<span>'.$userError.'</span>';
        ?>
    </div>

    <br><br>

    <div>
        <form action="settings.php" method="POST">
            <div class="formDiv">
                <label for="passwordOld">Current password</label>
                <input type="password" id="passwordOld" name="passwordOld" value="<?php if(isset($_POST['passwordOld'])) echo $_POST['passwordOld'];?>" required>
            </div>
            <br>
            <div class="formDiv">
                <label for="passwordNew">New password</label>
                <input type="password" id="passwordNew" name="passwordNew" value="<?php if(isset($_POST['passwordNew'])) echo $_POST['passwordNew'];?>" required>
            </div>
            <div class="formDiv">
                <label for="passwordRepeat">Repeat new password</label>
                <input type="password" id="passwordRepeat" name="passwordRepeat" required>
            </div>
            <div class="submitDiv">
                <input type="submit" value="Confirm">
                <input type="reset" value="Cancel">
            </div>
        </form>
        <br>
        <?php 
            if(isset($passError)) echo '<span>'.$passError.'</span>';
        ?>
    </div>
</main>
</body>
</html>
