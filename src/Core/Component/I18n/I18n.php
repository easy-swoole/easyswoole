<?php
/**
 * Created by PhpStorm.
 * User: windrunner414
 * Date: 18-5-9
 * Time: 下午9:59
 */

namespace EasySwoole\Core\Component\I18n;

use EasySwoole\Config;
use EasySwoole\Core\AbstractInterface\Singleton;
use EasySwoole\Core\Component\Trigger;

class I18n
{
    use Singleton;
    private $languageDir,
        $defaultCategory,
        $defaultLanguage,
        $dict = [];

    public function __construct()
    {
        $config = Config::getInstance()->getConf('I18N');
        $this->languageDir = $config['language_dir'];
        $this->defaultCategory = $config['default_category'];
        $this->defaultLanguage = $config['default_language'];
    }

    public function loadLanguage(string $language, string $category, string $key)
    {
        $file = $this->languageDir . '/' . $language . '/' . $category . '.php';
        if (!is_readable($file)) {
            Trigger::throwable(new \Exception('The Language file: ' . $file . ' is not readable'));
            return null;
        }
        $pack = require $file;
        $this->dict[$language][$category] = $pack;
        $text = $pack[$key] ?? null;
        return $text;
    }

    public function translate(string $path, ?array $replace = null, ?string $language = null)
    {
        if (is_null($language)) {
            $language = $this->defaultLanguage;
        }
        $tPath = explode('.', $path);
        switch (count($tPath) <=> 2) {
            case -1:
                $category = $this->defaultCategory;
                $key = $tPath[0];
                break;
            case 0:
                $category = $tPath[0];
                $key = $tPath[1];
                break;
            default:
                Trigger::throwable(new \Exception('The format of path is incorrect'));
                return null;
        }
        $text = $this->dict[$language][$category][$key] ?? $this->loadLanguage($language, $category, $key);
        if (is_null($text)) {
            Trigger::throwable(new \Exception($path . ' not found'));
            return null;
        }
        if (empty($replace)) {
            return $text;
        } else {
            return sprintf($text, ...$replace);
        }
    }
}