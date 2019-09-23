<?php

/**
 * 首页过滤文章和评论
 *
 * @package Filter
 * @author uy_sun
 * @version 0.0.3
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
        Typecho_Plugin::factory('Widget_Abstract_Comments')->filter = array('Filter_Plugin', 'commentsFilter');
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
     * 过滤加密文章
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

    /**
     * 给评论添加 show 属性，加密文章评论的值为 false
     *
     * @access public
     * @return array
     */
    public static function commentsFilter($value, $obj)
    {
        $value['show'] = true;
        # 获取用户
        $user = Typecho_Widget::widget('Widget_User');
        if (!$user->pass('editor', true)) {
            $db = Typecho_Db::get();
            $password = $db->fetchRow($db->select('password')->from('table.contents')->where('table.contents.cid = ?', $value['cid']))['password'];
            // 过滤加密评论
            if ($password) {
                $value['show'] = false;
                return $value;
            }
        }
        return $value;
    }
}
