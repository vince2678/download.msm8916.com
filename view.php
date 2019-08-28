<?php namespace download\view;

    include "releases.php";

    function get_stylesheet()
    {
        $style = "/css/style.css";
        return $style;
    }

    function print_stylesheet()
    {
        $style = get_stylesheet();
        echo "<link rel='stylesheet' href='$style'>";
    }

    function print_header()
    {

        //echo "<hr />\n";
    }

    function print_body()
    {

        //echo "<h1>Title</h1>\n";
        //echo "<script src='js/headers.js' type='text/javascript'></script>\n";
        //echo "<hr />\n";
    }

    function print_footer()
    {

    } 

    /* 
        echo $_SERVER['QUERY_STRING'] . PHP_EOL;

        ["QUERY_STRING"]=>
        string(27) "view=downloads&groupBy=date"
        ["REQUEST_URI"]=>
        string(61) "/~vincent/html/download/index.php?view=downloads&groupBy=date"
        ["SCRIPT_NAME"]=>
        string(33) "/~vincent/html/download/index.php"
        ["PHP_SELF"]=>
        string(33) "/~vincent/html/download/index.php"
    */
    function get_release_query_link($key, $value, $text = null)
    {
        //echo "<a href=\"?groupBy=${group}&${group}=${key}\"> <b>${key}</b></a>";
        if ($text == null)
            $text = $value;

        if ($_GET[$key] == null)
            $link = "<a href='?". $_SERVER["QUERY_STRING"] . "&amp;${key}=${value}'>${text}</a>";
        else
            $link = "<a href='?". $_SERVER["QUERY_STRING"] . "'>${text}</a>";

        return $link;
    }

    // memoization for the indentation
    $ind = array();

    function indent($num = 1)
    {
        if ($ind[$num])
            return $ind[$num];

        $ret = "";

        for ($i = 0; $i < $num; $i++)
            $ret = $ret . "    ";

        $ind[$num] = $ret;

        return $ret;
    }

    function print_releases($groupBy, $relGroup, $constraint)
    {
        $group = $_GET["groupBy"];

        if ($group == null)
            $group = "device";


        echo indent(2) . "<div id = 'build_div' class = 'div'>\n";

        //TODO: Make selector/navbar for choosing group sort selection

        //foreach($relGroup as $releases)
        foreach(array_keys($relGroup) as $key)
        {
            $releases = \download\releases\filter_releases($relGroup[$key], $constraint);

            if (count($releases) == 0)
                continue;

            echo indent(3) . "<h2>" . get_release_query_link($group, $key) . "</h2>" . PHP_EOL;

            echo indent(3) . "<table class = 'build_folder'>\n";

            echo indent(4) . "<tr class = 'header_tr'>\n";

            if ($group != "dist")
                echo indent(5) . "<th>Distribution</th>\n";

            if ($group != "version")
                echo indent(5) . "<th>Version</th>\n";

            echo indent(5) . "<th>Build</th>\n";

            if ($group != "device")
                echo indent(5) . "<th>Device</th>\n";

            if ($group != "date")
                echo indent(5) . "<th>Date</th>\n";

            echo indent(5) . "<th></th>\n";

            echo indent(4) . "</tr>\n";

            foreach($releases as $release)
            {

                $tag = $release->tag;
                

                echo indent(4) . "<tr class = 'build_tr'>\n";

                if ($group != "dist")
                    echo indent(5) . "<td class='build_dist'>" . get_release_query_link("dist", $release->getLongDist()) . "</td>\n";

                if ($group != "version")
                    echo indent(5) . "<td class='build_version'>"
                    . get_release_query_link("version", $release->getVersion()) . "</td>\n";

                echo indent(5) . "<td class='build_number'>" . $release->getBuildNum() . "</td>\n";

                if ($group != "device")
                    echo indent(5) . "<td class='build_device'>" . get_release_query_link("device", $release->getDevice()) . "</td>\n";

                if ($group != "date")
                    echo indent(5) . "<td class='build_date'>" . get_release_query_link("date", $release->getDate()) . "</td>\n";

                $tag_link = "<a class = 'release_url' href='?view=downloads&amp;tag=$tag'>View</a>";
                echo indent(5) . "<td class='build_dl_link'>" . $tag_link . "</td>\n";

                echo indent(4) . "</tr>\n";
            }
            echo indent(3) . "</table>\n";
        }
        echo indent(2) . "</div>\n";
    }

    function list_release_artifacts($tag)
    {
        // get and parse tags
        $tags = \download\releases\read_tags();
        $maps = \download\releases\parse_tags($tags);

        $release = $maps["tag"][$tag][0];

        $distLong = $release->getLongDist();
        $version = $release->getVersion();
        $device = $release->getDevice();
        $date = $release->getDate();
        $build = $release->getBuildNum();
        $deviceLong = $release->getLongDeviceName();
        $model = $release->getDeviceModel();

        $github_org_url = "https://github.com/Galaxy-MSM8916";

        $device_tree_url = "${github_org_url}/android_device_samsung_${device}";
        $kernel_tree_url = "${github_org_url}/android_kernel_samsung_msm8916";

        echo <<<EOF
        <hr />
        <div id="release">
            <h2> ${distLong} ${version} for the ${deviceLong}</h2>
            <hr />
            <h3>Info: </h3>
            <div id="release_info">
                <p>Device Codename:<span> ${device}</span></p>
                <p>Device Model:<span> ${model}</span></p>
                <p>Build Tag:<span> $tag</span></p>
                <p>Build Date:<span> $date</span></p>
                <p>Build Number:<span> $build</span></p>
            </div>
            <hr />
            <h3>Links: </h3>
            <div id="release_links">
                <p><a href='${device_tree_url}'>Device tree</a></p>
                <p><a href='${kernel_tree_url}'>Kernel tree</a></p>
                <p><a href='${github_org_url}/releases/releases/tag/$tag'>View all artifacts/downloads on GitHub</a></p>
            </div>
        </div>
EOF;

        /* TODO: Query github releases and put artifacts into table
        echo "<hr />\n";
        echo "<h2>Build Artifacts</h2>\n";
        echo "<p> ROM: </p>\n";
        echo "<p> Changelog: </p>\n";
        echo "<p> Recovery: </p>\n";
        echo "<p> Boot image: </p>\n";
        */

    }

    function parse_old_download_url()
    {
        // get and parse tags
        $tags = \download\releases\read_tags();
        $maps = \download\releases\parse_tags($tags);

        $prefix_len = strlen($_SERVER["CONTEXT_PREFIX"]);

        $old_url = substr($_SERVER["REDIRECT_URL"], $prefix_len);

        $split_url = explode("/", $old_url);

        $constraint = array();

        foreach ($split_url as $substr)
        {
            if (array_key_exists($new = str_replace("_", " ", $substr), $maps["dist"]))
                {
                    $constraint["dist"] = $new;
                    continue;
                }
            elseif (array_key_exists($substr, $maps["device"]))
                {
                    $constraint["device"] = $substr;
                    continue;
                }
            elseif (array_key_exists($substr, $maps["version"]))
                {
                    $constraint["version"] = $substr;
                    continue;
                }
        }

        return $constraint;
    }

    function list_releases($constraint = null)
    {
        // get and parse tags
        $tags = \download\releases\read_tags();
        $maps = \download\releases\parse_tags($tags);

        if ($constraint == null)
        {
            $constraint = array(
                "date" => $_GET["date"],
                "device" => $_GET["device"],
                "dist" => $_GET["dist"],
                "version" => $_GET["version"]
            );
        }

        if (null !== ($case = $_GET["groupBy"]))
        {
            print_releases($case, $maps[$case], $constraint);
        }
        else
        {
            $case = "device";
            print_releases($case, $maps[$case], $constraint);
        }
        
        //var_dump(filter_releases($maps["device"], $constraint));
        //echo "</pre>\n";

        /*
        */
        //echo "<hr />\n";
    }

    function print_home()
    {
        echo <<<EOF
        <div id="home">
            <h1>Under construction</h1>
            <hr />
            <h2>Links:</h2>
            <div id="home_links">
                <a href="?view=downloads">View build downloads</a>
                <a href="https://jenkins.msm8916.com">Jenkins CI</a>
                <a href="https://review.msm8916.com">Gerrit Code Review</a>
                <a href="https://github.com/Galaxy-MSM8916">GitHub Page</a>
            </div>
        </div>
EOF;
    }

    function print_404()
    {
        echo <<<EOF
        <h2>Error 404 - Not found</h2>
        <br />
        <h3>The page requested could not be found.</h2>
        <br />
EOF;
    }

    function generate_view()
    {
        print_header();
        print_body();
        print_footer();

        switch($case = $_GET["view"])
        {
            case "downloads":
            {
                if (null != ($tag = $_GET["tag"]))
                    list_release_artifacts($tag);
                else
                    list_releases();

                break;
            }
            case "about":
            {
                //TODO: do something
                break;
            }
            case "build_status":
            {
                //TODO: do something
                break;
            }
            default:
            {
                if (strlen($_SERVER["REDIRECT_URL"]) > 0)
                {
                    $constraint = parse_old_download_url();

                    if (count($constraint) > 0)
                        list_releases($constraint);
                    else
                        print_404();
                }
                else //default (home) case
                {
                    print_home();
                }

                break;
            }
        }
    }

?>