<?php
/**
 * Created by PhpStorm.
 * User: windrunner414
 * Date: 18-5-9
 * Time: 下午9:59
 */

namespace EasySwoole\Core\Component\I18n;

use EasySwoole\Config;
use EasySwoole\Core\Component\Invoker;
use EasySwoole\Core\Component\Spl\SplArray;
use EasySwoole\Core\Component\Trigger;

class I18n
{
    private $languageDir,
        $defaultCategory,
        $defaultLanguage,
        $loaded = false,
        $lang;
    private static $instance;

    public function __construct()
    {
        $config = Config::getInstance()->getConf('I18N');
        $this->languageDir = $config['language_dir'];
        if (substr($this->languageDir, -1) != '/') {
            $this->languageDir .= '/';
        }
        $this->defaultCategory = $config['default_category'];
        $this->defaultLanguage = $config['default_language'];
        $this->lang = new SplArray();
    }

    public function load() : bool
    {
        if ($this->loaded) {
            return true;
        }
        $iterator = new \RecursiveDirectoryIterator($this->languageDir);
        $files = new \RecursiveIteratorIterator($iterator);
        foreach ($files as $file) {
            $extension = pathinfo($file, PATHINFO_EXTENSION);
            if ($extension == '') {
                continue;
            }
            $path = str_replace([$this->languageDir, '.' . $extension], '', $file);
            [$language, $category] = explode('/', $path, 2);
            $category = str_replace('/', '.', $category);
            $parser = __NAMESPACE__ . '\Parser\\' . ucfirst($extension) . 'Parser';
            $call = [$parser, 'parse'];
            if (is_callable($call)) {
                $lang = Invoker::callUserFunc($call, $file);
            } else {
                Trigger::error($parser . ' not found');
                continue;
            }
            if ($lang === null) {
                Trigger::error($file . ' parse error');
            } else {
                $this->lang->set($language . '.' . $category, $lang);
            }
        }
        $this->loaded = true;
        return true;
    }

    public function getRealKey(string $key, ?string $locale) : string
    {
        if ($locale === null) {
            $locale = $this->defaultLanguage;
        }
        if (strpos($key, '.') === false) {
            $key = implode([$this->defaultCategory, $key], '.');
        }
        return implode('.', [$locale, $key]);
    }

    public function format(string $text, array $params) : string
    {
        $params = array_values($params);
        return sprintf($text, ...$params);
    }

    public static function translate(string $key, ?array $params = null, ?string $locale = null) : ?string
    {
        if(!isset(self::$instance)){
            self::$instance = new static();
        }
        $instance = self::$instance;
        if (!$instance->loaded) {
            $instance->load();
        }
        $realKey = $instance->getRealKey($key, $locale);
        $text = $instance->lang->get($realKey);
        if ($text === null) {
            Trigger::error($realKey . ' not found');
            return null;
        } else {
            return empty($params) ? $text : $instance->format($text, $params);
        }
    }
}