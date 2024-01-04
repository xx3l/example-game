<?php declare(strict_types=1);

namespace App;

use Exception;

/**
 * Supports simple .env (no array, multibyte, different encoding values)
 */
final class DotEnvManager
{
    private string $envPath;
    private array $tempEnv = [];

    public function getTempEnv() : array
    {
        return $this->tempEnv;
    }

    private string $src = "";

    /**
     * Reads .env file's data to $this->src. Call parse() to continue handling .env data
     * @throws Exception if .env file
     * - doesn't exist
     * - is not readable
     * - is not writeable
     * - can't be opened
     */
    function __construct($env_path){
        // Check if .env file path has provided
        if(empty($env_path)){
            throw new Exception(".env file path is missing");
        }

        $this->envPath = realpath($env_path);

        //Check .env file exists
        if(!is_file($this->envPath)){
            throw new Exception("Environment File is Missing.");
        }

        //Check .env file is readable
        if(!is_readable($this->envPath)){
            throw new Exception("Permission Denied for reading the " . (realpath($this->envPath)));
        }

        //Check .env file is writable
        if(!is_writable($this->envPath)) {
            throw new Exception("Permission Denied for writing on the " . (realpath($this->envPath)));
        }

        $this->src = file_get_contents($this->envPath);

        if(!$this->src) {
            throw new Exception("Error while reading the " . $this->envPath);
        }
    }

    /**
     * Parses .env file's data
     * @throws Exception
     * @return DotEnvManager this object
     */
    public function parse() : DotEnvManager
    {
        // Convert line breaks to same format
        preg_replace("/\r\n?/m", "\n", $this->src);

        $entryRegex = <<<some
/(?:^|^)\s*(?:export\s+)?([\w.-]+)(?:\s*=\s*?|:\s+?)(\s*'(?:\\\'|[^'])*'|\s*\"(?:\\\"|[^\"])*\"|\s*`(?:\\`|[^`])*`|[^#\r\n]+)?\s*(?:#.*)?(?:$|$)/m
some;
        $matches = array();

        preg_match_all($entryRegex, $this->src, $matches);

        for($i = 0; $i < count($matches[1]); $i++){

            $key   = $matches[1][$i];
            $value = $matches[2][$i];

            // Remove whitespace
            $value = trim($value);

            // Check if double-quoted
            $maybeQuote = str_starts_with($value, '"');

            // Remove surrounding quotes
            $value = preg_replace("/^(['\"`]\s*)|(\s*['\"`])$/m", '', $value);


            $this->tempEnv[$key] = $value;
        }

        return $this;
    }

    /**
     * Saves .env data to $_ENV
     * @return DotEnvManager this object
     */
    function load(): DotEnvManager
    {
        foreach($this->tempEnv as $name=> $value){
            if(is_numeric($value)) {
                $value = floatval($value);
            }
            if(in_array(strtolower($value),["true","false"])) {
                $value = strtolower($value) == "true";
            }
            $_ENV[$name] = $value;
        }

        return $this;
    }
}
