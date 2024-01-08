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

if(isset($_POST['username']) && $_POST['username']!=$row['username'])
{//sprawdzic czy nowy username nie wystepuje juz w bazie
    mysqli_query($conn, "UPDATE user_data SET username='".$_POST['username']."' WHERE user_id='".$_SESSION['user_id']."';");
    $row['username']=$_POST['username'];
}    

if(isset($_POST['passwordOld']) && $_POST['passwordOld']==$row['password'])
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
    $passError='The password does not match your current password.'
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <style>
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
    <form action="settings.php" method="POST">
        <div class="formDiv">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php if(isset($row['username'])) echo $row['username']?>">
        </div>
        <input type="submit" value="Confirm">
        <input type="submit" value="Cancel" formaction="settings.php">
    </form>

    <br><br>

    <form action="settings.php" method="POST">
        <div class="formDiv">
            <label for="passwordOld">Current password</label>
            <input type="password" id="passwordOld" name="passwordOld" value="<?php if(isset($_POST['passwordOld'])) echo $_POST['passwordOld'];?>" required>
        </div>
        <div class="formDiv">
            <label for="passwordNew">New password</label>
            <input type="password" id="passwordNew" name="passwordNew" value="<?php if(isset($_POST['passwordNew'])) echo $_POST['passwordNew'];?>" required>
        </div>
        <div class="formDiv">
            <label for="passwordRepeat">Repeat new password</label>
            <input type="password" id="passwordRepeat" name="passwordRepeat" required>
        </div>
        <input type="submit" value="Confirm">
        <input type="reset" value="Cancel">
    </form>
    <?php 
        if(isset($passError)) echo '<span>'.$passError.'</span>';
    ?>
    <a href="welcome.php">Go back</a>
</body>
</html>
