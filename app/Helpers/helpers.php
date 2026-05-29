<?php 

function site(string $key)
{
	return cache('site')->$key;
}
function sitee(string $key)
{
	return cache('sitee')->$key;
}

function active(string $route, $group = false, string $res = 'active'): String
{
	$active = $group ? request()->is($route) || request()->is($route.'/*') : request()->is($route);
	return $active ? $res : '';
}

function image(string $image)
{
	return asset('storage/images/'.$image);
}

function localDate(string $date): String
{
	return date('d M Y', strtotime($date));
}

function timeDate(string $date): String
{
	return date('d M Y H:i A', strtotime($date));
}

 ?>