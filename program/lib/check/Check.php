<?PHP
class Check 
{

  function isInput($str){
    return $str == "" ? 0 : 1 ;
  }
  function isMail($str){
    //return  preg_match("/^[\.!#%&\-_0-9a-z]+\@[!#%&\-_0-9a-z]+(\.[!#%&\-_0-9a-z]+)+$/i",$str) ;
//      return  preg_match("/^[\/.!#%&\-_0-9a-z]+\@[!#%&\-_0-9a-z]+(\.[!#%&\-_0-9a-z]+)+$/i",$str) ;
      return  preg_match("/^[\/.!#&\-_0-9a-z\+]+\@[!#&\-_0-9a-z]+(\.[!#&\-_0-9a-z]+)+$/i",$str) ;
  }
  function isDocomo($str){
    return ereg( "^docomo.ne.jp",substr($str,-12) ) ;
  }
  function isSoftbank($str){
    $return = False;
    if( ereg( "^.vodafone.ne.jp",substr($str,-15) ) ){
      $return = True;
    }else if( ereg( "^.softbank.ne.jp",substr($str,-15) ) ){
      $return = True;
    }
    return $return  ;
  }
  function isVodafone($str){
    return $this->isSoftbank($str);
  }
  function isVodafoneOld($str){
    return ereg( "^.vodafone.ne.jp",substr($str,-15) )  ;
  }
  function isAu($str){
    return ereg( "^ezweb.ne.jp",substr($str,-11) )  ;
  }
  function isUrl($str){
    return preg_match("/s?https?:\/\/[-_.!~*'()a-zA-Z0-9;\/?:@&=+$,%#]+/",$str) ;
  }
  function isNumber($str){
    if(ereg("^[0-9]+$", $str)){
      return 1 ;
    }else{
      return 0 ;
    }
  }
  function isMbNumber($str){
    return ereg("^[��-��]+$", $str);
  }
  function isNumberLen($str, $len){
    if(ereg("^[0-9]{{$len}}$", $str)){
      return 1 ;
    }else{
      return 0 ;
    }
  }
  function isNumberRange($str, $start,$end){
    return ereg("^[0-9]{{$start},{$end}}$", $str);

  }
  function isLen( $str,$limit ){
    if(! $this->isInput($str) ) return False ;
    return strlen($str) > $limit ? False : True ;
  }
  function isMblen( $str,$limit ){
    if(! $this->isInput($str) ) return False ;
    return mb_strlen($str) > $limit ? False : True ;
  }
  function isA2Z($str){
   return ereg("^[A-Z]+$", $str);
  }
  function isA2Zs($str){
   return ereg("^[a-z]+$", $str);
  }
  function isA2Zi($str){
   return eregi("^[A-Za-z]+$", $str);
  }
  function isEisu($str){
   return ereg("^[0-9A-Z]+$", $str);
  }
  function isEisus($str){
   return ereg("^[0-9a-z]+$", $str);
  }
  function isEisui($str){
    return eregi("^[0-9A-Za-z]+$", $str);
  }
  function isEisuSpace($str){
     return ereg("^[0-9A-Z ]+$", $str);
  }
  function isEisuSpaces($str){
     return ereg("^[0-9a-z ]+$", $str);
  }
  function isEisuSpacei($str){
     return eregi("^[0-9A-Za-z ]+$", $str);
  }
  function isEisuKigou($str){
     return ereg("^[]0-9A-Za-z\\!\"#$%&'\(\)*+,./:;<=>?@[\^_`{|}~]+$", $str);
  }

  function isHiragana($str){
	 	return mbereg('^[��-��]+$',$str);
  }
  
  function isKataKana($str){
    if(mb_ereg_match("^[��-����]+$",$str)){
      return 1 ;
    }else{
      return 0 ;
    }
  }

}
?>
