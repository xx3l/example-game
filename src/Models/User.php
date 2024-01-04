<?php declare(strict_types=1);

namespace App\Models;

use App\Exceptions\ValueBelowZeroException;
use App\Exceptions\ValueExceedMax;

class User
{
    public int $x;
    public int $y;
    public int    $xp = 0;
    public int    $hp = 10;
    public int    $maxHp = 10;
    public string $name;
    public string $password;
    public int    $powerAttack = 1;
    public int    $inventorySize = 10;

    public function __construct(string $name, string $password){
        $this->x = rand(-10, 10);
        $this->y = rand(-10, 10);
        $this->name = $name;
        $this->password = $password;
    }

    public static function create(
        int $x,
        int $y,
        int $hp,
        int $xp,
        int $maxHp,
        string $name,
        string $password,
        int $powerAttack,
        int $inventorySize
    ) : User
    {
        $output = new User($name, $password);
        $output->x = $x;
        $output->y = $y;
        $output->hp = $hp;
        $output->xp = $xp;
        $output->maxHp = $maxHp;
        $output->powerAttack = $powerAttack;
        $output->inventorySize = $inventorySize;

        return $output;
    }

    /**
     * @throws ValueExceedMax if the specified hp is greater than max_hp
     * @throws ValueBelowZeroException if the specified hp is less than zero
     */
    public function setHP(int $hp): void
    {
        if ($hp > $this->maxHp) throw new ValueExceedMax("Specified hp is greater than max_hp");
        if ($hp < 0) throw new ValueBelowZeroException("Specified hp is less than zero");
        $this->hp = $hp;
    }

    /**
     * @throws ValueBelowZeroException if the specified xp is less than zero
     */
    public function setXP(int $xp): void
    {
        if ($xp < 0) throw new ValueBelowZeroException("Specified xp is less than zero");
        $this->xp = $xp;
    }

    /**
     * @throws ValueBelowZeroException if the specified max_hp is less than zero
     */
    public function setMaxHp(int $maxHp): void
    {
        if ($maxHp < 0) throw new ValueBelowZeroException("Specified max_hp is less than zero");
        $this->maxHp = $maxHp;
    }
}
