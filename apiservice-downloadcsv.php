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

$cond='';
if($stateid>0) $cond.= " AND state_id='".$stateid."'";
if($stateid>0 && $cityid>0) $cond.= " AND city_id='".$cityid."'";
if($precint>0){ $cond.= " AND precinct='".$precint."'"; }
if($precsub>0){ $cond.= " AND precsub='".$precsub."'"; }
if($street!=''){ $cond.= " AND streetname='".$street."'"; }
if($fromage>0 && $toage>0){ $fromaged = 2019-$fromage;	$toaged = 2019-$toage;
if($fromage==101) $cond.= " AND b.birthdate<".$fromaged; else $cond.= " AND (birthdate>".$toaged." AND birthdate<".$fromaged.")"; }
if($gender!=''){ $cond.= " AND gender='".$gender."'"; }

$fetchdata = $pollObj->SelectQuery("SELECT state_code,city_name,precinct,precsub,streetname,gender,birthdate,voter_status FROM polling_info WHERE SOS_VoterID!=''".$cond." ORDER BY poll_id LIMIT 0,500");

$results = array();
if(!empty($fetchdata))
{
$results['Response'] = '201';
$results['Data'] = $fetchdata;
}
else
{
$results['Response'] = '500';
}
echo json_encode($results);
}
$dbObj->close();
?>