<?php

namespace Group\ExampleGame\Domain;

use Group\ExampleGame\Models\User;

class UserRepository
{
    public function __construct(private $db){}

    /**
     * Get amount of users at the specified position
     * @param $x
     * @param $y
     * @return mixed
     */
    public function pointInfo($x, $y)
    {
        $query = $this->db->query("select count(*) n from users where x=$x and y=$y");
        $data = $query->fetch_assoc();
        print_r($data);
        return $data['n'];
    }

    public function isPositionFree() : bool
    {
        $query = $this->db->query("select count(*) n from users where x=$x and y=$y");
        $data = $query->fetch_assoc();
        return is_null($data);
    }

    public function process_actions(User $user, int $difference_x, int $difference_y): void
    {
        if (abs($difference_x > 1 || $difference_y > 1)) { return; }

        $temp_x = $user->x + $difference_x;
        $temp_y = $user->y + $difference_y;

        if ($this->isPositionFree($temp_x, $temp_y)) {
            $this->database->query("update users set x=$temp_x, y=$temp_y where name='" . $user->username . "'");
            $user->x = $temp_x;
            $user->y = $temp_y;
        }
    }

    public function getDistanceToClosestEnemy(User $user): float
    {
        $x = $user->x;
        $y = $user->y;
        $query = $this->db->query("select 
      min((x-$x)*(x-$x)+(y-$y)*(y-$y)) min 
      from users where name!='" . $user->username . "'");
        return sqrt(($query->fetch_assoc())['min']);
    }
}