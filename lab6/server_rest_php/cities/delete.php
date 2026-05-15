<?php

use config\Database;
use models\Cities;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: DELETE");

include_once '../config/Database.php';
include_once '../models/Cities.php';

$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!empty($id)) {
    $database = new Database();
    $db = $database->getConnection();
    $cities = new Cities($db);

    $cities->id = $id;

    if ($cities->delete()) {
        http_response_code(200);
        echo json_encode(array("message" => "City was deleted."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to delete city."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to delete city. ID is missing."));
}