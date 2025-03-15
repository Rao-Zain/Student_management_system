<!DOCTYPE html>
<html lang="en">
<head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <title>Login</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #1f293a;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }
        .container {
            position: relative;
            width: 380px;
            height: 400px;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 50%;
            background: #1f293a;
        }
        .container span {
            position: absolute;
            left: 0;
            width: 20px;
            height: 9px;
            background: #2c4766;
            border-radius: 80px;
            transform-origin: 200px;
            transform: rotate(calc(var(--i) * (360deg / 50)));
            animation: blink 3s linear infinite;
            animation-delay: calc(var(--i) * (3s / 50));
        }
        @keyframes blink {
            0% { background: #0ef; }
            25% { background: #2c4766; }
        }
        .login-box {
            position: absolute;
            width: 100%;
            height: 350px;
            max-width: 300px;
            z-index: 1;
            padding: 20px;
            border-radius: 20px;
        }
        .login-box:hover{
            transform: scale(0.98);
  border-radius: 20px;
        }
        form {
            width: 100%;
            padding: 0 10px;
        }
        h2 {
            font-size: 1.8em;
            color: #0ef;
            text-align: center;
            margin-bottom: 10px;
        }
        .input-box {
    position: relative;
    margin: 15px 0;
}

input {
    width: 100%;
    height: 45px;
    background: transparent;
    border: 2px solid #2c4766;
    outline: none;
    border-radius: 40px;
    font-size: 1em;
    color: #fff;
    padding: 10px 15px;
    transition: 0.3s ease;
}

label {
    position: absolute;
    top: 50%;
    left: 15px;
    transform: translateY(-50%);
    font-size: 1em;
    color: #fff;
    transition: 0.3s ease;
    pointer-events: none;
}

/* Move label when input is focused or has content */
input:focus ~ label,
input:not(:placeholder-shown) ~ label {
    top: 5px;
    font-size: 0.8em;
    background: #1f293a;
    padding: 0 5px;
    color: #0ef;
}

        .btn {
            width: 100%;
            height: 45px;
            background: #0ef;
            border: none;
            outline: none;
            border-radius: 40px;
            cursor: pointer;
            font-size: 1em;
            color: #1f293a;
            font-weight: 600;
        }
        .error {
            color: red;
            text-align: center;
            margin-top: 10px;
            font-size: 0.9em;
        }
        .success {
            color: #0ef;
            text-align: center;
            margin-top: 15px 15px 10px 10px;
            font-size: 1.5em;
            font-weight: 600;
        }
        .extra-links {
            text-align: center;
            margin-top: 15px;
        }
        .extra-links a {
            color: #0ef;
            text-decoration: none;
            font-size: 0.9em;
            margin: 0 10px;
        }
        .extra-links p{
        margin-top: 20px;
            color: #fff;
            font-size: 0.9em;
            margin: 0 10px;
        }
       
    </style>
</head>
<body>
<?php
    session_start();
    if (isset($_GET['success'])) {
        echo '<div class="success">' . htmlspecialchars($_GET['success']) . '</div>';
    }
?>
<div class="container">
  <div class="login-box">
    <h2 class="mb-4">Login</h2>
    <?php if(isset($_GET['error'])): ?>
        <div class="error"> <?php echo htmlspecialchars($_GET['error']); ?> </div>
    <?php endif; ?>
    <form action="login_process.php" method="POST">
    <div class="input-box">
    <input name="email" type="email" placeholder=" " required />
    <label>Email</label>
</div>
<div class="input-box">
    <input name="password" type="password" placeholder=" " required />
    <label>Password</label>
</div>
      <button class="btn" type="submit">Login</button>
    </form>
    <div class="extra-links">
        <a href="forgot_password.php">Forgot Password?</a>
<br>
  <p>  Don't have an Account?  </p>  <a href="register.php" class="mt-2"> Click Here </a>
    </div>
  </div>
  <span style="--i:0;"></span>
  <span style="--i:1;"></span>
  <span style="--i:2;"></span>
  <span style="--i:3;"></span>
  <span style="--i:4;"></span>
  <span style="--i:5;"></span>
  <span style="--i:6;"></span>
  <span style="--i:7;"></span>
  <span style="--i:8;"></span>
  <span style="--i:9;"></span>
  <span style="--i:10;"></span>
  <span style="--i:11;"></span>
  <span style="--i:12;"></span>
  <span style="--i:13;"></span>
  <span style="--i:14;"></span>
  <span style="--i:15;"></span>
  <span style="--i:16;"></span>
  <span style="--i:17;"></span>
  <span style="--i:18;"></span>
  <span style="--i:19;"></span>
  <span style="--i:20;"></span>
  <span style="--i:21;"></span>
  <span style="--i:22;"></span>
  <span style="--i:23;"></span>
  <span style="--i:24;"></span>
  <span style="--i:25;"></span>
  <span style="--i:26;"></span>
  <span style="--i:27;"></span>
  <span style="--i:28;"></span>
  <span style="--i:29;"></span>
  <span style="--i:30;"></span>
  <span style="--i:31;"></span>
  <span style="--i:32;"></span>
  <span style="--i:33;"></span>
  <span style="--i:34;"></span>
  <span style="--i:35;"></span>
  <span style="--i:36;"></span>
  <span style="--i:37;"></span>
  <span style="--i:38;"></span>
  <span style="--i:39;"></span>
  <span style="--i:40;"></span>
  <span style="--i:41;"></span>
  <span style="--i:42;"></span>
  <span style="--i:43;"></span>
  <span style="--i:44;"></span>
  <span style="--i:45;"></span>
  <span style="--i:46;"></span>
  <span style="--i:47;"></span>
  <span style="--i:48;"></span>
  <span style="--i:49;"></span>
  <span style="--i:50;"></span>
</div>

</body>
</html>
