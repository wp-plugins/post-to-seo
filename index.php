<?php
/*
  Plugin Name: PostToSEO
  Version: 1.0
  Plugin URI:
  Description: Convert any text in post editor into SEO components such as Keywords, meta description and tags
  Author: Yarob Al-Taay
  Author URI:
 */

if (is_admin())
{
    new PostToSEO();
}

class PostToSEO
{

    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;
    public static $STOP_WORDS = array("a", "about", "above", "above", "across", "after", "afterwards", "again", "against", "all", "almost", "alone", "along", "already", "also", "although", "always", "am", "among", "amongst", "amoungst", "amount", "an", "and", "another", "any", "anyhow", "anyone", "anything", "anyway", "anywhere", "are", "around", "as", "at", "back", "be", "became", "because", "become", "becomes", "becoming", "been", "before", "beforehand", "behind", "being", "below", "beside", "besides", "between", "beyond", "bill", "both", "bottom", "but", "by", "call", "can", "cannot", "cant", "co", "con", "could", "couldnt", "cry", "de", "describe", "detail", "do", "done", "down", "due", "during", "each", "eg", "eight", "either", "eleven", "else", "elsewhere", "empty", "enough", "etc", "even", "ever", "every", "everyone", "everything", "everywhere", "except", "few", "fifteen", "fify", "fill", "find", "fire", "first", "five", "for", "former", "formerly", "forty", "found", "four", "from", "front", "full", "further", "get", "give", "go", "had", "has", "hasnt", "have", "he", "hence", "her", "here", "hereafter", "hereby", "herein", "hereupon", "hers", "herself", "him", "himself", "his", "how", "however", "hundred", "ie", "if", "in", "inc", "indeed", "interest", "into", "is", "it", "its", "itself", "keep", "last", "latter", "latterly", "least", "less", "ltd", "made", "many", "may", "me", "meanwhile", "might", "mill", "mine", "more", "moreover", "most", "mostly", "move", "much", "must", "my", "myself", "name", "namely", "neither", "never", "nevertheless", "next", "nine", "no", "nobody", "none", "noone", "nor", "not", "nothing", "now", "nowhere", "of", "off", "often", "on", "once", "one", "only", "onto", "or", "other", "others", "otherwise", "our", "ours", "ourselves", "out", "over", "own", "part", "per", "perhaps", "please", "put", "rather", "re", "same", "see", "seem", "seemed", "seeming", "seems", "serious", "several", "she", "should", "show", "side", "since", "sincere", "six", "sixty", "so", "some", "somehow", "someone", "something", "sometime", "sometimes", "somewhere", "still", "such", "system", "take", "ten", "than", "that", "the", "their", "them", "themselves", "then", "thence", "there", "thereafter", "thereby", "therefore", "therein", "thereupon", "these", "they", "thickv", "thin", "third", "this", "those", "though", "three", "through", "throughout", "thru", "thus", "to", "together", "too", "top", "toward", "towards", "twelve", "twenty", "two", "un", "under", "until", "up", "upon", "us", "very", "via", "was", "we", "well", "were", "what", "whatever", "when", "whence", "whenever", "where", "whereafter", "whereas", "whereby", "wherein", "whereupon", "wherever", "whether", "which", "while", "whither", "who", "whoever", "whole", "whom", "whose", "why", "will", "with", "within", "without", "would", "yet", "you", "your", "yours", "yourself", "yourselves", "the");
    private $permanentKeywords = [];
    private $stopwords = [];

    /**
     * Start up
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'add_plugin_page']);
        add_action('admin_init', [$this, 'page_init']);
        add_action('wp_ajax_post_to_seo_generate', [$this, 'handleAjax']);

        $this->permanentKeywords = self::getPermanentKeywords();
        $this->stopwords = self::getStopWords();
        new PostToSEOMetaBox();
    }

    /**
     * Add options page
     */
    public function add_plugin_page()
    {
        add_options_page('PostToSEO', 
                'PostToSEO', 
                'manage_options', 
                'PostToSEO', 
                array($this, 'create_admin_page'));
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        $filteredInput = filter_input_array(INPUT_POST);

        ?>
        <style>
            table {width:60%;}
            @media screen and (max-width: 600px) {
                table {width:100%;}
                thead {display: none;}
                tr:nth-of-type(2n) {background-color: inherit;}
                tr td:first-child {background: #f0f0f0; font-weight:bold;font-size:1.3em;}
                tbody td {display: block;  text-align:center;}
                tbody td:before { 
                    content: attr(data-th); 
                    display: block;
                    text-align:center;  
                }
            }</style>
        <div class="wrap">
            <h2><?php '<h2>' . _e('Post To SEO Settings') . '</h2>' ?></h2>
            <p>Use the text areas below to add any permanent keywords (that should appear in each post) or add any stop words (ignored list) so it won't show in any keywords generation</p>
            <form method="post" action="">
                <table>
                    <tbody>
                        <tr>
                            <td><?php _e("Permanent keywords: "); ?></td>
                            <td>
                                <textarea name="permanent_keywords" placeholder="Permanent keywords comma ',' separated..." cols="80" rows="4"><?= @$filteredInput['permanent_keywords'] ? $filteredInput['permanent_keywords'] : implode(', ', $this->permanentKeywords) ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td><?php _e("Stopwords (ignore list): "); ?></td>
                            <td>
                                <textarea name="stopwords" cols="80" rows="4"><?= @$filteredInput['stopwords'] ? $filteredInput['stopwords'] : implode(', ', $this->stopwords) ?></textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <?php submit_button('Save Settings'); ?>
            </form>


            <?php
            if (isset($filteredInput['submit']) and $filteredInput['submit'] == 'Save Settings')
            {
                $this->stopwords = explode(',', $filteredInput['stopwords']);
                self::updateStopWords($this->stopwords);

                $this->permanentKeywords = explode(',', $filteredInput['permanent_keywords']);
                self::updatePermanentKeywords($this->permanentKeywords);  
            }
            ?>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init()
    {
        register_setting(
                'my_option_group', // Option group
                'my_option_name', // Option name
                array($this, 'sanitize') // Sanitize
        );

        add_settings_section(
                'setting_section_id', // ID
                'PostToSEO', // Title
                array($this, 'print_section_info'), // Callback
                'SentenceToKeywords' // Page
        );

        add_settings_field(
                'id_number', // ID
                'ID Number', // Title 
                array($this, 'id_number_callback'), // Callback
                'PostToSEO', // Page
                'setting_section_id' // Section           
        );

        add_settings_field(
                'title', 'Title', array($this, 'title_callback'), 'SentenceToKeywords', 'setting_section_id'
        );
    }

    public static function stringToSEO($text = NULL, $stopwords = [], $lang='en')
    {
        $keywords = trim(preg_replace('/\s+/', ' ', $text)); // remove new lines
        
        if(!in_array($lang, ['ar']))
        {
            $keywords = preg_replace('/[^A-Za-z0-9\s]/', '', $keywords);
        }
        $keywords = explode(' ', $keywords);
        $keywords = array_merge($keywords, self::getPermanentKeywords());

        $keywordsCounter = [];

        function inArray($needle, $haystack)
        {
            foreach ($haystack as $v)
            {
                if (trim(strtolower($needle)) == trim(strtolower($v)))
                {
                    return true;
                }
            }
            return FALSE;
        }

        function tokenTruncate($string, $your_desired_width)
        {
            $parts = preg_split('/([\s\n\r]+)/', $string, null, PREG_SPLIT_DELIM_CAPTURE);
            $parts_count = count($parts);

            $length = 0;
            $last_part = 0;
            for (; $last_part < $parts_count; ++$last_part)
            {
                $length += strlen($parts[$last_part]);
                if ($length > $your_desired_width)
                {
                    break;
                }
            }

            return implode(array_slice($parts, 0, $last_part));
        }

        foreach ($keywords as $keyword)
        {
            if (strlen($keyword) > 2 and ! inArray(($keyword), $stopwords))
            {
                $keyword = strtolower($keyword);

                $keywordsCounter[$keyword] = isset($keywordsCounter[$keyword]) ? $keywordsCounter[$keyword] + 1 : 1;
            }
        }

//arsort($keywordsCounter);

        $tags = [];
        foreach ($keywordsCounter as $k => $v)
        {
            if ($v > 1)
            {
                $tags[] = $k;
            }
        }

        return array(
            'keywords' => implode(', ', array_keys($keywordsCounter)),
            'tags' => implode(', ', $tags),
            'description' => tokenTruncate($text, 160)
        );
    }

    public static function getStopWords()
    {
        return get_option('postToSEO_stopwords', self::$STOP_WORDS);
    }

    public static function updateStopWords($stopWords = [])
    {
        if (is_array($stopWords))
        {
            $f = update_option('postToSEO_stopwords', $stopWords);
        }
    }

    public static function getPermanentKeywords()
    {
        return get_option('postToSEO_permanentKeywords', []);
    }

    public static function updatePermanentKeywords($stopWords = [])
    {
        if (is_array($stopWords))
        {
            foreach ($stopWords as $k => $v)
            {
                $stopWords[$k] = trim($v);
            }
            update_option('postToSEO_permanentKeywords', $stopWords);
        }
    }

    function handleAjax()
    {
        $filteredInput = filter_input_array(INPUT_POST);
        
        if ($filteredInput['action'] == 'post_to_seo_generate')
        {
            $seo = $this->stringToSEO($filteredInput['post_content'], $this->getStopWords(), $filteredInput['lang']);
            echo json_encode(['seo' => $seo]);
            
        }
        exit;
    }

    function getAminNotice($msg = '', $type = 'updated')
    {
        ?><div class="<?= $type ?>">
            <p><?php _e($msg); ?></p>
        </div><?php
    }
}

class PostToSEOMetaBox
{
    public function __construct()
    {
        /* Define the custom box */
        add_action('add_meta_boxes', [$this, 'postToSEOAddCustomBox']);

        /* Do something with the data entered */
        add_action('save_post', [$this, 'postTOSEOSavePostdata']);

        add_action('plugins_loaded', 'postToSEOtextdomain');

        function postToSEOtextdomain()
        {
            //load_plugin_textdomain('PostToSEO', false, dirname(plugin_basename(__FILE__)) . '/lang/');
        }

    }

    /* Adds a box to the main column on the Post and Page edit screens */

    function postToSEOAddCustomBox()
    {
        add_meta_box('post_to_seo_section', 
                __('Post To SEO', 'postToSEO'), 
                [$this, 'postTOSEOInnerCustomBox'], 
                'post', 
                'side', 
                'high');
    }

    /* Prints the box content */

    function postTOSEOInnerCustomBox($post)
    {
        //wp_nonce_field('wpse_61041_wpse_61041_field_nonce', 'wpse_61041_noncename');

        {
            ?>
            <script src="<?= plugin_dir_url(__FILE__) ?>/js/engine.js"></script>
            <table>
                <tbody>
                    <tr>
                        <td><label for="lang"><b>Language:</b></label></td>
                        <td><select name="lang" id="post_to_seo_lang">
                                <option value="en" selected="">English</option>
                                <option value="ar">Other</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="100%">
                            <div style="">
                                <input type="button" class="button button-primary post_to_seo_generate" id="post_to_seo_generate_upper" value="Generate SEO">
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="100%">Keywords</td>
                    </tr>
                    <tr>
                        <td colspan="100%"><textarea id="post_to_seo_keywords" cols="30" rows="10"></textarea></td>
                    </tr>
                    <tr>
                        <td colspan="100%">Description (160 chars)</td>
                    </tr>
                    <tr>
                        <td colspan="100%">
                            <textarea id="post_to_seo_description" cols="30" rows="5"></textarea><br />
                        </td>
                    </tr>
                    <tr>
                        <td colspan="100%">Tags</td>
                    </tr>
                    <tr>
                        <td colspan="100%"><textarea id="post_to_seo_tags" cols="30" rows="3" ></textarea></td>
                    </tr>

                    <tr>
                        <td colspan="100%">
                            <div style="">
                                <input type="button" class="button button-primary post_to_seo_generate" id="post_to_seo_generate_lower" value="Generate SEO">
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <script>
                var seo = new PostTOSEOEngine();
                seo.start();
            </script>
            <?php
        }
    }

}
