<?php
namespace App\Http\Controllers\preprocessing;
use  App\Http\Controllers\preprocessing\Stemmer;
use DB;
use Storage;
// preprocesing: tokenization, filtering, stopterm removal dan stemming.
// Processing
// Steaming,Stopterm, penghilangan tanda baca, pemisahan term

class Preprocessing extends Stemmer
{
	public function preprocesing($date,$start_time_tweet,$end_time_tweet){
        $terms = array();
        $clear_tweet = "";
        // $clear_tweets = array();
    	$Stemmer = new Stemmer();

    	$tweets = DB::table('tweets')
                    ->where('date_tweet','like',date('D M d%Y',strtotime($date)))->get();
 		foreach ($tweets as $tweet) {
            if(strtotime($tweet->date_tweet) >= strtotime($date.$start_time_tweet) && strtotime($tweet->date_tweet) <= strtotime($date.$end_time_tweet))
            {
     			foreach ($this->parsing($tweet->tweet) as $term)
     			{ 
                    $tokenizing = $this->tokenizing($term);
                    if($tokenizing)
                    {
                        if($this->stopword($tokenizing))
                        {
                            // $data_term = $this->NAZIEF($tokenizing);
                            $data_term = $tokenizing;
                            $clear_tweet .= $data_term." ";
                            if(strlen($data_term) != 0)
                            {
                                if(!array_key_exists($data_term, $terms))
                                    $terms[$data_term] = 0;
                                $terms[$data_term] = $terms[$data_term] + 1;
                            }
                        }
                    }
     			}
                DB::table('tweet_preprocessing')->insert(['id_tweet' => $tweet->id,'preprocessing' => rtrim($clear_tweet,' ')]);
                $clear_tweet = "";
            }
 		}
        // arsort($terms);
        return $terms;
    }
    public function tokenizing($term)
    {
        if((substr($term, 0,4) == 'http') || (substr($term, 0,1) == '@'))
           return false;
        return preg_replace('/[^a-z]/i','', $this->caseFolding($term));
    }

    public function caseFolding($term)
    {
    	return strtolower($term);
    }

    public function parsing($tweet)
    {
    	return $parsing = explode(' ',$tweet);
    }
    public function stopword($term)
    {
        $stop_term = DB::table('stopwords')->where('stopword',$term)->count();
        if($stop_term < 1)
            return true;
        return false;
    }
}