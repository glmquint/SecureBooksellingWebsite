<?php
require_once '../vendor/autoload.php';
use Monolog\Level;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Log{
    private static $lowdetailLog;
    private static $highdetailLog;
    private static $lowdetailLogPath = '../lowdetails.log';
    private static $highdetailLogPath = '../highdetails.log';

    private static $formatter;
    private static $LowstreamHandler;
    private static $HighstreamHandler;
    private static $formatString= "[%datetime%] %channel%.%level_name%: %message% %context%\n";
    public function __construct() {}

    public static function getInstanceLow(): Logger {
        if (self::$lowdetailLog == null) {
            self::$formatter = new LineFormatter(self::$formatString);
            self::$LowstreamHandler = new StreamHandler(self::$lowdetailLogPath);
            self::$LowstreamHandler->setFormatter(self::$formatter);
            self::$lowdetailLog = new Logger('lowdetailLog');
            self::$lowdetailLog->pushHandler(self::$LowstreamHandler);
            return self::$lowdetailLog;
        }
        return self::$lowdetailLog;
    }
    public static function getInstanceHigh(): Logger {
        if (self::$highdetailLog == null) {
            self::$formatter = new LineFormatter(self::$formatString);
            self::$HighstreamHandler = new StreamHandler(self::$highdetailLogPath);
            self::$HighstreamHandler->setFormatter(self::$formatter);
            self::$highdetailLog = new Logger('highdetailLog');
            self::$highdetailLog->pushHandler(self::$HighstreamHandler);
            return self::$highdetailLog;
        }
        return self::$highdetailLog;
    }
}
function performLog($level, $lowInfo, $highInfo): void{

        $highdetailLog = Log::getInstanceHigh();
        $lowdetailLog = Log::getInstanceLow();
        switch ($level){
            case "Info":
                $lowdetailLog->info($lowInfo);
                $highdetailLog->info($lowInfo . $highInfo);
                break;

            case "Warning":
                $lowdetailLog->warning($lowInfo);
                $highdetailLog->warning($lowInfo . $highInfo);
                break;
            case "Error":
                $lowdetailLog->error($lowInfo);
                $highdetailLog->error($lowInfo . $highInfo);
                break;
        }
    }
?>
