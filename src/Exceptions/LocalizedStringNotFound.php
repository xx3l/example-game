<?php

namespace Group\ExampleGame\Exceptions;

use Exception;

class LocalizedStringNotFound extends Exception {
    protected $message = "Localized string not found";
}