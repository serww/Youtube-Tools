<?php

/**
 * @author Mixlion
 * @copyright Mixlion 09.01.2012
 * @version 1.0 beta
 * @link http://mixlion.ru
 * @desc Youtube Tools - Get information and firect links to youtube video
 */

class Youtube_Tools{

    /**
     * @var array $info - Video data
     */
    public $info;

    /**
     * @var string $id - Video id
     */
    public $id;

    /**
     * @var array $links - Links array
     */
    private $links = array();

    /**
     * @var string $user_agent - useragent for getting data
     */
    private $user_agent = 'Youtube Tools v.1';

    /**
     * @var array $proxy_list - List of the proxy servers
     */
    private $proxy_list = array(
        '41.75.201.201:80',
        '84.237.33.3:3128',
        '41.75.201.200:80',
        '200.37.63.11:3128',
        '41.75.201.198:80'
    );

    /**
     * @var array $formats - Formats of youtube video
     */
    private $formats = array(
            '17'=>'3gp',
            '5'=>'flv',
            '34'=>'flv',
            '35'=>'flv',
            '18'=>'mp4',
            '22'=>'mp4',
            '37'=>'mp4',
            '38'=>'mp4',
            '43'=>'webm',
            '44'=>'webm',
            '45'=>'webm'
        );

    /**
     * Method for processing getting information about video
     * @param bool $proxy
     * @param int $i
     * @return array|null
     */
    public function get_video_info($proxy = false, $i = 0){
        if(empty($this->id)) die('Enter video id');
        # Get video data
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://www.youtube.com/get_video_info?video_id='. $this->id);
        # Use proxy
        if($proxy){
            $proxy = $this->proxy_list[($i-1)];
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_PROXY, $proxy);
        }
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt ($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec ($ch);
        curl_close ($ch);

        # Parsing data
        parse_str($data, $info);

        # Check the returned status and, if necessary, use a proxy
        # 4 - number of attempts to obtain data
        if($i<4){
            if(@$info['status'] != 'ok'){
                $this->info =  $this->get_video_info(true, ++$i);
            } else $this->info = $info;
        } else exit('Video not available');
    }

    /**
     * Method for getting direct links to video
     * @return array
     */
    public function get_links(){
        $urls = ','.urldecode($this->info['url_encoded_fmt_stream_map']);
        $links_map = explode(',url=', $urls);
        unset($links_map[0]);
        foreach($links_map as $link){
            # Get number type of video
            preg_match('|\&itag\=([0-9]+)|', $link, $numb);
            # Get information of type of video
            preg_match('|'.$numb[1].'/([0-9]{2,4}x[0-9]{2,4})|', $this->info['fmt_list'], $format);
            # Link for video
            $link = preg_replace('|&itag='. $numb[1].'$|U', '', $link);
            # Create array of information of video
            $this->links[$this->formats[$numb[1]] .'-'. $format[1]] = array($this->formats[$numb[1]], $format[1], $link);
        }
        return $this->links;
    }

    /**
     * Method to save video to local path
     * @param $video - Video type
     * @param $path - Dir to save video
     * @param null|string $name - Name of video (without extension)
     */
    public function save($video, $path, $name = null){
        error_reporting(E_ALL);
        if(empty($this->links)) $this->get_links();
        if(!isset($this->links[$video])) die('Video `'. $video .'` not found');

        # Define name of video
        $name = empty($name) ? $this->info['title'] : $name;

        if($path[mb_strlen($path, 'utf-8')-1] != '/') $path .= '/';
        $url = trim($this->links[$video][2]) . '&title='. urlencode($name);
        $ch = curl_init($url);
        # Handle for copy video
        $fo = fopen($path . $name . '.' . $this->links[$video][0], 'w');
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FILE, $fo);
        curl_exec($ch);
        curl_close($ch);
        fclose($fo);
    }

    /**
     * Method for getting data about video
     * @return array
     */
    public function get_data(){
        $entry = simplexml_load_file('http://gdata.youtube.com/feeds/mobile/videos/' . $this->id);
        $media = $entry->children('http://search.yahoo.com/mrss/');
        $entry->registerXPathNamespace('feed', 'http://www.w3.org/2005/Atom');
        $related = $entry->xpath("feed:link[@rel='http://gdata.youtube.com/schemas/2007#video.related']");
        $related = (string)$related[0]['href'];
        $data = array(
            'keywords' => $this->info ['keywords'],
            'title' => $this->info ['title'],
            'description' => (string)$media->group->description,
            'category' => (string)$media->group->category,
            'duration' => $this->info ['length_seconds'],
            'views' => $this->info ['view_count'],
            'rate' => $this->info ['avg_rating'],
            'thumbnail' => array(
                'big' => $this->info ['iurlmaxres'],
                'small' => $this->info ['iurlsd'],
                'default' => $this->info ['thumbnail_url']
            ),
            # Link for get related videos
            'related' => $related
        );
        return $data;
    }

    /**
     * Method for search video
     * @param string $query - Query string
     * @param string $order - Type of order video (relevance, published, viewCount, rating)
     * @param int $start - Number of first element
     * @param int $count - Count of return results
     * @param array $need - Needed fields to return, may be id, title, description, author, thumbnails, keywords, player
     * @return array
     */
    public function search($query, $need = array('id', 'title', 'description',  'author', 'thumbnails', 'keywords', 'player'), $order = 'published', $start = 1, $count = 10) {
        # Orders type
        $allowOrder = array('relevance', 'published', 'viewCount', 'rating');
        if(!in_array($order, $allowOrder)) exit('Wrong type of order');

        # Make url
        $url = 'http://gdata.youtube.com/feeds/api/videos?vq='. urlencode($query) .'&orderby='. $order .'&start-index='.
            intval($start) .'&format=1&max-results='. intval($count);

        # Get data
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt ($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec ($ch);
        curl_close ($ch);
        $data = simplexml_load_string($data);

        # Total results
        $return = array('count' => (int)$data->children('http://a9.com/-/spec/opensearchrss/1.0/')->totalResults);

        # Parsing data
        if(sizeof($data->entry)>0){
            foreach($data->entry as $entry){

                $now = array();
                if(in_array('id', $need))
                    $now += array('id' => basename($entry->id));
                if(in_array('title', $need))
                    $now += array('title' => (string)$entry->title);
                if(in_array('description', $need))
                    $now += array('description' => (string)$entry->content);
                if(in_array('author', $need))
                    $now += array('author' => (string)$entry->author->name);
                if(in_array('thumbnails', $need) || in_array('keywords', $need) || in_array('player', $need)){
                    $media = $entry->children('http://search.yahoo.com/mrss/');
                }
                if(in_array('keywords', $need))
                    $now += array('keywords' => (string)$media->group->keywords);
                if(in_array('player', $need))
                    $now += array('player' => (string)$media->group->player->attributes()->url);
                if(in_array('thumbnails', $need))
                    $now += array('thumbnails' => array(
                        'default' => (string)$media->group->thumbnail[0]->attributes()->url,
                        1 => (string)$media->group->thumbnail[1]->attributes()->url,
                        2 => (string)$media->group->thumbnail[2]->attributes()->url,
                        3 => (string)$media->group->thumbnail[3]->attributes()->url
                    ));
                $return[] = $now;
            }
            return $return;
        }  else return array();
    }
}