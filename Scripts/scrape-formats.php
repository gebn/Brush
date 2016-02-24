<?php

/*
 * Re-generates the `formats.ini` file.
 */

const API_PAGE = 'http://pastebin.com/api';

// download the API page
$html = file_get_contents(API_PAGE);

// extract a substring containing the code/name pairs
if (preg_match('/language in question\.(.+)/', $html, $matches) !== 1) {
	error_log('Failed to identify formats section within page');
	exit(1);
}

// drill further into this section, extracting individual pairs
if (!preg_match_all('/(?:&nbsp;){4}(.+?) = (.+?)<br \/>/', $matches[1], $matches)) {
	error_log('Failed to parse any formats');
	exit(2);
}

// format and sort the results
$formats = array_combine($matches[1], $matches[2]);
if (empty($formats)) {
	error_log('Failed to parse any formats');
	exit(3);
}
ksort($formats);

// attempt to create a new formats.ini file
if (($handle = fopen('formats.ini', 'w')) === false) {
	error_log('Unable to open formats.ini for writing');
	exit(4);
}

// write preamble
fwrite($handle, "; This file lists formats in code => name format.\n");
$example_code = key($formats);
$example_name = $formats[$example_code];
fprintf($handle, "; e.g. `%s = \"%s\"` means Pastebin supports a format called %s, for which the code is %s.\n\n",
	$example_code, $example_name, $example_name, $example_code);

// write the formats
foreach ($formats as $code => $name) {
	fprintf($handle, "%s = \"%s\"\n", $code, $name);
}
printf("Wrote %d format(s)\n", count($formats));

// finish
fclose($handle);