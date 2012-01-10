<?php
require 'yt.php';
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

# Get video links  (must be used $manager->get_video_info())
$manager->id = 'SqhWFk5wts4';
echo '<pre>';
print_r($manager->get_links());
echo '</pre>';

# Save video (must be used $manager->get_video_info() or $manager->get_links())
$manager->id = 'SqhWFk5wts4';
$manager->get_video_info();
$path = realpath(dirname(__FILE__));
$manager->save('mp4-640x360', $path);

# Video search
echo '<pre>';
print_r($manager->search('Ukraine'));
echo '</pre>';
?>