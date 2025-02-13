<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('css_url'))
{
    function css_url($nom)
    {
        return base_url() . 'assets/css/' . $nom . '.css';
    }
}

if (!function_exists('js_url'))
{
    function js_url($nom)
    {
        return base_url() . 'assets/js/' . $nom . '.js';
    }
}

if (!function_exists('img_url'))
{
    function img_url($nom)
    {
        return base_url() . 'assets/img/' . $nom;
    }
}

if (!function_exists('font_url'))
{
    function font_url($nom)
    {
        return base_url() . 'assets/fonts/' . $nom;
    }
}
if (!function_exists('img'))
{
    function img($nom, $alt = '')
    {
        return '<img src="' . img_url($nom) . '" alt="' . $alt . '" />';
    }
}

if (!function_exists('upload_url'))
{
    function upload_url($nom)
    {
        return base_url() . 'uploads/' . $nom;
    }
}

if (!function_exists('is_selected'))
{
    function is_selected($option, $value)
    {
        if ($option == $value)
			echo "selected=\"selected\"";
    }
}