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

use context_system;
use core_course_category;
use core_course_list_element;
use coursecat_helper;
use lang_string;
use moodle_url;

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

    /**
     * Returns HTML to print list of available courses for the frontpage
     *
     * @return string
     */
    public function frontpage_available_courses() {
        global $CFG;

        $chelper = new coursecat_helper();
        $chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED)->
        set_courses_display_options(
            array(
                'recursive' => true,
                'limit' => $CFG->frontpagecourselimit,
                'viewmoreurl' => new moodle_url('/course/index.php'),
                'viewmoretext' => new lang_string('fulllistofcourses')
            )
        );

        $chelper->set_attributes(array('class' => 'frontpage-course-list-all nhsuk-grid-row nhsuk-card-group'));
        $courses = core_course_category::top()->get_courses($chelper->get_courses_display_options());
        $totalcount = core_course_category::top()->get_courses_count($chelper->get_courses_display_options());

        if (!$totalcount && !$this->page->user_is_editing() && has_capability('moodle/course:create', context_system::instance())) {
            // Print link to create a new course, for the 1st available category.
            return $this->add_new_course_button();
        }

        return $this->coursecat_courses($chelper, $courses, $totalcount);
    }

    /**
     * Returns the user picture
     * @param $user
     * @param $imgsize
     *
     * @return string
     * @throws \core\exception\coding_exception
     */
    public function get_user_picture($user) {
        global $DB, $PAGE;

        if (!isset($user->picture)) {
            $user = $DB->get_record('user', ['id' => $user?->id]);
        }

        return $this->user_picture($user);
    }

    /**
     * Returns HTML to display course contacts.
     * @param $course
     *
     * @return array
     * @throws \coding_exception
     * @throws \core\exception\coding_exception
     */
    public function get_course_contacts($course) {
        $contacts = [];
        if ($course->has_course_contacts()) {
            $instructors = $course->get_course_contacts();
            foreach ($instructors as $instructor) {
                $user = $instructor['user'];
                $contact = [
                    'id' => $user->id,
                    'fullname' => fullname($user),
                    'role' => $instructor['role']->displayname,
                ];

                $contact['userpicture'] = $this->get_user_picture($user);

                $contacts[] = $contact;
            }
        }

        return $contacts;
    }

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
        if ($course instanceof stdClass) {
            $course = new core_course_list_element($course);
        }

        $courseenrolmenticons = !empty($courseenrolmenticons) ? $this->render_enrolment_icons($courseenrolmenticons) : false;
        $images = $this->course_images($course);
        $category = core_course_category::get($course?->category, IGNORE_MISSING);

        $data = [
            'id' => $course->id,
            'fullname' => $chelper->get_course_formatted_name($course),
            'visible' => $course->visible,
            'image' => array_pop($images),
            'summary' => $this->course_summary($chelper, $course),
            'coursecategory' => $category->name ?? null,
            'customfields' => $this->course_custom_fields($course),
            'progress' => $this->get_progress($course) ?: 0,
            'gridcolumns' => get_config( 'theme_nhse', 'grid_columns') ?: 'nhsuk-grid-column-full',
            'multigridcolumns' => (!empty($gridcColumns) && $gridcColumns != 'nhsuk-grid-column-full'),
            'hasenrolmenticons' => $courseenrolmenticons != false,
            'enrolmenticons' => $courseenrolmenticons,
            'contacts' => $this->get_course_contacts($course),
            'hascontacts' => !empty($this->get_course_contacts($course)),
            'showtutors' => false, // optional tutors in home page
            'courseurl' => new moodle_url('/course/view.php', ['id' => $course->id]),
        ];

        return $this->render_from_template('theme_nhse/coursecard', $data);
    }

    /**
     * Add nhsuk grid for course card listing
     * @param  coursecat_helper  $chelper
     * @param $courses
     * @param $totalcount
     *
     * @return string
     */
    protected function coursecat_courses(coursecat_helper $chelper, $courses, $totalcount = null)
    {
        $chelper = new coursecat_helper();
        $chelper->set_attributes(array('class' => 'nhsuk-grid-row nhsuk-card-group'));

        return parent::coursecat_courses($chelper, $courses, $totalcount);
    }
}
