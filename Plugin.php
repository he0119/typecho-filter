<?php

namespace TypechoPlugin\Filter;

use Typecho\Db;
use Typecho\Db\Query;
use Typecho\Plugin\Exception;
use Typecho\Plugin\PluginInterface;
use Typecho\Widget;
use Typecho\Widget\Helper\Form;
use Widget\Archive;
use Widget\Comments\Recent as CommentsRecent;
use Widget\Contents\Post\Recent as PostRecent;
use Widget\Options;
use Widget\User;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

/**
 * 首页过滤文章和评论
 *
 * @package Filter
 * @author uy_sun
 * @version 0.1.1
 * @since 1.2.0
 * @link https://github.com/he0119/typecho-filter
 */
class Plugin implements PluginInterface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     *
     * @access public
     * @return void
     * @throws Exception
     */
    public static function activate()
    {
        # 过滤加密文章
        Archive::pluginHandle()->handleInit = __CLASS__ . '::archiveFilter';
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     *
     * @static
     * @access public
     * @return void
     * @throws Exception
     */
    public static function deactivate()
    {
    }

    /**
     * 获取插件配置面板
     *
     * @access public
     * @param Form $form 配置面板
     * @return void
     */
    public static function config(Form $form)
    {
    }

    /**
     * 个人用户的配置面板
     *
     * @access public
     * @param Form $form
     * @return void
     */
    public static function personalConfig(Form $form)
    {
    }

    /**
     * 过滤加密文章
     *
     * @access public
     * @param object $obj
     * @param Query $select
     * @return Query
     */
    public static function archiveFilter($obj, $select)
    {
        $user = User::alloc();
        if (!$user->pass('editor', true)) {
            // 过滤加密文章
            $select = $select->where('table.contents.password IS NULL');
        }
        return $select;
    }

    /**
     * 创建定制的最新文章组件
     *
     * @access public
     * @return Widget
     */
    public static function recentPosts()
    {
        # 如果插件未激活则使用 Typecho 自带的组件
        $options = Options::alloc();
        if (!isset($options->plugins['activated']['Filter'])) {
            return PostRecent::alloc();
        }
        return WidgetContentsPostRecentFilter::alloc();
    }

    /**
     * 创建定制的最近回复组件
     *
     * @access public
     * @return Widget
     */
    public static function recentComments()
    {
        # 如果插件未激活则使用 Typecho 自带的组件
        $options = Options::alloc();
        if (!isset($options->plugins['activated']['Filter'])) {
            return CommentsRecent::alloc();
        }
        return WidgetCommentsRecentFilter::alloc();
    }
}

class WidgetCommentsRecentFilter extends CommentsRecent
{
    /**
     * 执行函数
     *
     * @access public
     * @return void
     */
    public function execute()
    {
        $select = $this->select()->limit($this->parameter->pageSize)
            ->where('table.comments.status = ?', 'approved')
            ->order('table.comments.coid', Db::SORT_DESC);

        if (!$this->user->pass('editor', true)) {
            # 过滤加密文章的评论
            $select = $select
                ->join('table.contents', 'table.comments.cid = table.contents.cid', Db::LEFT_JOIN)
                ->where('table.contents.password IS NULL');
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

        $this->db->fetchAll($select, [$this, 'push']);
    }
}

class WidgetContentsPostRecentFilter extends PostRecent
{
    /**
     * 执行函数
     *
     * @access public
     * @return void
     */
    public function execute()
    {
        $this->parameter->setDefault(['pageSize' => $this->options->postsListSize]);

        $select = $this->select()
            ->where('table.contents.status = ?', 'publish')
            ->where('table.contents.created < ?', $this->options->time)
            ->where('table.contents.type = ?', 'post')
            ->order('table.contents.created', Db::SORT_DESC)
            ->limit($this->parameter->pageSize);

        # 获取用户
        if (!$this->user->pass('editor', true)) {
            # 过滤加密文章
            $select = $select->where('table.contents.password IS NULL');
        }

        $this->db->fetchAll($select, [$this, 'push']);
    }
}
