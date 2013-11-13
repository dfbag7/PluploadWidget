<?php
/**
 * $Id$
 */

class DimboPluploadWidget extends CWidget
{
    const ASSETS_DIR_NAME             = 'assets';
    const PLUPLOAD_FILE_NAME          = '/plupload.full.min.js';
    const PLUPLOAD_DEBUG_FILE_NAME    = '/plupload.dev.js';
    const MOXIE_DEBUG_FILE_NAME       = '/moxie.js';
    const JQUERY_QUEUE_FILE_NAME      = '/jquery.plupload.queue/jquery.plupload.queue.min.js';
    const JQUERY_QUEUE_DEBUG_FILE_NAME = '/jquery.plupload.queue/jquery.plupload.queue.js';
    const JQUERY_QUEUE_CSS_FILE_NAME  = '/jquery.plupload.queue/css/jquery.plupload.queue.css';
    const JQUERY_UI_FILE_NAME         = '/jquery.ui.plupload/jquery.ui.plupload.min.js';
    const JQUERY_UI_DEBUG_FILE_NAME   = '/jquery.ui.plupload/jquery.ui.plupload.js';
    const JQUERY_UI_CSS_FILE_NAME     = '/jquery.ui.plupload/css/jquery.ui.plupload.css';
    const DEFAULT_RUNTIMES            = 'html5,flash,silverlight,html4';
    const FLASH_FILE_NAME             = '/Moxie.swf';
    const SILVERLIGHT_FILE_NAME       = '/Moxie.xap';


    public $config = array();
    public $model = null;
    public $attribute = null;
    public $type = 'ui'; // 'ui' | 'queue' | 'custom'

    public function init()
    {
        $localPath = dirname(__FILE__) . '/' . self::ASSETS_DIR_NAME;
        $publicPath = Yii::app()->assetManager->publish($localPath);

        if(!isset($this->config['runtimes']))
            $this->config['runtimes'] = self::DEFAULT_RUNTIMES;

        $runtimes = preg_split('/\s*,\s*/', $this->config['runtimes']);

        if(in_array('flash', $runtimes) && !isset($this->config['flash_swf_url']))
            $this->config['flash_swf_url'] = $publicPath . self::FLASH_FILE_NAME;

        if(in_array('silverlight', $runtimes) && !isset($this->config['silverlight_xap_url']))
            $this->config['silverlight_xap_url'] = $publicPath . self::SILVERLIGHT_FILE_NAME;

        if($this->model && $this->attribute)
            $this->config['file_data_name'] = get_class($this->model) . "[$this->attribute]";

        $uniqueId = 'Yii.' . __CLASS__ . '#' . $this->id;
        $jsConfig = CJavaScript::encode($this->config);

        // Register JS
        if($this->type === 'ui')
        {
            Yii::app()->clientScript->registerCoreScript('jquery');
            Yii::app()->clientScript->registerCoreScript('jquery.ui');

            if(YII_DEBUG)
            {
                Yii::app()->clientScript->registerScriptFile($publicPath . self::MOXIE_DEBUG_FILE_NAME);
                Yii::app()->clientScript->registerScriptFile($publicPath . self::PLUPLOAD_DEBUG_FILE_NAME);
                Yii::app()->clientScript->registerScriptFile($publicPath . self::JQUERY_UI_DEBUG_FILE_NAME);
            }
            else
            {
                Yii::app()->clientScript->registerScriptFile($publicPath . self::PLUPLOAD_FILE_NAME);
                Yii::app()->clientScript->registerScriptFile($publicPath . self::JQUERY_UI_FILE_NAME);
            }

            //TODO: add jQuery UI theme css
            Yii::app()->clientScript->registerCssFile($publicPath . self::JQUERY_UI_CSS_FILE_NAME);

            $jQueryScript = stripcslashes("jQuery('#{$this->id}').plupload({$jsConfig});");
            Yii::app()->clientScript->registerScript($uniqueId, $jQueryScript, CClientScript::POS_READY);
        }
        elseif($this->type === 'queue')
        {
            Yii::app()->clientScript->registerCoreScript('jquery');

            if(YII_DEBUG)
            {
                Yii::app()->clientScript->registerScriptFile($publicPath . self::MOXIE_DEBUG_FILE_NAME);
                Yii::app()->clientScript->registerScriptFile($publicPath . self::PLUPLOAD_DEBUG_FILE_NAME);
                Yii::app()->clientScript->registerScriptFile($publicPath . self::JQUERY_QUEUE_DEBUG_FILE_NAME);
            }
            else
            {
                Yii::app()->clientScript->registerScriptFile($publicPath . self::PLUPLOAD_FILE_NAME);
                Yii::app()->clientScript->registerScriptFile($publicPath . self::JQUERY_QUEUE_FILE_NAME);
            }

            Yii::app()->clientScript->registerCssFile($publicPath . self::JQUERY_QUEUE_CSS_FILE_NAME);
            $jQueryScript = stripcslashes("jQuery('#{$this->id}').pluploadQueue({$jsConfig});");
            Yii::app()->clientScript->registerScript($uniqueId, $jQueryScript, CClientScript::POS_READY);
        }
        elseif($this->type === 'custom')
        {
            if(YII_DEBUG)
            {
                Yii::app()->clientScript->registerScriptFile($publicPath . self::MOXIE_DEBUG_FILE_NAME);
                Yii::app()->clientScript->registerScriptFile($publicPath . self::PLUPLOAD_DEBUG_FILE_NAME);
            }
            else
            {
                Yii::app()->clientScript->registerScriptFile($publicPath . self::PLUPLOAD_FILE_NAME);
            }

            $customScript = "var uploader = new plupload.Uploader({$jsConfig});";
            Yii::app()->clientScript->registerScript('plupload_custom_script', $customScript, CClientScript::POS_END);
            Yii::app()->clientScript->registerScript('plupload_custom_script_init', 'uploader.init();', CClientScript::POS_READY);
        }
        else
            throw new CException('Invalid value: ' . $this->type);
    }

    public function run()
    {
        if($this->type !== 'custom')
        {
            $fallBackText = CHtml::tag('p', array(), "Your browser don't have Flash, Silverlight or HTML5 support.");
            echo CHtml::tag('div', array('id' => $this->id), $fallBackText);
        }
    }
}
