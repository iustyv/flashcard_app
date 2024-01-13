<?php
session_start();

if(isset($_SESSION['user_id']))
{
    header('Location: welcome.php');
    exit();
}

include('autoryzacja.php');
$conn=mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die('Connection error: '.mysqli_connect_error());

if((isset($_GET['signUp']) && $_GET['signUp']) || (isset($_GET['action']) && $_GET['action']=='signUp')) //kontrola treści formularza
    $action='signUp';
else
    $action='logIn';
/*
dwa przypadki: 
1. użytkownik wybrał opcję sign up, dlatego dane formularza muszą zostać odpowiednio dopasowane
2. użytkownik niepoprawnie wpisał potwierdzenie hasła, dlatego ponownie musi zostać wyświetlony formularz sign up
*/

if(isset($_POST['username']))
{
    if($_GET['action']=='logIn') //musi być GET, ponieważ ta część jest obsługiwana po przesłaniu formularza - formularz korzysta ze zmiennej $action, żeby ustawić odpowiednie pola, a po wypełnieniu przesyła ją z powrotem GETem
    {
        $result=mysqli_query($conn, "SELECT * FROM user_data WHERE username=CAST('".$_POST['username']."' AS BINARY) AND password=CAST('".$_POST['password']."' AS BINARY);");
        if(mysqli_num_rows($result)==1)
        {
            $row=mysqli_fetch_array($result);
            $_SESSION['user_id']=$row['user_id'];
            $_SESSION['username']=$row['username'];
            header('Location: welcome.php');
        }   
        else 
            $logInError='Please check your username and password one more time.';
    }
    else 
    {
        $result=mysqli_query($conn,"SELECT * FROM user_data WHERE username=CAST('".$_POST['username']."' AS BINARY);");
        if(!mysqli_num_rows($result))
        {
            if($_POST['password']==$_POST['passwordRepeat'])
            {
                mysqli_query($conn, "INSERT INTO user_data(username, password) VALUES ('".$_POST['username']."', '".$_POST['password']."');");
                $result=mysqli_query($conn, "SELECT * FROM user_data WHERE username=CAST('".$_POST['username']."' AS BINARY) AND password=CAST('".$_POST['password']."' AS BINARY);");

                $row=mysqli_fetch_array($result);
                $_SESSION["user_id"]=$row["user_id"];
                $_SESSION['username']=$row['username'];
                header('Location: welcome.php');
            }
            else
                $signUpError='Please make sure your passwords match.';
        }
        else 
            $signUpError='Username already exists. Please try again.';
    }
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
        a {
        color: rgb(139, 134, 167);
        transition-duration: 300ms;
    }

    a:hover {
        color: rgb(95, 71, 235);
        transition-duration: 300ms;
    }

    input[type=submit] {
        padding: 7px 15px;
        font-size: 17px;
        margin-right: 20px;
    }

    span {
        font-size: 17px;
    }
    </style>
    <title></title>
</head>
<body>
<main>
    <form method="POST" action="index.php?action=<?php echo $action;?>">
        <div class="formDiv">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?php if(isset($_POST['username'])) echo $_POST['username']?>" autocomplete="off" required>
        </div>
        <div class="formDiv">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" value="<?php if(isset($_POST['password'])) echo $_POST['password']?>" autocomplete="off" required>
        </div>
        <?php
            if($action=='logIn')
            {
                ECHO<<<HTML
                <br><input type="submit" value="Log in">
                <span>Don't have an account? <a href="index.php?signUp=1">Sign up</a></span>
                HTML;
            }
            else
            {
                ECHO<<<HTML
                <div class="formDiv">
                    <label for="passwordRepeat">Repeat password</label>
                    <input type="password" id="passwordRepeat" name="passwordRepeat" autocomplete="off" required>
                </div><br>
                <input type="submit" value="Sign up">
                <span>Already have an account? <a href="index.php?signUp=0">Log in</a></span>
                HTML;
            }

            if (isset($logInError))    
                echo '<p>'.$logInError.'</p>';

            if (isset($signUpError))    
                echo '<p>'.$signUpError.'</p>';

            unset($logInError, $signUpError);

            ?>  
    </form>
</main>
</body>
</html>