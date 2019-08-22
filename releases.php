<?php namespace download\releases;

    include "helpers.php";

    $format_map = array();

    //$DELIM = "\\-"; //this is not getting interpreted proper for some reason?

    class rel_format
    {
        public $replace_uscore;
        public $dist_name;
        public $date_offset;
        public $dist_offset;
        public $device_offset;
        public $build_offset;
        public $channel_offset;
        public $version_offset;
        public $extra_offset;

        function __construct($dist_name, $date_offset, $dist_offset, $device_offset, $build_offset,
            $version_offset, $replace_uscore=true, $channel_offset=null, $extra_offset=null)
        {
            $this->dist_name = $dist_name;
            $this->date_offset = $date_offset;
            $this->dist_offset = $dist_offset;
            $this->device_offset = $device_offset;
            $this->build_offset = $build_offset;
            $this->version_offset = $version_offset;

            $this->replace_uscore = $replace_uscore;
            $this->channel_offset = $channel_offset;
            $this->extra_offset = $extra_offset;
        }

    }

    function get_format_map()
    {
        /*
        #TWRP-3.2.3-lineage-15.1-j10-20180921-gprimelte
        #dotOS-o-j17-20190101-NIGHTLY-gprimelte
        #oc_hotplug-bootimage-lineage-15.1-j3-20190101-gprimelte
        #rr-oreo-j30-20180804-NIGHTLY-gprimelte
        #lineage-16.0-j5-20181014-NIGHTLY-fortuna3g
        #lineage-go-16.0-j8-20181117-NIGHTLY-fortuna3g
        */

        if (count($format_map) == 0)
        {
            $format_map["TWRP"] = new rel_format("TWRP", 5, 0, 6, 4, 1, true);
            $format_map["dot"] = new rel_format("DotOS", 3, 0, 5, 2, 1, true, 4);
            $format_map["rr"] = new rel_format("ResurrectionRemix", 3, 0, 5, 2, 1, true, 4);
            $format_map["lineage-go"] = new rel_format("LineageOS Go", 4, 0, 6, 3, 2, true, 5);
            $format_map["lineage"] = new rel_format("LineageOS", 3, 0, 5, 2, 1, true, 4);
            $format_map["oc_hotplug"] = new rel_format("Kernel", 5, 0, 6, 4, 3, false);
        }
        return $format_map;
    }


    class Release
    {

        var $format;
        var $tag;
        var $tokens;
        /*
        var $dist;
        var $dist_version;
        var $rel_type; //bootimage, otapackage, etc
        var $date;
        var $build_num;
        var $channel; //NIGHTLY, WEEKLY, etc
        var $device;
        var $extra;

        function __construct($long_dist, $version, $date, $build_num, $device,
            $rel_type, $channel, $extra)
            //$rel_type = "otapackage", $channel = "NIGHTLY", $extra = "")
        {
            $this->long_dist = $long_dist;
            $this->dist_version = $version;
            $this->date = $date;
            $this->build_num = $build_num;
            $this->device = $device;
            $this->channel = $channel;
            $this->rel_type = $rel_type;
            $this->extra = $extra;
        }
        */

        function __construct(&$tag, &$format)
        {
            /*
            if ($format->replace_uscore == true)
                //$tag = str_replace("_", $DELIM, $this->tag);
                $this->tag = str_replace("_", "-", $tag);
            else
            */
            $this->tag = $tag;

            $this->format = $format;
            //$this->tokens = null;
        }

        function getTokens()
        {
            if ($this->format->replace_uscore == true)
                //$tag = str_replace("_", $DELIM, $this->tag);
                $tag = str_replace("_", "-", $this->tag);
            else
                $tag = $this->tag;

            if ($this->tokens == null)
                //$this->tokens = explode($DELIM, $tag);
                $this->tokens = explode("-", $tag);
            
            return $this->tokens;
        }

        function getLongDist()
        {
            return $this->format->dist_name;
        }

        function getShortDist()
        {
            return $this->getTokens()[$this->format->dist_offset];
        }

        function getVersion()
        {
            return $this->getTokens()[$this->format->version_offset];
        }

        function getDate()
        {
            $dateStr = $this->getTokens()[$this->format->date_offset];

            $day = substr($dateStr, 6);
            $year = substr($dateStr, 0, 4);
            $month = substr($dateStr, 4, 2);

            //$date = date_create($year . '-' . $month . '-' . $day);
            $date = $year . '-' . $month . '-' . $day;

            return $date;
        }

        function getBuildNum()
        {
            return $this->getTokens()[$this->format->build_offset];
        }

        function getDevice()
        {
            return $this->getTokens()[$this->format->device_offset];
        }

        function getChannel()
        {
            if ($this->format->channel_offset !== null)
                $channel = $this->getTokens()[$this->format->channel_offset];
            else
                $channel = "NIGHTLY";

            return $channel;
        }

        function getExtra()
        {
            if ($this->format->extra_offset !== null)
                $extra = $this->getTokens()[$this->format->extra_offset];
            else
                $extra = "";

            return $extra;
        }

    }

    function read_tags($tagpath = "releases.txt")
    {
        //$cwd = getcwd();
        //$relpath = "$cwd/releases.txt";
        $relfile = fopen($tagpath, "r");

        $tags = array();

        if ($relfile == false)
        {
            echo "Error: Could not open releases file $relpath\n";
            return $tags;
        }

        $s = fgets($relfile);

        while ($s != false)
        {
            //Strip newlines from tags
            $tags[] = rtrim($s);
            $s = fgets($relfile);
        }

        fclose($relfile);

        return $tags; 
    }

    function parse_tags(&$tags)
    {
        $ret = array(
            "date" => array(),
            "dist" => array(),
            "version" => array(),
            "device" => array()
        );

        for ($i = 0; $i < count($tags); $i++)
        {
            $tag = $tags[$i];
            $rel = get_release($tag); 
            
            \download\helpers\add_value_to_2d_arr($ret["date"], $rel->getDate(), $rel);
            \download\helpers\add_value_to_2d_arr($ret["dist"], $rel->getLongDist(), $rel);
            \download\helpers\add_value_to_2d_arr($ret["version"], $rel->getVersion(), $rel);
            \download\helpers\add_value_to_2d_arr($ret["device"], $rel->getDevice(), $rel);
        }
        return $ret;
    }

    function get_release(&$tag)
    {
        $format = null;
        $release = null;

        $map = get_format_map();

        foreach (array_keys($map) as $key)
        {
            $n = strlen($key);

            if (strncasecmp($key, $tag, $n) == 0)
            {
                $format = $map[$key];
                $release = new Release($tag, $format);
                break;
            }
        }
        return $release;
    }

   function filter_releases($releases, $constraint)
    {
        $ret = array();

        //foreach($twoDArr as $arr)
        //{
        //TODO: Test if all constraint values are null and
        //      return the orig array if so
        foreach($releases as $release)
        {
            $date = $release->getDate();
            $version = $release->getVersion();
            $device = $release->getDevice();
            $dist = $release->getLongDist();

            if ($constraint["date"] && $date != $constraint["date"])
                continue;
            if ($constraint["version"] && $version != $constraint["version"])
                continue;
            if ($constraint["device"] && $device != $constraint["device"])
                continue;
            if ($constraint["dist"] && $dist != $constraint["dist"])
                continue;

            $ret[] = $release;
        }
        //}
        return $ret;
    }

?>