<?php
session_start();

if (isset($_POST['submit'])) {
    $_SESSION['appId'] = $_POST['appId'];
    $_SESSION['appSecret'] = $_POST['appSecret'];
    $_SESSION['callback'] = $_POST['callback'];
}
var_dump($_SESSION);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>
    <form method="POST">
        <input type="text" name="appId" id="" placeholder="App Id">
        <input type="text" name="appSecret" id="" placeholder="App Secret">
        <input type="text" name="callback" id="" placeholder="Callback Url">
        <input type="submit" name="submit" value="submit">
    </form>
</body>

</html>