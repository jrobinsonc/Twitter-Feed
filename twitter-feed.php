<?php

/**
 * Twitter_Feed
 * 
 * @author      Jose Robinson <jr@joserobinson.com>
 * @license     MIT License
 * @link        https://github.com/jrobinsonc/Twitter-Feed
 * @version     0.1
 */
class Twitter_Feed {
    
    private $config = array();
    
    public function __construct($config) 
    {
        $this->config['screen_name'] = '';
        $this->config['cache_time'] = 300; // 5 minutes
        $this->config['count'] = 5;
        
        $this->config = array_merge($this->config, $config);
        
        $this->config['cache_file'] = dirname(__FILE__) . "/cache/{$this->config['screen_name']}.cache";
        
        if (!is_dir(dirname($this->config['cache_file'])))
            mkdir(dirname($this->config['cache_file']));
    }
    
    public function download_feed()
    {
        $json_string = @file_get_contents(sprintf('https://api.twitter.com/1/statuses/user_timeline.json?include_entities=true&include_rts=true&screen_name=%s&count=%u', $this->config['screen_name'], $this->config['count']));
        
        if ($json_string === FALSE)
            return FALSE;
        
        $data = json_decode($json_string);
    
        if ($data === FALSE)
            return FALSE;
            
        if (file_put_contents($this->config['cache_file'], time() . '|' . $json_string) === FALSE)
            return FALSE;
        
        return $data;
    }
    
    public function get_feed_array() 
    {
        if (!is_file($this->config['cache_file']))
        {
            $data = $this->download_feed();
            
            if ($data === FALSE)
                return array();
            else
                return $data;
        }
        
        $cache_content = file_get_contents($this->config['cache_file']);
        
        preg_match('#^([0-9]+)\|(.+)$#', $cache_content, $match);
        
        //---------------------------------------------------
        if (($match[1] + $this->config['cache_time']) < time())
        {
            $data = $this->download_feed();
            
            if ($data === FALSE)
                $data = json_decode($match[2]);
        }
        else
        {
            $data = json_decode($match[2]);
        }
        
        //---------------------------------------------------
        return $data;
    }
}
