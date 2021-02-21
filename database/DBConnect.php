<?php
class DBConnect extends DBData
{
    private $connect;

    public function __construct() {
        $this->connect = mysqli_connect($this->host, $this->user, $this->pw, $this->dbName);
    }

    /**
     * @param $tableName - 테이블 명
     * @param $data - 넣을 컬럼 => 컬럼에 넣을 값 
     */
    public function insertDB($tableName, $data) {
        if($tableName == null || $data == null) return false; // 예외처리

        $query = null;
        $columns = implode(", ",array_keys($data));
        $values = implode("', '",$data);

        $query = "
            INSERT INTO " . $tableName . " (" .$columns  . ")
            VALUES ('" . $values . "')
        ";
        var_dump($query);
        $return = $this->queryExecute($query);

        return $return;
    }

    public function queryExecute($query) {
        $result = mysqli_query($this->connect, $query);

        return $result;
    }

    public function getSelectOneRow($query) {
        $result_set = $this->queryExecute($query);
        $row = mysqli_fetch_array($result_set);

        return $row;
    }

    protected function connectExit() {
        mysqli_close($this->connect);
    }

    function __destruct()
    {
        $this->connectExit();
    }
}
?>