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
 * An embedded layout for the boost theme.
 *
 * @package   theme_boost
 * @copyright 2016 Damyon Wiese
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$fakeblockshtml = $OUTPUT->blocks('side-pre', array(), 'aside', true);
$hasfakeblocks = strpos($fakeblockshtml, 'data-block="_fake"') !== false;
$renderer = $PAGE->get_renderer('core');

$templatecontext = [
    'favicons' => [
        'mask' => new moodle_url($CFG->wwwroot . '/theme/nhse/pix/nhsuk-icon-mask.svg'),
        'svg' => new moodle_url($CFG->wwwroot . '/theme/nhse/pix/favicon.svg'),
        'apple-180' => new moodle_url($CFG->wwwroot . '/theme/nhse/pix/nhsuk-icon-180.png'),
        'apple-192' => new moodle_url($CFG->wwwroot . '/theme/nhse/pix/nhsuk-icon-192.png'),
        'apple-512' => new moodle_url($CFG->wwwroot . '/theme/nhse/pix/nhsuk-icon-512.png'),
    ],
    'manifest_url' => new moodle_url($CFG->wwwroot . '/theme/nhse/pix/manifest.json'),
    'output' => $OUTPUT,
    'headercontent' => $PAGE->activityheader->export_for_template($renderer),
    'hasfakeblocks' => $hasfakeblocks,
    'fakeblocks' => $fakeblockshtml,
];

// Include NHSUK Frontend js file
$PAGE->requires->js_module(new moodle_url($CFG->wwwroot . '/theme/nhse/javascript/nhse.min.js'), true);

echo $OUTPUT->render_from_template('theme_nhse/embedded', $templatecontext);
