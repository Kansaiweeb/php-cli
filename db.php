<?php

class DataBase
{
    private $config;
    private $connection;

    public function __construct()
    {
        $this->config = parse_ini_file("settings.ini", true)["database"];
        $this->connection = new mysqli($this->config["HOST"], $this->config["USER"], $this->config["PASS"], $this->config["DB"], $this->config["PORT"]);
    }

    public function select_tasks()
    {
        $query = "SELECT `TaskId`, `Task` from `TASKS`";

        $result = $this->connection->query($query);

        return $result->fetch_all();
    }

    public function insert_task(array $tasks): void
    {
        $query = "INSERT INTO `TASKS` (Task) values " . str_repeat("(?)", count($tasks) - 1) . "(?)";

        $this->connection->query($query);
    }

    public function get_digits_array(): array
    {
        $query = "SELECT digit from `rand_numbers`";

        $result = $this->connection->query($query);
        // $result = array();
        $rows = $result->fetch_all(MYSQLI_NUM);
        $arr = array();
        foreach ($rows as $row) {
            array_push($arr, $row[0]);
        };
        return $arr;
    }

    public function insert_digits_array(array $array): void
    {
        $query = "INSERT INTO `rand_numbers` (DigitId, Digit) values ";
        for ($i = 0; $i < count($array); $i++) {
            if ($i != 0) {
                $query .= ", ";
            }
            $query .= sprintf(
                "('%d', '%d')",
                $i + 1,
                $array[$i]
            );
        }

        $this->connection->query($query);
    }

    public function migrate()
    {
        $query = "DROP TABLE IF EXISTS `tasks`;";
        $query .= "CREATE TABLE `tasks` (
                    `TaskID` int NOT NULL AUTO_INCREMENT,
                    `Task` VARCHAR(20) NOT NULL,
                    `Status` boolean ,
                    PRIMARY KEY (`TaskId`)
                    );";
        $query .= "DROP TABLE IF EXISTS `rand_numbers`;";
        $query .= "CREATE TABLE `rand_numbers` (
                    `DigitId` int NOT NULL AUTO_INCREMENT,
                    `Digit` int,
                    PRIMARY KEY  (`DigitId`)
                    );";

        $this->connection->multi_query($query);
        while ($this->connection->next_result()) {;
        }

        $this->connection->close();
    }

    public function seed()
    {
        $query = "INSERT INTO `tasks` (Task) values ('sort'), ('titles'), ('factorial_1'), ('factorial_2');";
        $this->connection->query($query);
        $random_number_array = range(0, 100);
        shuffle($random_number_array);
        $random_number_array = array_slice($random_number_array, 0, 10);
        $query = $this->connection->prepare("INSERT INTO `rand_numbers` (`Digit`) values " . str_repeat("(?), ", count($random_number_array) - 1) . "(?)");
        $query->bind_param(str_repeat("i", count($random_number_array)), ...$random_number_array);
        $query->execute();
    }
}
