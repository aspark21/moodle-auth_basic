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
 * Master Password Settings
 *
 * @package    auth_basic
 * @copyright  2018 Nathan Nguyen <nathannguyen@catalyst-au.nete>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('./classes/form/savepassword_form.php');
require_once($CFG->libdir.'/tablelib.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

admin_externalpage_setup('auth_basic_masterpassword');
$thispage = '/auth/basic/masterpassword.php';

$PAGE->set_url(new moodle_url($thispage));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('masterpassword', 'auth_basic'));

if (!isset($CFG->auth_basic_enabled_master_password)) {
    echo $OUTPUT->notification(get_string('masterpassword_not_enabled', 'auth_basic'), 'notifyproblem');
}

if (!is_enabled_auth('basic')) {
    echo $OUTPUT->notification(get_string('auth_basic_not_enabled', 'auth_basic'), 'notifyproblem');
}

$whitelist = $CFG->auth_basic_whitelist_ips;
if (!isset($whitelist)) {
    echo $OUTPUT->notification(get_string('whitelist_not_set', 'auth_basic'), 'notifyproblem');
} else {
    echo $OUTPUT->notification(get_string('whitelistonly', 'auth_basic', $whitelist), 'notifyproblem');
}

// Save Password Form.
$password = time().uniqid();
$mform = new savepassword_form(null, array('password' => $password));

if ($formdata = $mform->get_data()) {
    $record = new stdClass();
    $record->password = $formdata->password;
    $record->userid = $USER->id;
    $record->usage = 0;
    $record->timecreated = time();
    $record->timeexpired = time() + DAYSECS;
    $DB->insert_record('auth_basic_master_password', $record);
    redirect(new moodle_url($thispage));
} else {
    $mform->set_data($toform);
    $mform->display();
}

// Master Password Table.
echo $OUTPUT->heading(get_string('generated_masterpassword', 'auth_basic'));
$table = new table_sql('master_password');
$table->set_sql('*', "{auth_basic_master_password}", 'userid = :userid', array('userid' => $USER->id));
$table->define_baseurl("$CFG->wwwroot/auth/basic/masterpassword.php");
$table->sortable(true, 'timecreated', SORT_DESC);
$table->out(50, true);

echo $OUTPUT->footer();