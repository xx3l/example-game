
<div> Вы в игре, <?=$user?> ! </div>

<form method="post">
    <input type="hidden" name="logout">
    <input type="submit" value="Выйти">
</form>

<div>Здоровье: <?=$this->hp?></div>
<div>Опыт: <?=$this->xp?></div>
<div>X: <?=$this->x?></div>
<div>Y: <?=$this->y?></div>
<div>Ближайший враг: <?=$this->getClosestDistance()?></div>

<form method="post">
    <input type="hidden" name="direction" value="N">
    <input type="submit" value="Идти на север">
</form>

<form method="post">
    <input type="hidden" name="direction" value="S">
    <input type="submit" value="Идти на юг">
</form>

<form method="post">
    <input type="hidden" name="direction" value="E">
    <input type="submit" value="Идти на восток">
</form>

<form method="post">
    <input type="hidden" name="direction" value="W">
    <input type="submit" value="Идти на запад">
</form>