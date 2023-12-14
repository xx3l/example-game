<?php

namespace Group\ExampleGame\Models;

use Group\ExampleGame\Domain\Database;
use Group\ExampleGame\Exceptions\HealthPointExceedMax;
use Group\ExampleGame\Exceptions\HealthPointsBelowZeroException;

class User
{
    public Database $database;
    private int $xp;
    public float $hp;
    private float $max_hp;
    public float $exp;
    public int $x;
    public int $y;
    public string $username = "game";
    public string $password = "123Game!!!";
    private float $power_attack;

    /**
     * @throws HealthPointExceedMax if the specified hp is greater than max_hp
     * @throws HealthPointsBelowZeroException if the specified hp is less than zero
     */
    public function setHP(float $hp) : void
    {
        if ($hp > $this->max_hp) throw new HealthPointExceedMax();
        if ($hp < 0) throw new HealthPointsBelowZeroException();
        $this->hp = $hp;
    }

    /**
     * @throws HealthPointsBelowZeroException if the specified max_hp is less than zero
     */
    public function setMaxHP(float $max_hp) : void
    {
        if ($max_hp < 0) throw new HealthPointsBelowZeroException();
        $this->max_hp = $max_hp;
    }

    public function __constructor(){}
}
