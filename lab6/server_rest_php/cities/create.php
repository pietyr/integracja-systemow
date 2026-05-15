<?php

use config\Database;
use models\Cities;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

include_once '../config/Database.php';
include_once '../models/Cities.php';

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->Name) && !empty($data->CountryCode) && !empty($data->District) && isset($data->Population)) {
    $database = new Database();
    $db = $database->getConnection();
    $cities = new Cities($db);

    $cities->name = $data->Name;
    $cities->countryCode = $data->CountryCode;
    $cities->district = $data->District;
    $cities->population = $data->Population;

    if ($cities->create()) {
        http_response_code(201);
        echo json_encode(array("message" => "City was created."));
    } else {
        http_response_code(503);
        echo json_encode(array("message" => "Unable to create city."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to create city. Data is incomplete."));
}