<?php
// declare(strict_types=1);
require_once(__DIR__ . '/../vendor/autoload.php');
use Symfony\Component\ErrorHandler\ErrorHandler;
// ErrorHandler::register(null, false);
set_exception_handler([new ErrorHandler(), 'handleException']);

