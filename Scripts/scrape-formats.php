<?php

/*
 * Re-generates the `formats.ini` file.
 */

const LANGUAGES_PAGE = 'https://pastebin.com/languages';

if (($html = file_get_contents(LANGUAGES_PAGE)) === false) {
	error_log('Failed to retrieve languages page');
	exit(1);
}

if (!preg_match_all('/<div class="lang_div">\d+\. <a href="\/archive\/(.+?)">(.+?)<\/a>/', $html, $matches)) {
	error_log('Failed to parse any formats');
	exit(1);
}

// format and sort the results
$formats = array_combine($matches[1], $matches[2]);
if (empty($formats)) {
	error_log('Failed to parse any formats');
	exit(1);
}
ksort($formats);

// attempt to create a new formats.ini file
if (($handle = fopen('formats.ini', 'w')) === false) {
	error_log('Unable to open formats.ini for writing');
	exit(1);
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
