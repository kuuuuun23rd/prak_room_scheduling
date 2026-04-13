<?php
//$info = $_REQUEST['info'];
$cb = $_REQUEST['cb'];
$selected_room = $_REQUEST['selected_room'] ?? null;
//$events_today  = $_REQUEST['events_today']  ?? null;
//$events_week   = $_REQUEST['events_week']   ?? null;
$availability  = $_REQUEST['availability']  ?? 'unknown';
$go_back       = $_REQUEST['go_back']       ?? null;

$payload = [];

if ($selected_room !== null){
    $payload['selected_room'] = $selected_room;
    $payload['availability']  = $availability;
}


if ($go_back !== null) {
    $payload['go_back']      = true;
    //$payload['events_today'] = json_decode($events_today, true);
    //$payload['events_week']  = json_decode($events_week,  true);
    $payload['availability'] = $availability;
}

// Send callback to CPEE
$opts = array(
    'http' => array(
        'method'  => 'PUT',
        'header'  => "Content-type: application/json\r\n",
        'content' => json_encode($payload)
    )
);

$context = stream_context_create($opts);
file_get_contents($cb, false, $context);

exit;
?>
