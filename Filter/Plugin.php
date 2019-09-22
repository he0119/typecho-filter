<?php

/**
 * 首页过滤指定文章，评论
 *
 * @package Filter
 * @author uy_sun
 * @version 0.0.1
 * @link https://hehome.xyz/
 */
class Filter_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Archive')->indexHandle = array('Filter_Plugin', 'filter');
        return _t('插件已激活，现在可以对插件进行设置！');
    }
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    { }
    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    { }
    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    { }

    /**
     * 插件实现方法
     *
     * @access public
     * @return void
     */

    /**
     * 插件实现方法
     *
     * @access public
     * @return void
     */
    public static function filter($obj, $select)
    {
        return $select;
    }
}
