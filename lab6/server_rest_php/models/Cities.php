<?php

namespace models;
class Cities
{
    public $id;
    public $name;
    public $countryCode;
    public $district;
    public $population;
    private $citiesTable = "city";
    /**
     * @var mixed
     */
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function read()
    {
        if ($this->id) {
            $stmt = $this->conn->prepare("SELECT * FROM " . $this->citiesTable . " WHERE ID = ?");
            $stmt->bind_param("i", $this->id);
        } else {
            $stmt = $this->conn->prepare("SELECT * FROM " . $this->citiesTable);
        }
        $stmt->execute();
        return $stmt->get_result();
    }
}