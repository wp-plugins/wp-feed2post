<?php
error_reporting(0);

class RSS { 
	 
	// parser
	var $error_log;
	var $fp;
	var $File;
	var $i = 0;
	var $tag;
	var $parent;
	var $channel=array();
	var $image=array();
	var $textinput=array();
	var $item=array();
	var $skiphours=array();
	var $skipdays=array();
	var $stylesheet_url;
	var $XSL_stylesheet_url;
	
	var $infos = array();
	
	// RSS parser
	
	function Parser($file) {
		
		$this->i = 0;
		
		$this->channel=array(
		'TITLE' => '',
		'DESCRIPTION' => '',
		'LINK' => '',
		'LANGUAGE' => '',
		'RATING' => '',
		'COPYRIGHT' => '',
		'PUBDATE' => '',
		'LASTBUILDDATE' => '',
		'DOCS' => '',
		'MANAGINGEDITOR' => '',
		'WEBMASTER' => '');
		$this->image=array(
		'TITLE' =>'',
		'URL' => '',
		'LINK' => '',
		'WIDTH' => '',
		'HEIGHT' => '',
		'DESCRIPTION' => ''
		);
		$this->textinput=array(
		'TITLE' => '',
		'NAME' => '',
		'DESCRIPTION' => '',
		'LINK' => '');
		//$this->item=array('TITLE' => '',  'DESCRIPTION' => '', 'LINK' => '');
		$this->item = array();
		$this->skiphours=array('HOUR' => '');
		$this->skipdays=array('DAY' => '');
		
		$i=0;
		$this->File = $file;
		
		/*
		$file=substr($file, 7);
		$domaine= substr( $file, 0, strpos($file, "/") );
		$chemin=strstr($file, '/');
		
		echo $domaine,'*',$chemin;
		
		$this->fp = fsockopen( $domaine , 80, &$errno, &$errstr, 30);
		if(!$this->fp) {
			echo "$errstr ($errno)<br>\n";
		} else {
			fputs($this->fp,"GET $chemin HTTP/1.0\n\n");
			while(!feof($this->fp)) {
				echo fgets($this->fp,128);
			}
		fclose($this->fp);
		}
*/

		if (!$this->fp = @fopen($this->File,"r")) {
			$this->error_log = 'Impossible d ouvrir : '.$this->File;
			//die($this->error_log);
			return false;
		}

		
	}
	
	function Start($handle, $tag, $a) {
		
		$this->parent = $this->tag;
		$this->tag = $tag;
	}
	
	function End($handle, $tag) {
		
		switch($this->tag) {
			case 'IMAGE' :
			case 'ITEM' :
			case 'TEXTINPUT' :
			case 'SKIPHOURS' :
			case 'SKIPDAYS' :
			
			$this->parent = 'CHANNEL';
			break;
		}
		
		if($tag === 'ITEM') $this->i++;
		$this->tag = $this->parent;
	}
	
	function Data($handle, $str) {
		$str = trim($str);
		if($str !== '') {
			switch($this->parent) {
				
				case 'CHANNEL' :
				switch($this->tag) {
					
					// 4 required
					case 'TITLE':
					case 'LINK' :
					case 'DESCRIPTION' :
					case 'LANGUAGE' :
					case 'COPYRIGHT' :
					case 'MANAGINGEDITOR' :
					case 'WEBMASTER' :
					
					case 'PUBDATE' :
					case 'LASTBUILDDATE' :
					case 'GENERATOR' :					
					case 'DOCS' :
					case 'CLOUD' :					
					case 'TTL' :					
//					case 'IMAGE' :
					case 'RATING' :
//					case 'TEXTINPUT' :
//					case 'SKIPHOURS' :
//					case 'SKIPDAYS' :
					
					// Dublin Core
					case 'DC:TITLE' :      
                    case 'DC:CREATOR' :
                    case 'DC:SUBJECT' :
                    case 'DC:DESCRIPTION' :
                    case 'DC:PUBLISHER' :
                    case 'DC:CONTRIBUTOR' :
                    case 'DC:DATE' :
					case 'DC:TIME' :
                    case 'DC:FORMAT' :
				    case 'DC:IDENTIFIER' :
				    case 'DC:SOURCE' :	
				    case 'DC:LANGUAGE' :
				    case 'DC:RELATION' :					
				    case 'DC:COVERAGE' :
				    case 'DC:RIGHTS' :			
					
					$this->channel[$this->tag] .= $str;
				}
				break;
				
				case 'IMAGE' :
				switch($this->tag) {
					case 'TITLE':
					case 'URL' :
					case 'LINK' :
					case 'WIDTH' :
					case 'HEIGHT' :
					case 'DESCRIPTION' :
					
					// Dublin Core
					case 'DC:TITLE' :      
                    case 'DC:CREATOR' :
                    case 'DC:SUBJECT' :
                    case 'DC:DESCRIPTION' :
                    case 'DC:PUBLISHER' :
                    case 'DC:CONTRIBUTOR' :
                    case 'DC:DATE' :
					case 'DC:TIME' :
                    case 'DC:FORMAT' :
				    case 'DC:IDENTIFIER' :
				    case 'DC:SOURCE' :	
				    case 'DC:LANGUAGE' :
				    case 'DC:RELATION' :					
				    case 'DC:COVERAGE' :
				    case 'DC:RIGHTS' :			
					
					$this->image[$this->tag] .= $str;
				}
				break;
				
				case 'TEXTINPUT' :
				switch($this->tag) {
					case 'TITLE' :
					case 'DESCRIPTION' :
					case 'NAME' :
					case 'LINK' :
					
					// Dublin Core
					case 'DC:TITLE' :      
                    case 'DC:CREATOR' :
                    case 'DC:SUBJECT' :
                    case 'DC:DESCRIPTION' :
                    case 'DC:PUBLISHER' :
                    case 'DC:CONTRIBUTOR' :
                    case 'DC:DATE' :
					case 'DC:TIME' :
                    case 'DC:FORMAT' :
				    case 'DC:IDENTIFIER' :
				    case 'DC:SOURCE' :	
				    case 'DC:LANGUAGE' :
				    case 'DC:RELATION' :					
				    case 'DC:COVERAGE' :
				    case 'DC:RIGHTS' :				
					
					$this->textinput[$this->tag] .= $str;
				}
				break;
				
				case 'ITEM' :
                switch($this->tag) {
					case 'TITLE' :
					case 'DESCRIPTION' :
					case 'CONTENT:ENCODED' :
					case 'LINK' :

					case 'AUTHOR' :
					case 'CATEGORY' :
					case 'COMMENTS' :
//					case 'ENCLOSURE' :
 					case 'GUID' : //permalink
 					case 'PUBDATE' : 
  					case 'SOURCE' : 
 
 					// Dublin Core
                    case 'DC:TITLE' :      
                    case 'DC:CREATOR' :
                    case 'DC:SUBJECT' :
                    case 'DC:DESCRIPTION' :
                    case 'DC:PUBLISHER' :
                    case 'DC:CONTRIBUTOR' :
                    case 'DC:DATE' :
					case 'DC:TIME' :
                    case 'DC:FORMAT' :
				    case 'DC:IDENTIFIER' :
				    case 'DC:SOURCE' :	
				    case 'DC:LANGUAGE' :
				    case 'DC:RELATION' :					
				    case 'DC:COVERAGE' :
				    case 'DC:RIGHTS' :					
	
					// MEDIA
					case 'MEDIA:CATEGORY':

					// FANFIRST
					case 'FF:RATING':
					case 'FF:MAXRATING':
					case 'FF:PLATFORM':
					case 'FF:EVENT':
					case 'FF:ITEMTYPE':
					case 'FF:SECTOR':
					case 'FF:TENDENCY':
					
					//HUBRSS
					case 'HUBRSS:ICON':
					case 'HUBRSS:FEEDID':
					case 'HUBRSS:FEEDNAME':
					case 'HUBRSS:ITEMID':
					case 'HUBRSS:LANGUAGE':

					case 'HUBRSS:TAGS':

					case 'HUBRSS:NOTE':
					case 'HUBRSS:NB_VOTES':
					case 'HUBRSS:TOTAL_VOTES':
					
					case 'HUBRSS:HITS':
					case 'HUBRSS:VISITORS':

					if(isset($this->item[$this->i][$this->tag]))
					$this->item[$this->i][$this->tag] .= $str;
					else
					$this->item[$this->i][$this->tag] = $str;
				}
				break;
				
				case 'SKIPHOURS' :
				switch($this->tag) {
					case 'HOUR' :
					$this->skiphours[] = $str;
				}
				break;
				
				case 'SKIPDAYS' :
				switch($this->tag) {
					case 'DAY' :
					$this->skipdays[] = $str;
				}
				break;
			}
		}
	}
	
	function Parse() {
		$this->x = xml_parser_create('ISO-8859-1');
		xml_set_object($this->x, &$this);
		xml_parser_set_option($this->x, XML_OPTION_CASE_FOLDING, TRUE);
		xml_parser_set_option($this->x, XML_OPTION_TARGET_ENCODING, 'ISO-8859-1');
		xml_set_element_handler($this->x, 'Start', 'End');
		xml_set_character_data_handler($this->x, 'Data');
		
		while ($data = @fread($this->fp, 4096) ) {
	
			if (!xml_parse($this->x, $data, feof($this->fp))) {
				/*
				echo(sprintf("XML error: %s at line %d",
				xml_error_string(xml_get_error_code($this->x)),
				xml_get_current_line_number($this->x)));
				*/
				//echo 'Le fichier RSS est invalide !';
				break;
			}
		}
		
		@fclose($this->fp);
		xml_parser_free($this->x);
		
		$infos = array(
		'channel' => $this->channel,
		'item' => $this->item,
		'image' => $this->image,
		'textinput' => $this->textinput,
		'skiphours' => $this->skiphours,
		'skipdays' => $this->skipdays
		
		);
		
		$this->infos = $infos;
		return $infos;
	}
	
	/**
	* @return array
	* @desc Retourne un array comportant les infos de votre choix
	*/
	function return_infos() {
		$args = func_get_args();
		
		$Ret = array();
		$T = array('channel', 'item', 'image', 'skipdays', 'skiphours');
		
		$infos = $this->Parse();
		
		foreach($args as $arg) {
			if(in_array($arg, $T))
			$Ret[$arg] = $infos[$arg];
		}
		
		$this->infos = $Ret;
		return $Ret;
	}
	
	/**
	* @return string HTML
	* @param limit Nombre d item a afficher
	* @desc Affichage Simple des resultats.
	*/
	function Output($limit=10) {
		$Ret = '';
		if(!empty($this->infos)) {
			
			//echo '<pre>';
			//echo print_r($this->infos);
			//echo '</pre>';
			
			$Ret .= '<div class="tableau">';
			
			$ret .= '<div class="channel">';
			$Ret .= '<a href="'.$this->infos['channel']['LINK'].'"';
			$Ret .= ' title="'.$this->infos['channel']['TITLE'].'"';
			$Ret .= ' hreflang="'.$this->infos['channel']['LANG'].'">';
			$Ret .= $this->infos['channel']['TITLE'];
			$Ret .= '</a>';
			$Ret .= '</div>';
			
			$Ret .= '<ul class="item">';
			
			$i = 0;
			foreach($this->infos['item'] as $val) {
				print_r($val);
				$Ret .= "\t<li><a href=\"$val[LINK]\">$val[TITLE] - ".$val['DC:DATE']."</a></li>\n";
				if($i == $limit) break;
				$i++;
			}
			
			$Ret .= '</ul>';
			
			$Ret .= '</div>';
		}
		return $Ret;
	}
	
	// --------------------------------------------------------------------- //
	//						RSS Creator                                    //
	// --------------------------------------------------------------------- //
	
	function Creator($file) {
		
		$this->channel = array();
		$this->image = array();
		$this->textinput = array();
		$this->skiphours = array();
		$this->skipdays = array();
		$this->item = array();
		
		$this->i = 0;
		
		$this->File = $file;
		
		if (!$this->fp = @fopen($this->File,"w+")) {
			$this->error_log = 'Impossible d ouvrir : '.$this->File;
			die($this->error_log);
		}
	}
	
	/**
	* @return
	* @param encoding string : l encodage de votre fichier XML
	* @desc Ajoute l encodage du fichier
	* @access public
	*/
	function Add_encoding($encoding) {
		
		$this->encoding = $encoding;
	}
	
	/**
	 * @return 
	 * @param url URL vers la feuille de style CSS
	 * @desc Ajoute une feuille de style au document XML
	 * @access public
	 */
	function Add_stylesheet($url) {
		
		$this->stylesheet_url = $url;
	}
	
	/**
	 * @return 
	 * @param url URL vers la feuille de style XSLT
	 * @desc Ajoute une feuille de style au document XML
	 * @access public
	 */
	function Add_XSL_stylesheet($url) {
		
		$this->XSL_stylesheet_url = $url;
	}
	
	/**
	* @return
	* @param desc string Description du Channel
	* @param lang string langue du Channel
	* @param link string Lien vers le site
	* @param title titre du Channel
	* @desc Ajoute les 4 elements minimum pour que le fichier RSS soit valide
	*/
	function Create_channel($desc, $lang, $link, $title) {
		
		$this->channel['DESCRIPTION'] = $desc;
		$this->channel['LANGUAGE'] = $lang;
		$this->channel['LINK'] = $link;
		$this->channel['TITLE'] = $title;
	}
	
	function Add_copyright($copyright) {
		
		$this->channel['COPYRIGHT'] = $copyright;
	}
	
	function Add_pubdate($pubdate) {
		
		$this->channel['PUBDATE'] = $pubdate;
	}
	
	function Add_lastbuilddate($lastbuilddate) {
		
		$this->channel['LASTBUILDDATE'] = $lastbuilddate;
	}
	
	function Add_docs($docs) {
		
		$this->channel['DOCS'] = $docs;
	}
	
	function Add_rating($rating) {
		
		$this->channel['RATING'] = $rating;
	}
	
	function Add_managingeditor($managingeditor) {
		
		$this->channel['MANAGINGEDITOR'] = $managingeditor;
	}
	
	function Add_webmaster($webmaster) {
		
		$this->channel['WEBMASTER'] = $webmaster;
	}
	
	function Add_image($title, $url, $link, $width='', $height='', $desc='') {
		
		$this->image['TITLE'] = $title;
		$this->image['URL'] = $url;
		$this->image['LINK'] = $link;
		$this->image['WIDTH'] = $width;
		$this->image['HEIGHT'] = $height;
		$this->image['DESCRIPTION'] = $desc;
	}
	
	function Add_textinput($title, $desc, $name, $link) {
		
		$this->textinput['TITLE'] = $title;
		$this->textinput['DESCRIPTION'] = $desc;
		$this->textinput['NAME'] = $name;
		$this->textinput['LINK'] = $link;
	}
	
	function Add_skiphours($hours) {
		
		if(is_array($hours)) {
			foreach($hours as $hour) {
				if($hour >= 0 || $hour < 24)
				$this->skiphours[] = $hour;
			}
		}
		else
		if($hours >= 0 || $hours < 24)
		$this->skiphours[] = $hours;
	}
	
	function Add_skipdays($days) {
		
		if(is_array($days)) {
			foreach($days as $day) {
				$this->skipdays[] = $day;
			}
		}
		else
		$this->skipdays = $days;
	}
	
	function Add_item($title, $desc, $link) {
		
		$this->item[$this->i]['TITLE'] = $title;
		$this->item[$this->i]['DESCRIPTION'] = $desc;
		$this->item[$this->i]['LINK'] = $link;
		
		$this->i++;
	}
	
	function Create_file() {
			
		$R  = '<?xml version="1.0" encoding="'.$this->encoding.'" ?>'."\n";
		
		if($this->XSL_stylesheet_url)
		$R .= '<?xml-stylesheet href="'.$this->XSL_stylesheet_url.'" type="text/xsl"?>'."\n";
		
		if($this->stylesheet_url)
		$R .= '<?xml-stylesheet href="'.$this->stylesheet_url.'" type="text/css"?>'."\n";
		
		$R .= '<!DOCTYPE rss SYSTEM "http://my.netscape.com/publish/formats/rss-0.91.dtd">'."\n";
		$R .= '<rss version="0.91">'."\n";
		$R .= '<channel>'."\n";
		
		if(isset($this->channel['COPYRIGHT']))
		$R .= "<copyright>".$this->channel['COPYRIGHT']."</copyright>\n";
		
		if(isset($this->channel['PUBDATE']))
		$R .= "<pubDate>".$this->channel['PUBDATE']."</pubDate>\n";
		
		if(isset($this->channel['LASTBUILDDATE']))
		$R .= "<lastBuildDate>".$this->channel['LASTBUILDDATE']."</lastBuildDate>\n";
		
		if(isset($this->channel['DOCS']))
		$R .= "<docs>".$this->channel['DOCS']."</docs>\n";
		
		$R .= "<language>".$this->channel['LANGUAGE']."</language>\n";
		$R .= "<description>".$this->channel['DESCRIPTION']."</description>\n";
		$R .= "<link>".$this->channel['LINK']."</link>\n";
		$R .= "<title>".$this->channel['TITLE']."</title>\n";
		
		if(!empty($this->image)) {
			$R .= '<image>'."\n";
			$R .= "\t<link>".$this->image['LINK']."</link>\n";
			$R .= "\t<title>".$this->image['TITLE']."</title>\n";
			$R .= "\t<url>".$this->image['URL']."</url>\n";
			
			if($this->image['WIDTH'] !== '')
			$R .= "\t<width>".$this->image['WIDTH']."</width>\n";
			
			if($this->image['HEIGHT'] !== '')
			$R .= "\t<height>".$this->image['HEIGHT']."</height>\n";
			
			if($this->image['DESCRIPTION'] !== '')
			$R .= "\t<description>".$this->image['DESCRIPTION']."</description>\n";
			
			$R .= "</image>\n";
		}
		
		if(isset($this->channel['MANAGINGEDITOR']))
		$R .= "<managingEditor>".$this->channel['MANAGINGEDITOR']."</managingEditor>\n";
		
		if(isset($this->channel['WEBMASTER']))
		$R .= "<webmaster>".$this->channel['WEBMASTER']."</webmaster>\n";
		
		if(!empty($this->skiphours)) {
			$R .= "<skipHours>\n";
			foreach($this->skiphours as $hour) {
				$R .= "\t<hour>$hour</hour>\n";
			}
			$R .= "</skipHours>\n";
		}
		
		if(!empty($this->skipdays)) {
			$R .= "<skipDays>\n";
			foreach($this->skipdays as $day) {
				$R .= "\t<day>$day</day>\n";
			}
			$R .= "</skipDays>\n";
		}
		
		if(isset($this->channel['RATING']))
		$R .= "<rating>".$this->channel['RATING']."</rating>\n";
		
		if(!empty($this->item)) {
			foreach($this->item as $items) {
				
				$R .= "<item>\n";
				$R .= "\t<title>".$items['TITLE']."</title>\n";
				$R .= "\t<link>".$items['LINK']."</link>\n";
				$R .= "\t<description>".$items['DESCRIPTION']."</description>\n";
				$R .= "</item>\n";
			}
			
		}
		
		
		$R .= "\n</channel>\n";
		$R .= "</rss>\n";
		
		fwrite($this->fp, $R);
		fclose($this->fp);
		
		return true;
	}
	
	
	
} // end class

?>
