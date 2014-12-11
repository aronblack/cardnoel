<?php
error_reporting(E_ALL ^ E_NOTICE);
ini_set('error_reporting',E_ALL ^ E_NOTICE);  

function print_array($array, $str = '')
{
	echo '<pre class="debug">'.$str.' : '.count($array). 'results<br >';
	print_r($array);
	echo '</pre>'."\n";
}


function lw_get_youtube_user_video_feed($user_id, $incl_readmore = false)
{
	$feedURL = 'http://gdata.youtube.com/feeds/api/users/'.$user_id.'/uploads?max-results=50';
	
	//print_array($feedURL);
	
	if($sxml = simplexml_load_file($feedURL))
	{
		
		//print_array($sxml);
		
		foreach ($sxml->entry as $entry)
		{
			if ( ! empty($entry->id) )
			{
				$media = $entry->children('media', true);
				$watch = 'http://www.youtube.com/watch?v='.str_replace('http://gdata.youtube.com/feeds/api/videos/','',$entry->id ).'rel=0';
				$thumbnail = (string)$media->group->thumbnail[0]->attributes()->url;
				?>
				<!--h3><a href="<?php echo $watch; ?>" class="watchvideo" rel="shadowbox"><?php echo $media->group->title; ?></a></h3-->
				<div class="videothumb"><a href="<?php echo $watch; ?>" class="watchvideo" rel="prettyPhoto"><img src="<?php echo $thumbnail;?>" alt="<?php echo $media->group->title; ?>" /></a></div>
					<!--div class="videotitle">
						<p><?php echo $media->group->description; ?></p>
					</div-->
				<?php 
				
				break;
			}
		}
	
	}
	else{
		echo '<p>Video temporarly unavailable.</p>';
	}

	if($incl_readmore)
	{
		//echo '<span class="readmore"><a href="http://www.youtube.com/user/'.$user_id.'?feature=mhee" target="_blank">Visit Our YouTube Channel</a> &raquo;</span>';
	}
}

function parseVideoEntry($entry) {      
      $obj= new stdClass;
      
      // get author name and feed URL
      $obj->author = $entry->author->name;
      $obj->authorURL = $entry->author->uri;
      
      // get nodes in media: namespace for media information
      $media = $entry->children('http://search.yahoo.com/mrss/');
      $obj->title = $media->group->title;
      $obj->description = $media->group->description;
      
      // get video player URL
      $attrs = $media->group->player->attributes();
      $obj->watchURL = $attrs['url']; 
      
      // get video thumbnail
      $attrs = $media->group->thumbnail[0]->attributes();
      $obj->thumbnailURL = $attrs['url']; 
            
      // get <yt:duration> node for video length
      $yt = $media->children('http://gdata.youtube.com/schemas/2007');
      $attrs = $yt->duration->attributes();
      $obj->length = $attrs['seconds']; 
      
      // get <yt:stats> node for viewer statistics
      $yt = $entry->children('http://gdata.youtube.com/schemas/2007');
      $attrs = $yt->statistics->attributes();
      $obj->viewCount = $attrs['viewCount']; 
      
      // get <gd:rating> node for video ratings
      $gd = $entry->children('http://schemas.google.com/g/2005'); 
      if ($gd->rating) { 
        $attrs = $gd->rating->attributes();
        $obj->rating = $attrs['average']; 
      } else {
        $obj->rating = 0;         
      }
        
      // get <gd:comments> node for video comments
      $gd = $entry->children('http://schemas.google.com/g/2005');
      if ($gd->comments->feedLink) { 
        $attrs = $gd->comments->feedLink->attributes();
        $obj->commentsURL = $attrs['href']; 
        $obj->commentsCount = $attrs['countHint']; 
      }
      
      // get feed URL for video responses
      $entry->registerXPathNamespace('feed', 'http://www.w3.org/2005/Atom');
      $nodeset = $entry->xpath("feed:link[@rel='http://gdata.youtube.com/schemas/2007#video.responses']"); 
      if (count($nodeset) > 0) {
        $obj->responsesURL = $nodeset[0]['href'];      
      }
         
      // get feed URL for related videos
      $entry->registerXPathNamespace('feed', 'http://www.w3.org/2005/Atom');
      $nodeset = $entry->xpath("feed:link[@rel='http://gdata.youtube.com/schemas/2007#video.related']"); 
      if (count($nodeset) > 0) {
        $obj->relatedURL = $nodeset[0]['href'];      
      }
    
      // return object to caller  
      return $obj;      
    }   
?>