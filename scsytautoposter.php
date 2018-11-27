<?php
/**
 * Plugin Name:       SCS YouTube Auto Poster
 * Description:       Auto Posts newest YouTube videos from the Youtube channel(s) of your choice.
 * Version:           2018.11.23
 * Author:            Mike Mind
 * Author URI:        https://mikemind.me
 * Text Domain:       mikemind.me
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * GitHub Plugin URI: https://github.com/
 */

//here we make the settings menu
class MySettingsPage
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            'SCS Auto Poster Options',
            'SCS Auto Poster',
            'manage_options',
            'scs_ytap',
            array($this, 'create_admin_page')
            //array($this, 'tytttap')
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        include dirname(__FILE__) . "/functions.php";
        $this->options = get_option('scs_ytap_options');

        ?>
         <div class="wrap">
            <h1>SCS YouTube Auto Poster Settings</h1>
            <div style="background-color:#3398CC;color:#ffffff;font-size:130%;line-height:130%;padding:10px;">
            I'm Mike, a <a href="http://mikemind.me/" style="color:#ffffff;font-weight:bold;" target="_blank">Full-Stack Web Developer Freelancer</a>, and you can contact me for feedback, bugs, feature requests or other work at 
            <a href="mailto:admin@webwealth.me?Subject=Hello%20Mike" style="color:#ffffff;font-weight:bold;" target="_blank">admin@webwealth.me</a> or at my YouTube Channel: 
            <a href="https://www.youtube.com/channel/UC3f86MEyfT0DLaa6uxbFF9w/videos" style="color:#ffffff;font-weight:bold;" target="_blank">MikeMindAcodeMY</a>
            </div>
            <br>
            <div id="scs_ytap_accordion" class="accordion">
    <label for="tm" class="accordionitem"><h1><b>Click for Settings</b></h1></label>
    <input type="checkbox" id="tm"/>
    <div class="hiddentext">
            <form method="post" action="options.php">
            <?php
// This prints out all hidden setting fields
        settings_fields('scs_ytap_option_group');
        do_settings_sections('scs_ytap');
        submit_button();
        ?>
        <p>*Required</p>
            </form>
        </div>
        <form method="post" action="">
        <input type="text" name="action" value="start" hidden>        
        <input class="scs_ytap_ytbutton" type="submit" value="Make YT Posts!">
        </form>
        </div>
  </div> 

        <?php
scs_ytap_outputcss();
global $scs_apikey;
if( ($scs_apikey == "") || (!isset($scs_apikey))){echo "<style>.hiddentext{display:block!important;opacity:1!important}</style>";}

        scs_ytap_outputjs();

        if (isset($_POST['action'])) {
            // echo $_POST['action'];
            $this->tytttap();}

    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
            'scs_ytap_option_group', // Option group
            'scs_ytap_options', // Option name
            array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
            'setting_section_scs_ytap', // ID
            'YouTube Settings', // Title
            array($this, 'print_section_info'), // Callback
            'scs_ytap' // Page
        );

        add_settings_field(
            'apikey',
            'Api Key*',
            array($this, 'apikey_callback'),
            'scs_ytap',
            'setting_section_scs_ytap'
        );

        add_settings_field(
            'channelId',
            'Channel Id*',
            array($this, 'channelId_callback'),
            'scs_ytap',
            'setting_section_scs_ytap'
        );

        add_settings_field(
            'noofvids',
            'Number of Videos*',
            array($this, 'noofvids_callback'),
            'scs_ytap',
            'setting_section_scs_ytap'
        );
        add_settings_field(
            'scs_ytap_publishedAfter',
            'Published After Date',
            array($this, 'publishedAfter_callback'),
            'scs_ytap',
            'setting_section_scs_ytap'
        );
        add_settings_field(
            'scs_ytap_publishedBefore',
            'Published Before Date',
            array($this, 'publishedBefore_callback'),
            'scs_ytap',
            'setting_section_scs_ytap'
        );
        add_settings_section(
            'setting_section_scs_ytap_wppost', // ID
            'Post Settings', // Title
            array($this, 'print_section_info_wppost'), // Callback
            'scs_ytap' // Page
        );
        add_settings_field(
            'post_status',
            'Post Status',
            array($this, 'post_status_callback'),
            'scs_ytap',
            'setting_section_scs_ytap_wppost'
        );
        add_settings_field(
            'post_category',
            'Post Category',
            array($this, 'post_category_callback'),
            'scs_ytap',
            'setting_section_scs_ytap_wppost'
        );
        add_settings_field(
            'post_author',
            'Post Author',
            array($this, 'post_author_callback'),
            'scs_ytap',
            'setting_section_scs_ytap_wppost'
        );
        add_settings_field(
            'post_date',
            'Post Date',
            array($this, 'post_date_callback'),
            'scs_ytap',
            'setting_section_scs_ytap_wppost'
        );
        add_settings_field(
            'scs_ytap_shortcodes',
            'Customize Post Content',
            array($this, 'scs_ytap_shortcodes_callback'),
            'scs_ytap',
            'setting_section_scs_ytap_wppost'
        );
        add_settings_section(
            'setting_section_scs_ytap_cron', // ID
            'Automatization/Cron Settings', // Title
            array($this, 'print_section_info_cron'), // Callback
            'scs_ytap' // Page
        );
        add_settings_field(
            'scs_ytap_cronDay',
            'Automatically check/post every',
            array($this, 'cronDay_callback'),
            'scs_ytap',
            'setting_section_scs_ytap_cron'
        );
        add_settings_field(
            'scs_ytap_cronHour',
            'Automatically check/post every',
            array($this, 'cronHour_callback'),
            'scs_ytap',
            'setting_section_scs_ytap_cron'
        );
 
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input)
    {
        $new_input = array();

        if (isset($input['apikey'])) {
            $new_input['apikey'] = sanitize_text_field($input['apikey']);
        }
               

        if (isset($input['channelId'])) {
            $new_input['channelId'] = sanitize_text_field($input['channelId']);
        }

        if (isset($input['noofvids'])) {
            $new_input['noofvids'] = sanitize_text_field($input['noofvids']);
        }

        if (isset($input['post_status'])) {
            $new_input['post_status'] = sanitize_text_field($input['post_status']);
        }
        if (isset($input['post_category'])) {
            $new_input['post_category'] = sanitize_text_field($input['post_category']);
        }
        if (isset($input['post_author'])) {
            $new_input['post_author'] = sanitize_text_field($input['post_author']);
        }
        if (isset($input['post_date'])) {
            $new_input['post_date'] = sanitize_text_field($input['post_date']);
        }
        if (isset($input['scs_ytap_shortcodes'])) {
            //$new_input['scs_ytap_shortcodes'] = sanitize_text_field($input['scs_ytap_shortcodes']);
            $new_input['scs_ytap_shortcodes'] = sanitize_text_field(htmlspecialchars($input['scs_ytap_shortcodes']));
            
        }
        if (isset($input['publishedAfter'])) {
            $new_input['publishedAfter'] = sanitize_text_field($input['publishedAfter']);
        }
        if (isset($input['publishedBefore'])) {
            $new_input['publishedBefore'] = sanitize_text_field($input['publishedBefore']);
        }
        if (isset($input['cronDay'])) {
            $new_input['cronDay'] = sanitize_text_field($input['cronDay']);
        }
        if (isset($input['cronHour'])) {
            $new_input['cronHour'] = sanitize_text_field($input['cronHour']);
        }

        return $new_input;
    }

    /**
     * Print the Section text
     */
    public function print_section_info()
    {
        //print '*Required';
    }
    public function print_section_info_wppost()
    {
        //print 'Enter your post settings below:';
    }
    public function print_section_info_cron()
    {
        //print 'Wordpress Cron activates when someone visits the site, if you want to work as a real cron (activate on time) without someone visiting the site, then use this ';
    }
    /**
     * Get the settings option array and print one of its values
     */

    public function apikey_callback()
    {
        global $scs_apikey;
        if (isset($this->options['apikey'])) {
            $scs_apikey = $this->options['apikey'];} else { $scs_apikey = "";}

        printf(
            '<input type="text" id="apikey" name="scs_ytap_options[apikey]" value="%s" required /> <a href="https://developers.google.com/youtube/v3/getting-started" target="_blank">How to get one?</a>',
            isset($this->options['apikey']) ? esc_attr($this->options['apikey']) : ''
        );
    }

    public function channelId_callback()
    {global $scs_channelId;
        if (isset($this->options['channelId'])) {
            $scs_channelId = $this->options['channelId'];} else { $scs_channelId = "";}
        printf(
            '<input type="text" id="channelId" name="scs_ytap_options[channelId]" value="%s" required /> eg: <b title="https://www.youtube.com/channel/UC3f86MEyfT0DLaa6uxbFF9w">UC3f86MEyfT0DLaa6uxbFF9w</b>',
            isset($this->options['channelId']) ? esc_attr($this->options['channelId']) : ''
        );
    }

    public function noofvids_callback()
    {global $scs_noofvids;
        if (isset($this->options['noofvids'])) {
            $scs_noofvids = $this->options['noofvids'];} else { $scs_noofvids = "";}

        printf(
            '<input type="number" id="noofvids" name="scs_ytap_options[noofvids]" min="1" max="50" value="%s" required /> <span title="Maximum 50 videos at the moment, use published before and after to get older videos">(1-50)</span>',
            isset($this->options['noofvids']) ? esc_attr($this->options['noofvids']) : ''
        );
    }

    public function post_status_callback()
    {
        global $scs_post_status;
        if (isset($this->options['post_status'])) {
            $scs_post_status = $this->options['post_status'];} else { $scs_post_status = "";}

        $post_status_code = post_status_array_loop($scs_post_status);

        printf(
            '<select id="post_status" name="scs_ytap_options[post_status]" value="%s">
            ' . $post_status_code . '
      </select>',
            isset($this->options['post_status']) ? esc_attr($this->options['post_status']) : ''
        );

    }

    public function post_category_callback()
    {
        global $scs_post_category;

        if (isset($this->options['post_category'])) {
            $scs_post_category = $this->options['post_category'];} else { $scs_post_category = "";}

        $categories = get_categories(array('hide_empty' => 0));

        $post_category_code = "";
        foreach ($categories as $category) {
            if ($scs_post_category == $category->term_id) {$selected = "selected='selected'";} else { $selected = "";}
            $post_category_code .= '<option class="" value="' . $category->term_id . '" ' . $selected . '>' . $category->name . '</option>';
        }

        printf(
            '<select id="post_category" name="scs_ytap_options[post_category]" value="%s">
            ' . $post_category_code . '
      </select>',
            isset($this->options['post_category']) ? esc_attr($this->options['post_category']) : ''
        );

    }

    public function post_author_callback()
    {
        global $scs_post_author;
        if (isset($this->options['post_author'])) {
            $scs_post_author = $this->options['post_author'];} else { $scs_post_author = "";}

        $authors = get_users();

        $post_author_code = "";
        foreach ($authors as $author) {
            if ($scs_post_author == $author->ID) {$selected = "selected='selected'";} else { $selected = "";}
            $post_author_code .= '<option class="" value="' . $author->ID . '" ' . $selected . '>' . $author->user_nicename . '</option>';
        }

        printf(
            '<select id="post_author" name="scs_ytap_options[post_author]" value="%s">
            ' . $post_author_code . '
      </select>',
            isset($this->options['post_author']) ? esc_attr($this->options['post_author']) : ''
        );

    }

    public function post_date_callback()
    {
        global $scs_post_date;
        if (isset($this->options['post_date'])) {
            $scs_post_date = $this->options['post_date'];} else { $scs_post_date = "";}

        $post_date_code = post_date_array_loop($scs_post_date);

        printf(
            '<select id="post_date" name="scs_ytap_options[post_date]" value="%s">
            ' . $post_date_code . '
      </select>',
            isset($this->options['post_date']) ? esc_attr($this->options['post_date']) : ''
        );

    }

    public function scs_ytap_shortcodes_callback()
    {
        global $scs_ytap_shortcodes;
        global $autogencaptionsswitch;
        //here we replace the shortcode values with the actual variables
        if (isset($this->options['scs_ytap_shortcodes'])) {
            $scs_ytap_shortcodes = $this->options['scs_ytap_shortcodes'];} else { $scs_ytap_shortcodes = "";}
            if (strpos($scs_ytap_shortcodes, '[scs_ytap_video-captions]') == false) {
                $autogencaptionsswitch = false;
            }else{$autogencaptionsswitch = true;}

        printf(
            //<span title="Leave blank for default: [scs_ytap_video-embed] [scs_ytap_video-description] &lt;br&gt; &lt;h3&gt;Auto Generated Captions&lt;/h3&gt; [scs_ytap_video-captions]">
            //Shortcodes: [scs_ytap_video-title] [scs_ytap_video-id] [scs_ytap_video-embed] [scs_ytap_video-description] [scs_ytap_video-captions] [scs_ytap_video-tags] [scs_ytap_video-thumbnail]</span><br>            
            '       </span><br>
            <span><b>Shortcodes:</b></span><br>
            <span title="YouTube Video Title (used as post name as well)">[scs_ytap_video-title] eg: <span class="scsytapeg">Frontend Developer vs Backend Developer - What Should You Learn? (Funducational)</span></span><br>
            <span title="YouTube Video ID">[scs_ytap_video-id] eg: <span class="scsytapeg">yBA7lOu4W8Q</span></span><br>
            <span title="YouTube Video with Wordpress Video Embed Code">[scs_ytap_video-embed] eg: <span class="scsytapeg">[embed]https://www.youtube.com/watch?v=yBA7lOu4W8Q[/embed]</span></span><br>
            <span title="YouTube Video Description">[scs_ytap_video-description] eg: <span class="scsytapeg">Frontend Developer vs Backend Developer - The most funducational video out there!...</span></span><br>
            <span title="YouTube Video Captions (works with automated generated captions too!)">[scs_ytap_video-captions] eg: <span class="scsytapeg">hi I\m Mike Mind and welcome to Mike Mind Acodemy...</span></span><br>
            <span title="YouTube Video Tags/Keywords (used as post Tags as well)">[scs_ytap_video-tags] eg: <span class="scsytapeg">Frontend Developer vs Backend Developer,frontend web development,frontend,backend web development,backend,fullstack,javascript,html,css,node,node.js,...</span></span><br>
            <span title="YouTube Video Thumbnail (used as fetured image as well if theme supports)">[scs_ytap_video-thumbnail] eg: <span class="scsytapeg">https://i.ytimg.com/vi/yBA7lOu4W8Q/hqdefault.jpg</span></span><br>
            <i><span><b>TIP:</b> You can use HTML tags here, for eg use &lt;br&gt; for line break, &lt;hr&gt; for horizontal line etc</span></i><br>
            <i><span><b>Example:</b> [scs_ytap_video-embed] [scs_ytap_video-description] &lt;br&gt; &lt;h3&gt;Auto Generated Captions&lt;/h3&gt; [scs_ytap_video-captions]</span></i><br>
            
            
            
            <textarea rows="4" cols="50" id="scs_ytap_shortcodes" name="scs_ytap_options[scs_ytap_shortcodes]" value="" >%s</textarea>            
            <br> Note: [scs_ytap_video-captions] might not always work due to multiple reasons and are in no way proof read.',
            isset($this->options['scs_ytap_shortcodes']) ? esc_attr($this->options['scs_ytap_shortcodes']) : "[scs_ytap_video-embed] [scs_ytap_video-description] &lt;br&gt; &lt;h3&gt;Auto Generated Captions&lt;/h3&gt; [scs_ytap_video-captions]"
        );
//$currvidtitle [scs_ytap_video-title]
        //$currvidid [scs_ytap_video-id]
        //$currviddes [scs_ytap_video-description]
        //$autogencaptions [scs_ytap_video-captions]
        //$currvidtags [scs_ytap_video-tags]
        //$curthumb [scs_ytap_video-thumbnail]

    }

    public function publishedAfter_callback()
    {global $scs_publishedAfter;
        if (isset($this->options['publishedAfter'])) {
            $scs_publishedAfter = $this->options['publishedAfter'];} else { $scs_publishedAfter = "";}

        printf(
            '<input type="date" id="publishedAfter" class="scsytapdate" name="scs_ytap_options[publishedAfter]" value="%s" /> <span title="If facing issues, use Published Before Date as well">(Optional)</span>',
            isset($this->options['publishedAfter']) ? esc_attr($this->options['publishedAfter']) : ''
        );
    }

    public function publishedBefore_callback()
    {global $scs_publishedBefore;
        if (isset($this->options['publishedBefore'])) {
            $scs_publishedBefore = $this->options['publishedBefore'];} else { $scs_publishedBefore = "";}

        printf(
            '<input type="date" id="publishedBefore" class="scsytapdate" name="scs_ytap_options[publishedBefore]" value="%s" /> <span title="If facing issues, use Published After Date as well">(Optional)</span>',
            isset($this->options['publishedBefore']) ? esc_attr($this->options['publishedBefore']) : ''
        );
        
    }

    public function cronDay_callback()
    {global $scs_cronDay;
        if (isset($this->options['cronDay'])) {
            $scs_cronDay = $this->options['cronDay'];} else { $scs_cronDay = "";}

        printf(
            '<input type="number" id="cronDay" name="scs_ytap_options[cronDay]" min="1" max="999" value="%s" />days (1-999)',
            isset($this->options['cronDay']) ? esc_attr($this->options['cronDay']) : ''
        );
    }

    public function cronHour_callback()
    {global $scs_cronHour;
        if (isset($this->options['cronHour'])) {
            $scs_cronHour = $this->options['cronHour'];} else { $scs_cronHour = "";}

        printf(
            '<input type="number" id="cronHour" name="scs_ytap_options[cronHour]" min="0" max="23" value="%s" />hours (0-23)',
            isset($this->options['cronHour']) ? esc_attr($this->options['cronHour']) : ''
        );
    }

    public function tytttap()
    {

        global $scs_apikey;
        global $scs_channelId;
        global $scs_noofvids;
        global $scs_post_status;
        global $scs_ytap_shortcodes;
        global $scs_publishedAfter;
        global $scs_publishedBefore;
        global $scs_post_category;
        global $scs_post_author;
        global $scs_post_date;
        global $autogencaptionsswitch;

        echo "<h1>POSTS CREATED! ...</h1>";

        $allwpytids = scs_ytap_getYtIdsFromPosts();

        $data = scs_ytap_getYtVideoListData($scs_apikey, $scs_channelId, $scs_noofvids, $scs_publishedAfter, $scs_publishedBefore);
        //echo $data;

        for ($j = 0; $j < $scs_noofvids; $j++) {
            //            echo "<pre>";
            // var_dump($data['items'][$j]);
            // echo "</pre>";
            echo "<br><h2>Video $j:</h2>";
            $currvidid = $data['items'][$j]['id']['videoId'];
            $currvidtitle = $data['items'][$j]['snippet']['title'];
            $scs_yt_post_date = $data['items'][$j]['snippet']['publishedAt'];

            //first we check if post was already created in wordpress by video id
            if (!in_array($currvidid, $allwpytids)) {

                $viddata = scs_ytap_getYtVideoIndividualData($scs_apikey, $currvidid);

                echo "ID: " . $currvidid . "<br>";
                $currviddes = $viddata['items'][0]['snippet']['description'];
                //echo "DESCRIPTION: " . $currviddes . "<br>";
                $curthumb = $viddata['items'][0]['snippet']['thumbnails']['high']['url'];
                //echo "THUMBNAIL: " . $curthumb . "<br>";
                //todo category id and matches category from site
                $currvidcatid = $viddata['items'][0]['snippet']['categoryId'];
                //echo "CATEGORY ID: " . $currvidcatid . "<br>";
                $currvidtags = $viddata['items'][0]['snippet']['tags'];
                //echo "TAGS: " . $currvidtags . "<br>";

                for ($i = 0; $i < count($currvidtags); $i++) {
                    // echo $currvidtags[$i] . ", ";
                }
                //then we get the autogenerated captions
if($autogencaptionsswitch){$autogencaptions = getClosedCaptionsForVideo($currvidid);}else{$autogencaptions = "";}
          

                scs_ytap_createPost($currvidtitle, $scs_post_status, $currvidid, $currviddes, $autogencaptions, $currvidtags, $curthumb, $scs_ytap_shortcodes, $scs_post_category, $scs_post_author, $scs_post_date, $scs_yt_post_date);

            } else {echo "Video <b>'" . $currvidtitle . "'</b> already posted! <br>";}

        }

    }

}

if (is_admin()) {
    $my_settings_page = new MySettingsPage();
}
