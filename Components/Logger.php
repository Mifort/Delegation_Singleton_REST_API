<?php

namespace RestApiPay\Components;

class Logger
{
    private static  $instance;
    private  int $_maxFileSize = 1024; // in KB
    /**
     * @var integer number of log files used for rotation
     */
    private int $_maxLogFiles = 5;
    /**
     * @var string directory storing log files
     */
    private string $_logPath;
    /**
     * @var string log file name
     */
    private string $_logFile = 'log.log';

    private bool $rotateByCopy = false;

    public function setLogPath(string $value): void
    {
        $this->_logPath = $value . '/runtime';
    }
    private function __construct($file){
        $this->setLogFile($file);
        $this->setLogPath(dirname(__DIR__));
    }
    public static function run(string $file):object
    {
        if(self::$instance===null){
            self::$instance = new self($file);
        }
        return self::$instance;
    }
    public function setLogFile(string $file):void{
        $this->_logFile = $file;
    }
    public  function process($logs):void
    {
        $text = $this->formatLogMessage($logs);

        $logFile = $this->_logPath . DIRECTORY_SEPARATOR . $this->_logFile;
        $fp = @fopen($logFile, 'a');
        @flock($fp, LOCK_EX);
        if (@filesize($logFile) > $this->_maxFileSize * 1024) {
            $this->rotateFiles();
            @flock($fp, LOCK_UN);
            @fclose($fp);
            @file_put_contents($logFile, $text, FILE_APPEND | LOCK_EX);
        } else {
            @fwrite($fp, $text);
            @flock($fp, LOCK_UN);
            @fclose($fp);
        }
    }
    private  function formatLogMessage($message): string
    {
        $message = var_export($message, true);
        return @date('Y/m/d H:i:s') . "\n $message\n";
    }
    private  function rotateFiles(): void
    {
        $file = $this->_logPath . DIRECTORY_SEPARATOR .$this->_logFile;
        for ($i = $this->_maxLogFiles; $i > 0; --$i) {
            $rotateFile = $file . '.' . $i;
            if (is_file($rotateFile)) {
                // suppress errors because it's possible multiple processes enter into this section
                if ($i === $this->_maxLogFiles)
                    @unlink($rotateFile);
                else
                    @rename($rotateFile, $file . '.' . ($i + 1));
            }
        }
        if (is_file($file)) {
            // suppress errors because it's possible multiple processes enter into this section
            if ($this->rotateByCopy) {
                @copy($file, $file . '.1');
                if ($fp = @fopen($file, 'a')) {
                    @ftruncate($fp, 0);
                    @fclose($fp);
                }
            } else
                @rename($file, $file . '.1');
        }
        // clear stat cache after moving files so later file size check is not cached
        clearstatcache();
    }
}