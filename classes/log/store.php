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
 * referrer log reader/writer.
 *
 * @package    logstore_referrer
 * @copyright  2023 Catalyst IT
 * @author     Leah Skinner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace logstore_referrer\log;

defined('MOODLE_INTERNAL') || die();

class store implements \tool_log\log\writer, \core\log\sql_internal_table_reader {
    use \tool_log\helper\store,
        \tool_log\helper\buffered_writer,
        \tool_log\helper\reader;

    /** @var string $logguests true if logging guest access */
    protected $logguests;

    /** @var string $logguests true if logging guest access */
    protected $origin;

    /** @var boolean $logempty true if logging guest access */
    protected $logempty;

    public function __construct(\tool_log\log\manager $manager) {
        $this->helper_setup($manager);
        // Log everything before setting is saved for the first time.
        $this->logguests = $this->get_config('logguests', 1);
        // JSON writing defaults to false (table format compatibility with older versions).
        // Note: This variable is defined in the buffered_writer trait.
        $this->jsonformat = (bool)$this->get_config('jsonformat', false);
        $this->origin = $this->get_config('origin', 'web');
        $this->logempty = (bool)$this->get_config('logempty', false);
    }

    /**
     * Should the event be ignored (== not logged)?
     * @param \core\event\base $event
     * @return bool
     */
    protected function is_event_ignored(\core\event\base $event) {
        if ((!CLI_SCRIPT or PHPUNIT_TEST) and !$this->logguests) {
            // Always log inside CLI scripts because we do not login there.
            if (!isloggedin() or isguestuser()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Finally store the events into the database.
     *
     * @param array $evententries raw event data
     */
    protected function insert_event_entries($evententries) {
        global $DB;

        $DB->insert_records('logstore_referrer_log', $evententries);
    }

    public function get_events_select($selectwhere, array $params, $sort, $limitfrom, $limitnum) {
        global $DB;

        $sort = self::tweak_sort_by_id($sort);

        $events = array();
        $records = $DB->get_recordset_select('logstore_referrer_log', $selectwhere, $params, $sort, '*', $limitfrom, $limitnum);

        foreach ($records as $data) {
            if ($event = $this->get_log_event($data)) {
                $events[$data->id] = $event;
            }
        }

        $records->close();

        return $events;
    }

    /**
     * Fetch records using given criteria returning a Traversable object.
     *
     * Note that the traversable object contains a moodle_recordset, so
     * remember that is important that you call close() once you finish
     * using it.
     *
     * @param string $selectwhere
     * @param array $params
     * @param string $sort
     * @param int $limitfrom
     * @param int $limitnum
     * @return \core\dml\recordset_walk|\core\event\base[]
     */
    public function get_events_select_iterator($selectwhere, array $params, $sort, $limitfrom, $limitnum) {
        global $DB;

        $sort = self::tweak_sort_by_id($sort);

        $recordset = $DB->get_recordset_select('logstore_referrer_log', $selectwhere, $params, $sort, '*', $limitfrom, $limitnum);

        return new \core\dml\recordset_walk($recordset, array($this, 'get_log_event'));
    }

    /**
     * Write event in the store with buffering. Method insert_event_entries() must be
     * defined.
     *
     * @param \core\event\base $event
     *
     * @return void
     */
    public function write(\core\event\base $event) {
        global $PAGE;

        if ($this->is_event_ignored($event)) {
            return;
        }

        // We need to capture current info at this moment,
        // at the same time this lowers memory use because
        // snapshots and custom objects may be garbage collected.
        $entry = $event->get_data();
        if ($this->jsonformat) {
            $entry['other'] = json_encode($entry['other']);
        } else {
            $entry['other'] = serialize($entry['other']);
        }
        $entry['origin'] = $PAGE->requestorigin;
        $entry['ip'] = $PAGE->requestip;
        $entry['realuserid'] = \core\session\manager::is_loggedinas() ? $GLOBALS['USER']->realuser : null;
        $entry['referrer'] = get_local_referer(false);

        $this->buffer[] = $entry;
        $this->count++;

        if (!isset($this->buffersize)) {
            $this->buffersize = $this->get_config('buffersize', 50);
        }

        if ($this->count >= $this->buffersize) {
            $this->flush();
        }
    }

    /**
     * Returns an event from the log data.
     *
     * @param stdClass $data Log data
     * @return \core\event\base
     */
    public function get_log_event($data) {

        $extra = array(
            'origin' => $data->origin,
            'ip' => $data->ip,
            'realuserid' => $data->realuserid
        );
        $data = (array)$data;
        $id = $data['id'];
        $data['other'] = self::decode_other($data['other']);
        if ($data['other'] === false) {
            $data['other'] = array();
        }
        unset($data['origin']);
        unset($data['ip']);
        unset($data['realuserid']);
        unset($data['id']);
        unset($data['referrer']);

        if (!$event = \core\event\base::restore($data, $extra)) {
            return null;
        }

        return $event;
    }

    public function get_events_select_count($selectwhere, array $params) {
        global $DB;
        return $DB->count_records_select('logstore_referrer_log', $selectwhere, $params);
    }

    public function get_internal_log_table_name() {
        return 'logstore_referrer_log';
    }

    /**
     * Get whether events are present for the given select clause.
     *
     * @param string $selectwhere select conditions.
     * @param array $params params.
     *
     * @return bool Whether events available for the given conditions
     */
    public function get_events_select_exists(string $selectwhere, array $params): bool {
        global $DB;
        if (!$this->init()) {
            return false;
        }

        if (!$dbtable = $this->get_config('dbtable')) {
            return false;
        }

        return $this->$DB->record_exists_select($dbtable, $selectwhere, $params);
    }

    /**
     * Are the new events appearing in the reader?
     *
     * @return bool true means new log events are being added, false means no new data will be added
     */
    public function is_logging() {
        // Only enabled stpres are queried,
        // this means we can return true here unless store has some extra switch.
        return true;
    }
}
