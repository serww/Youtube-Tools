<?php
require 'ygl.php';
$manager = new Youtube_Tools();

# Get information about video
$manager->id = 'SqhWFk5wts4';
$manager->get_video_info();
echo '<pre>';
print_r($manager->info);
echo '</pre>';

# Get video data
$manager->id = 'SqhWFk5wts4';
echo '<pre>';
print_r($manager->get_data());
echo '</pre>';

# Get video links
$manager->id = 'SqhWFk5wts4';
echo '<pre>';
print_r($manager->get_links());
echo '</pre>';

# Save video
$manager->id = 'SqhWFk5wts4';
$path = realpath(dirname(__FILE__));
$manager->save('flv-640x360', $path);

# Video search
echo '<pre>';
print_r($manager->search('Ukraine'));
echo '</pre>';

?>