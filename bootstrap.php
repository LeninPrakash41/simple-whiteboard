<?php

// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
// 
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// 
// You should have received a copy of the GNU General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.

defined('SW_INDEX') or die();

define('SW_BOOTSTRAP', 1);

define('SW_DIR_ROOT', __DIR__ . '/');
define('SW_DIR_CSS', SW_DIR_ROOT . 'css/');
define('SW_DIR_JAVASCRIPT', SW_DIR_ROOT . 'js/');
define('SW_DIR_MODULES', SW_DIR_ROOT . 'modules/');

$SW_DB = false;
$SW_CONFIG = require_once './config.inc.php';

/**
 * Returns the current database connection.
 * 
 * @return mysqli The current database connection.
 */
function sw_db() {
    global $SW_DB, $SW_CONFIG;

    if (false === $SW_DB) {
        $dbConf = @$SW_CONFIG['database'];
        if (empty($dbConf)) {
            $dbConf = array();
        }

        $SW_DB = new mysqli(
            @$dbConf['host'],
            @$dbConf['user'], @$dbConf['password'],
            @$dbConf['db'],
            @$dbConf['port'],
            @$dbConf['socket']
        );

        if ($SW_DB->connect_errno) {
            die(sprintf("Could not connect to MySQL database: [%s] '%s'",
                        $SW_DB->connect_errno, $SW_DB->connect_error));
        }
    }

    return $SW_DB;
}

/**
 * Executes an action buffered.
 * 
 * @param callable $action The action to invoke.
 * @param mixed &$result|false (optional) The variable where the result of the action is written to.
 *                                        If (false) a 404 error will be send to the client.
 * 
 * @return string The buffered output of the action.
 */
function sw_executed_buffered(callable $action, &$result = null) {
    ob_start();
    try {
        $result = call_user_func($action);
    }
    finally {
        $content = ob_get_contents();
        ob_end_clean();
    }

    return $content;
}

/**
 * Deactivates HTML frontend (header and footer).
 */
function sw_no_frontend() {
    global $app;

    $app['showHeader'] = false;
    $app['showFooter'] = false;
}

/**
 * Sends a value as JSON string to the client.
 * 
 * @param mixed $val The value to send.
 * @param boolean $noFrontend (optional) Deactivate frontend or not.
 */
function sw_send_json($val, $noFrontend = true) {
    global $app;

    if ($noFrontend) {
        sw_no_frontend();
    }

    $app['headers']['Content-type'] = 'application/json; charset=utf8';

    echo json_encode($val);
}
