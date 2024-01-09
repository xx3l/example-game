<?php declare(strict_types=1);

namespace App;

enum DbDriver: string
{
	case MySQL = "mysqli";
	case SQLite3 = "SQLite3";
}
