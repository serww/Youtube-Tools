<?php
require 'yt.php';

# Get information about video
YT::init('Sd3diED-M0c');
echo '<pre>';
print_r(YT::get_info());

# Get video data
YT::init('Sd3diED-M0c');
echo '<pre>';
print_r(YT::get_data());
echo '</pre>';

# Get video links
YT::init('Sd3diED-M0c');
echo '<pre>';
print_r(YT::get_links());
echo '</pre>';

# Get information about video
YT::init('SqhWFk5wts4');
echo '<pre>';
print_r(YT::get_info());
echo '</pre>';

# Get video data
YT::init('SqhWFk5wts4');
echo '<pre>';
print_r(YT::get_data());
echo '</pre>';

# Get video links
YT::init('SqhWFk5wts4');
echo '<pre>';
print_r(YT::get_links());
echo '</pre>';

# Save video
YT::init('SqhWFk5wts4');
YT::save('mp4-640x360', '.', 'Welcome to Ukraine');

# Video search
echo '<pre>';
print_r(YT::search('Ukraine'));
echo '</pre>';

# Get video and output to browser
YT::init('SqhWFk5wts4');
YT::get('flv-640x360');