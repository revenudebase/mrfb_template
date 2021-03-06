<?php
/**
 * Elgg pageshell for the admin area
 *
 * @package Elgg
 * @subpackage Core
 *
 * @uses $vars['head']        Parameters for the <head> element
 * @uses $vars['body']        The main content of the page
 * @uses $vars['sysmessages'] A 2d array of various message registers, passed from system_messages()
 */

// render content before head so that JavaScript and CSS can be loaded. See #4032

$notices_html = '';
$notices = elgg_get_admin_notices();
if ($notices) {
	foreach ($notices as $notice) {
		$notices_html .= elgg_view_entity($notice);
	}

	$notices_html = "<div class=\"elgg-admin-notices\">$notices_html</div>";
}

$messages = elgg_view('page/elements/messages', array('object' => $vars['sysmessages']));
$messages .= $notices_html;

$content = $vars['body'];


/**
 * Ajax call
 */
if (elgg_is_xhr()) {
	$params['system_messages'] = '';
	$params['body'] = $messages . $content;

	mfrb_execute_js(elgg_view('mfrb_template/page/reinitialize_elgg'));
	$code = ''; // reset $code !

	$params['js_code'] = '';

	foreach (mfrb_execute_js() as $code) {
		$params['js_code'] .= $code;
	}

	// Set the content type
	header("Content-type: application/json; charset=UTF-8");

	echo json_encode($params);
	exit;
}

$header = elgg_view('admin/header', $vars);
$footer = elgg_view('admin/footer', $vars);

$body = <<<__BODY
<div class="elgg-page elgg-page-admin">
	<div class="elgg-inner">
		<div class="elgg-page-header">
			<div class="elgg-inner clearfix">
				$header
			</div>
		</div>
		<div class="elgg-page-messages">
			$messages
		</div>
		<div class="elgg-page-body">
			<div class="elgg-inner">
				$content
			</div>
		</div>
		<div class="elgg-page-footer">
			<div class="elgg-inner">
				$footer
			</div>
		</div>
	</div>
</div>
__BODY;

$body .= elgg_view('page/elements/foot');

$head = elgg_view('page/elements/head', $vars['head']);

echo elgg_view('page/elements/html', array(
	'head' => $head,
	'body' => $body
));
