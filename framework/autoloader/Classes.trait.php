<?php

namespace framework\autoloader;

trait Classes {

    protected static $_classes = array();

    public static function getClassInfo($class) {
        $classes = self::getClasses();
        if (array_key_exists($class, $classes))
            return $classes[$class];
        return false;
    }

    public static function countGlobalizedClasses() {
        $classes = self::getClasses();
        $number = 0;
        foreach ($classes as &$class) {
            if ($class['isGlobalized'])
                $number++;
        }
        return $number;
    }

    public static function getClasses() {
        return self::$_classes;
    }

    protected static function _setClassInfo($class, $classSourceFilePath, $isCached = false, $isGlobalized = false) {
        if (!is_bool($isCached))
            throw new \Exception('isCached parameter must be a boolean');
        if (!is_bool($isGlobalized))
            throw new \Exception('isGlobalized parameter must be a boolean');
        self::$_classes[$class] = array(
            'sourceFilePath' => $classSourceFilePath,
            'isCached' => $isCached,
            'isGlobalized' => $isGlobalized);
    }

}

?>