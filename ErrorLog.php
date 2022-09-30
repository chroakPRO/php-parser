<?php

class ErrorLog {

    public static string $last_errmsg = "hej";
    public array $error_stack;

    public function __construct(){
        $this->error_stack = [];
    }

    public static function customErr(string $message, string $split_at=""): string{

        self::$last_errmsg = $message;
        return self::$last_errmsg;
    }

    public static function subMain(){
        echo 'echo ErrorLog::customErr("hello");';
    }
}
