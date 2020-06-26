<?php
/*
====================

The MIT License (MIT)

Copyright (c) 2018 cryptapus <info@cryptapus.org>

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

====================
*/

include('utils.php');

$config = include('config.php');

$settings = [
    'coinname' => $config['coinname'],
    'blocksperpage' => $config['blocksperpage'],
    'latest_version' => $config['latest_blk_version_8bit_lsbhex'],
    'explorer_link_blk' => $config['explorer_link_blk'],
    'release_link' => $config['release_link'],
    'rpchost' => $config['rpchost'],
    'rpcport' => $config['rpcport'],
    'rpcusername' => $config['rpcusername'],
    'rpcpassword' => $config['rpcpassword'],
];

require_once('easybitcoin.php');

date_default_timezone_set('UTC');

function getminerinfo($cb) {
    $name = 'unknown';
    $url = 'none';
    $scb = hex2str($cb);

    $mdb = include('pools.php');

    foreach ($mdb as $pool) {
        foreach ($pool["cbstrings"] as $cbs) {
            if (strpos($scb,$cbs) !== false) {
                $name = $pool["name"];
                $url = $pool["url"];
            }
        }
    }
    $ret = [ 'name' => $name, 'url' => $url];
    return $ret;
}

function blocklist($settings) {
    $coin = new Bitcoin($settings['rpcusername'],$settings['rpcpassword'],
        $settings['rpchost'],$settings['rpcport']);
    $info = $coin->getblockchaininfo();
    $tipblock = $info["blocks"];
    if (array_key_exists('startblock',$_GET)) {
        $lastblock = $_GET["startblock"];
    } else {
        $lastblock = $tipblock;
    }
    if ($lastblock==$tipblock) {
        print("<p><a href='.?startblock=".
            ($lastblock-$settings['blocksperpage']-1)."'>Previous ".
            $settings['blocksperpage']."</a></p>");
    } else {
        print("<p><a href=.?startblock=".
            ($lastblock+$settings['blocksperpage']+1)."> Next ".
            $settings['blocksperpage']."</a> -- <a href='.?startblock=".
            ($lastblock-$settings['blocksperpage']-1)."'>Previous ".
            $settings['blocksperpage']."</a></p>");
    }
    print("<hr>");
    print("<table class='table'>");
    print("<thead>");
    print("<tr>");
    print("<th scope='col'>Block Number</th>");
    print("<th scope='col'>Block Time (UTC)</th>");
    print("<th scope='col'>Block Version</th>");
    print("<th scope='col'><a href=".$settings['release_link'].
        " target='_blank'>Latest Block Version</a></th>");
    print("<th scope='col'>Algo</th>");
    print("<th scope='col'>Difficulty</th>");
    print("<th scope='col'>PoW</th>");
    print("<th scope='col'>TX Count</th>");
    print("<th scope='col'>Block Size</th>");
    print("<th scope='col'>Block Weight</th>");
    print("<th scope='col'>Mined By</th>");
    print("<th scope='col'>Coinbase String</th>");
    print("</tr>");
    print("<tbody>");
    for ($i=0; $i<=$settings['blocksperpage']; $i++) {
        $blocknum = $lastblock - $i;
        $bhash = $coin->getblockhash($blocknum);
        $block = $coin->getblock($bhash);
        $blocktime = $block["time"];
        $blockversion = $block["version"];
        $algo = $block["pow_algo"];
        $difficulty = $block["difficulty"];
        $blocksize = $block["size"];
        $blockweight = $block["weight"];
        $isauxpow = false;
        $numtxs = sizeof($block["tx"]);;
        if (array_key_exists('auxpow',$block)) $isauxpow = true;
        // Get coinbase:
        if ($isauxpow) {
            $cb = $block["auxpow"]["tx"]["vin"][0]["coinbase"];
        } else {
            $cbtx = $coin->getrawtransaction($block["tx"][0],true);
            $cb = $cbtx["vin"][0]["coinbase"];
        }
        $pool = getminerinfo($cb);
        //print_r($cb);
        if ($pool["name"] == "unknown") {
            $poollink = "";
        } else {
            $poollink = "<a href='".$pool["url"]."' target='_blank'>".
                $pool["name"]."</a>";
        }
        if ($isauxpow) $saux = 'AuxPow'; else $saux = 'PoW';
        $supgraded_class = 'bg-danger';
        $supgraded = 'no';
        if (dechex($blockversion & 0x000000ff) == $settings['latest_version']) {
            $supgraded_class = 'bg-success';
            $supgraded = 'yes';
        }
        if (array_key_exists('filteralgo',$_GET)) {
            // we can use this to filter, so something like:
            // index.php?startblock=12345&filteralgo=sha256d
            if ($_GET['filteralgo']==$algo) {
                $printline = true;
            } else {
                $printline = false;
            }
        } else {
            $printline = true;
        }
        if ($printline) {
            print("<tr><td><a href='".$settings['explorer_link_blk'].$bhash.
                "' target='_blank'>".$blocknum."</a></td><td>".
                gmdate("M d Y H:i:s",$blocktime)."</td><td>0x".
                dechex($blockversion)."</th><td class='".$supgraded_class.
                "'>".$supgraded."</th><td>".$algo."</td><td>".
                num2sci(floatval($difficulty),3).
                "</td><td>".$saux."</td><td>".$numtxs."</td><td>".
                num2sci(floatval($blocksize))."</td><td>".
                num2sci(floatval($blockweight))."</td><td>".$poollink.
                "</td><td>".hex2str($cb)."</td></tr>");
        }
    }
    print("</tbody>");
    print("</table>");
    print("<hr>");
    if ($lastblock==$tipblock) {
        print("<p><a href='.?startblock=".
            ($lastblock-$settings['blocksperpage']-1)."'>Previous ".
            $settings['blocksperpage']."</a></p>");
    } else {
        print("<p><a href=.?startblock=".
            ($lastblock+$settings['blocksperpage']+1)."> Next ".
            $settings['blocksperpage']."</a> -- <a href='.?startblock=".
            ($lastblock-$settings['blocksperpage']-1)."'>Previous ".
            $settings['blocksperpage']."</a></p>");
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="refresh" content="600">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<title><?php print($settings['coinname']) ?> Miners</title>
</head>
<body>

<nav class="navbar navbar-inverse">
<div class="container-fluid">

<div class="navbar-header">
  <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
    <span class="icon-bar"></span>
  </button>
  <a class="navbar-brand" href="."><?php print($settings['coinname']) ?> Miners</a>
</div>

<div class="collapse navbar-collapse" id="myNavbar">

  <!--
  <ul class="nav navbar-nav">

    <li><a href="#">nt</a></li>
    <li><a href="#">nt</a></li>
    <li><a href="#">nt</a></li>

  </ul>
  -->

  <!--
  <ul class="nav navbar-nav navbar-right">
    <li><a href="/logout"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
    <li><a href="/login"><span class="glyphicon glyphicon-log-in"></span> Login</a></li>
  </ul>
  -->

</div>

</div>
</nav>

<div class="container">
<div class="row">
<div class="col-sm-12">

<h3><?php print($settings['coinname']) ?> Miners</h3>

<p>
If you are a pool operator, please consider contributing your coinbase tag here: <a href="https://github.com/cryptapus/php-coinbase-miners" target="_blank">php-coinbase-miners</a>
</p>

<p>
Raw node information: <a href=".?action=getblockchaininfo">getblockchaininfo</a> <a href=".?action=getmempoolinfo">getmempoolinfo</a> <a href=".?action=getmininginfo">getmininginfo</a>
</p>

<p>
<?php
switch ($_GET["action"]) {
    case "getblockchaininfo":
        $coin = new Bitcoin($settings['rpcusername'],$settings['rpcpassword'],
            $settings['rpchost'],$settings['rpcport']);
        $info = $coin->getblockchaininfo();
        print("<pre>");
        print_r($info);
        print("</pre>");
    case "getmempoolinfo":
        $coin = new Bitcoin($settings['rpcusername'],$settings['rpcpassword'],
            $settings['rpchost'],$settings['rpcport']);
        $info = $coin->getmempoolinfo();
        print("<pre>");
        print_r($info);
        print("</pre>");
    case "getmininginfo":
        $coin = new Bitcoin($settings['rpcusername'],$settings['rpcpassword'],
            $settings['rpchost'],$settings['rpcport']);
        $info = $coin->getmininginfo();
        print("<pre>");
        print_r($info);
        print("</pre>");
    default:
        blocklist($settings);
}
?>
</p>

<hr/>

</div>
</div>
</div>

<footer class="footer">
  <div class="container">
    <span class="text-muted">Please see <a href="https://github.com/cryptapus/php-coinbase-miners" target="_blank">php-coinbase-miners</a> to add your pool.</span>
  </div>
</footer>

</body>
</html>
