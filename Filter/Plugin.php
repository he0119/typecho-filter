<?php

/**
 * 首页过滤文章，和其评论
 *
 * @package Filter
 * @author uy_sun
 * @version 0.0.2
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
        # 过滤加密文章
        Typecho_Plugin::factory('Widget_Archive')->handleInit = array('Filter_Plugin', 'archiveFilter');
        // TODO: 过滤最新文章处的加密文章和最近回复处加密文章下的评论
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
    public static function archiveFilter($obj, $select)
    {
        # 获取用户
        $user = Typecho_Widget::widget('Widget_User');
        if (!$user->pass('editor', true)) {
            // 过滤加密文章
            $select = $select->where('table.contents.password IS NULL');
        }
        return $select;
    }
}
