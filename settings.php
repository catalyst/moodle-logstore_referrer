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
 * Standard log store settings.
 *
 * @package    logstore_referrer
 * @copyright  2023 Catalyst IT
 * @author     Leah Skinner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    $settings->add(new admin_setting_configcheckbox('logstore_referrer/logguests',
        new lang_string('logguests', 'core_admin'),
        new lang_string('logguests_help', 'core_admin'), 1));

    $settings->add(new admin_setting_configcheckbox('logstore_referrer/jsonformat',
            new lang_string('jsonformat', 'logstore_referrer'),
            new lang_string('jsonformat_desc', 'logstore_referrer'), 1));

    $options = array(
        0    => new lang_string('neverdeletelogs'),
        1000 => new lang_string('numdays', '', 1000),
        365  => new lang_string('numdays', '', 365),
        180  => new lang_string('numdays', '', 180),
        150  => new lang_string('numdays', '', 150),
        120  => new lang_string('numdays', '', 120),
        90   => new lang_string('numdays', '', 90),
        60   => new lang_string('numdays', '', 60),
        35   => new lang_string('numdays', '', 35),
        10   => new lang_string('numdays', '', 10),
        5    => new lang_string('numdays', '', 5),
        2    => new lang_string('numdays', '', 2));
    $settings->add(new admin_setting_configselect('logstore_referrer/loglifetime',
        new lang_string('loglifetime', 'core_admin'),
        new lang_string('configloglifetime', 'core_admin'), 0, $options));

    $settings->add(new admin_setting_configtext('logstore_referrer/buffersize',
        get_string('buffersize', 'logstore_referrer'),
        '', '50', PARAM_INT));

    $options = array(
        'web' => get_string('web', 'logstore_referrer'),
        'cli' => get_string('cli', 'logstore_referrer'),
        'ws' => get_string('ws', 'logstore_referrer'),
        'cron' => get_string('cron', 'logstore_referrer'));
    $settings->add(new admin_setting_configselect('logstore_referrer/origin',
        get_string('origin', 'logstore_referrer'),
        '', 'web', $options));

    $settings->add(new admin_setting_configcheckbox('logstore_referrer/logempty',
    new lang_string('logempty', 'logstore_referrer'),
    '', 0));
}
