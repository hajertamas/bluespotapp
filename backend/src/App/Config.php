<?php

namespace App;

use Illuminate\Database\Capsule\Manager;

class Config
{
    private const GLOBAL_ID = "__bluespot_app_config";
    private const CFG_FILE = __DIR__ . "/_app_config.json";

    private $appOrigin = "http://localhost:4200";

    private $devMode = false;

    private $logFile = __DIR__ . "/_app_error_log.txt";

    private $logFileMaxSize = "1MB";

    private $sessionLifeTimeMinutes = 30;

    private $eloquentDriver = [];

    private $eloquentManager;

    public function __construct(array $data = [])
    {
        if (!empty($GLOBALS[self::GLOBAL_ID]) && $GLOBALS[self::GLOBAL_ID] instanceof Config) {
            throw new AppException(AppException::CONFIG_ALREADY_EXISTS);
        }

        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (\property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }

        $GLOBALS[self::GLOBAL_ID] = $this;

        if (!\file_exists(self::CFG_FILE)) {
            $this->save();
        }
    }

    public static function get(): Config
    {
        if (empty($GLOBALS[self::GLOBAL_ID]) || !($GLOBALS[self::GLOBAL_ID] instanceof Config)) {
            return self::load();
        }

        return $GLOBALS[self::GLOBAL_ID];
    }

    private function save()
    {
        $data = \json_encode([
            "appOrigin" => $this->appOrigin,
            "devMode" => $this->devMode,
            "logFile" => $this->logFile,
            "logFileMaxSize" => $this->logFileMaxSize,
            "sessionLifeTimeMinutes" => $this->sessionLifeTimeMinutes,
            "eloquentDriver" => $this->eloquentDriver
        ]);

        $handle = \fopen(self::CFG_FILE, "w");
        \fwrite($handle, $data);
        \fclose($handle);
    }

    private static function load(): Config
    {
        if (\file_exists(self::CFG_FILE)) {
            $data = \json_decode(\file_get_contents(self::CFG_FILE), true);
            return new Config($data);
        }
        return new Config();
    }

    #region SETTERS + GETTERS
    public function getAppOrigin(): string
    {
        return $this->appOrigin;
    }

    public function setAppOrigin(string $origin): void
    {
        $this->appOrigin = rtrim($origin, "/");
        $this->save();
    }

    public function getDevMode(): bool
    {
        return $this->devMode;
    }

    public function setDevMode(bool $devMode): void
    {
        $this->devMode = $devMode;
        $this->save();
    }


    public function getLogFile(): string
    {
        return $this->logFile;
    }

    public function setLogFile(string $path): void
    {
        $this->logFile = $path;
        $this->save();
    }


    public function getLogFileMaxSize(): int
    {
        return Utils::convertToBytes($this->logFileMaxSize);
    }

    public function setLogFileMaxSize(string $maxSize): void
    {
        $this->logFileMaxSize = $maxSize;
        $this->save();
    }

    public function setSessionLifeTimeMinutes(int $minutes = null): void
    {
        $this->sessionLifeTimeMinutes = $minutes;
        $this->save();
    }

    public function getSessionLifeTimeMinutes(): int|null{
        return $this->sessionLifeTimeMinutes;
    }
    
    public function getEloquentDriver(): array
    {
        return $this->eloquentDriver;
    }

    public function setEloquentDriver(array $driver): void
    {
        $this->eloquentDriver = $driver;
        $this->eloquentManager = $this->getEloquentManager();
        $this->save();
    }

    public function getEloquentManager(): Manager
    {
        if (empty($this->eloquentManager) || !($this->eloquentManager instanceof Manager)) {
            $manager = new Manager;
            $manager->addConnection($this->getEloquentDriver());
    
            $manager->setAsGlobal();
            $manager->bootEloquent();

            return $manager;
        }

        return $this->eloquentManager;
    }

    #endregion
}
