<?php
# See LICENSE for full license information.

final class tf_smarty
{
    private static $_module;
    private function __construct() {}

    private static function _get_module()
    {
        if( !self::$_module ) self::$_module = \cms_utils::get_module('TwoFactor');
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
        // Convert headings (must be done before paragraphs)
        $htmlContent = preg_replace('/^### (.+)$/m', '<h3>$1</h3>', $input);
        $htmlContent = preg_replace('/^## (.+)$/m', '<' . $subtitleElemType . '>$1</' . $subtitleElemType . '>', $htmlContent);
        $htmlContent = preg_replace('/^# (.+)$/m', '<h1>$1</h1>', $htmlContent);
        
        // Convert bold and italic
        $htmlContent = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $htmlContent);
        $htmlContent = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $htmlContent);
        
        // Convert code blocks
        $htmlContent = preg_replace('/`(.+?)`/', '<code>$1</code>', $htmlContent);
        
        // Convert lists
        $htmlContent = preg_replace('/^- (.+)$/m', '<li>$1</li>', $htmlContent);
        $htmlContent = preg_replace('/((?:<li>.*<\/li>\n?)+)/', '<ul>$1</ul>', $htmlContent);
        
        // Convert numbered lists
        $htmlContent = preg_replace('/^\d+\. (.+)$/m', '<li>$1</li>', $htmlContent);
        $htmlContent = preg_replace('/((?:<li>.*<\/li>\n?)+)/', '<ol>$1</ol>', $htmlContent);
        
        // Convert links
        $htmlContent = preg_replace('/\[(.+?)\]\((.+?)\)/', '<a href="$2">$1</a>', $htmlContent);
        
        // Convert paragraphs (must be last)
        $htmlContent = preg_replace('/^(?!<[holu]|<li)(.+)$/m', '<p>$1</p>', $htmlContent);
        
        // Clean up empty paragraphs
        $htmlContent = preg_replace('/<p>\s*<\/p>/', '', $htmlContent);
        
        return $htmlContent;
    }

    
}

#
# EOF
#
?>
