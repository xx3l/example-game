<?php declare(strict_types=1); ?>

<style>
    <?php require_once "./css/main.css"; ?>
</style>
<script>
    <?php require_once "./js/control.js" ?>
</script>

<?php
use App\Game;

require '../vendor/autoload.php';

$game = new Game();
$game->start();