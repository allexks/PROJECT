<!DOCTYPE html>

<html>
	<?php if (isset($_SESSION["user_id"]) && $_SESSION["user_id"] > 0) : ?>
		<div class="navbar">
			<img class="position-left" src="images/logo.png">
			<a class="position-left" href="index.php">Начало</a>
			<a class="position-left" href="import.php">Импорт</a>
			<a class="position-left" href="browse.php">Моите тестове</a>
			<a class="position-right" href="exit.php">Изход</a>
		</div>
	<?php else : ?>
		<div class="navbar">
			<img class="position-left" src="images/logo.png">
			<a class="positio-left" href="index.php">Начало</a>
			<a class="position-right" href="signup.php">Регистрация</a>
			<a class="position-right" href="login.php">Вход</a>
		</div>
	<?php endif;?>
</html>