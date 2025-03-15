
<!DOCTYPE html>
<html lang="en">
<head>
    
    <title>Register</title>
    <style>
      *{
        margin: 0;
        padding: 0;
        box-sizing: border-box;
       
      }
      .form {
  display: flex;
  flex-direction: column;
  align-self: center;
  font-family: inherit;
  gap: 10px;
  padding-inline: 2em;
  padding-bottom: 0.4em;
  background-color: #171717;
  
  border-radius: 20px;
  /* height: 100%;  height: 100%; */

}

.form-heading {
  text-align: center;
  margin: 2em;
  color: #64ffda;
  font-size: 1.2em;
  background-color: transparent;
  align-self: center;
}

.form-field {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5em;
  border-radius: 10px;
  padding: 0.6em;
  border: none;
  outline: none;
  color: white;
  background-color: #171717;
  box-shadow: inset 2px 5px 10px rgb(5, 5, 5);
}

.input-field {
  background: none;
  border: none;
  outline: none;
  width: 100%;
  color: #ccd6f6;
  padding-inline: 1em;
}

.sendMessage-btn {
  cursor: pointer;
  margin-top: 2em;
  margin-bottom: 2em;
  padding: 1em;
  border-radius: 10px;
  border: none;
  outline: none;
  background-color: transparent;
  color: #64ffda;
  font-weight: bold;
  outline: 1px solid #64ffda;
  transition: all ease-in-out 0.3s;
}

.sendMessage-btn:hover {
  transition: all ease-in-out 0.3s;
  background-color: #64ffda;
  color: #000;
  cursor: pointer;
  box-shadow: inset 2px 5px 10px rgb(5, 5, 5);
}

.form-card1 {
  background: #090a00;
  /* border-radius: 22px; */
  transition: all 0.3s;
  justify-content: center;
    align-items: center;
    display: flex;
    height: 100vh;

}

.form-card1:hover {
  box-shadow: 0px 0px 30px 1px rgba(100, 255, 218, 0.3);
}

.form-card2 {
  border-radius: 0;
  transition: all 0.2s;
  height: 100vh;
  justify-content: center;
    align-items: center;
    display: flex;
}

.form-card2:hover {
  transform: scale(0.98);
  border-radius: 20px;
}
.form{
    height: 65vh;
}
.form-login{
  color: #ccd6f6;
  margin-bottom: 30px
}
.form-login a{
    color: #64ffda; 
}
.error{
    color: red;
    margin-bottom: 10px;
}
    </style>
</head>
<body>

<div class="form-card1">
  <div class="form-card2">
    <form class="form" action="register_process.php" method="POST">
      
      <p class="form-heading">Get In Touch</p>
      <?php session_start(); ?>
<?php if (isset($_SESSION['register_error'])): ?>
    <div class="error">
        <?php 
            echo $_SESSION['register_error']; 
            unset($_SESSION['register_error']); // Clear error after showing it
        ?>
    </div>
<?php endif; ?>

      <div class="form-field">
        <input required="" name="username" placeholder="Name" class="input-field" type="text" />
      </div>

      <div class="form-field">
        <input
        name="email"
          required=""
          placeholder="Email"
          class="input-field"
          type="email"
        />
      </div>

      <div class="form-field">
        <input
        name="password"
          required=""
          placeholder="password"
          class="input-field"
          type="Password"
        />
      </div>

      
      

      <button class="sendMessage-btn">Create Account</button>
      <p class="form-login">Already have an Account <a href="login.php">Click Here</a></p>
    </form>
  </div>
</div>


</body>
</html>
