<?php
#--------------------------------------------------
# See LICENSE for full license information.
#--------------------------------------------------
if (!defined('CMS_VERSION')) exit;

final class TwoFactorSmarty
{
    private static $_module;
    private function __construct() {}

    private static function _get_module()
    {
        if (!self::$_module) self::$_module = \cms_utils::get_module('TwoFactor');
        return self::$_module;
    }

    /**
     * Convert markdown to HTML
     * @param string $input Input Markdown
     * @param string $subtitleElemType HTML Tag name to use for second-level headings
     * @return string HTML output
     */
    public static function mdToHTML($input, $subtitleElemType = 'h2')
    {
        $html = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $input);
        $html = preg_replace('/^## (.+)$/m', '<' . $subtitleElemType . '>$1</' . $subtitleElemType . '>', $html);
        $html = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $html);

        $html = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $html);

        $html = preg_replace('/`(.+?)`/', '<code>$1</code>', $html);

        $html = preg_replace('/^- (.+)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/((?:<li>.*<\/li>\n?)+)/', '<ul>$1</ul>', $html);

        $html = preg_replace('/^\d+\. (.+)$/m', '<li>$1</li>', $html);
        $html = preg_replace('/((?:<li>.*<\/li>\n?)+)/', '<ol>$1</ol>', $html);

        $html = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2">$1</a>', $html);

        $html = preg_replace('/^(?!<[holu]|<li)(.+)$/m', '<p>$1</p>', $html);
        $html = preg_replace('/<p>\s*<\/p>/', '', $html);

        return $html;
    }
}
