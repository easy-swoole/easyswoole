<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2017/7/7
 * Time: 下午11:36
 */

namespace App\Utility;


class Smarty extends \Smarty
{
    function __construct()
    {
        parent::__construct();
        $this->setCompileDir(ROOT."/Temp/templates_c/");
        $this->setCacheDir(ROOT."/Temp/cache/");
        $this->setTemplateDir(ROOT."/App/Static/Template/");
        $this->setCaching(false);
    }
    function getDisplayString($tpl){
        return $this->fetch($tpl,$cache_id = null, $compile_id = null, $parent = null, $display = false,
            $merge_tpl_vars = true, $no_output_filter = false);
    }
}