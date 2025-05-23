<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.ico">
    <title>FDM Expenses - Login</title>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.html"> <img class="logo" src="images/FDM_Group_Logo_White.png"></a>
            <ul>

            </ul>
        </nav>
    </header>
    <h1>Login</h1>
    <form id="loginForm" method="post" action="php/login.php">
        <div id="error-message">
            <?php
            session_start();
            if (isset($_SESSION['errorMessage'])) {
                echo htmlspecialchars($_SESSION['errorMessage']);
                unset($_SESSION['errorMessage']);
            }
            ?>
        </div>
        <input type="text" id="email" name="username" placeholder="Username" required>
        <br>
        <input type="password" id="password" name="password" placeholder="Password" required>
        <br>
        <button type="submit">Login</button>
    </form>
</body>
</html>