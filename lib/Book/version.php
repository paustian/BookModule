<?php
// pnversion.php,v 1.7 2007/03/17 02:02:53 paustian Exp

// The following information is used by the Modules module 
// for display and upgrade purposes
// the version string must not exceed 10 characters!
$modversion['name'] = 'Book';
$modversion['version'] = '2.0';
$modversion['description'] = 'A module for displying a large structured document';

// The following in formation is used by the credits module
// to display the correct credits
$modversion['credits'] = 'docs/credits.txt';
$modversion['help'] = 'docs/help.txt';
$modversion['changelog'] = 'docs/changelog.txt';
$modversion['license'] = 'docs/license.txt';
$modversion['official'] = 1;
$modversion['author'] = 'Timothy Paustian';
$modversion['contact'] = 'http://http://www.bact.wisc.edu/faculty/paustian/';

// The following information tells the PostNuke core that this
// module has an admin option.
$modversion['admin'] = 1;

// This one adds the info to the DB, so that users can click on the 
// headings in the permission module
$modversion['securityschema'] = array('Book::Chapter' => 'Book id (int)::Chapter id (int)');
?>