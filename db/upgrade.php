<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Database log store upgrade.
 *
 * @package    logstore_referrer
 * @copyright  2023 Catalyst IT
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_logstore_referrer_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    if ($oldversion < 2023061602) {

        // Changing precision of field referrer on table logstore_referrer_log to (1333).
        $table = new xmldb_table('logstore_referrer_log');
        $field = new xmldb_field('referrer', XMLDB_TYPE_CHAR, '1333', null, null, null, null, 'realuserid');

        // Launch change of precision for field referrer.
        $dbman->change_field_precision($table, $field);

        // Referrer savepoint reached.
        upgrade_plugin_savepoint(true, 2023061602, 'logstore', 'referrer');
    }
    return true;
}