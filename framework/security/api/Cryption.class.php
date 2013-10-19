<?php

//TODO must be completed

namespace framework\security\api;

use framework\security\IApi;
use framework\security\Api;
use framework\Logger;
use framework\security\cryptography\Hash;

class Cryption extends Api implements IApi {

    protected $_key = '';
    protected $_passwordAlgorithmList = array('des', 'desExt', 'md5', 'blowfish', 'sha256', 'sha512');

    public function __construct($options = array()) {
        throw new \Exception('Not yet');

        /* if (!isset($options['key']))
          throw new \Exception('Api cryption need a key');
          $this->_key = Hash::hashString($options['key'], Hash::ALGORITHM_SHA1, false, 10); */
    }

    public function run() {
        if (self::getDebug())
            Logger::getInstance()->debug('Password API was run', 'securityApi');
    }

    public function stop() {
        
    }

    public function isValidPasswordAlgorithm($algorithm) {
        return (in_array((string) $algorithm, $this->_passwordAlgorithmList));
    }

    public function cryptPassword($password, $algorithm, $depth) {
        
    }

    public function checkPassword($cryptedPassword, $passwordCheck, $algorithm, $string, $depth) {
        
    }

    public function getPasswordInfo($cryptedPassword) {
        // algo, depth
    }

}

/* namespace framework\security\cryptography;

  use framework\utility\Tools;
  use framework\security\cryptography\hash\Algorithm;

  class Hash extends Algorithm {

  protected $_algorithm = null; // hashing parameters
  protected $_depth = 15;
  protected $_permute = false;
  protected $_permuteRules = array(
  'tv' => 'vy',
  '0123456789abcdef' => 'e875d60c4a2f1b93',
  'abcdef' => 'faz854');
  // salting parameters
  protected $_saltingOptionDelimiter = '$$$1'; // todo method for setting ...
  // autoGenerateSaltParameters
  protected $_autoGenerateSalt = false;
  protected $_saltCharsList = ''; //^!%&/()=?+~#,.;:_|<>@$';
  protected $_saltLengh = 20;
  protected $_saltEncode = false;

  public function __construct($algorithm = self::ALGORITHM_SHA1, $depth = 15, $permute = true) {
  if ($algorithm != self::ALGORITHM_SHA1 && $algorithm != self::ALGORITHM_MD5)
  throw new \Exception('Hash algorithm parameter must a valid algo');

  $this->_algorithm = $algorithm;


  if (!is_int($depth))
  throw new \Exception('hash depth (iteration) parameter must an integer');
  $this->_depth = $depth;

  if (!is_bool($permute))
  throw new \Exception('permute parameter must be a boolean');
  $this->_permute = $permute;
  }

  public function setAutoGenerateSaltParameters($autoGenerateSalt, $saltCharsList = '', $saltMinLengh = 20, $saltEncode = true) {
  if (!is_bool($autoGenerateSalt))
  throw new \Exception('autoGenerateSalt parameter must be a boolean');
  $this->_autoGenerateSalt = $autoGenerateSalt;
  if (!is_string($saltCharsList))//todo better check
  throw new \Exception('saltCharsList parameter must be a string');
  $this->_saltCharsList = $saltCharsList;
  if (!is_int($saltMinLengh))
  throw new \Exception('saltMinLengh parameter must be an integer');
  $this->_saltMinLengh = $saltMinLengh;
  if (!is_bool($saltEncode))
  throw new \Exception('saltEncodeparameter must be a boolean');
  $this->_saltEncode = $saltEncode;
  }

  public function hashString($string, $salt = null, $saltEncode = true) {
  // Charset String format ;)
  if (!is_string($string))
  throw new \Exception('string is not a string ^^');
  if (!is_null($salt) &&!is_string($salt))
  throw new \Exception('salt parameter must a string');

  // salt
  if (is_null($salt))
  $salt = $this->_autoGenerateSalt ? $this->_generateSalt($this->_saltEncode) : '';
  else {
  if ($saltEncode)
  $salt = base64_encode($salt);
  }
  //Permutation
  if ($this->_permute)
  $string = $this->_permutatation($string);

  \framework\Debugger::dump($string);
  switch ($this->_algorithm) {
  case self::ALGORITHM_MD5:
  break;
  case self::ALGORITHM_SHA1:
  for ($i = 0;
  $i <= $this->_depth;
  $i++)
  $string = sha1($string, false);
  break;
  default:
  throw new \Exception('Invalid hash algorithm setted');
  break;
  }
  $hash = $string;



  // concate salt
  $hash = $hash . $this->_saltingOptionDelimiter . $salt;

  return $hash;
  }

  protected function _permutatation(&$string) {
  foreach ($this->_permuteRules as $ruleSearch => $ruleReplace)
  $hash = $this->_appliRule($string, $ruleSearch, $ruleReplace);

  return $hash;
  }

  protected function _appliRule($string, $charsListFrom, $charsListTo, $iterate = false) {
  $countCharsListFrom = strlen($charsListFrom);
  if ($countCharsListFrom != strlen($charsListTo))
  return $string;


  if (!$iterate)
  $iterate = 1;

  // set permutation datas rules array
  $permuteDatas = array();
  for ($i = 0;
  $i < $countCharsListFrom;
  $i++)
  $permuteDatas[$charsListFrom[$i]] = $charsListTo[$i];
  // apply permutation datas
  $stringLengh = strlen($string);
  for ($i = 0;
  $i < $stringLengh;
  $i++) {
  if (isset($permuteDatas[$string[$i]]))
  $string[$i] = $permuteDatas[$string[$i]];
  }

  //if ($iterate > 0)
  //$this->_appliRule($string, $charsListFrom, $charsListTo, $iterate++);


  return $string;
  }

  protected function _generateSalt($encode) {
  // Remove delimiter from salt chars list
  $chars = str_ireplace($this->_saltingOptionDelimiter, '', $this->_saltCharsList);
  $salt = Tools::generateString($this->_saltMinLengh, $chars);
  if ($encode)
  $salt = base64_encode($salt);

  return $salt;
  }

  public function checkHashString($hash, $string, $encodeSalt = true) {
  $hashInfos = explode($this->_saltingOptionDelimiter, $hash);
  $check = $this->hashString($string, false, false, $hashInfos[1], $encodeSalt);

  return ($hash == $check);
  }

  } */
?>
