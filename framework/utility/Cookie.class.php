<?php

//TODO must be completed

namespace framework\utility;

use framework\network\Http;
use framework\utility\Validate;
use framework\utility\Date;

class Cookie {

    use \framework\debugger\Debug;

    const EXPIRE_TIME_SESSION = 0;
    const EXPIRE_TIME_SECOND = Date::SECOND;
    const EXPIRE_TIME_MINUTE = Date::MINUTE;
    const EXPIRE_TIME_DAY = Date::DAY;
    const EXPIRE_TIME_DAY = Date::DAY;
    const EXPIRE_TIME_WEEK = Date::WEEK;
    const EXPIRE_TIME_MONTH = Date::MONTH;
    const EXPIRE_TIME_YEAR = Date::YEAR;
    const EXPIRE_TIME_INFINITE = -1;

    //protected $_salt = null;
    protected $_name = null;
    protected $_value = null;
    protected $_autoFixDomain = true;
    protected $_expire = 0;
    protected $_path = '';
    protected $_domain = null;
    protected $_secure = false;
    protected $_httponly = false;
    protected $_sizeLimit = '4096'; // in ko

    public function __construct($name, $value = null, $expire = self::EXPIRE_TIME_WEEK, $path = null, $domain = null, $secure = false, $httponly = false) {
        $this->setName($name);
        $this->setValue($value);
        if ($expire !== 0)
            $this->setExpire($expire);
        if (!is_null($path))
            $this->setPath($path);
        if (!is_null($domain))
            $this->setDomain($domain);
        $this->setSecure($secure);
        $this->setHttponly($httponly);
    }

    public function setName($name) {
        if (!Validate::isVariableName($name))
            throw new \Exception('Name parameter must be a valid variable name');

        $this->_name = $name;
    }

    public function getName() {
        return $this->_name;
    }

    public function setValue($value) {
        //todo check;

        $lengh = (function_exists('mb_strlen') ? mb_strlen($value) : strlen($value));
        if ($lengh > $this->getSizeLimit())
            throw new \Exception('Cookie value exceeds autorize limit: "' . $this->getSizeLimit() . '" ko');

        $this->_value = $value;
    }

    public function getValue() {
        return $this->_value;
    }

    public function setExpire($expire = self::EXPIRE_TIME_WEEK, $multiplicator = 0) {
        if (!is_int($expire))
            throw new \Exception('Expire parameter must be an integer');
        if (!is_int($multiplicator))
            throw new \Exception('multiplicator parameter must be an integer');

        if ($multiplicator != 0 && $expire != -1)
            $expire = $expire * $multiplicator;

        if ($expire == -1)
            $this->_expire = mktime(3, 14, 7, 1, 19, 2038); //19 janvier 2038 à 3 h 14 min 7 s see http://fr.wikipedia.org/wiki/Bug_de_l%27an_2038 BIG JOKE
        else
            $this->_expire = time() + $expire;
    }

    public function getExpire() {
        return $this->_expire;
    }

    public function setPath($path) {
        //todo check;
        if (!is_string($path))
            throw new \Exception('path parameter must be a string');
        $this->_path = $path;
    }

    public function getPath() {
        return $this->_path;
    }

    public function setDomain($domain = null) {
        //todo check;
        if (!is_null($domain) && !is_string($domain))
            throw new \Exception('Domain parameter must be null or a string');
        // if not specified domain, get HTTP_HOST
        $domain = is_null($domain) ? Http::getServer('HTTP_HOST') : $domain;

        $this->_domain = $this->getAutoFixDomain() ? $this->_fixDomain($domain) : $domain;
    }

    public function getDomain() {
        return $this->_domain;
    }

    public function setSecure($secure) {
        if (!is_bool($secure))
            throw new \Exception('secure parameter must be an boolean');

        $this->_secure = $secure;
    }

    public function getSecure() {
        return $this->_secure;
    }

    public function setHttponly($httponly) {
        if (!is_bool($httponly))
            throw new \Exception('httponly parameter must be an boolean');

        $this->_httponly = $httponly;
    }

    public function getHttponly() {
        return $this->_httponly;
    }

    public function setAutoFixDomain($autoFix) {
        if (!is_bool($autoFix))
            throw new \Exception('autoFix parameter must be an boolean');

        $this->_autoFixDomain = $autoFix;
    }

    public function getAutoFixDomain() {
        return $this->_autoFixDomain;
    }

    protected function _fixDomain($domain) {
        // Fix the domain to accept domains with and without 'www.'.
        if (strtolower(substr($domain, 0, 4)) == 'www.')
            $domain = substr($domain, 4);
        // Add the dot prefix to ensure compatibility with subdomains
        if (substr($domain, 0, 1) != '.')
            $domain = '.' . $domain;

        // Remove port information.
        $port = strpos($domain, ':');
        if ($port !== false)
            $domain = substr($domain, 0, $port);

        return $domain;
    }

    public function setSizeLimit($size) {
        // todo check is int and not big ... and limit type: bit, kb, mb max 4096 il me semble
        $this->_sizeLimit = $size;
    }

    public function getSizeLimit() {
        return $this->_sizeLimit;
    }

    public function write() {
        setcookie($this->getName(), $this->getValue(), $this->getExpire(), $this->getPath(), $this->getDomain(), $this->getSecure(), $this->getHttponly());
    }

    public function delete() {
        //Delete on global
        if (self::get($this->getName()))
            unset($_COOKIE[$this->getName()]);

        // And reset cookie (set expire negative time)
        setcookie($this->getName(), null, -86400, $this->getPath(), $this->getDomain(), $this->getSecure(), $this->getHttponly());
    }

    public static function get($key = null, $default = null, $allowHtmlTags = false) {
        return Http::getCookie($key, $default, $allowHtmlTags);
    }

    /*



      public function set($name, $value, $domain, $expire = null, $path = null, $secure = false) {
      if (is_null($expire))
      $expire = $this->_expire;

      if ($expire !== 0)
      $expire += time();

      //return setcookie($name, $value, $expiration, self::$_path, self::$_domain, self::$_secure, self::$_httponly);
      }

      public function delete($name) {
      if (self::get($name))
      unset($_COOKIE[$name]);

      // Nullify the cookie and make it expire
      //return setcookie($name, NULL, -86400, self::$_path, self::$_domain, self::$_secure, self::$_httponly);
      }

      /*
      function createCookie($name, $value='', $maxage=0, $path='', $domain='', $secure=false, $HTTPOnly=false)
      {
      $ob = ini_get('output_buffering');

      // Abort the method if headers have already been sent, except when output buffering has been enabled
      if ( headers_sent() && (bool) $ob === false || strtolower($ob) == 'off' )
      return false;

      if ( !empty($domain) )
      {
      // Fix the domain to accept domains with and without 'www.'.
      if ( strtolower( substr($domain, 0, 4) ) == 'www.' ) $domain = substr($domain, 4);
      // Add the dot prefix to ensure compatibility with subdomains
      if ( substr($domain, 0, 1) != '.' ) $domain = '.'.$domain;

      // Remove port information.
      $port = strpos($domain, ':');

      if ( $port !== false ) $domain = substr($domain, 0, $port);
      }

      // Prevent "headers already sent" error with utf8 support (BOM)
      //if ( utf8_support ) header('Content-Type: text/html; charset=utf-8');

      header('Set-Cookie: '.rawurlencode($name).'='.rawurlencode($value)
      .(empty($domain) ? '' : '; Domain='.$domain)
      .(empty($maxage) ? '' : '; Max-Age='.$maxage)
      .(empty($path) ? '' : '; Path='.$path)
      .(!$secure ? '' : '; Secure')
      .(!$HTTPOnly ? '' : '; HttpOnly'), false);
      return true;
      }
      //$ob = ini_get('output_buffering');
      if ( !empty($domain) )
      {
      // Fix the domain to accept domains with and without 'www.'.
      if ( strtolower( substr($domain, 0, 4) ) == 'www.' ) $domain = substr($domain, 4);
      // Add the dot prefix to ensure compatibility with subdomains
      if ( substr($domain, 0, 1) != '.' ) $domain = '.'.$domain;
      // Remove port information.
      $port = strpos($domain, ':');
      if ( $port !== false ) $domain = substr($domain, 0, $port);
      }
      function storecookie( $inKey , $inValue , $inExpire ) {
      $decode = serialize( $inValue );
      $decode = gzcompress( $decode );
      $decode = base64_encode( $decode );

      $split = str_split( $decode , 4000 );//4k pieces
      $count = count( $split );

      for ( $index = 0 ; $index < $count ; $index += 1 ) {
      $result = setcookie( ( $index > 0 ) ? $inKey.COOKIE_PORTIONS.$index : $inKey , $split[$index] , $inExpire , '/' , '' , 0 );
      }

      clearpieces( $inKey , $count );
      }

      function fetchcookie( $inKey ) {
      $decode = $_COOKIE[$inKey];

      for ( $index = 1 ; array_key_exists( $inKey.COOKIE_PORTIONS.$index , $_COOKIE ) ; $index += 1 ) {
      $decode .= $_COOKIE[$inKey.COOKIE_PORTIONS.$index];
      }

      $decode = base64_decode( $decode );
      $decode = gzuncompress( $decode );

      return unserialize( $decode );
      }
     */
}

?>