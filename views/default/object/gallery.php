<?php
/**
 * Elgg divelog view
 *
 * @package Divelog
 */

elgg_load_library('divelog');

$divelog = elgg_extract('entity', $vars, FALSE);

if (!$divelog) {
	return;
}

get_divelog_galleries($divelog);

/* Get galleries through relationship "divelog_media"
 */
$options = array(
	'relationship_guid' => $divelog->guid,
	'relationship' => 'divelog_media',
	'inverse_relationship' => false, 
	'limit' => 0,
);

$galleries = array();
if($existing_rels = elgg_get_entities_from_relationship($options)) {
	foreach($existing_rels as $rel) {
		if($gallery = get_entity($rel->guid))
			$galleries[] = $gallery;
		/* else gallery not found, may be it was deleted, so we should remove relationship... */
	}
}

if ($galleries) {
	echo elgg_echo('divelog:gallery');
	
	foreach($galleries as $gallery) {
		// display nice stream
		//echo $gallery->title;
		echo elgg_view('object/hjalbum', array('entity' => $gallery, 'list_type' => 'river'));
	}
	
	//echo elgg_view_entity_list($galleries, array('full_view' => false));
} else {
	echo elgg_echo('divelog:nogallery');
}