<?php declare(strict_types=1); ?>

<style>
    <?php require_once "./css/main.css"; ?>
</style>
<script>
    <?php require_once "./js/control.js" ?>
</script>

<?php
use App\Game;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

require '../vendor/autoload.php';

$loader = new FilesystemLoader(array('components/', 'pages/'));
$twig   = new Environment($loader);
$game 	= new Game($twig);
$game->start();
