<?php

use config\Database;
use models\Cities;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: PUT");

include_once '../config/Database.php';
include_once '../models/Cities.php';

$id = isset($_GET['id']) ? $_GET['id'] : null;
$data = json_decode(file_get_contents("php://input"));

if (!empty($id) && !empty($data->Name) && !empty($data->CountryCode) && !empty($data->District) && isset($data->Population)) {
    $database = new Database();
    $db = $database->getConnection();
    $cities = new Cities($db);

    $cities->id = $id;
    $cities->name = $data->Name;
    $cities->countryCode = $data->CountryCode;
    $cities->district = $data->District;
    $cities->population = $data->Population;

    if ($cities->update()) {
        http_response_code(200);
        echo json_encode(array("message" => "City was updated."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to update city."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to update city. Data is incomplete."));
}
