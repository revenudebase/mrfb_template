<?php

/**
 * Drag and drop file upload input
 *
 * @uses $vars['class'] CSS classes to apply to the dropzone
 * @uses $vars['name'] Name of the input that you can use in your action. Defaults to file_guids
 * @uses $vars['value'] Were any files uploaded using this dropzone previously? Will the files be overwritten?
 * @uses $vars['multiple'] Allow multiple file uploads
 * @uses $vars['max'] Maximum number of files to handle, defaults to 10
 * @uses $vars['accept'] File types to accept
 * @uses $vars['action'] Alternative elgg action to handle temporary file uploads. Defaults to 'action/dropzone/upload'
 * @uses $vars['container_guid'] GUID of the container entity to which new files should be uploaded
 * @uses $vars['subtype'] Subtype of the file to be created
 */

$uid = substr(md5(microtime() . rand()), 0, 10);
$options['id'] = "dropzone-$uid";
$fallback_input_id = "dropzone-fallback-$uid";
$vars['id'] = $options['data-fallback-id'] = $fallback_input_id;

// Add dropzone class for JS initialization
if (isset($vars['class'])) {
	$options['class'] = "elgg-input-dropzone {$vars['class']}";
} else {
	$options['class'] = "elgg-input-dropzone";
}

$vars['class'] = 'hidden';

if (!isset($vars['name'])) {
	$vars['name'] = 'file_guids';
}

// Allow multiple file uploads
$multiple = false;
if (isset($vars['multiple'])) {
	$multiple = $vars['multiple'];
}
$options['data-upload-multiple'] = $multiple;
if ($multiple) {
	$vars['name'] = $vars['name'] . '[]';
}
$options['data-name'] = $vars['name'];

// Set input type
$vars['type'] = 'file';

// File previously uploaded
if (isset($vars['value']) && $vars['value']) {
	$options['data-fileexists'] = true;
}

if (isset($vars['max'])) {
	$options['data-max-files'] = $vars['max'];
	unset($vars['max']);
}

if (isset($vars['accept'])) {
	$options['data-accepted-files'] = $vars['accept'];
}

if (isset($vars['action'])) {
	$options['data-url'] = elgg_add_action_tokens_to_url(elgg_normalize_url($vars['action']));
	unset($vars['action']);
}

if (isset($vars['container_guid'])) {
	$options['data-container-guid'] = $vars['container_guid'];
}

if (isset($vars['subtype'])) {
	$options['data-subtype'] = $vars['subtype'];
}

$options['data-clickable'] = '.elgg-dropzone-fallback-control,.elgg-dropzone-instructions';

$language = array(
	'data-dict-default-message' => elgg_echo('dropzone:default_message'),
	'data-dict-fallback-message' => elgg_echo('dropzone:fallback_message'),
	'data-dict-fallback-text' => elgg_echo('dropzone:fallback_text'),
	'data-dict-invalid-filetype' => elgg_echo('dropzone:invalid_filetype'),
	'data-dict-file-toobig' => elgg_echo('dropzone:file_too_big'),
	'data-dict-response-error' => elgg_echo('dropzone:response_error'),
	'data-dict-cancel-upload' => elgg_echo('dropzone:cancel_upload'),
	'data-dict-cancel-upload-confirmation' => elgg_echo('dropzone:cancel_upload_confirmation'),
	'data-dict-remove-file' => elgg_echo('dropzone:remove_file'),
	'data-dict-max-files-exceeded' => elgg_echo('dropzone:max_files_exceeded')
);

$options = array_merge($language, $options);

$dropzone_attributes = elgg_format_attributes($options);

// Add a hidden field to use in the action hook to unserialize the values
echo elgg_view('input/hidden', array(
	'name' => 'elgg_dropzone_fields[]',
	'value' => $vars['name']
));


$file_input = elgg_view('input/file', $vars);
$template = <<<HTML
<div class="elgg-dropzone-preview pam">
	<div class="float elgg-dropzone-thumbnail prm">
		<img data-dz-thumbnail />
	</div>
	<div class="float elgg-dropzone-filename">
		<span class="elgg-dropzone-success-icon fi-check"></span>
		<span class="elgg-dropzone-error-icon fi-x"></span>
		<span data-dz-name></span>
	</div>
	<div class="float-alt">
		<a class="elgg-dropzone-remove-icon elgg-icon elgg-icon-delete-alt" title="{$remove_file_title}" data-dz-remove></a>
	</div>
	<div class="float-alt elgg-dropzone-size prl" data-dz-size></div>
	<div class="elgg-dropzone-messages">
		<div data-dz-successmessage></div>
		<div data-dz-errormessage></div>
	</div>
	<div class="elgg-dropzone-progress"><div class="elgg-dropzone-upload" data-dz-uploadprogress></div></div>
</div>
HTML;

echo <<<HTML
<div class="elgg-dropzone">
	<div $dropzone_attributes>
		<span class="elgg-dropzone-instructions dz-default dz-message phm pbm link">
			<span class="fi-upload-cloud"></span>{$language['data-dict-default-message']}
		</span>
	</div>
	<div data-template>$template</div>
</div>
{$file_input}
HTML;

