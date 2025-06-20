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
 * Renderer for use with the course section and all the goodness that falls
 * within it.
 *
 * This renderer should contain methods useful to courses, and categories.
 *
 * @package   moodlecore
 * @copyright 2010 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_nhse\output\core;

defined('MOODLE_INTERNAL') || die();

use moodle_url;
use html_writer;
use coursecat_helper;

/**
 * The core course renderer
 *
 * Can be retrieved with the following:
 * $renderer = $PAGE->get_renderer('core','course');
 */
class course_renderer extends \core_course_renderer
{
    /**
     * Returns array of course images.
     * @param  \core_course_list_element  $course
     * @return array
     */
    public function course_images(\core_course_list_element $course): array {
        global $CFG;
        $contentimages = [];

        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            if ($isimage) {
                $url = moodle_url::make_file_url("$CFG->wwwroot/pluginfile.php",
                    '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                    $file->get_filearea() . $file->get_filepath() . $file->get_filename(), !$isimage);
                $contentimages[] = [
                    'src' => $url,
                    'alt' => $course->get_formatted_name(),
                    'class' => 'courseimage',
                ];
            }
        }

        return $contentimages;
    }

//    /**
//     * Displays one course in the list of courses.
//     *
//     * This is an internal function, to display an information about just one course
//     * please use {@link core_course_renderer::course_info_box()}
//     *
//     * @param coursecat_helper $chelper various display options
//     * @param core_course_list_element|stdClass $course
//     * @param string $additionalclasses additional classes to add to the main <div> tag (usually
//     *    depend on the course position in list - first/last/even/odd)
//     * @return string
//     */
//    protected function coursecat_coursebox(coursecat_helper $chelper, $course, $additionalclasses = '') {
//        if (!isset($this->strings->summary)) {
//            $this->strings->summary = get_string('summary');
//        }
//        if ($chelper->get_show_courses() <= self::COURSECAT_SHOW_COURSES_COUNT) {
//            return '';
//        }
//        if ($course instanceof stdClass) {
//            $course = new core_course_list_element($course);
//        }
//        $content = '';
//        $classes = trim('coursebox clearfix '. $additionalclasses);
//        if ($chelper->get_show_courses() < self::COURSECAT_SHOW_COURSES_EXPANDED) {
//            $classes .= ' collapsed';
//        }
//
//        // .coursebox
//        $content .= html_writer::start_tag('div', array(
//            'class' => $classes,
//            'data-courseid' => $course->id,
//            'data-type' => self::COURSECAT_TYPE_COURSE,
//        ));
//
//        $content .= html_writer::start_tag('div', array('class' => 'info'));
//        $content .= $this->course_name($chelper, $course);
//        $content .= $this->course_enrolment_icons($course);
//        $content .= html_writer::end_tag('div');
//
//        $content .= html_writer::start_tag('div', array('class' => 'content'));
//        $content .= $this->coursecat_coursebox_content($chelper, $course);
//        $content .= html_writer::end_tag('div');
//
//        $content .= html_writer::end_tag('div'); // .coursebox
//
//        return $content;
//    }

    /**
     * Get the user progress in the course.
     * @param $course
     * @param $userid
     *
     * @return float|int|null
     * @throws \dml_exception
     */
    public function get_progress($course, $userid = null) {
        return \core_completion\progress::get_course_progress_percentage(get_course($course?->id), $userid);
    }

    /**
     * Displays one course in the list of courses.
     *
     * This is an internal function, to display an information about just one course
     * please use {@link core_course_renderer::course_info_box()}
     *
     * @param coursecat_helper $chelper various display options
     * @param core_course_list_element|stdClass $course
     * @param string $additionalclasses additional classes to add to the main <div> tag (usually
     *    depend on the course position in list - first/last/even/odd)
     * @return string
     */
    protected function coursecat_coursebox(coursecat_helper $chelper, $course, $additionalclasses = '') {
        if ($chelper->get_show_courses() < self::COURSECAT_SHOW_COURSES_EXPANDED) {
            return '';
        }

        if ($course instanceof stdClass) {
            $course = new core_course_list_element($course);
        }

//        $content = \html_writer::start_tag('div', ['class' => 'd-flex']);
//        $content .= $this->course_overview_files($course);
//        $content .= \html_writer::start_tag('div', ['class' => 'flex-grow-1']);
//        $content .= $this->course_summary($chelper, $course);
//        $content .= $this->course_contacts($course);
//        $content .= $this->course_category_name($chelper, $course);
//        $content .= $this->course_custom_fields($course);
//        $content .= \html_writer::end_tag('div');
//        $content .= \html_writer::end_tag('div');
//        return $content;

        $coursecontacts = $course->get_course_contacts();
        $courseenrolmenticons = !empty($courseenrolmenticons) ? $this->render_enrolment_icons($courseenrolmenticons) : false;
        $courseprogress = $this->get_progress($course);
        $hasprogress = $courseprogress != null;
        $images = $this->course_images($course);

        $data = [
            'id' => $course->id,
            'fullname' => $chelper->get_course_formatted_name($course),
            'visible' => $course->visible,
            'image' => array_pop($images),
            'summary' => $this->course_summary($chelper, $course),
            'category' => $this->course_category_name($chelper, $course),
            'customfields' => $this->course_custom_fields($course),
            'hasprogress' => $hasprogress,
            'progress' => (int) $courseprogress,
            'hasenrolmenticons' => $courseenrolmenticons != false,
            'enrolmenticons' => $courseenrolmenticons,
            'hascontacts' => !empty($coursecontacts),
            'contacts' => $this->course_contacts($course),
            'courseurl' => new moodle_url('/course/view.php', ['id' => $course->id]),
        ];

        return $this->render_from_template('theme_nhse/coursecard', $data);
    }
}
