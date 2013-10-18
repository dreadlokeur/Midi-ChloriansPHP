<?php

namespace framework\config;

use framework\Config;
use framework\security\Api;
use framework\utility\Tools;
use framework\Security as SecurityClass;

class Security extends Config {

    protected $_filename = null;
    protected $_format = null;

    public function __construct($filename, $format) {
        if (!file_exists($filename))
            throw new \Exception('File "' . $filename . '" don\'t exists');

        // Check format
        if ($format !== Config::XML && $format !== Config::INI)
            throw new \Exception('Invalid config format');

        $this->_filename = $filename;
        $this->_format = $format;
    }

    public function load() {
        switch ($this->_format) {
            case self::XML:
                $xml = @simplexml_load_file($this->_filename);
                if ($xml === null || $xml === false)
                    throw new \Exception('Invalid xml file');
                $apis = $xml->api;
                foreach ($apis as $api) {
                    $apiData = array();
                    if (!isset($api->type))
                        throw new \Exception('Miss api type value');
                    $type = (string) $api->type;


                    $autorun = isset($api->autorun) ? Tools::castValue((string) $api->autorun) : false;
                    switch ($type) {
                        case Api::TYPE_CRYPTION:
                        case Api::TYPE_SNIFFER:
                            $options = array();
                            foreach ((array) $api as $apiOption => $apiValue)
                                $options[$apiOption] = Tools::castValue((string) $apiValue);

                            $apiData = $options;
                            break;
                        case Api::TYPE_FORM:
                            $forms = array();
                            foreach ($api->form as $formData) {
                                $form = new \stdClass();
                                $form->name = Tools::castValue((string) $formData->name);
                                $protections = array();
                                foreach ($formData->protection as $protection) {
                                    $optionType = (string) $protection->type;
                                    $options = array();
                                    foreach ($protection->option as $option) {
                                        if (count($option->value) > 1) {
                                            $values = array();
                                            for ($i = 0; $i < count($option->value); $i++)
                                                $values[] = Tools::castValue((string) $option->value[$i]);
                                            $options[(string) $option->name] = $values;
                                        }
                                        else
                                            $options[(string) $option->name] = Tools::castValue((string) $option->value);
                                    }

                                    $protections[$optionType] = $options;
                                }
                                $form->protections = $protections;
                                $forms[] = $form;
                            }
                            // set api data
                            $apiData = $forms;
                            break;

                        default:
                            throw new \Exception('Invalid api type value');
                            break;
                    }
                    // Add api into api list
                    SecurityClass::registerApi($type, array('autorun' => $autorun, 'datas' => $apiData), true);
                }
                break;
            case self::INI:
                throw new \Exception('NOT YET');
                break;
            default:
                throw new \Exception('Config format must be setted');
                break;
        }
    }

}

?>