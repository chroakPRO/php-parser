<?php
require __DIR__ . '/../vendor/autoload.php';
// load in the simplexls scret somehow.

/** ['Easy of use class' - Parser]
 *
 * @author Christopher Ek < https://oaksec.dev >
 * @version 1.0
 * @copyright CryptoGuard
 * @license Proprietary Software - NO COPY
 *
 */
class ParserTest {

    //! PUBLIC DATA
    public array $arr;
    public array $csv;
    public $file_load;
    public array $title;
    /**
     * @var false|resource
     */
    public $ftp_conn;
    public $ftp_login;
    private bool $fpt_login;


    public function __construct(){
        $this->arr = [];
        $this->csv = [];
        $this->file_load = fopen("ls.csv", "wb+");
        $this->title = array("date", "gmt", "cat", "genre", "Title", "desc", "Flags", "Rating");
        $this->ftp_conn = ftp_connect("hostname", "21");
        $this->ftp_login = ftp_login($this->getFtpConn(), "username", "password");


    }

    public function getFtpConn(): resource
    {
        return $this->ftp_conn;
    }

    /**
     * @param mixed $ftp_conn
     */
    public function setFtpConn($ftp_conn): void
    {
        $this->ftp_conn = $ftp_conn;
    }

    /**
     * @return bool
     */
    public function isFptLogin(): bool
    {
        return $this->fpt_login;
    }

    /**
     * @param mixed $fpt_login
     */
    public function setFptLogin($fpt_login): void
    {
        $this->fpt_login = $fpt_login;
    }


    // -------- MAIN FUNCTIONS -------------------------

    /** ['Parse File' - Takes XLS file and convert to CSV file]
     *
     * @param string $csv_filename //? What should the CSV filed be called.
     * @param string $xls_filename //? xls filename
     * @return void $csv_filename
     */
    public function parseFile(string $csv_filename, string $xls_filename): bool
    {
        $arr = [];
        $csv = [];
        $file_load = fopen("ls.csv", "w");
        $title = array("date", "gmt", "cat", "genre", "Title", "desc", "Flags", "Rating");


        if ( $xls = SimpleXLS::parseFile($csv_filename) ) {
            $index = 0;
            foreach($xls->rows() as $value){
                $min = $value[0];
                $max = $value[count($value)];

                if(empty($value) === false) {
                    $jndex = 0;
                    if ($index !== 0) {
                        foreach ($value as $row) {
                            if (empty($row) === false) {

                                //print_r($row . "\n");
                                $arr[$jndex] = (string)$row;
                            }
                            $jndex++;
                        }

                        print_r($arr);
                        fputcsv($file_load, $arr, ";");

                    } else {fputcsv($file_load, $title, ";");}
                }
                $index++;
            }
            return true;
            // echo $xls->toHTML();
        }
        echo SimpleXLS::parseError();
        return false;
    }

    /** ['returns true if worked, but also prints all the files in ftp server']
     * @return bool
     */
    public function ListAllFiles(): bool {
        try {
            if ($this->isFptLogin() === false){
                $this->setFptLogin(ftp_login($this->getFtpConn(), "--", "--"));
            }
            $files = ftp_mlsd($this->getFtpConn(), ".");
            foreach($files as $value){
                print_r($value);
            }
            return true;
        } catch (Exception $e) { return false; }


    }

    /** ['Downloads all files from FTP Server']
     *
     */
    public function DownloadAllFiles(): void
    {
        if ($this->isFptLogin() === false){
            $this->setFptLogin(ftp_login($this->getFtpConn(), "--", "--"));
        }
        $files = ftp_mlsd($this->getFtpConn(), ".");

        foreach($files as $value) {
            // try to download $server_file and save to $local_file
            if (ftp_get($this->getFtpConn(), $value['name'], $value['name'], FTP_BINARY)) {
                echo "Successfully written to" . $value["name"]."\n";
            } else {
                echo "There was a problem\n";
            }
        }
    }

    // --------------------------------------- STATIC FUNCTIONS ------------------------

    /** ['FindMinMax Function']
     * //! STATIC FUNCTION
     *
     * @param array $arr //? 2d array, structured like $x
     *
     * @return array[] //? return 2d array -> curMax/Min_date[index value in $arr, output from strtotime]
     * @throws Exception
     */
    public static function FindMinMax(array $arr): array {

        $curMin_date = [];
        $curMax_date = [];

        for($i = 0, $iMax = count($arr); $i < $iMax; $i++){

            $new_arr = array($arr[$i][0], $arr[$i][1]);
            $form_str = implode(" ", $new_arr);
            $currendate = strtotime($form_str);
            if ($i === 0){
                // On first iteration set some default values.
                $curMax_date[0] = 0;
                $curMin_date[0] = 0;
                $curMax_date[1] = $currendate;
                $curMin_date[1] = $currendate;
            } else {
                // Take the current time and create DateTime Objects.
                $diff1_min = new DateTime(date('m/d/Y h:i:s a', $curMin_date[1]));
                $diff1_max = new DateTime(date('m/d/Y h:i:s a', $curMax_date[1]));

                $diff2 = new DateTime(date('m/d/Y h:i:s a', $currendate));
                // Debugging
                echo $diff2->diff($diff1_min)->format("Year: %Y %M %D %H %M");
                echo "\n".$diff2->diff($diff1_max)->format("Year: %Y %M %D %H %M");

                // Check if the current iteration element is less then current min.
                if ((int)$diff2->diff($diff1_min)->format("%F") < 0){
                    // Sets the current min date.
                    $curMin_date = [$i, $currendate];
                    // -||- ^ greater then current max.
                } elseif ((int)$diff2->diff($diff1_max)->format("%F") > 0){
                    // Sets the new current max date.
                    $curMax_date = [$i, $currendate];
                }

            }

        }
        // returns an 2d array. currmin&max [index, output from strtotime](
        return [$curMin_date, $curMax_date];
    }

    /** ['Move file - function']
     * //! STATIC FUNCTION
     *
     * @param string $file //? filename/src
     * @param string $to //? dest folder
     *
     * @return string | null
     * @throws $none
     */
    protected static function MoveFile(string $file, string $to): ?string
    {
        $path_parts = pathinfo($file);
        $newplace   = "$to/{$path_parts['basename']}";
        if(rename($file, $newplace)) {
            return $newplace;
        }
        return null;
    }
    // Simple help function for commented code.
    public function Help(){
        echo "Debug Array".'$x'."= [['2022/04/10', '21:00:00', '23:00:00'], ['2022/05/10', '20:00:00', 
            '21:00:00']];";
    }

}


// Debug Array.
// $x = [['2022/04/10', '21:00:00', '23:00:00'], ['2022/05/10', '20:00:00', '21:00:00']];

$test = new ParserTest();
