<?php
//$query= something::complexQueryGoogle("me,earl and the dying girl");


	class askResult{
	    private $title;
	    private $abstract;
	    private $link;
	    private $page;
	    private $rank;
	   // private $searchEngine;
	    public function getRank(){
	    	return $this->rank;
	    }
	    public function getTitle(){
	    	return $this->title;
	    }
	    public function getAbstract(){
	    	return $this->abstract;
	    }
	    public function getLink(){
	    	return $this->link;
	    }

	    public function  __construct($title,$link,$abstract,$pageNum,$rank){
	    	$this->title=$title;
	    	$this->link=$link;
	    	$this->abstract=$abstract;
	    	$this->pageNum=$pageNum;
	    	$this->rank=$rank;

	    }
	 }
	 class askParser{
	    private static function complexQuery($q){
			
			$q=str_replace(" NOT "," -",$q);
			$q=urlencode("$q");
			return $q;
		}
		public static function getAskSearchResultsByPage($query,$pageNum){
				$objArray= array();
				$url=askParser::formatAskQuery($query,$pageNum);
				$urlContent=file_get_contents($url);
				$formatContentstr=stristr($urlContent,"<div class=\"PartialSearchResults-item\" data-zen=\"true\">");
				$formatContentstr=stristr($formatContentstr,"<script>",true);
				$resultsInHtml=explode("class=\"PartialSearchResults-item\"",$formatContentstr);
				$count=1;
				foreach($resultsInHtml as $mkey=>$mvalue){
					//pass title , link ,abstract to constructor
				
			        $res = new askResult(askParser::extract_title($mvalue),askParser::extract_link($mvalue)
			        	,askParser::extract_abstract($mvalue),$pageNum,$count++);
			        if((empty($res->getTitle())||empty($res->getLink())))
			        	continue;
			      	//add res object to the array
			        $objArray[]=$res;
			      
	    		}
	    		return $objArray;

		}

		public static function getAggeregatedSearchResults($query,$resultsNum){
				$objArray= array();
				$i=1;
				while(count($objArray)<=$resultsNum){
					$newArr=askParser::getAskSearchResultsByPage($query,$i);
					//no more results available so break
					if(count($newArr)<1)
						break;
					//still more pages needs to be loaded to reach the required results number
					if(count($newArr)+count($objArray)<=$resultsNum)
						$objArray=array_merge($objArray,$newArr);
					//pick certain amount of results to reach the required number from the last page to be loaded
					else{
						$j=count($objArray);
						
						foreach ($newArr as $key => $value) {
							if($j++<$resultsNum){
								$objArray[]=$value;
							}
						}
						break;
					}
					$i++;
				}
				echo "Ask ... :: ".count($objArray)."<br>";
				
				return $objArray;
		}
		public static function extract_title($htmlStr){
	 		$title1=stristr(stristr($htmlStr,"class=\"PartialSearchResults-item-title-link result-link\""),"</",true);
	        $title1=stristr($title1,">");
	        $title1=str_replace(">", "", $title1);
	        return $title1;

		}
		public static function extract_abstract($htmlStr){
			$abstract=stristr(stristr($htmlStr,"class=\"PartialSearchResults-item-abstract\""),"</",true);
			$abstract=stristr($abstract,">");
	        $abstract=str_replace(">", "", $abstract);
	        return $abstract;
		}
		public static function extract_link($htmlStr){
			//$link=$htmlStr;
			//$link=stristr($htmlStr,"class=\"PartialSearchResults-item-url\"");
		    $link="";
		    $link[0]=" ";
			$link=stristr(stristr($htmlStr,"class=\"PartialSearchResults-item-url\""),"</",true);
			$link=stristr($link,">");
			$link=str_replace(">", "", $link);
			$charArr=str_split($link);
			
			if($charArr[0]=='.'){
			     $link=substr($link, 1);
			}	
	        //if(empty($link)&&$link[0] == ".")
	        //	return "//".$link;
			
				return "http://".$link;


		}
		public static function formatAskQuery($query,$pageNum){
			$q="http://www.ask.com/web?q=";
			$q.=askParser::complexQuery($query);
			$q.="&qsrc=998&page=";
			$q.=$pageNum;
			echo "url :: ".$q."<br>";
			return $q;
		}
		/*public static function getResultsSet($arr){
			$resSet=new resultSet();
			foreach ($arr as $key => $value) {
				$resSet
			}

		}*/
		

	}

?>