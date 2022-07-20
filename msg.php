<?php

require_once(__DIR__ . '/vendor/autoload.php');
require_once("db.php");
require_once("utils.php");

use PhpAmqpLib\Connection\AMQPSocketConnection;

class Msg
{
    public $connection;
    public $config;

    public function __construct()
    {
        // Parse settings.ini
        $this->config = parse_ini_file("settings.ini", true)["rabbitmq"];

        // Create a connection
        $this->connection = new AMQPSocketConnection(
            $this->config["HOST"],
            $this->config["PORT"],
            $this->config["USER"],
            $this->config["PASS"],
            $this->config["VHOST"]
        );
    }
}

class Initiator extends Msg
{
    public function insert()
    {
        $db = new DataBase();
        $tasks = $db->select_tasks();

        $connection = $this->connection;

        $channel = $connection->channel();
        $channel->queue_declare("queue", false, true, false, false, false, null, null);

        foreach ($tasks as  $task) {
            if ($task[1] === "sort") {
                $jobArray = array(
                    'task' => $task[1],
                    'id' => $task[0],
                    'content' => $db->get_digits_array(),
                );
            } else
                $jobArray = array(
                    'task' => $task[1],
                    'id' => $task[0],
                );



            $msg = new \PhpAmqpLib\Message\AMQPMessage(
                json_encode($jobArray),
                array('delivery_mode' => 2) # make message persistent
            );


            $channel->basic_publish($msg, '', "queue");
            print 'Job created' . PHP_EOL;
        }
    }

    public function insert_array(array $array)
    {
        $db = new DataBase();
        $db->insert_digits_array($array);
        print_r($db->get_digits_array());
    }
}

class Executor extends Msg
{

    public function test()
    {
        echo "Test";
    }
    // Listen for messages
    public function listen()
    {
        $connection = $this->connection;

        $channel = $connection->channel();

        $callback = function ($encoded_msg) {
            $msg = json_decode($encoded_msg->body, true);
            echo "Received task ", $msg["task"], "\n";
            $result = $this->match_job($msg);
            if ($msg['task'] == "sort") {
                $init = new Initiator();
                $init->insert_array($result);
            }
            if ($result) {
                echo "Done", "\n";
                echo "Result: ";
                print_r($result);
                echo "\n";
            } else {
                echo $result;
                echo "Unknown task", "\n";
            }
        };
        $channel->basic_qos(null, 1, null);
        $channel->queue_declare("queue", false, true, false, false, false, null, null);
        $channel->basic_consume("queue", '', false, true, false, false, $callback);
        echo "Waiting for messages", "\n";

        while ($channel->is_open()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }

    private function match_job(array $msg)
    {
        $result = match ($msg["task"]) {
            "sort" => Utils::sort_array($msg["content"]),
            "titles" => Utils::get_10_newest_titles(),
            "factorial_1" => Utils::calc_factorial_by_loop(10),
            "factorial_2" => Utils::calc_factorial_by_recursion(10),
            default => null
        };

        return $result;
    }
}
