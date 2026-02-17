<?php
#BEGIN_LICENSE
#-------------------------------------------------------------------------
# Module: MAMS (c) 2020-2021 by CMS Made Simple Foundation
#  An add-on module for CMS Made Simple to provide useful functions
#  and commonly used gui capabilities to other modules.
#-------------------------------------------------------------------------
# A fork of:
#
# Module: FrontEndUsers (c) 2008-2014 by Robert Campbell
#         (calguy1000@cmsmadesimple.org)
#
#-------------------------------------------------------------------------
#
# CMSMS - CMS Made Simple is (c) 2006 - 2021 by CMS Made Simple Foundation
# CMSMS - CMS Made Simple is (c) 2005 by Ted Kulp (wishy@cmsmadesimple.org)
# Visit the CMSMS Homepage at: http://www.cmsmadesimple.org
#
#-------------------------------------------------------------------------
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# However, as a special exception to the GPL, this software is distributed
# as an addon module to CMS Made Simple.  You may not use this software
# in any Non GPL version of CMS Made simple, or in any version of CMS
# Made simple that does not indicate clearly and obviously in its admin
# section that the site was built with CMS Made simple.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
# Or read it online: http://www.gnu.org/licenses/licenses.html#GPL
#
#-------------------------------------------------------------------------
#END_LICENSE

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
