<?php
/**
 * Autoloading file:
 * ##############################################
 * ########### Do not touch this file ###########
 * ##############################################
 * Firstly load the class Autoloader with name App
 * Adds the configuration file to the start of all
 * Verify if you are in debugging version or not
 * Set the application language automatically
 * Load vendor, classes and functions in the following order
 * Deny access to the folder app and vendor if using php webserver
 * And finally it adds and load all the routes then initialize all
 */

$autoloader = DIRECTORY . SEPARATOR . 'vendor' . SEPARATOR . 'Autoloader.php';
$configfile = DIRECTORY . SEPARATOR . 'app' . SEPARATOR . 'config.php';

if (!file_exists($configfile)) { die('Configuration file not found.'); }
require_once $configfile;

if (!file_exists($autoloader)) { die('Autoloader not found.'); }
require_once $autoloader;

if (DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 'on');

    // Funcție pentru a afișa CSS-ul doar o singură dată
    function print_debug_styles_once() {
        static $printed = false;
        if ($printed) return;
        $printed = true;

        echo "
        <style>
            body {
                background: #121212;
                color: #ccc;
                font-family: 'Courier New', monospace;
            }
            .debug-box {
                background: #1e1e1e;
                color: #ddd;
                border: 1px solid #ff4d4f;
                padding: 20px;
                margin: 40px;
                border-radius: 10px;
                box-shadow: 0 0 10px #000;
            }
            .debug-box strong {
                color: #ff4d4f;
            }
            .debug-box pre {
                background: #2a2a2a;
                padding: 10px;
                overflow-x: auto;
                border-radius: 6px;
                color: #ccc;
            }
            .debug-line {
                display: block;
                padding: 2px 8px;
            }
            .debug-line.highlight {
                background: #8b0000;
                color: #fff;
                border-left: 4px solid #ff4d4f;
            }
        </style>
        ";
    }

    // Handler pentru erori non-fatale
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        print_debug_styles_once();

        echo "<div class='debug-box'>";
        echo "<strong>Error:</strong> $errstr<br>";
        echo "<strong>File:</strong> $errfile<br>";
        echo "<strong>Line:</strong> $errline<br>";

        if (file_exists($errfile)) {
            $lines = file($errfile);
            $start = max($errline - 3, 0);
            $end = min($errline + 2, count($lines));
            echo "<pre>";
            for ($i = $start; $i < $end; $i++) {
                $lineNumber = $i + 1;
                $content = htmlspecialchars($lines[$i]);
                $class = ($lineNumber == $errline) ? "debug-line highlight" : "debug-line";
                echo "<span class='$class'>{$lineNumber}: $content</span>";
            }
            echo "</pre>";
        }

        echo "</div>";
    });

    // Handler pentru erori fatale (Parse, Fatal etc.)
    register_shutdown_function(function () {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_PARSE, E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR])) {
            print_debug_styles_once();

            echo "<div class='debug-box'>";
            echo "<strong>Fatal Error:</strong> {$error['message']}<br>";
            echo "<strong>File:</strong> {$error['file']}<br>";
            echo "<strong>Line:</strong> {$error['line']}<br>";

            if (file_exists($error['file'])) {
                $lines = file($error['file']);
                $start = max($error['line'] - 3, 0);
                $end = min($error['line'] + 2, count($lines));
                echo "<pre>";
                for ($i = $start; $i < $end; $i++) {
                    $lineNumber = $i + 1;
                    $content = htmlspecialchars($lines[$i]);
                    $class = ($lineNumber == $error['line']) ? "debug-line highlight" : "debug-line";
                    echo "<span class='$class'>{$lineNumber}: $content</span>";
                }
                echo "</pre>";
            }

            echo "</div>";
        }
    });
}

\App::setLanguage();
foreach(\App::loadVendor() as $dir) { require_once($dir); }
foreach(\App::loadClasses() as $dir) { require_once($dir); }
foreach(\App::loadFunctions() as $dir) { require_once($dir); }

\App::isForbidden(
    ['app', 'vendor']
);
use Vendor\Router;
$Route = new Router();
require_once 'routes.php';
$Route->initialize();
