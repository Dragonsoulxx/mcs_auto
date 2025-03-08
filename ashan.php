<?php
// be honest, and give value to our hard work, support us by don't remove our copyright.
// function get profiles data, made by Muhammad Ashan ( http://multics-exchange.com )
require ("config.php");
$cache_link = "http://" . $user . ":" . $pass . "@" . $url . ":" . $port . "/editor" . $editor_cache;
$cccam_link = "http://" . $user . ":" . $pass . "@" . $url . ":" . $port . "/editor" . $editor_cccam;
$mgcamd_link = "http://" . $user . ":" . $pass . "@" . $url . ":" . $port . "/editor" . $editor_mgcamd;
$server_link = "http://" . $user . ":" . $pass . "@" . $url . ":" . $port . "/servers";

function generateRandomString($length = 8)
{
    return substr(str_shuffle(str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))) , 1, $length);
}

function ashan_get_data($string, $start, $end)
{
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

$ranuser = generateRandomString();
$ranpass = generateRandomString();

$call = $_POST['call'];

if ($call == 'cache')
{

    //security for checking provided data.
    if ($_POST["ip"])
    {
        if (filter_var(gethostbyname($_POST["ip"]) , FILTER_VALIDATE_IP))
        {
        }
        else
        {
            echo '<br><font color="brown">Invalid Host!</font>';
            die();
        }
    }
    else
    {
        echo '<br><font color="brown">Please Enter Host.</font>';
        die();
    }

    if ($_POST["port"])
    {
        if (!ctype_digit($_POST["port"]))
        {
            echo "Invalid Port.";
        }
    }
    else
    {
        echo '<br><font color="brown">Please Enter Port.</font>';
        die();
    }
    // security check pass done..
    

    $store_current_data = file_get_contents($cache_link);
    $store_current_cache = trim(ashan_get_data($store_current_data, 'name="textedit">', '</textarea>'));

    $serverip = trim($_POST['ip']);
    $serverport = trim($_POST['port']);
    $fullcache = 'CACHE PEER: ' . $serverip . ' ' . $serverport . ' 1';

    if (preg_match("/{$fullcache}/i", $store_current_cache))
    {
        echo '<font color="brown">This Cache Already Exchanged: <b>' . $fullcache . '</b></font>';
        die();
    }

    $get_new_cache[] = $store_current_cache;

    $get_new_cache[] = $fullcache;

    $data_ashan_pk = implode("\n", $get_new_cache);

    $post = ['textarea' => $data_ashan_pk];

    $ch = curl_init($cache_link);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
    $response = curl_exec($ch);
    curl_close($ch);
    echo '<style> .hideforum {display:none;} </style><br/><br/><hr/><br/><center><br><b><font color="orange">Your Cache Is Ready...</font></b></br></center>
<textarea class="form-control" rows="16" cols="40" id="prince">
' . $cache_deta . '
</textarea> 
<input type="submit" value="Copy My Cache" onclick="myFunction()">';

}

if ($call == 'cccam')
{
    //security for checking provided data.
    if ($_POST["ip"])
    {
        if (filter_var(gethostbyname($_POST["ip"]) , FILTER_VALIDATE_IP))
        {
        }
        else
        {
            echo '<br><font color="brown">Invalid Host!</font>';
            die();
        }
    }
    else
    {
        echo '<br><font color="brown">Please Enter Host.</font>';
        die();
    }

    if ($_POST["port"])
    {
        if (!ctype_digit($_POST["port"]))
        {
            echo "Invalid Port.";
        }
    }
    else
    {
        echo '<br><font color="brown">Please Enter Port.</font>';
        die();
    }
    if ($_POST["mybuser"])
    {
        if (!ctype_alnum($_POST["mybuser"]))
        {
            echo 'Username only contain the <b>a to z, A to Z, 0 to 9</b>';
            die();
        }
    }
    else
    {
        echo '<br><font color="brown">Please Enter Username.</font>';
        die();
    }
    if ($_POST["mybpass"])
    {
        if (!ctype_alnum($_POST["mybpass"]))
        {
            echo 'Password only contain the <b>a to z, A to Z, 0 to 9</b>';
            die();
        }
    }
    else
    {
        echo '<br><font color="brown">Please Enter Password.</font>';
        die();
    }
    // security check pass done..
    

    $store_current_data = file_get_contents($cccam_link);
    $store_current_cccam = trim(ashan_get_data($store_current_data, 'name="textedit">', '</textarea>'));

    $get_new_cccam[] = $store_current_cccam;

    $match_cccam = 'C: ' . trim($_POST["ip"]) . ' ' . trim($_POST["port"]) . ' ' . trim($_POST["mybuser"]) . ' ' . trim($_POST["mybpass"]) . '';
    $get_new_cccam[] = $match_cccam;

    $get_new_cccam[] = "F: $ranuser $ranpass\n\n";

    $data_ashan_pk = implode("\n", $get_new_cccam);

    if (preg_match("/{$match_cccam}/i", $store_current_cccam))
    {
        echo '<br><font color="brown">This Line Already Exchanged: ' . $match_cccam . '</font>';
        die();
    }

    $linedeta = trim($match_cccam);
    $ids = explode(' ', $linedeta);

    $PROTOCOLLINE = $ids['0'];
    $HOST = $ids['1'];
    $PORT = $ids['2'];
    $USR = $ids['3'];
    $PASS = $ids['4'];

    if ($PROTOCOLLINE == 'C:' || $PROTOCOLLINE == 'c:')
    {

        $post = ['textarea' => $data_ashan_pk];

        $ch = curl_init($cccam_link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $response = curl_exec($ch);
        curl_close($ch);

        sleep($delay_sec);

        $info_servers = file_get_contents($server_link);
        $start = '<table class=maintable width=100%>';
        $end = '</html>';
        $data = trim(ashan_get_data($info_servers, $start, $end));
        $rows = preg_split('/<tr id/', $data);
        $row = end($rows);
        $row = preg_replace('/="Row(.*?)td class="/is', "", $row);
        $is_online = trim(preg_replace('/">(.*?)<\/body>/is', "", $row));

        if ($is_online == 'online' || $is_online == 'busy')
        {

            echo "<div style='border: dashed 1px green; padding: 30px;'><B><font color='orange'>Your CCcamd Line Status :</font> <font color='green'> Online</font></B></div>";
            echo "<style> .hideforum {display:none;} </style><br><B><font color='green'>Your CCcamd Line is Ready.</font> <font color='red'>(keep online your line otherwise your server ip will blocked in our exchange so you can't exchange with us anymore.)</font><br></B>";
            echo "<br><input type='text' value='C: " . $url . " " . $portcccam . " " . $ranuser . " " . $ranpass . "' id='prince'>";
            echo "<input type='submit' onclick='myFunction()' value='Copy Line'>";

            die();
        }
        else
        {
            echo "<br><br><div style='border: dashed 1px red; padding: 30px;'><B><font color='orange'>Your CCcamd Line Status,</font> <font color='red'> Offline: $match_cccam</font></B></div>";
            $post = ['textarea' => $store_current_cccam];

            $ch = curl_init($cccam_link);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            $response = curl_exec($ch);
            curl_close($ch);

            die();
        }

    }

    die();
}

if ($call == 'mgcamd')
{

    //security for checking provided data.
    if ($_POST["ip"])
    {
        if (filter_var(gethostbyname($_POST["ip"]) , FILTER_VALIDATE_IP))
        {
        }
        else
        {
            echo '<br><font color="brown">Invalid Host!</font>';
            die();
        }
    }
    else
    {
        echo '<br><font color="brown">Please Enter Host.</font>';
        die();
    }

    if ($_POST["port"])
    {
        if (!ctype_digit($_POST["port"]))
        {
            echo "Invalid Port.";
        }
    }
    else
    {
        echo '<br><font color="brown">Please Enter Port.</font>';
        die();
    }
    if ($_POST["mybuser"])
    {
        if (!ctype_alnum($_POST["mybuser"]))
        {
            echo 'Username only contain the <b>a to z, A to Z, 0 to 9</b>';
            die();
        }
    }
    else
    {
        echo '<br><font color="brown">Please Enter Username.</font>';
        die();
    }
    if ($_POST["mybpass"])
    {
        if (!ctype_alnum($_POST["mybpass"]))
        {
            echo 'Password only contain the <b>a to z, A to Z, 0 to 9</b>';
            die();
        }
    }
    else
    {
        echo '<br><font color="brown">Please Enter Password.</font>';
        die();
    }
    // security check pass done..
    $store_current_data = file_get_contents($mgcamd_link);
    $store_current_mgcamd = trim(ashan_get_data($store_current_data, 'name="textedit">', '</textarea>'));

    $get_new_mgcamd[] = $store_current_mgcamd;

    $match_mgcamd = 'N: ' . trim($_POST["ip"]) . ' ' . trim($_POST["port"]) . ' ' . trim($_POST["mybuser"]) . ' ' . trim($_POST["mybpass"]) . ' ' . $keys . '';

    $get_new_mgcamd[] = $match_mgcamd;

    $get_new_mgcamd[] = "MG: $ranuser $ranpass\n\n";

    $data_ashan_pk = implode("\n", $get_new_mgcamd);

    if (preg_match("/{$match_mgcamd}/i", $store_current_mgcamd))
    {
        echo '<br><font color="brown">This Line Already Exchanged: ' . $match_mgcamd . '</font>';
        die();
    }

    $linedeta = trim($match_mgcamd);
    $ids = explode(' ', $linedeta);

    $PROTOCOLLINE = $ids['0'];
    $HOST = $ids['1'];
    $PORT = $ids['2'];
    $USR = $ids['3'];
    $PASS = $ids['4'];

    if ($PROTOCOLLINE == 'N:' || $PROTOCOLLINE == 'n:')
    {

        $post = ['textarea' => $data_ashan_pk];

        $ch = curl_init($mgcamd_link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $response = curl_exec($ch);
        curl_close($ch);

        sleep($delay_sec);

        $info_servers = file_get_contents($server_link);
        $start = '<table class=maintable width=100%>';
        $end = '</html>';
        $data = trim(ashan_get_data($info_servers, $start, $end));
        $rows = preg_split('/<tr id/', $data);
        $row = end($rows);
        $row = preg_replace('/="Row(.*?)td class="/is', "", $row);
        $is_online = trim(preg_replace('/">(.*?)<\/body>/is', "", $row));

        if ($is_online == 'online' || $is_online == 'busy')
        {

            echo "<br><div style='border: dashed 1px green; padding: 30px;'><B><font color='orange'>Your MGcamd Line Status :</font> <font color='green'> Online</font></B></div>";
            echo "<style> .hideforum {display:none;} </style><br><B><font color='green'>Your MGcamd Line is Ready.</font> <font color='red'>(keep online your line otherwise your server ip will blocked in our exchange so you can't exchange with us anymore.)</font><br></B>";
            echo "<br><input type='text' value='N: " . $url . " " . $portmgcamd . " " . $ranuser . " " . $ranpass . " " . $keys . "' id='prince'>";
            echo "<input type='submit' onclick='myFunction()' value='Copy Line'>";

        }
        else
        {

            $post = ['textarea' => $store_current_mgcamd];

            $ch = curl_init($mgcamd_link);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            $response = curl_exec($ch);
            curl_close($ch);
            echo "<br><br><div style='border: dashed 1px red; padding: 30px;'><B><font color='orange'>Your MGcamd Line Status,</font> <font color='red'> Offline: $match_mgcamd</font></B></div>";

        }

    }

}

if ($call == 'newcamd')
{
    //security for checking provided data.
    if ($_POST["ip"])
    {
        if (filter_var(gethostbyname($_POST["ip"]) , FILTER_VALIDATE_IP))
        {
        }
        else
        {
            echo '<br><font color="brown">Invalid Host!</font>';
            die();
        }
    }
    else
    {
        echo '<br><font color="brown">Please Enter Host.</font>';
        die();
    }

    if ($_POST["port"])
    {
        if (!ctype_digit($_POST["port"]))
        {
            echo "Invalid Port.";
        }
    }
    else
    {
        echo '<br><font color="brown">Please Enter Port.</font>';
        die();
    }
    if ($_POST["mybuser"])
    {
        if (!ctype_alnum($_POST["mybuser"]))
        {
            echo 'Username only contain the <b>a to z, A to Z, 0 to 9</b>';
            die();
        }
    }
    else
    {
        echo '<br><font color="brown">Please Enter Username.</font>';
        die();
    }
    if ($_POST["mybpass"])
    {
        if (!ctype_alnum($_POST["mybpass"]))
        {
            echo 'Password only contain the <b>a to z, A to Z, 0 to 9</b>';
            die();
        }
    }
    else
    {
        echo '<br><font color="brown">Please Enter Password.</font>';
        die();
    }
    // security check pass done..
    

    $store_current_data = file_get_contents($mgcamd_link);
    $store_current_mgcamd = trim(ashan_get_data($store_current_data, 'name="textedit">', '</textarea>'));

    $get_new_mgcamd[] = $store_current_mgcamd;

    $match_mgcamd = 'N: ' . trim($_POST["ip"]) . ' ' . trim($_POST["port"]) . ' ' . trim($_POST["mybuser"]) . ' ' . trim($_POST["mybpass"]) . ' ' . $keys . '';

    $get_new_mgcamd[] = $match_mgcamd;

    $newcamd_profile_port = $_POST['pro'];

    $get_new_mgcamd[] = "USER: $ranuser $ranpass {profiles=$newcamd_profile_port;}\n\n";

    $data_ashan_pk = implode("\n", $get_new_mgcamd);

    if (preg_match("/{$match_mgcamd}/i", $store_current_mgcamd))
    {
        echo '<br><font color="brown">This Line Already Exchanged: ' . $match_mgcamd . '</font>';
        die();
    }

    $linedeta = trim($match_mgcamd);
    $ids = explode(' ', $linedeta);

    $PROTOCOLLINE = $ids['0'];
    $HOST = $ids['1'];
    $PORT = $ids['2'];
    $USR = $ids['3'];
    $PASS = $ids['4'];

    if ($PROTOCOLLINE == 'N:' || $PROTOCOLLINE == 'n:')
    {

        $post = ['textarea' => $data_ashan_pk];

        $ch = curl_init($mgcamd_link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        $response = curl_exec($ch);
        curl_close($ch);

        sleep($delay_sec);

        $info_servers = file_get_contents($server_link);
        $start = '<table class=maintable width=100%>';
        $end = '</html>';
        $data = trim(ashan_get_data($info_servers, $start, $end));
        $rows = preg_split('/<tr id/', $data);
        $row = end($rows);
        $row = preg_replace('/="Row(.*?)td class="/is', "", $row);
        $is_online = trim(preg_replace('/">(.*?)<\/body>/is', "", $row));

        if ($is_online == 'online' || $is_online == 'busy')
        {

            echo "<br><div style='border: dashed 1px green; padding: 30px;'><B><font color='orange'>Your Newcamd Line Status :</font> <font color='green'> Online</font></B></div>";
            echo "<style> .hideforum {display:none;} </style><br><font color='green'><B>Your Newcamd Line is Ready.</font> <font color='red'>(keep online your line otherwise your server ip will blocked in our exchange so you can't exchange with us anymore.)</font><br></B>";
            echo "<br><input type='text' value='N: " . $url . " " . $newcamd_profile_port . " " . $ranuser . " " . $ranpass . " " . $keys . "' id='prince'>";
            echo "<input type='submit' onclick='myFunction()' value='Copy Line'>";

        }
        else
        {

            $post = ['textarea' => $store_current_mgcamd];

            $ch = curl_init($mgcamd_link);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            $response = curl_exec($ch);
            curl_close($ch);

            echo "<br><br><div style='border: dashed 1px red; padding: 30px;'><B><font color='orange'>Your Newcamd Line Status :</font> <font color='red'> Offline</font></B></div>";

        }
    }
}

?>
