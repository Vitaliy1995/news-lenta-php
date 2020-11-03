<?php
$_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__."/../");

require_once $_SERVER['DOCUMENT_ROOT'] . "/DBController.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/ParserController.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/NewsController.php";

$result = NewsController::updateNews();

if ($result) {
    echo "ok";
} else {
    echo "error";
}
