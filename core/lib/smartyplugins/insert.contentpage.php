<?php
/**
 * Smarty plugin Content Page
 * @package pollencms
 * @subpackage plugins
 */

function smarty_insert_contentpage($params, &$smarty)
{
        $pathToTakeContent = isset($params['PATH'])?$params['PATH']:"";
        $countChar = isset($params['COUNT'])?$params['COUNT']:"";
        $sortHtml = isset($params['HTML'])?$params['HTML']:"true";
        $endTag = isset($params['END_TAG'])?$params['END_TAG']:" ...";

        $pattern = array('{$MEDIAS_URL}','{$THEME_URL}','{$SITE_PATH}');
        $replace = array(POFile::getPathUrl(MEDIAS_PATH).SLASH,THEME_URL,SITE_PATH);

        $fullpath = PAGES_PATH.SLASH.$pathToTakeContent;
        if (is_file($fullpath)){

                $existFilePath = &getFileObject($fullpath);
                if ($existFilePath->isPublished()){

                        $contentTmp = file_get_contents($fullpath);
                        if ( $sortHtml == "false" ){
                                $content = strip_tags($contentTmp);
                        } else {
                                $content = str_replace($pattern, $replace, $contentTmp);
                        }
                        if ( $countChar != "" ){
                                if (strlen($content) > $countChar) {
                                        $content = substr($content, 0, $countChar);
                                        $last_space = strrpos($content, " ");
                                        $content = substr($content, 0, $last_space).$endTag;
                                }
                        }

                } else {
                        $content = "file not published";
                }

        } else {
                $content = "file not exist";
        }

        return $content;
}
?>