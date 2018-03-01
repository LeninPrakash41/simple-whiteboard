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

/** @var array $app **/

defined('SW_INDEX') or die();

if ('GET' !== $_SERVER['REQUEST_METHOD']) {
    http_response_code(405);  // no GET
    die();
}

$board = sw_board();

$db = sw_db();

$currentVersion = sw_current_version( $board );
if (empty($currentVersion)) {
    sw_send_json_result(1);
}
else {
    $response = array(
        'content' => $currentVersion['content'],
        'content_type' => $currentVersion['content_type'],
        'files' => array(),
        'id' => (int)$currentVersion['id'],
        'time' => DateTime::createFromFormat('Y-m-d H:i:s', $currentVersion['time']),
    );

    $stmt = $db->prepare("SELECT `id`,`name` FROM `files` WHERE `board_id`=? AND `is_deleted`='0' ORDER BY `id` DESC,`time` DESC;");
    try {
        $stmt->bind_param('i', $board);
        $stmt->execute();
    
        $result = $stmt->get_result();
        try {
            while ($row = $result->fetch_array()) {
                $response['files'][] = array(
                    'id' => (int)$row[0],
                    'name' => $row[1],
                );
            }
        }
        finally {
            $result->close();
        }
    }
    finally {
        $stmt->close();
    }

    sw_send_json_result(0, $response);
}
