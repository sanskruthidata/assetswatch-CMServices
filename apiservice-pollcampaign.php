<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once('config.php');
ini_set('max_execution_time', 0);
ini_set('memory_limit', '-1');
$postdata = json_decode(file_get_contents("php://input"),true);

$stateid = $cityid = $precint = $precsub = $fromage = $toage = $street = $gender = $etype = '';

/*$postdata['action'] = 'voters';
$postdata['stateid'] = '44';
$postdata['cityid'] = '35896';
$postdata['precint'] = '2601';
*/
if(!empty($postdata))
{
if($postdata['stateid']>0) $stateid = $pollObj->cleanstr($postdata['stateid']);
if($postdata['cityid']>0) $cityid = $pollObj->cleanstr($postdata['cityid']);
if($postdata['precint']>0) $precint = $pollObj->cleanstr($postdata['precint']);
if($postdata['precsub']>0) $precsub = $pollObj->cleanstr($postdata['precsub']);
if($postdata['fromage']>0) $fromage = $pollObj->cleanstr($postdata['fromage']);
if($postdata['toage']>0) $toage = $pollObj->cleanstr($postdata['toage']);
if($postdata['street']!='') $street = $pollObj->cleanstr($postdata['street']);
if($postdata['gender']!='') $gender = $pollObj->cleanstr($postdata['gender']);
if($postdata['etype']!='') $etype = $pollObj->cleanstr($postdata['etype']);

$whcond = $st_cond = $cty_cond = $precint_cond = $precsub_cond = ""; 
if($stateid>0){ $whcond.= " AND state_id =".$stateid; $st_cond = " AND state_id = ".$stateid; }
if($cityid>0){ $whcond.= " AND city_id = ".$cityid; $cty_cond = " AND city_id = ".$cityid; }
if($precint>0){ $whcond.= " AND precinct = ".$precint; $precint_cond = " AND precinct = ".$precint; }
if($precsub>0){ $whcond.= " AND precsub = ".$precsub; $precsub_cond = " AND precsub = ".$precsub; }
if($etype!=''){ $whcond.= " AND election_id IN (".$etype.")"; }
if($street!=''){ $whcond.= " AND streetname = '".$street."'"; }
if($fromage>0 && $toage>0){ $fromaged = 2019-$fromage;	$toaged = 2019-$toage;
if($fromage==101) $whcond.= " AND birthdate<".$fromaged; else $whcond.= " AND (birthdate>".$toaged." AND birthdate<".$fromaged.")"; }
if($gender!=''){ $whcond.= " AND gender ='".$gender."'"; }

$states_list = $pollObj->SelectQuery("SELECT state_id,state_code,state_name FROM states_info WHERE state_name!='' ORDER BY state_name");
if($stateid>0)
{
$cities_list = $pollObj->SelectQuery("SELECT city_id,city_name,state_code FROM polling_info WHERE SOS_VoterID!=''".$st_cond." GROUP BY city_name ORDER BY city_name");
}
$precinct_list = $pollObj->SelectQuery("SELECT DISTINCT precinct FROM polling_info WHERE SOS_VoterID!=''".$cty_cond." ORDER BY precinct");
$precsub_list = $pollObj->SelectQuery("SELECT DISTINCT precsub FROM polling_info WHERE precsub>0".$precint_cond." ORDER BY precsub");
if($cityid>0)
{
$street_list = $pollObj->SelectQuery("SELECT city_id,streetname FROM polling_info WHERE streetname!=''".$st_cond."".$cty_cond."".$precint_cond."".$precsub_cond." GROUP BY streetname ORDER BY streetname");
}
$election_list = $pollObj->SelectQuery("SELECT election_id,election_code FROM election_info WHERE election_code!='' ORDER BY election_code");
$pollcnt = $pollObj->SelectQuery("SELECT a.voter_status,count(a.poll_id) as cnt FROM polling_info a WHERE a.SOS_VoterID!=''".$whcond." GROUP BY a.voter_status");

$latlng = array();
if($stateid>0)
{
$chkstate = $pollObj->SelectQuery("SELECT state_name,lat_long FROM states_info WHERE state_id='".$stateid."'"); $lat_lng = $chkstate[0]['lat_long'];
$splitlat = explode('###',$lat_lng);	$lat = $splitlat[0]; $lng = $splitlat[1];
$latlng['lat'] = $lat; $latlng['lng'] = $lng;
}
if($stateid>0 && $cityid>0)
{
$chkcity = $pollObj->SelectQuery("SELECT state_name,city_name,lat_long FROM cities_info WHERE city_id='".$cityid."'"); $lat_lng = $chkcity[0]['lat_long'];
$splitlat = explode('###',$lat_lng);	$lat = $splitlat[0]; $lng = $splitlat[1];
$latlng['lat'] = $lat; $latlng['lng'] = $lng;
}
if($street!='')
{
$chkstreets = $pollObj->SelectQuery("SELECT streetname,lat,lng FROM streets_info WHERE streetname='".$street."' LIMIT 0,1");
$lat = $chkstreets[0]['lat']; $lng = $chkstreets[0]['lng'];
$latlng['lat'] = $lat; $latlng['lng'] = $lng;
}

if(!empty($street_list) && $cityid>0 && $precint>0 && $street=='')
{
$streetarry1 = $streetarry2 = array();
foreach($street_list as $keys=>$vals)
{
$chkstreet = $pollObj->SelectQuery("SELECT lat,lng FROM streets_info WHERE streetname='".$vals['streetname']."' AND state_id='".$stateid."' AND city_id='".$cityid."' AND lat!='' LIMIT 0,1");
$pollcnts = $pollObj->SelectQuery("SELECT a.voter_status,count(a.poll_id) as cnt FROM polling_info a WHERE a.SOS_VoterID!=''".$whcond." AND streetname='".$vals['streetname']."' GROUP BY a.voter_status");
$stcasted = $stnoncasted =0;
if(!empty($pollcnts))
{
$voterss = array();$voterss['A'] = $voterss['S'] = 0;
foreach($pollcnts as $keyss=>$valss){$voterss[$valss['voter_status']] = $valss['cnt'];}
$stcasted = $voterss['A']; $stnoncasted = $voterss['S'];
}

if(!empty($chkstreet) && ($stcasted>0 || $stnoncasted>0))
{
$lat = number_format($chkstreet[0]['lat'],3).'0'; $lng = number_format($chkstreet[0]['lng'],3).'0';
$lat2 = number_format($chkstreet[0]['lat'],3).'1'; $lng2 = number_format($chkstreet[0]['lng'],3).'1';
$streetarry1[] = '{lat: '.$lat.', lng: '.$lng.'}, "'.$vals['streetname'].'", "'.$stcasted.'"';
$streetarry2[] = '{lat: '.$lat2.', lng: '.$lng2.'}, "'.$vals['streetname'].'", "'.$stnoncasted.'"';
}
}
}


if(!empty($states_list))$response['States'] = $states_list;
if(!empty($cities_list))$response['Cities'] = $cities_list;
if(!empty($street_list))$response['Streets'] = $street_list;
if(!empty($precinct_list))$response['Precint'] = $precinct_list;
if(!empty($precsub_list))$response['Precsub'] = $precsub_list;
if(!empty($election_list))$response['Election'] = $election_list;
if(!empty($pollcnt))$response['Polls'] = $pollcnt;
if(!empty($latlng))$response['latlng'] = $latlng;
if(!empty($streetarry1) && !empty($streetarry2))$response['streetarry1'] = $streetarry1;
if(!empty($streetarry1) && !empty($streetarry2))$response['streetarry2'] = $streetarry2;

if(!empty($response))
{
$result['Response'] = '201';
$result['Data'] = $response;
}
else
{
$result['Response'] = '500';
}
echo json_encode($result);
}
$dbObj->close();
?>