<?php

// Copyright (C) 2014 Ling Li
//
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

/**
 * This plugin is used to access media drop videos
 *
 * @since 2.0
 * @package    repository_mediadrop
 * @copyright  2014 Ling Li <lilingv@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/repository/lib.php');

/**
 * repository_local class is used to browse moodle files
 *
 * @since 2.0
 * @package    repository_mediadrop
 * @copyright  2014 Ling Li <lilingv@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class repository_mediadrop extends repository {

    var $server_url = 'http://mdone.info.bit.edu.cn';
    var $api_key = 'Jd7tqOGoulFg7OIZObn';

    /**
     * Get instance options
     *
     * @return array of option names
     */
    public static function get_instance_option_names() {
        return array('mediadrop_server_url', 'mediadrop_api_key');
    }

    public static function instance_config_form($mform) {
        $mform->addElement('text', 'mediadrop_server_url', get_string('mediadrop_server_url', 'repository_mediadrop'), array('size' => '40'));
        $mform->addRule('mediadrop_server_url', get_string('required'), 'required', null, 'client');

        $mform->addElement('text', 'mediadrop_api_key', get_string('mediadrop_api_key', 'repository_mediadrop'), array('size' => '40'));
        $mform->addRule('mediadrop_api_key', get_string('required'), 'required', null, 'client');
    }

    public function __construct($repositoryid, $context = SYSCONTEXTID, $options = array()) {
        parent::__construct($repositoryid, $context, $options);
        $this->api_key = $this->get_option('mediadrop_api_key');
        $this->server_url = $this->get_option('mediadrop_server_url');
    }

    /**
     * Get file listing
     *
     * @param string $encodedpath
     * @param string $page no paging is used in repository_local
     * @return mixed
     */
    public function get_listing($encodedpath = '', $page = '') {
        global $CFG, $USER, $OUTPUT;
        $ret = array();
        $ret['dynload'] = true;
        $ret['nosearch'] = true;
        $ret['nologin'] = true;
        $ret['list'] = array();
        
        $c = new curl();
        try {
            $params = array('api_key' => $this->api_key, 'type' => 'video', 'embded_player' => 1);
            $json = $c->get($this->server_url.'/api/media', $params);
file_put_contents('/tmp/a.json', $json);
            $data = json_decode($json);
        } catch (moodle_exception $e) {
            $this->setError(0, 'connection time-out or invalid url');
            return false;
        }

        foreach ($data->media as $media) {
            $item = array();
            $item['title'] = $media->title;
            $item['date'] = strtotime($media->publish_on);
            $item['thumbnail'] = $media->thumbs->s->url;
            $item['thumbnail_width'] = $media->thumbs->s->x;
            $item['thumbnail_height'] = $media->thumbs->s->y;
            $item['source'] = 'media:'.$media->slug;
            $item['author'] = $media->author;
            $ret['list'][] = $item;
        }

        return $ret;
    }

    public function get_file_reference($source) {
        list($t, $s) = explode(':', $source, 2);
        return $this->server_url.'/'.$t.'/'.$s;
    }

    public function get_link($link) {
        return $link.'/embed_player';
    }

    /**
     * Tells how the file can be picked from this repository
     *
     * @return int
     */
    public function supported_returntypes() {
        return FILE_EXTERNAL;
    }

    /**
     * Does this repository used to browse moodle files?
     *
     * @return boolean
     */
    public function has_moodle_files() {
        return false;
    }

    /**
     * Is this repository accessing private data?
     *
     * @return bool
     */
    public function contains_private_data() {
        return false;
    }

}
