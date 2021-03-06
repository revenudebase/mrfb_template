<?php
/**
 * Action for adding and editing comments
 *
 * @package Elgg.Core
 * @subpackage Comments
 */

$entity_guid = (int) get_input('entity_guid', 0, false);
$comment_guid = (int) get_input('comment_guid', 0, false);
$comment_text = get_input('generic_comment');
$is_edit_page = (bool) get_input('is_edit_page', false, false);

if (empty($comment_text)) {
	register_error(elgg_echo('generic_comment:blank'));
	forward(REFERER);
}

if ($comment_guid) {
	// Edit an existing comment
	$comment = get_entity($comment_guid);

	if (!elgg_instanceof($comment, 'object', 'comment')) {
		register_error(elgg_echo('generic_comment:notfound'));
		forward(REFERER);
	}
	if (!$comment->canEdit()) {
		register_error(elgg_echo('actionunauthorized'));
		forward(REFERER);
	}

	$comment->description = $comment_text;
	if ($comment->save()) {
		system_message(elgg_echo('generic_comment:updated'));
	} else {
		register_error(elgg_echo('generic_comment:failure'));
	}
} else {
	// Create a new comment on the target entity
	$entity = get_entity($entity_guid);
	if (!$entity) {
		register_error(elgg_echo('generic_comment:notfound'));
		forward(REFERER);
	}

	$user = elgg_get_logged_in_user_entity();

	$comment = new ElggComment();
	$comment->description = $comment_text;
	$comment->owner_guid = $user->getGUID();
	$comment->container_guid = $entity->getGUID();
	$comment->access_id = $entity->access_id;
	$guid = $comment->save();

	if (!$guid) {
		register_error(elgg_echo('generic_comment:failure'));
		forward(REFERER);
	}

	// Notify if poster wasn't owner
	if ($entity->owner_guid != $user->guid) {
		notify_user($entity->owner_guid,
			$user->guid,
			elgg_echo('generic_comment:email:subject', array(elgg_get_site_entity()->name)),
			elgg_echo('generic_comment:email:body', array(
				$user->getURL(),
				$user->name,
				$entity->getURL(),
				$comment_text,
				$comment->getURL()
			)),
			array(
				'object' => $comment,
				'action' => 'create',
			)
		);
	}

	// Add to river
	elgg_create_river_item(array(
		'view' => 'river/object/comment/create',
		'action_type' => 'comment',
		'subject_guid' => $user->guid,
		'object_guid' => $guid,
		'target_guid' => $entity_guid,
	));

	elgg_nodejs_broadcast(array(
		'type'=> 'new_comment',
		'message' => get_comment_river($comment)
	));

	if (elgg_is_xhr()) {
		echo json_encode(get_comment_river($comment));
	} else {
		system_message(elgg_echo('generic_comment:posted'));
	}
}

if ($is_edit_page) {
	forward($comment->getURL());
}

forward(REFERER);
