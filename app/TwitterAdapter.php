<?php
namespace App;

use App\SocialFeedInterface;

class TwitterAdapter implements SocialFeedInterface
{
    protected $friendsArr = array();
    
    public function __construct($screenName)
    {

        $this->screenName = $screenName;
        
    }
    
    /**
     * 
     * Retrieve tweets of people being followed if they have an tweet id greater than the last
     * id retrieved
     */
    public function getFeed()
    {
            
        $since_id = \DB::table('social_media')
            ->where('source', '=', 'twitter')
            ->orderBy('social_id', 'DESC')
            ->take(1)
            ->pluck('social_id');

        $paramArr = [
            'count' => 200,
            'include_entities' => 1
        ];
        if ($since_id) {
            $paramArr['since_id'] = $since_id;
        }

        $r = \Twitter::getHomeTimeline($paramArr);


        if (count($r) == 0) {
            return false;
        }
                
        $socialMediaArr = $this->parseFeed($r);

        return $socialMediaArr;
        
    }
    
    /**
     * Parse the feed
     * @param array $r feed result
     * @return array
     */
    public function parseFeed(array $r)
    {
        printR($r);exit;
        $socialMediaArr = [];
        foreach($r as $key => $obj) {
            
            $memberSocialId = strtolower($obj->user->screen_name);
            $socialId = $obj->id_str;
            $link = 'https://twitter.com/' . $memberSocialId . '/status/' . $socialId;
            $mediaUrl = '';
            $mediaHeight = '';
            $mediaWidth = '';
            if (!empty($obj->entities->media)) {
                $media = $obj->entities->media;
                if (isset($media[0]->media_url)) {
                    $mediaUrl = $media[0]->media_url;
                    $mediaHeight = $media[0]->sizes->thumb->h;
                    $mediaWidth = $media[0]->sizes->thumb->w;
                }
            }

            // replace shortened urls with full urls
            $text = $obj->text;
            if (!empty($obj->entities->urls)) {
                foreach ($obj->entities->urls as $key => $urlObj) {
                    $text = str_replace($urlObj->url, $urlObj->expanded_url, $text);
                }
            }
            
            // for retweets, set full retweeted text to $text 
            if (!empty($obj->retweeted_status)) {
                $retweetedText = $obj->retweeted_status->text;
                $text = preg_match("~RT @[^:]+: (.*?)~is", $text, $arr);
                $text = $arr[0] . $retweetedText;
            }
           
            $writtenAt = date("Y-m-d H:m:i", strtotime($obj->created_at));

            $socialMediaArr[] = [
                'memberSocialId' => $memberSocialId,
                'memberId' => 0,
                'socialId' => $socialId,
                'text' => $text,
                'link' => $link,
                'mediaUrl' => $mediaUrl,
                'mediaHeight' => $mediaHeight,
                'mediaWidth' => $mediaWidth,
                'source' => 'twitter',
                'written_at' => $writtenAt
            ];
            
        }
        
        return $socialMediaArr;

    }

    /**
     * 
     * Get list of people being followed
     * 
     */
    public function getFriends($nextCursor = -1)
    {

        // get members being followed on twitter
        $paramArr = [
            'screen_name' => $this->screenName, 
            'skip_status' => true, 
            'include_user_entities' => false, 
            'cursor' => $nextCursor,
            'count' => 200
        ];
         
        $r = \Twitter::getFriends($paramArr);
        
        if (!isset($r->users)) {
            throw new \Exception('users is not a property of twitter result');
        }
        
        $this->parseFriends($r->users);
               
        return $r->next_cursor_str;
        
    }
    
    /**
     * Parse list of people being followed
     * 
     * @param array $twitterFriendsArr 
     */
    public function parseFriends(array $twitterFriendsArr)
    {

        foreach ($twitterFriendsArr as $obj) {

            $this->friendsArr[] = [
                'name' => $obj->name,
                'member_social_id' => $obj->screen_name,
                'source' => 'twitter',
                'avatar' => $obj->profile_image_url,
                'description' => $obj->description,
                'website' => $obj->url
            ];
            
        }
        
    }

    /**
     * Retrieve array of people being followed that were parsed
     * 
     * @return array
     */
    public function getFriendsArr()
    {
        return $this->friendsArr;
    }
     
}