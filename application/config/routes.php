<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	http://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There area two reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router what URI segments to use if those provided
| in the URL cannot be matched to a valid route.
|
*/

$route['default_controller'] = "home";
$route['create/fullview/(:any)']='create/tldefault/view/$1';
$route['create/jsoncreate/(:any)']='create/tldefault/jsoncreate/$1';
$route['create/field/(:any)']='create/tldefault/field/$1';
$route['create/ajaxsubmit/(:any)']='create/tldefault/ajaxsubmit/$1';
$route['create/autosave/(:any)']='create/tldefault/autosave/$1';
$route['create/embed/(:any)']='create/tldefault/embed/$1';
$route['create/(:any)']='create/frame/display/$1';

$route['edit/fullview/(:any)']='edit/tldefault/view/$1';
$route['edit/checkin/(:any)']='edit/tldefault/checkin/$1';
$route['edit/ajaxsubmit/(:any)']='edit/tldefault/ajaxsubmit/$1';
$route['edit/fileupload/(:any)']='edit/tldefault/fileupload/$1';
$route['edit/autosave/(:any)']='edit/tldefault/autosave/$1';
$route['edit/clear_autosave/(:any)']='edit/tldefault/clear_autosave/$1';
$route['edit/row/(:any)']='edit/tldefault/row/$1';
$route['edit/jsonedit/(:any)']='edit/tldefault/jsonedit/$1';
$route['edit/field/(:any)']='edit/tldefault/field/$1';
$route['edit/(:any)']='edit/frame/fulldisplay/$1';

$route['list/(:any)']='list/tldefault/$1';

$route['delete/(:any)']='delete/tldefault/$1';
$route['404_override'] = '';


/* End of file routes.php */
/* Location: ./application/config/routes.php */