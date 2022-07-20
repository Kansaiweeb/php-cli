<?php
require("msg.php");
class Cli
{
    public function runCommand(array $argv)
    {
        match ($argv[1]) {
            "insert" => $this->handle_insert(),
            "process" => $this->handle_process(),
            "migrate" => $this->handle_migrate(),
            "seed" => $this->handle_seed(),
            default => $this->handle_unknown($argv[1]),
        };
    }

    private function handle_insert()
    {
        $initiator = new Initiator();
        echo "Inserting";
        $initiator->insert();
    }

    private function handle_process()
    {
        $executor = new Executor;
        $executor->listen();
    }

    private function handle_migrate()
    {
        $db = new DataBase();
        echo "Migrating";
        $db->migrate();
    }

    private function handle_seed()
    {
        $db = new DataBase();
        $db->seed();
    }

    private function handle_unknown(string $command)
    {
        echo "Command " . $command . " not found";
    }
}
