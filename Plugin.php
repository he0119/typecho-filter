<?php

/**
 * 首页过滤文章和评论
 *
 * @package Filter
 * @author uy_sun
 * @version 0.1.1
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
            $select = $select->where('table.contents.password is null');
        }
        return $select;
    }

    /**
     * 创建定制的最新文章组件
     *
     * @access public
     * @return array
     */
    public static function recentPosts()
    {
        # 如果插件未激活则使用 Typecho 自带的组件
        $options = Typecho_Widget::widget('Widget_Options');
		if (!isset($options->plugins['activated']['Filter'])) {
			return Typecho_Widget::widget('Widget_Contents_Post_Recent');
		}
        return Typecho_Widget::widget('Widget_Contents_Post_Recent_Filter_Plugin');
    }

    /**
     * 创建定制的最近回复组件
     *
     * @access public
     * @return array
     */
    public static function recentComments()
    {
        # 如果插件未激活则使用 Typecho 自带的组件
        $options = Typecho_Widget::widget('Widget_Options');
        if (!isset($options->plugins['activated']['Filter'])) {
			return Typecho_Widget::widget('Widget_Comments_Recent');
		}
        return Typecho_Widget::widget('Widget_Comments_Recent_Filter_Plugin');
    }
}

class Widget_Comments_Recent_Filter_Plugin extends Widget_Comments_Recent
{
    /**
     * 执行函数
     *
     * @access public
     * @return void
     */
    public function execute()
    {
        $select  = $this->select()->limit($this->parameter->pageSize)
            ->where('table.comments.status = ?', 'approved')
            ->order('table.comments.coid', Typecho_Db::SORT_DESC);

        # 获取用户
        $user = Typecho_Widget::widget('Widget_User');
        if (!$user->pass('editor', true)) {
            # 过滤加密文章的评论
            $select =  $select
                ->join('table.contents', 'table.comments.cid = table.contents.cid', Typecho_Db::LEFT_JOIN)
                ->where('table.contents.password is null');
        }

        if ($this->parameter->parentId) {
            $select->where('cid = ?', $this->parameter->parentId);
        }

        if ($this->options->commentsShowCommentOnly) {
            $select->where('type = ?', 'comment');
        }

        /** 忽略作者评论 */
        if ($this->parameter->ignoreAuthor) {
            $select->where('ownerId <> authorId');
        }

        $this->db->fetchAll($select, array($this, 'push'));
    }
}

class Widget_Contents_Post_Recent_Filter_Plugin extends Widget_Contents_Post_Recent
{
    /**
     * 执行函数
     *
     * @access public
     * @return void
     */
    public function execute()
    {
        $this->parameter->setDefault(array('pageSize' => $this->options->postsListSize));

        $select = $this->select()
            ->where('table.contents.status = ?', 'publish')
            ->where('table.contents.created < ?', $this->options->time)
            ->where('table.contents.type = ?', 'post')
            ->order('table.contents.created', Typecho_Db::SORT_DESC)
            ->limit($this->parameter->pageSize);

        # 获取用户
        $user = Typecho_Widget::widget('Widget_User');
        if (!$user->pass('editor', true)) {
            # 过滤加密文章
            $select =  $select->where('table.contents.password is null');
        }

        $this->db->fetchAll($select, array($this, 'push'));
    }
}
