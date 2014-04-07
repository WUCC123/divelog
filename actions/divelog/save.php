<?php
/**
* Elgg divelog save action
*
* @package Divelog
*/

elgg_load_library('divelog');

// get the form inputs
$dive_site = get_input('dive_site');
$dive_date = get_input('dive_date');
$dive_start_time = get_input('dive_start_time');
$dive_depth = get_input('dive_depth');
$dive_duration = get_input('dive_duration');
$dive_buddies = get_input('dive_buddies');
$dive_pictures = get_input('dive_pictures');
$dive_briefing = get_input('dive_briefing');
$dive_debriefing = get_input('dive_debriefing');

$tagarray = string_to_tag_array(get_input('tags'));

$access_id = get_input('access_id');
$guid = get_input('guid');
$share = get_input('share');
$container_guid = get_input('container_guid', elgg_get_logged_in_user_guid());

elgg_make_sticky_form('divelog');

$new = false;
if ($guid == 0) {
	$divelog = new ElggObject;
	$divelog->subtype = "divelog";
	$divelog->container_guid = (int)get_input('container_guid', $_SESSION['user']->getGUID());
	$new = true;
} else {
	$divelog = get_entity($guid);
	if (!$divelog->canEdit()) {
		system_message(elgg_echo('divelog:save:failed'));
		forward(REFERRER);
	}
}

$divelog->dive_site = $dive_site;
$divelog->dive_date = $dive_date;
$divelog->dive_start_time = $dive_start_time;
$divelog->dive_depth = $dive_depth;
$divelog->dive_duration = $dive_duration;
$divelog->dive_buddies = $dive_buddies;
$divelog->dive_debriefing = $dive_debriefing;
$divelog->dive_media = $dive_media;
$divelog->dive_briefing = $dive_briefing;
$divelog->dive_suggestor = elgg_get_logged_in_user_guid();
$divelog->tags = $tagarray;

$divelog->units = get_user_units();

$divelog->title = divelog_prettyprint($divelog, "title");
$divelog->description = divelog_prettyprint($divelog, "description");

$divelog->access_id = $access_id;

if ($divelog->save()) {

	elgg_clear_sticky_form('divelog');

	// @todo
	if (is_array($shares) && sizeof($shares) > 0) {
		foreach($shares as $share) {
			$share = (int) $share;
			add_entity_relationship($divelog->getGUID(), 'share', $share);
		}
	}
	// save reference in buddy relationships
	if($dive_buddies != '') {
		$dive_buddies = explode(',', $buddy_list);
		foreach($dive_buddies as $diver) {
			$elgg_diver = get_user_by_username($diver);
			if(    $elgg_diver
				&& ($elgg_diver instanceof ElggUser)
				&& !check_entity_relationship($elgg_diver->getGUID(), "divelog_buddy", $divelog->getGUID())
			  )
				add_entity_relationship($elgg_diver->getGUID(), "divelog_buddy", $divelog->getGUID());
		}
	}

	system_message(elgg_echo('divelog:save:success'));

	//add to river only if new
	if ($new) {
		add_to_river('river/object/divelog/create','create', elgg_get_logged_in_user_guid(), $divelog->getGUID());
		//elgg_trigger_event('publish','object',$divelog);
	}

	forward($divelog->getURL());
} else {
	register_error(elgg_echo('divelog:save:failed'));
	forward("divelog");
}
